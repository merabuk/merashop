<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

trait VfsStreamTrait
{
    private vfsStreamDirectory $vfsRoot;

    protected function setupVfs(string $rootDir = 'root'): void
    {
        $this->vfsRoot = vfsStream::setup($rootDir);
    }

    protected function createVirtualFile(string $name, string $content = '', ?int $permissions = null): string
    {
        $file = vfsStream::newFile($name);

        if (null !== $permissions) {
            $file->chmod($permissions);
        }

        $file->withContent($content)->at($this->vfsRoot);

        return $file->url();
    }

    protected function createVirtualDirectory(string $path): void
    {
        vfsStream::newDirectory($path)->at($this->vfsRoot);
    }

    protected function getVfsBaseUrl(): string
    {
        return $this->vfsRoot->url();
    }
}
