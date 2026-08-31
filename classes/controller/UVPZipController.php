<?php

namespace Grav\Plugin;

use Grav\Common\Grav;
use RocketTheme\Toolbox\Event\Event;

class UVPZipController
{
    public Grav $grav;

    public function __construct(Grav $grav) {
        $this->grav = $grav;
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