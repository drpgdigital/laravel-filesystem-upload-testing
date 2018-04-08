<?php

namespace Drp\UserManagement\Test;

use Drp\ProjectHelpers\Tests\Concerns\InteractsWithAlerts;
use Drp\ProjectHelpers\Tests\Concerns\InteractsWithExceptions;
use Drp\ProjectHelpers\Tests\Concerns\InteractsWithModels;
use Drp\UserManagement\Events\AccountActivated as AccountActivatedEvent;
use Drp\UserManagement\Events\UserCreatedWithLoginCode;
use Drp\UserManagement\Mail\AccountActivation as AccountActivatedMail;
use Drp\UserManagement\Mail\FirstTimeAccountSetup;
use Drp\UserManagement\UserManagementServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\BrowserKit\TestCase as Orchestra;
use Tests\Support\InteractsWithUsers;

/**
 * Class TestCase
 */
class TestCase extends Orchestra
{



    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            UserManagementServiceProvider::class
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->testHelper->initializeTempDirectory();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('mail.driver', 'log');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->getTempDirectory() . '/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [base_path('resources/views'), $this->testHelper->getTestViews()]);
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
        $app['config']->set('user_management.requires_approval', true);
    }


    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->testHelper->getTempDirectory() . '/database.sqlite', null);

        include_once __DIR__.'/../database/migrations/create_users_table.php.stub';
        (new \CreateUsersTable())->up();

        $this->withFactories(__DIR__ . '/Support/factories');
    }

    protected function registerNotifications()
    {
        Event::listen(UserCreatedWithLoginCode::class, FirstTimeAccountSetup::class);
        Event::listen(AccountActivatedEvent::class, AccountActivatedMail::class);
    }

    protected function registerTestRoutes()
    {
        Route::get('/', [
            'middleware' => ['approved', 'web'],
            'as' => 'home',
            'uses' => function () {
                return response('');
            }
        ]);


        Route::get('/admin', [
            'as' => 'admin.index',
            'middleware' => 'setupComplete',
            'uses' => function () {
                return view('admin');
            }
        ]);
    }

}
