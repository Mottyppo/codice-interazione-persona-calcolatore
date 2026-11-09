<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Laravel\Jetstream\Http\Livewire\UpdatePasswordForm;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_uses_italian_and_all_auth_pages_are_translated(): void
    {
        $this->assertSame('it', app()->getLocale());
        $this->get('/login')->assertOk()->assertSee('Ricordami')->assertDontSee('Remember me');
        $this->get('/register')->assertOk()->assertSee('Conferma password')->assertSee('Registrati');
        $this->get('/forgot-password')->assertOk()->assertSee('Invia il link di recupero');
        $this->get('/reset-password/token?email=test@example.com')->assertOk()->assertSee('Reimposta password');
        $this->actingAs(User::factory()->create());
        $this->get('/user/confirm-password')->assertOk()->assertSee('Conferma');
        $this->get('/user/profile')->assertOk()->assertSee('Informazioni del profilo')->assertSee('Sessioni del browser')->assertSee('Elimina account');
    }

    public function test_login_reports_missing_and_incorrect_credentials_in_italian(): void
    {
        $this->post('/login', [])->assertSessionHasErrors([
            'email' => 'Il campo email è obbligatorio.',
            'password' => 'Il campo password è obbligatorio.',
        ]);
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'Le credenziali inserite non sono corrette.']);
    }

    public function test_registration_reports_every_missing_field_and_confirmation(): void
    {
        $this->post('/register', [])->assertSessionHasErrors([
            'name' => 'Il campo nome è obbligatorio.',
            'email' => 'Il campo email è obbligatorio.',
            'password' => 'Il campo password è obbligatorio.',
            'password_confirmation' => 'Il campo conferma password è obbligatorio.',
        ]);
        $this->post('/register', ['name' => 'Anna', 'email' => 'invalid', 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertSessionHasErrors(['email' => 'Il campo email deve contenere un indirizzo email valido.', 'password' => 'Il campo password deve contenere almeno 8 caratteri.']);
    }

    public function test_password_recovery_and_reset_messages_are_italian(): void
    {
        $this->post('/forgot-password', [])->assertSessionHasErrors(['email' => 'Il campo email è obbligatorio.']);
        $this->post('/forgot-password', ['email' => 'not-an-email'])->assertSessionHasErrors(['email' => 'Il campo email deve contenere un indirizzo email valido.']);
        $this->post('/forgot-password', ['email' => 'missing@example.com'])->assertSessionHasErrors(['email' => 'Non esiste un account con questo indirizzo email.']);
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status', 'Ti abbiamo inviato il link per reimpostare la password.');
        $this->post('/reset-password', [])->assertSessionHasErrors(['email', 'password', 'token']);
        $this->post('/reset-password', ['email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password', 'token' => 'invalid'])
            ->assertSessionHasErrors(['email' => 'Il link per reimpostare la password non è valido o è scaduto.']);
        $token = Password::broker()->createToken($user);
        $this->post('/reset-password', ['email' => $user->email, 'password' => 'new-password', 'token' => $token])
            ->assertSessionHasErrors(['password_confirmation' => 'Il campo conferma password è obbligatorio.']);
        $this->post('/reset-password', ['email' => $user->email, 'password' => 'new-password', 'password_confirmation' => 'new-password', 'token' => $token])
            ->assertSessionHas('status', 'La password è stata reimpostata.');
    }

    public function test_password_reset_email_is_entirely_italian(): void
    {
        $user = User::factory()->create();
        $mail = (new ResetPassword('test-token'))->toMail($user);

        $this->assertSame('Reimpostazione della password', $mail->subject);
        $this->assertSame('Reimposta password', $mail->actionText);
        $this->assertStringContainsString('richiesta la reimpostazione', implode(' ', $mail->introLines));
        $this->assertStringContainsString('ignora questa email', implode(' ', $mail->outroLines));
    }

    public function test_profile_livewire_trims_whitespace_and_reports_all_fields(): void
    {
        $this->actingAs($user = User::factory()->create());
        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state.name', '   ')->set('state.email', 'invalid')->call('updateProfileInformation')
            ->assertHasErrors(['name' => 'required', 'email' => 'email'])
            ->assertSee('Il campo nome è obbligatorio.')->assertSee('Il campo email deve contenere un indirizzo email valido.');
        $this->assertSame($user->name, $user->fresh()->name);
        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state.name', '  Anna Rossi  ')->set('state.email', '  ANNA@example.com  ')->call('updateProfileInformation')->assertHasNoErrors();
        $this->assertSame('Anna Rossi', $user->fresh()->name);
        $this->assertSame('anna@example.com', $user->fresh()->email);
    }

    public function test_registration_action_handles_whitespace_and_invalid_types_without_server_errors(): void
    {
        foreach ([['name' => '   ', 'email' => []], ['name' => [], 'email' => ['bad']]] as $invalid) {
            try {
                app(CreateNewUser::class)->create($invalid + ['password' => 'password', 'password_confirmation' => 'password']);
                $this->fail('Invalid input was accepted.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('name', $e->errors());
                $this->assertArrayHasKey('email', $e->errors());
            }
        }
    }

    public function test_named_error_bags_are_preserved_in_profile_and_password_actions(): void
    {
        $this->actingAs($user = User::factory()->create());
        foreach ([[UpdateUserProfileInformation::class, 'updateProfileInformation'], [UpdateUserPassword::class, 'updatePassword']] as [$action, $bag]) {
            try {
                app($action)->update($user, []);
                $this->fail('Invalid input was accepted.');
            } catch (ValidationException $e) {
                $this->assertSame($bag, $e->errorBag);
                $this->assertStringContainsString('obbligatorio', implode(' ', array_merge(...array_values($e->errors()))));
            }
        }
    }

    public function test_password_livewire_reports_current_password_and_confirmation(): void
    {
        $this->actingAs($user = User::factory()->create());
        Livewire::test(UpdatePasswordForm::class)->set('state', ['current_password' => '', 'password' => 'new-password', 'password_confirmation' => ''])
            ->call('updatePassword')->assertHasErrors(['current_password', 'password_confirmation'])
            ->assertSee('Il campo password attuale è obbligatorio.')->assertSee('Il campo conferma password è obbligatorio.');
        Livewire::test(UpdatePasswordForm::class)->set('state', ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'different'])
            ->call('updatePassword')->assertHasErrors(['current_password', 'password'])
            ->assertSee('La password inserita non corrisponde alla password attuale.')->assertSee('La conferma del campo password non corrisponde.');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_confirmation_two_factor_and_destructive_actions_show_italian_errors(): void
    {
        $this->actingAs($user = User::factory()->create());
        $this->post('/user/confirm-password', ['password' => 'wrong'])->assertSessionHasErrors(['password' => 'La password inserita non è corretta.']);
        Livewire::test(DeleteUserForm::class)->set('password', 'wrong')->call('deleteUser')->assertHasErrors(['password'])->assertSee('La password inserita non è corretta.');
        config(['session.driver' => 'database']);
        Livewire::test(LogoutOtherBrowserSessionsForm::class)->call('confirmLogout')->set('password', 'wrong')->call('logoutOtherBrowserSessions')->assertHasErrors(['password'])->assertSee('La password inserita non è corretta.');
        $this->withSession(['auth.password_confirmed_at' => time()]);
        Livewire::test(TwoFactorAuthenticationForm::class)->call('enableTwoFactorAuthentication')->set('code', 'invalid')->call('confirmTwoFactorAuthentication')
            ->assertHasErrors(['code'])->assertSee('Il codice di autenticazione a due fattori non è valido.');
        $this->assertNotNull($user->fresh());
    }

    public function test_malformed_auth_requests_return_field_errors_instead_of_server_errors(): void
    {
        $this->post('/login', ['email' => ['invalid'], 'password' => ['invalid']])
            ->assertSessionHasErrors(['email' => 'Il campo email deve essere un testo.', 'password' => 'Il campo password deve essere un testo.']);
        $this->post('/register', ['name' => [], 'email' => ['invalid'], 'password' => []])
            ->assertSessionHasErrors(['name', 'email', 'password', 'password_confirmation']);
        $this->post('/reset-password', ['email' => 'test@example.com', 'token' => ['bad'], 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors(['token' => 'Il campo codice di recupero deve essere un testo.']);
        $this->actingAs(User::factory()->create());
        $this->post('/user/confirm-password', ['password' => ['bad']])
            ->assertSessionHasErrors(['password' => 'Il campo password deve essere un testo.']);
        $this->post('/user/confirm-password', [])
            ->assertSessionHasErrors(['password' => 'Il campo password è obbligatorio.']);
    }

    public static function malformedFormValues(): iterable
    {
        yield 'login' => ['/login', '/login', ['email' => ['bad'], 'password' => 'password'], 'email'];
        yield 'register' => ['/register', '/register', ['name' => ['bad'], 'email' => ['bad'], 'password' => 'password', 'password_confirmation' => 'password'], 'name'];
        yield 'forgot password' => ['/forgot-password', '/forgot-password', ['email' => ['bad']], 'email'];
        yield 'reset password' => ['/reset-password/test-token', '/reset-password', ['token' => 'test-token', 'email' => ['bad'], 'password' => 'password', 'password_confirmation' => 'password'], 'email'];
    }

    #[DataProvider('malformedFormValues')]
    public function test_malformed_values_still_render_feedback_after_redirect(string $form, string $action, array $input, string $field): void
    {
        $response = $this->from($form)->post($action, $input);
        $messages = session('errors')['default']['messages'];
        $this->assertArrayHasKey($field, $messages);
        $response->assertRedirect($form);
        $this->get($form)->assertOk()->assertSee($messages[$field][0]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_reset_form_ignores_non_scalar_email_in_query(): void
    {
        $this->get('/reset-password/test-token?email[]=bad')->assertOk()
            ->assertSee('Reimposta password');
    }

    public function test_profile_password_does_not_hash_check_malformed_current_password(): void
    {
        $this->actingAs($user = User::factory()->create());
        Livewire::test(UpdatePasswordForm::class)
            ->set('state', ['current_password' => ['bad'], 'password' => 'password', 'password_confirmation' => 'password'])
            ->call('updatePassword')->assertHasErrors(['current_password'])
            ->assertSee('Il campo password attuale deve essere un testo.');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_two_factor_challenge_feedback_is_italian_for_both_methods(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-secret'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->withSession(['login.id' => $user->id])->get('/two-factor-challenge')->assertOk()->assertSee('Usa un codice di recupero');
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => 'invalid'])
            ->assertSessionHasErrors(['code' => 'Il codice di autenticazione a due fattori non è valido.']);
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['recovery_code' => 'invalid'])
            ->assertSessionHasErrors(['recovery_code' => 'Il codice di recupero non è valido.']);
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => ['invalid']])
            ->assertSessionHasErrors(['code' => 'Il campo codice di autenticazione deve essere un testo.']);
        $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['recovery_code' => 'recovery-secret'])
            ->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_duplicate_email_and_length_limits_remain_localized(): void
    {
        $existing = User::factory()->create(['email' => 'anna@example.com']);
        $this->post('/register', ['name' => str_repeat('a', 256), 'email' => 'ANNA@example.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors(['name' => 'Il campo nome può contenere al massimo 255 caratteri.', 'email' => 'Il valore del campo email è già in uso.']);
        $this->actingAs($user = User::factory()->create());
        Livewire::test(UpdateProfileInformationForm::class)->set('state.name', str_repeat('a', 256))->set('state.email', $existing->email)
            ->call('updateProfileInformation')->assertHasErrors(['name' => 'max', 'email' => 'unique'])
            ->assertSee('Il valore del campo email è già in uso.');
        Livewire::test(UpdateProfileInformationForm::class)->set('state.email', ['bad'])->call('updateProfileInformation')
            ->assertHasErrors(['email' => 'string'])->assertSee('Il campo email deve essere un testo.');
    }

    public function test_livewire_password_confirmation_modal_reports_italian_feedback(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(TwoFactorAuthenticationForm::class)->call('startConfirmingPassword', 'enable')
            ->set('confirmablePassword', 'wrong')->call('confirmPassword')
            ->assertHasErrors(['confirmable_password'])->assertSee('La password inserita non è corretta.');
    }

    public function test_translation_catalogue_covers_installed_framework_rules_and_auth_views(): void
    {
        $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
        $italian = require lang_path('it/validation.php');
        $this->assertSame([], array_diff_key($english, $italian));
        foreach ($english as $key => $value) {
            if (is_array($value) && ! in_array($key, ['custom', 'attributes'])) {
                $this->assertSame([], array_diff_key($value, $italian[$key]), $key);
            }
        }
        $catalogue = json_decode(file_get_contents(lang_path('it.json')), true, flags: JSON_THROW_ON_ERROR);
        $views = array_merge(glob(resource_path('views/auth/*.blade.php')), glob(resource_path('views/profile/*.blade.php')), [resource_path('views/components/confirms-password.blade.php')]);
        foreach ($views as $view) {
            preg_match_all("/__\\('((?:\\\\'|[^'])*)'/", file_get_contents($view), $matches);
            foreach ($matches[1] as $key) {
                $this->assertArrayHasKey(str_replace("\\'", "'", $key), $catalogue, $view);
            }
        }
    }

    public function test_livewire_sensitive_actions_reject_non_string_passwords_and_codes(): void
    {
        $this->actingAs($user = User::factory()->create());
        Livewire::test('profile.delete-user-form')->set('password', ['bad'])->call('deleteUser')
            ->assertHasErrors(['password'])->assertSee('Il campo password deve essere un testo.');
        config(['session.driver' => 'database']);
        Livewire::test('profile.logout-other-browser-sessions-form')->call('confirmLogout')->set('password', ['bad'])->call('logoutOtherBrowserSessions')
            ->assertHasErrors(['password'])->assertSee('Il campo password deve essere un testo.');
        Livewire::test(TwoFactorAuthenticationForm::class)->call('startConfirmingPassword', 'enable')
            ->set('confirmablePassword', ['bad'])->call('confirmPassword')
            ->assertHasErrors(['confirmable_password'])->assertSee('La password inserita non è corretta.');
        $this->withSession(['auth.password_confirmed_at' => time()]);
        Livewire::test(TwoFactorAuthenticationForm::class)->call('enableTwoFactorAuthentication')->set('code', ['bad'])->call('confirmTwoFactorAuthentication')
            ->assertHasErrors(['code'])->assertSee('Il codice di autenticazione a due fattori non è valido.');
        $this->assertNotNull($user->fresh());
    }

    public function test_auth_throttling_preserves_italian_field_feedback_and_retry_headers(): void
    {
        $this->freezeTime();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'wrong']);
        }
        $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'wrong'])
            ->assertRedirect()->assertHeader('Retry-After', '60')
            ->assertSessionHasErrors(['email' => 'Troppi tentativi di accesso. Riprova tra 60 secondi.']);
        $this->postJson('/login', ['email' => 'blocked@example.com', 'password' => 'wrong'])
            ->assertStatus(429)->assertHeader('Retry-After', '60')
            ->assertJsonPath('errors.email.0', 'Troppi tentativi di accesso. Riprova tra 60 secondi.');
    }

    public function test_password_reset_canonicalizes_email_like_login_and_registration(): void
    {
        $user = User::factory()->create(['email' => 'anna@example.com']);
        $token = Password::broker()->createToken($user);
        $this->post('/reset-password', ['email' => '  ANNA@EXAMPLE.COM  ', 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'La password è stata reimpostata.');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_registered_livewire_aliases_keep_successful_sensitive_operations(): void
    {
        config(['session.driver' => 'database']);
        $this->actingAs(User::factory()->create());
        // Livewire's isolated test transport omits the web session middleware.
        request()->setLaravelSession(app('session')->driver());
        $component = app(ComponentRegistry::class)->new('profile.logout-other-browser-sessions-form');
        $component->confirmLogout();
        $component->password = 'password';
        app()->call([$component, 'logoutOtherBrowserSessions']);
        $this->assertFalse($component->confirmingLogout);
        config(['session.driver' => 'array']);
        $this->actingAs($user = User::factory()->create());
        Livewire::test('profile.delete-user-form')->call('confirmUserDeletion')
            ->set('password', 'password')->call('deleteUser')->assertHasNoErrors()->assertRedirect('/');
        $this->assertNull($user->fresh());
        $this->assertGuest();
    }
}
