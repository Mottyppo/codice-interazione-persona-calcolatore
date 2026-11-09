<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm as JetstreamDeleteUserForm;

class DeleteUserForm extends JetstreamDeleteUserForm
{
    public function deleteUser(Request $request, DeletesUsers $deleter, StatefulGuard $auth)
    {
        $this->resetErrorBag();
        Validator::make(['password' => $this->password], [
            'password' => ['required', 'string'],
        ])->validate();

        return parent::deleteUser($request, $deleter, $auth);
    }
}
