<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Actions\ConfirmPassword as FortifyConfirmPassword;

class ConfirmPassword extends FortifyConfirmPassword
{
    public function __invoke(StatefulGuard $guard, $user, $password = null)
    {
        // Livewire state can contain arrays; the vendor action expects a string.
        if (! is_string($password)) {
            return false;
        }

        return parent::__invoke($guard, $user, $password);
    }
}
