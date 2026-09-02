<?php

namespace Grav\Plugin;
use Grav\Common\Grav;
use Grav\Common\Utils;

class UVPZipIndex
{
    public function __construct()
    {
    }

    public static function indexJob(int $directorySizeLimit): array
    {
        $lang = Grav::instance()['language'];
        DebugHelper::debug('Start job: UVP Zip Cleanup');
        $locator = Grav::instance()['locator'];
        $folderPath = $locator->findResource('user-data://', true);
        $zipPath = $folderPath . '/downloads/zip';
        $time = date("d.m.Y H:i", time());
        $msg = $lang->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.INDEXING_ZIP', $zipPath, $time]);
        if (is_dir($zipPath)) {
            $directorySize = FileHelper::getFolderSize($zipPath);
            $directorySizeLimitInByte = $directorySizeLimit * 1024 * 1024 * 1024;
            if ($directorySize > $directorySizeLimitInByte) {
                $msg .= PHP_EOL . $lang->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.INDEXING_ZIP_DIRECTORY_SIZE', Utils::prettySize($directorySize), $directorySizeLimit]);
                $files = FileHelper::sortDirectoryFilesByOldest($zipPath);
                foreach ($files as $file) {
                    $filepath = $file['path'];
                    unlink($filepath);
                    $msg .= PHP_EOL . $lang->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.INDEXING_ZIP_FILE_DELETE', $filepath]);
                    $directorySize = FileHelper::getFolderSize($zipPath);
                    if ($directorySize < $directorySizeLimitInByte) {
                        break;
                    }
                }
            } else {
                $msg .= PHP_EOL . $lang->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.INDEXING_ZIP_DIRECTORY_SIZE', Utils::prettySize($directorySize), $directorySizeLimit]);
            }
        }
        DebugHelper::debug('Finished job: UVP Zip Cleanup');
        return ['', $msg];
    }
}
