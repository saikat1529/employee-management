<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Documents' }} — Demo Portal</title>
    <style>
        :root {
            --bg: #FDFDFC;
            --card: #ffffff;
            --text: #1b1b18;
            --muted: #706f6c;
            --line: #e3e3e0;
            --accent: #1b1b18;
            --error: #b42318;
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --bg: #0a0a0a;
                --card: #161615;
                --text: #EDEDEC;
                --muted: #A1A09A;
                --line: #3E3E3A;
                --accent: #EDEDEC;
                --error: #f97066;
            }
        }
        :root[data-theme="dark"] {
            --bg: #0a0a0a;
            --card: #161615;
            --text: #EDEDEC;
            --muted: #A1A09A;
            --line: #3E3E3A;
            --accent: #EDEDEC;
            --error: #f97066;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 32px 28px;
        }
        h1 { font-size: 20px; font-weight: 600; margin: 0 0 6px; }
        p { margin: 0 0 14px; color: var(--muted); }
        label { display: block; font-weight: 500; margin-bottom: 6px; color: var(--text); }
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--bg);
            color: var(--text);
            font: inherit;
            margin-bottom: 14px;
        }
        button, .button {
            display: inline-block;
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--accent);
            border-radius: 4px;
            background: var(--accent);
            color: var(--bg);
            font: inherit;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .secondary { background: transparent; color: var(--text); border-color: var(--line); }
        .error { color: var(--error); font-size: 14px; margin: -6px 0 14px; }
        ul.docs { list-style: none; padding: 0; margin: 0 0 18px; }
        ul.docs li { border: 1px solid var(--line); border-radius: 4px; margin-bottom: 10px; }
        ul.docs a { display: block; padding: 12px 14px; color: var(--text); text-decoration: none; font-weight: 500; }
        ul.docs a:hover { border-color: var(--accent); }
        form.inline { margin: 0; }
        @media (max-width: 480px) {
            .card { padding: 24px 18px; }
        }
    </style>
</head>
<body>
    <main class="card">
        @yield('content')
    </main>
</body>
</html>
