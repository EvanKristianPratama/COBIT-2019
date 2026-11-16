<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UserAdminController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class InactiveUserLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('jabatan');
            $table->string('organisasi')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('role')->nullable();
            $table->string('access_profile')->nullable();
            $table->boolean('isActivated')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Route::middleware(['web', 'auth'])->get('/test-active-session', fn () => response('OK'));
    }

    public function test_inactive_user_with_correct_password_cannot_login_and_sees_message(): void
    {
        $user = $this->makeUser(false);

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/login')->assertSessionHasErrors(['email' => User::INACTIVE_ACCOUNT_MESSAGE]);
        $this->assertGuest();
        $this->get('/login')->assertSee(User::INACTIVE_ACCOUNT_MESSAGE);
    }

    public function test_incorrect_password_does_not_disclose_account_status(): void
    {
        $user = $this->makeUser(false);

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $this->assertGuest();
    }

    public function test_active_user_can_login_with_password(): void
    {
        $user = $this->makeUser(true);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_reactivated_user_can_login_again(): void
    {
        $user = $this->makeUser(false);
        $user->isActivated = true;
        $user->save();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_session_is_logged_out_on_next_request(): void
    {
        $user = $this->makeUser(true);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/home');
        $this->get('/test-active-session')->assertOk();
        User::whereKey($user->id)->update(['isActivated' => false]);
        auth()->forgetGuards();

        $this->get('/test-active-session')->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => User::INACTIVE_ACCOUNT_MESSAGE]);
        $this->assertGuest();
    }

    public function test_deactivated_session_receives_json_error(): void
    {
        $user = $this->makeUser(false);

        $this->actingAs($user)->getJson('/test-active-session')->assertForbidden()
            ->assertJson(['message' => User::INACTIVE_ACCOUNT_MESSAGE]);
        $this->assertGuest();
    }

    public function test_inactive_google_user_is_rejected(): void
    {
        $user = $this->makeUser(false);
        $this->mockGoogleUser($user->email);

        $this->get('/login/google/callback')->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => User::INACTIVE_ACCOUNT_MESSAGE]);
        $this->assertGuest();
    }

    public function test_active_google_user_can_login(): void
    {
        $user = $this->makeUser(true);
        $this->mockGoogleUser($user->email);

        $this->get('/login/google/callback')->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    public function test_new_google_user_can_continue_to_pending_approval(): void
    {
        $this->mockGoogleUser('new@example.com');

        $this->get('/login/google/callback')->assertRedirect('/home');
        $this->assertAuthenticated();
        $this->assertTrue((bool) User::where('email', 'new@example.com')->firstOrFail()->isActivated);
    }

    public function test_deactivation_revokes_the_existing_remember_token(): void
    {
        $admin = $this->makeUser(true, 'admin@example.com');
        $admin->role = 'admin';
        $admin->save();
        $user = $this->makeUser(true);
        $user->setRememberToken('old-token');
        $user->save();
        $this->actingAs($admin);

        app(UserAdminController::class)->deactivate($user);

        $user->refresh();
        $this->assertFalse((bool) $user->isActivated);
        $this->assertNotSame('old-token', $user->getRememberToken());
    }

    private function makeUser(bool $active, string $email = 'user@example.com'): User
    {
        $user = User::create([
            'name' => 'User', 'email' => $email, 'password' => Hash::make('password'),
            'jabatan' => 'Staff', 'role' => 'user',
        ]);
        $user->isActivated = $active;
        $user->save();

        return $user;
    }

    private function mockGoogleUser(string $email): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn('Google User');
        Socialite::shouldReceive('driver->stateless->user')->once()->andReturn($googleUser);
    }
}
