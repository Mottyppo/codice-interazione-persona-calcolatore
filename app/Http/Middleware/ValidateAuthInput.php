<?php

namespace App\Http\Middleware;

use App\Actions\Fortify\CreateNewUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class ValidateAuthInput
{
    public function handle(Request $request, Closure $next): Response
    {
        // Registration canonicalizes the username before invoking our action.
        if ($request->routeIs('register.store') &&
            $request->has(Fortify::username()) &&
            ! is_string($request->input(Fortify::username()))) {
            app(CreateNewUser::class)->validateInput($request->all());
        }

        if ($request->routeIs('password.update')) {
            $email = $request->input(Fortify::email());
            if (config('fortify.lowercase_usernames') && is_string($email)) {
                $request->merge([Fortify::email() => Str::lower(Str::trim($email))]);
            }

            // The vendor broker hashes the reset token before the password action.
            $request->validate([
                'token' => ['bail', 'required', 'string'],
                Fortify::email() => ['bail', 'required', 'string', 'email'],
                'password' => ['required', 'string'],
                'password_confirmation' => ['required', 'string'],
            ]);
        }

        if ($request->routeIs('password.confirm.store')) {
            $request->validate(['password' => ['required', 'string']]);
        }

        return $next($request);
    }
}
