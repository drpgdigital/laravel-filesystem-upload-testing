<?php

namespace Drpgroup\LaravelFileSystemUploadTesting\Tests;

use Drpgroup\LaravelFileSystemUploadTesting\Concerns\InteractsWithFilesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Orchestra\Testbench\BrowserKit\TestCase;
use PHPUnit\Framework\AssertionFailedError;

class InteractsWithFilesystemTest extends TestCase
{
    use InteractsWithFilesystem;

    /**
     * @test
     */
    public function hijack_filesystem()
    {
        $this->hijackFilesystem();

        $this->assertInstanceOf(MemoryAdapter::class, $this->app['filesystem']->getAdapter());
    }

    /**
     * @test
     */
    public function file_in_filesystem_fails_when_file_does_not_exits()
    {
        try {
            $this->assertFileInFileSystem('FILE_PATH');
        } catch (AssertionFailedError $exception) {
            return;
        }

        $this->fail('Assertion did not fail when file does not exist');
    }

    /**
     * @test
     */
    public function file_in_filesystem_fails_when_file_is_not_the_same()
    {
        try {
            $this->app['filesystem']->put('FILE_PATH', 'FILE_CONTENTS');
            $this->assertFileInFileSystem('FILE_PATH', 'DIFFERENT_FILE_CONTENTS');
        } catch (AssertionFailedError $exception) {
            return;
        }

        $this->fail('Assertion did not fail when file content is not the same');
    }

    /**
     * @test
     */
    public function file_in_filesystem_passes_when_file_is_the_same()
    {
        $this->app['filesystem']->put('FILE_PATH', 'FILE_CONTENTS');
        $this->assertFileInFileSystem('FILE_PATH', 'FILE_CONTENTS');
    }

    /**
     * @test
     */
    public function file_not_in_filesystem_fails_when_file_does_exits()
    {
        try {
            $this->app['filesystem']->put('FILE_PATH', 'FILE_CONTENTS');
            $this->assertFileNotInFileSystem('FILE_PATH');
        } catch (AssertionFailedError $exception) {
            return;
        }

        $this->fail('Assertion did not fail when file exists');
    }

    /**
     * @test
     */
    public function file_not_in_filesystem_passes_when_file_does_not_exist()
    {
        $this->assertFileNotInFileSystem('FILE_PATH');
    }
}
