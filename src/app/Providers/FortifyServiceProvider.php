<?php

namespace App\Providers;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\AttemptToAuthenticate;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $u = auth()->user();
                    return $u && method_exists($u, 'isAdmin') && $u->isAdmin()
                        ? redirect()->intended(route('admin.attendance.list'))
                        : redirect()->intended(route('user.attendance.index')); 
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
    Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
    Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

    Fortify::registerView(fn () => view('auth.register'));
    Fortify::loginView(fn () => view('auth.login'));

    
    Fortify::authenticateUsing(function (LoginRequest $request) {
        return app(AttemptToAuthenticate::class)($request) ?? auth()->user();
    });

    RateLimiter::for('login', function (Request $request) {
        $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
        return Limit::perMinute(5)->by($throttleKey);
    });

    RateLimiter::for('two-factor', function (Request $request) {
        return Limit::perMinute(5)->by($request->session()->get('login.id'));
    });

    $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    
                    $isAdminContext = $request->is('admin/*');

                return redirect()->to(
                    $isAdminContext
                        ? route('admin.login')
                        : route('login')
                );
                }
            };
        });
}
}