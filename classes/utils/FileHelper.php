<?php

namespace Grav\Plugin;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class FileHelper
{

    public static function getFolderSize(string $path): int
    {
        $size = 0;

        foreach (new \RecursiveIteratorIterator(
                     new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                 ) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    public static function sortDirectoryFilesByOldest(string $path): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = [
                    'path' => $file->getPathname(),
                    'mtime' => $file->getMTime(),
                    'size' => $file->getSize(),
                ];
            }
        }

        usort($files, fn($a, $b) => $a['mtime'] <=> $b['mtime']);

        return $files;
    }
}