<?php

namespace App\Providers;

use App\Actions\Fortify\ConfirmPassword;
use App\Actions\Fortify\ConfirmTwoFactorAuthentication;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Middleware\ValidateAuthInput;
use App\Livewire\Profile\DeleteUserForm;
use App\Livewire\Profile\LogoutOtherBrowserSessionsForm;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Laravel\Fortify\Actions\ConfirmPassword::class, ConfirmPassword::class);
        $this->app->bind(\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication::class, ConfirmTwoFactorAuthentication::class);

        // Run before Fortify passes request values to string-only vendor APIs.
        config(['fortify.middleware' => array_values(array_unique([
            ...config('fortify.middleware', ['web']),
            ValidateAuthInput::class,
        ]))]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('profile.delete-user-form', DeleteUserForm::class);
        Livewire::component('profile.logout-other-browser-sessions-form', LogoutOtherBrowserSessionsForm::class);

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $username = $request->input(Fortify::username());
            $throttleKey = Str::transliterate(Str::lower(is_string($username) ? $username : '').'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey)->response(
                fn (Request $request, array $headers) => $this->throttledAuthResponse($request, $headers, Fortify::username())
            );
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'))->response(
                fn (Request $request, array $headers) => $this->throttledAuthResponse(
                    $request, $headers, $request->filled('recovery_code') ? 'recovery_code' : 'code'
                )
            );
        });
    }

    private function throttledAuthResponse(Request $request, array $headers, string $field)
    {
        $message = __('auth.throttle', ['seconds' => $headers['Retry-After'] ?? 60]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 429, $headers);
        }

        return redirect()->back()
            ->withInput($request->only(Fortify::username()))
            ->withErrors([$field => $message])
            ->withHeaders($headers);
    }
}
