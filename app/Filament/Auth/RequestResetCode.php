<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\URL;

// Step 1 of "forgot password". No email is sent; we just move on to the code step.
class RequestResetCode extends RequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();

        session()->flash('password_reset_status', 'Email sent');

        // Filament's reset page only opens with a signed link.
        $this->redirect(URL::signedRoute('filament.admin.auth.password-reset.reset', [
            'email' => $data['email'],
        ]));
    }
}
