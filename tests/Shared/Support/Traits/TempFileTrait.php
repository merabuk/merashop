<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

trait TempFileTrait
{
    protected function createTempFilePath(string $prefix = 'test_', ?string $extension = null): string
    {
        $tempDir = sys_get_temp_dir();
        $fileName = $prefix.uniqid().($extension ? '.'.$extension : '');
        $fullPath = $tempDir.DIRECTORY_SEPARATOR.$fileName;

        touch($fullPath);

        return $fullPath;
    }
}
