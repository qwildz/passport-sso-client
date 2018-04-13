<?php

namespace Qwildz\SSOClient;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Laravel\Socialite\Contracts\Factory;

class PassportSSOClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $socialite = $this->app->make(Factory::class);
        $socialite->extend('sso', function ($app) use ($socialite) {
            $config = $app['config']['sso'];
            return $socialite->buildProvider(PassportSSOProvider::class, $config);
        });

        $this->app->singleton(SSOClient::class, function () {
            return new SSOClient(new Client());
        });

        $this->setupConfig();
        $this->setupRoute();
    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../resources/config/sso.php');
        $this->publishes([$source => config_path('sso.php')]);
        $this->mergeConfigFrom($source, 'sso');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'passportsso');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/passport'),
        ], 'passportsso-views');
    }

    protected function setupRoute()
    {
        $options = [
            'namespace' => '\Qwildz\SSOClient\Http\Controllers',
        ];

        Route::group($options, function ($router) {
            $router->group(['middleware' => ['slo']], function ($router) {
                $router->post('/logoutSSO', [
                    'uses' => 'SSOController@handleLogout',
                ]);
            });
        });

        Route::view('/rp-frame', 'passportsso::rpframe');
    }

    public function register()
    {
        $this->app->afterResolving('blade.compiler', function () {
            $this->addBladeDirective($this->app['blade.compiler']);
        });
    }

    public function addBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('opframe', function () {
            return "<iframe id='opFrame' name='opFrame' border='0' width='0' height='0' src='". config('sso.url').'/session/op-frame' . "' style='visibility:hidden'></iframe>";
        });

        $blade->directive('rpframe', function () {
            return "<iframe id='rpFrame' name='rpFrame' border='0' width='0' height='0' src='/rp-frame' style='visibility:hidden'></iframe>";
        });
    }
}