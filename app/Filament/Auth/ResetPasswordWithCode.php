<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

// Step 2 of "forgot password". Accepts the fixed demo code and saves the new password.
class ResetPasswordWithCode extends ResetPassword
{
    public const RESET_CODE = '222222';

    public ?string $code = '';

    public function mount(?string $email = null, ?string $token = null): void
    {
        $this->form->fill([
            'email' => $email ?? request()->query('email'),
        ]);
    }

    public function getSubheading(): ?string
    {
        return session('password_reset_status');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),
            TextInput::make('code')
                ->label('Reset code')
                ->required(),
            TextInput::make('password')
                ->label('New password')
                ->password()
                ->required()
                ->same('passwordConfirmation'),
            TextInput::make('passwordConfirmation')
                ->label('Confirm new password')
                ->password()
                ->required()
                ->dehydrated(false),
        ]);
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        $data = $this->form->getState();

        if ($data['code'] !== self::RESET_CODE) {
            Notification::make()->title('Invalid reset code')->danger()->send();

            return null;
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            Notification::make()->title('No account found with this email')->danger()->send();

            return null;
        }

        // The User model casts "password" to a hashed value on save.
        $user->password = $data['password'];
        $user->save();

        return app(PasswordResetResponse::class);
    }
}
