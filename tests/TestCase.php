<?php

namespace MityDigital\StatamicVariableNumberFieldtype\Tests;

use Facades\Statamic\Version;
use Illuminate\Support\Facades\File;
use MityDigital\StatamicVariableNumberFieldtype\ServiceProvider;
use Statamic\Console\Processes\Composer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected $shouldFakeVersion = true;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function set2Up(): void
    {
        parent::setUp();

        $this->withoutVite();

        if ($this->shouldFakeVersion) {
            Version::shouldReceive('get')
                ->andReturn(Composer::create(__DIR__.'/../')->installedVersion(Statamic::PACKAGE));
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        config()->set('filesystems.disks', array_merge(
            config('filesystems.disks'),
            [
                'fonts' => [
                    'driver' => 'local',
                    'root' => __DIR__.'/fonts',
                    'url' => env('APP_URL').'/storage',
                    'visibility' => 'public',
                ],
            ],
        ));
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'forms',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require (__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        // set the forms folder
        $app['config']->set('statamic.forms.forms', __DIR__.'/__fixtures__/forms');

        Statamic::booted(function () {
            Blueprint::setDirectory(__DIR__.'/__fixtures__/blueprints');

            // configure to be an AU site
            Site::setSites([
                'default' => [
                    'name' => config('app.name'),
                    'locale' => 'en_AU',
                    'url' => '/',
                ],
            ]);
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->getTempDirectory());

        parent::tearDown();
    }

    public function getTempDirectory($suffix = ''): string
    {
        return __DIR__.'/TestSupport/'.($suffix == '' ? '' : '/'.$suffix);
    }
}
