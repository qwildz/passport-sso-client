<?php
namespace Qwildz\PassportExtended;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use Qwildz\SSOClient\PassportSSOProvider;
use Qwildz\SSOClient\SSOClient;

class PassportSSOClientServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $socialite = $this->app->make(Factory::class);
        $socialite->extend('sso', function($app) use ($socialite) {
            $config = $app['config']['sso'];
            return $socialite->buildProvider(PassportSSOProvider::class, $config);
        });

        $this->app->singleton(SSOClient::class, function() {
            return new SSOClient(new Client());
        });

        $this->setupConfig();
        $this->setupRoute();
    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../resources/config/sso.php');
        $this->publishes([$source => config_path('sso.php')]);
        $this->mergeConfigFrom($source, 'sso');
    }

    protected function setupRoute()
    {
        $options = [
            'namespace' => '\Qwildz\SSOClient\Http\Controllers',
        ];

        Route::group($options, function ($router)  {
            $router->group(['middleware' => ['api']], function ($router) {
                $router->post('/logoutSSO', [
                    'uses' => 'SSOController@handleLogout',
                ]);
            });
        });
    }
}