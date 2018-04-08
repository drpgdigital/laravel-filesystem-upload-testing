<?php

namespace Drpgroup\LaravelFileSystemUploadTesting\Concerns;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

trait InteractsWithFilesystem
{
    /** @before */
    public function hijackFilesystem(): void
    {
        if (method_exists($this, 'afterApplicationCreated')) {
            $this->afterApplicationCreated(function () {
                $this->useMemoryAdapter();
            });
        } else {
            $this->useMemoryAdapter();
        }
    }

    /**
     *
     */
    private function useMemoryAdapter(): Filesystem
    {
        $filesystem = new Filesystem(new MemoryAdapter());

        $this->app['filesystem']->extend('memory', function () use ($filesystem) {
            return $filesystem;
        });

        $this->app['config']['filesystems.disks.memory.driver'] = 'memory';
        $this->app['config']['filesystems.default'] = 'memory';

        return $filesystem;
    }


    public function assertFileInFileSystem(string $path, $fileContents = null): self
    {
        $this->assertTrue(
            $this->app['filesystem']->has($path),
            "A file with the path $path was not found in the filesystem."
        );

        if ($fileContents !== null) {
            $this->assertEquals(
                $this->app['filesystem']->read($path),
                $fileContents,
                "The file at path $path does not match the given file."
            );
        }

        return $this;
    }

    public function assertFileNotInFileSystem(string $path): self
    {
        $this->assertFalse(
            $this->app['filesystem']->has($path),
            "A file with the path $path was found in the filesystem."
        );

        return $this;
    }
}
