<?php

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Utils;
use RocketTheme\Toolbox\Event\Event;

class UVPZipController
{
    public Grav $grav;

    public function __construct(Grav $grav) {
        $this->grav = $grav;
    }

    public function getCount(): array
    {
        $lang = $this->grav['language'];
        $config = $this->grav['config']->get('plugins.ingrid-grav-utils.uvp.zip');
        $locator = $this->grav['locator'];
        $folderPath = $locator->findResource('user-data://', true);
        $zipPath = $folderPath . '/downloads/zip';
        $directorySize = 0;
        if (is_dir($zipPath)) {
            $directorySize = FileHelper::getFolderSize($zipPath);
        }
        $directorySizeLimitInByte = $config['limit'] * 1024 * 1024 * 1024;
        $msg = $lang->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.ZIP_INDEXING_DIRECTORY_SIZE', Utils::prettySize($directorySize)]);
        return [$directorySize < $directorySizeLimitInByte, $msg];
    }

    public function taskReindex(Event $e): void
    {
        $controller = $e['controller'];

        header('Content-type: application/json');

        if (!$controller->authorizeTask('reindexUVPZip', ['admin.configuration', 'admin.super'])) {
            $json_response = [
                'status'  => 'error',
                'message' => '<i class="fa fa-warning"></i> '. self::getCount()[1],
                'details' => $this->grav['language']->translate(['PLUGIN_INGRID_GRAV_UTILS.UVP.ZIP_INDEXING_UNPERMISSION'])
            ];
            echo json_encode($json_response);
            exit;
        }

        // disable warnings
        error_reporting(1);
        // disable execution time
        set_time_limit(0);

        self::indexJob();

        $json_response = [
            'status'  => 'success',
            'message' => self::getCount()[1]
        ];

        echo json_encode($json_response);
        exit;
    }

    public static function indexJob(): array
    {
        ob_start();
        $grav = Grav::instance();
        $config = $grav['config']->get('plugins.ingrid-grav-utils.uvp.zip');

        [$status, $msg] = UVPZipIndex::indexJob($config['limit']);
        $output = ob_get_clean();

        return [$status, $msg, $output];
    }

    public function setScheduler(Event $e): void
    {
        $config = $this->grav['config']->get('plugins.ingrid-grav-utils.uvp.zip.scheduled_index');
        if ($config['enabled']) {
            /** @var Scheduler $scheduler */
            $scheduler = $e['scheduler'];
            $at = $config['at'];
            $logs = $config['logs'];
            $job = $scheduler->addCommand('bin/plugin', ['ingrid-grav-utils', 'index-uvp-zip'], 'ingrid-uvp-zip-index');
            $job->at($at);
            $job->output($logs);
            $job->backlink('/plugins/ingrid-grav-utils');
        }
    }
}