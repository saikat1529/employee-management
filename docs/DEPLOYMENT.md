# Demo Portal: Server Setup Guide

This guide is for the owner. It explains, step by step, how to set up the Demo Portal on the server, and how automatic deploys work after that.

Words in `ANGLE BRACKETS` or the example values below (like `demo.example.com` and `/var/www/demo-portal`) are placeholders. Replace them with the real values.

---

## Before you start: fill in these values

Write these down before you begin. Ask the owner if you do not know them.

| Item | Your value | Example |
|---|---|---|
| Web address (subdomain) for the portal | ______________ | `demo.example.com` |
| Folder on the server | ______________ | `/var/www/demo-portal` |
| Server login name (the SSH user) | ______________ | `deploy` |
| Server address (IP or host name) | ______________ | `203.0.113.10` |

The server already runs 3 other apps. Pick a subdomain (recommended) or a port that none of them use. Do not change the other 3 apps.

---

## Step 1. Choose a folder and an address

1. Choose a folder that does not exist yet, for example `/var/www/demo-portal`.
2. Choose a subdomain, for example `demo.example.com`. Create a DNS record for it that points to the server address.
3. Check that no other app uses this subdomain. Look at the Nginx config files in `/etc/nginx/sites-enabled/`.

---

## Step 2. Check PHP 8.4 and Composer

The portal needs **PHP 8.4**. Log in to the server and run:

```sh
php -v
composer -V
```

If `php -v` shows a different version, the server may have PHP 8.4 under another name, such as `php8.4`. Check with `ls /usr/bin/php*`. Ask the owner or your hosting help before you change the default PHP version, because the other 3 apps may depend on it.

Check the PHP extensions the portal needs:

```sh
php -m | grep -Ei 'sqlite3|pdo_sqlite|mbstring|xml|curl|zip|intl|bcmath|exif'
```

If one is missing, install it. On Ubuntu with PHP 8.4 it looks like this (the names may differ on your server):

```sh
sudo apt install php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-intl php8.4-bcmath
```

If Composer is missing, install it from https://getcomposer.org. Git must also be installed (`git --version`).

---

## Step 3. Download the code

You can use HTTPS (simpler) or a read-only deploy key (more secure, recommended).

**Option A, HTTPS (simplest):**

```sh
sudo mkdir -p /var/www/demo-portal
sudo chown -R $USER:$USER /var/www/demo-portal
git clone https://github.com/saikat1529/employee-management.git /var/www/demo-portal
```

**Option B, read-only deploy key:** create a key on the server with `ssh-keygen -t ed25519 -f ~/.ssh/demo_portal_deploy`, add the public key (the `.pub` file) in GitHub under the repository's **Settings > Deploy keys** (leave "Allow write access" unticked), then clone with the SSH address `git@github.com:saikat1529/employee-management.git`.

Check that the folder now holds the code: `ls /var/www/demo-portal` should show `artisan`, `app`, `public`.

---

## Step 4. Create the server settings file (.env)

The `.env` file holds the secret settings for this server. **Never put it in Git and never share it.**

1. Go to the folder and copy the example file:

   ```sh
   cd /var/www/demo-portal
   cp .env.example .env
   ```

2. Open `.env` in a text editor (for example `nano .env`) and set these values:

   ```
   APP_NAME="Demo Portal"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://demo.example.com
   DB_CONNECTION=sqlite
   DB_DATABASE=/var/www/demo-portal/database/database.sqlite
   ```

   - `APP_ENV=production` and `APP_DEBUG=false` hide error details from visitors. Do not change them to `true`.
   - `DB_DATABASE` must be the full path to the database file (it must start with `/`).
   - `APP_URL` must match the web address from Step 1.

3. Generate the secret key **once**:

   ```sh
   php artisan key:generate
   ```

   This fills in `APP_KEY=` in `.env`. Do not generate a new key later. A new key makes existing logins and encrypted data stop working.

**Secret values (never commit, never share):**

- `APP_KEY`: the app's secret key.
- `DB_PASSWORD` and mail passwords (if you add them).
- The whole `.env` file.

The `.env` file is already listed in `.gitignore`, so Git ignores it. The GitHub deploy never overwrites it.

---

## Step 5. Create the database file and set folder permissions

SQLite stores the data in one file. Create it and give the web server permission to write to it.

```sh
cd /var/www/demo-portal
touch database/database.sqlite

# Install the PHP packages (no developer tools needed on the server)
composer install --no-dev --optimize-autoloader --no-interaction

# Give the web server user (www-data on Ubuntu) write access to these folders
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R ug+rwX storage bootstrap/cache database
```

Important: the **folder** `database` must also be writable, not just the file. SQLite creates small helper files next to the database file.

---

## Step 6. Set up Nginx and PHP-FPM

The web address must point to the `public` folder inside the project, never to the project folder itself.

1. Create a new Nginx file, for example `/etc/nginx/sites-available/demo-portal`:

   ```nginx
   server {
       listen 80;
       server_name demo.example.com;
       root /var/www/demo-portal/public;

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           # Use the PHP 8.4 socket. Check the real name with: ls /var/run/php/
           fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

2. Turn it on and check the settings:

   ```sh
   sudo ln -s /etc/nginx/sites-available/demo-portal /etc/nginx/sites-enabled/demo-portal
   sudo nginx -t
   sudo systemctl reload nginx
   ```

   If `nginx -t` shows an error, do not reload. Fix the error first.

3. Add an HTTPS certificate for `demo.example.com` if the server already uses HTTPS for the other apps (for example with Certbot). Set `APP_URL` in `.env` to `https://` as in Step 4.

---

## Step 7. Run the first setup steps by hand, then check the site

Run these once, in this order, from the project folder:

```sh
cd /var/www/demo-portal
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

What the first migrate does: it creates all the tables. It also inserts the **demo data once** (the admin user, 5 departments, 15 employees, 30 days of attendance, and the payroll rules). It never runs again on later deploys.

Check the site:

1. Open `https://demo.example.com/admin/login` in a browser. The login page should load.
2. Log in with `admin@demo.com` and the demo password. **Change this password on the first login** (the demo password is public).

If the page shows an error, look at `storage/logs/laravel.log` on the server.

---

## Step 8. Add the GitHub secrets (so automatic deploys work)

The deploy only runs when all 4 secrets exist. Until then, the deploy job prints a message and skips safely.

1. Open the repository on GitHub: `https://github.com/saikat1529/employee-management`.
2. Click **Settings** (top menu of the repository).
3. In the left menu, click **Secrets and variables**, then **Actions**.
4. Click **New repository secret** and add each of these 4 secrets, one at a time:

   | Name | Value to paste |
   |---|---|
   | `SSH_HOST` | The server address, for example `203.0.113.10` |
   | `SSH_USER` | The server login name, for example `deploy` |
   | `SSH_PRIVATE_KEY` | The full private key text, including the `-----BEGIN` and `-----END` lines. Use a key made just for deploys. |
   | `DEPLOY_PATH` | The folder on the server, for example `/var/www/demo-portal` |

5. Click **Add secret** after each one.

The server must also accept that key. Add the matching public key (the `.pub` text) to `~/.ssh/authorized_keys` for the SSH user. The SSH user must be able to run `git pull` in the folder and `composer` and `php` from the command line.

Secret values are hidden in GitHub after you save them. You cannot read them again, only replace them.

---

## Step 9. Check a deploy, and roll back if needed

**Check a deploy:**

1. Open the repository on GitHub and click the **Actions** tab.
2. Click the newest run named **CI and Deploy**.
3. A green check means it worked. A red cross means it failed. Click the job (`test` or `deploy`) and then the failed step to see the message.
4. If the deploy job says "Deploy skipped", the 4 secrets are missing (see Step 8). This is normal until they are added.

**Roll back (go back to an older version of the code):**

Do this on the server. Replace `ABC1234` with the short code of the good version (from the GitHub Actions or `git log --oneline` on the server).

```sh
cd /var/www/demo-portal
git log --oneline -5                 # find the good version
git reset --hard ABC1234             # go back to it
composer install --no-dev --optimize-autoloader --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

Do **not** run `migrate:rollback` unless you know what it deletes. The demo data migration keeps its data on rollback on purpose.

Note: a rollback changes the code only. After a rollback, the next push to `main` deploys the newest code again.

---

## Step 10. Warnings (please read)

- **Never commit `.env`.** It holds the secret key and settings. Git ignores it, so keep it that way. Do not paste it into chats, emails or tickets.
- **Never commit or share the database file** (`database/database.sqlite`). It holds the employee and salary data. Git ignores it too.
- **Keep a database backup.** Copy `database/database.sqlite` to a safe place regularly, for example once a day, before risky changes. To back it up safely, copy it while no one is saving changes, or use the command `sqlite3 database/database.sqlite ".backup /root/demo-backup.sqlite"`.
- **The 3 existing apps must not be changed.** Do not edit their Nginx files, folders, databases or PHP settings. Only add the new Nginx file for this portal. Test with `sudo nginx -t` before every reload.
- **Demo data is for demonstration.** The admin password `password` is public. Change it on the first login, and do not put real employee data in this portal until it is reviewed.
