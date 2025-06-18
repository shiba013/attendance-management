<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('verification.notice');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(function ()
        {
            return view('auth.register');
        });

        Fortify::loginView(function (Request $request)
        {
            session(['login_type' => 'user']);
            return view('auth.login_user');
        });

        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new SimpleViewResponse('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request)
        {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        app()->bind(FortifyLoginRequest::class, LoginRequest::class);
    }
}
