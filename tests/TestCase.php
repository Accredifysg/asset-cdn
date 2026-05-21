<?php

namespace Arubacao\AssetCdn\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TemporaryDirectory\TemporaryDirectory;

abstract class TestCase extends Orchestra
{
    protected TemporaryDirectory $tempDir;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->tempDir = (new TemporaryDirectory())->create();
        parent::setUp();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->tempDir->delete();
        parent::tearDown();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Arubacao\AssetCdn\AssetCdnServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $publicPath = __DIR__.'/testfiles/public';

//        $app['config']->set('filesystems.disks.public', [
//            'driver' => 'local',
//            'root' => $this->getMediaDirectory(),
//        ]);
        $app['config']->set('app.key', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
        $app['config']->set('filesystems.disks.test_filesystem', [
            'driver' => 'local',
            'root' => $this->tempDir->path(),
        ]);

        $app['config']->set('asset-cdn.use_cdn', true);
        $app['config']->set('asset-cdn.cdn_url', 'http://cdn.localhost');
        $app['config']->set('asset-cdn.filesystem.disk', 'test_filesystem');

        if (method_exists($app, 'usePublicPath')) {
            $app->usePublicPath($publicPath);
        }

        $app->bind('path.public', function () use ($publicPath) {
            return $publicPath;
        });
    }

    protected function setFilesInConfig(array $config): void
    {
        $emptyConfig = [
            'ignoreDotFiles' => true,
            'ignoreVCS' => true,
            'include' => [
                'paths' => [],
                'files' => [],
                'extensions' => [],
                'patterns' => [],
            ],
            'exclude' => [
                'paths' => [],
                'files' => [],
                'extensions' => [],
                'patterns' => [],
            ],
        ];

        $files = array_merge_recursive($emptyConfig, $config);
        $result = [
            'use_cdn' => true,
            'cdn_url' => 'http://cdn.localhost',
            'filesystem' => [
                'disk' => 'test_filesystem',
                'options' => [],
            ],
            'files' => $files,
        ];

        $this->app->make('config')->set('asset-cdn', $result);
    }
}
