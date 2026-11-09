<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication as FortifyConfirmTwoFactorAuthentication;

class ConfirmTwoFactorAuthentication extends FortifyConfirmTwoFactorAuthentication
{
    public function __invoke($user, $code)
    {
        if (! is_string($code)) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two factor authentication code was invalid.')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        parent::__invoke($user, $code);
    }
}
