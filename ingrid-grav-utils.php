<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class IngridGravUtilsPlugin
 * @package Grav\Plugin
 */
class IngridGravUtilsPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onAdminMenu'              => ['onAdminMenu', 0],
            'onAdminTaskExecute'       => ['onAdminTaskExecute', 0],
            'onTwigTemplatePaths'      => ['onTwigTemplatePaths', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
            'onSchedulerInitialized'   => ['onSchedulerInitialized', 0],
            'onTwigLoader'             => ['onTwigLoader', 0],
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {

        // Set proxy
        $systemProxy = $this->grav['config']->get('system.http.proxy');
        if ($systemProxy) {
            $streamContext = array(
                'http' => array(
                    'proxy' => 'http://' . $systemProxy,
                )
            );
            stream_context_set_default($streamContext);
        }

        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigAdminVariables', 0]
            ]);
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            // Put your main events here
        ]);
    }

    public function onTwigTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }


    public function onSchedulerInitialized(Event $e): void
    {
        $codelist = new CodelistController($this->grav);
        $codelist->setScheduler($e);

        $rss = new RssController($this->grav);
        $rss->setScheduler($e);
    }

    /**
     * Add reindex button to the admin QuickTray
     */
    public function onAdminMenu(): void
    {
        $this->grav['twig']->plugins_quick_tray['InGrid Codelist'] = [
            'authorize' => 'taskReindexCodelist',
            'hint' => $this->grav['language']->translate(['PLUGIN_INGRID_GRAV_UTILS.CODELIST_API.GRAV_MENU_TEXT']),
            'class' => 'codelist-reindex',
            'icon' => 'fa-book'
        ];
        $this->grav['twig']->plugins_quick_tray['InGrid RSS'] = [
            'authorize' => 'taskReindexRss',
            'hint' => $this->grav['language']->translate(['PLUGIN_INGRID_GRAV_UTILS.RSS.GRAV_MENU_TEXT']),
            'class' => 'rss-reindex',
            'icon' => 'fa-rss'
        ];
    }

    /**
     * Handle the Reindex task from the admin
     *
     * @param Event $e
     */
    public function onAdminTaskExecute(Event $e): void
    {
        switch ($e['method']) {
            case 'taskReindexCodelist':
                $codelist = new CodelistController($this->grav);
                $codelist->taskReindex($e);
                break;
            case 'taskReindexRss':
                $rss = new RssController($this->grav);
                $rss->taskReindex($e);
                break;
            default:
                break;
        }
    }

    /**
     * Add the Twig template paths to the Twig loader
     */
    public function onTwigLoader(): void
    {
        $this->grav['twig']->addPath(__DIR__ . '/templates');
    }

    public function onAdminTwigTemplatePaths($event) {
        $event['paths'] = array_merge($event['paths'], [__DIR__ . '/templates']);
        return $event;
    }

    public function onTwigAdminVariables(): void
    {
        if ($this->isAdmin()) {
            $twig = $this->grav['twig'];
            try {
                $codelist = new CodelistController($this->grav);
                [$status, $msg] = $codelist->getCount();
                $twig->twig_vars['codelist_index_status'] = ['status' => $status, 'msg' => $msg];
                $this->grav['assets']->addCss('plugin://ingrid-grav-utils/assets/admin/codelist/codelist.css');
                $this->grav['assets']->addJs('plugin://ingrid-grav-utils/assets/admin/codelist/codelist.js');

                $rss = new RssController($this->grav);
                [$status, $msg] = $rss->getCount();
                $twig->twig_vars['rss_index_status'] = ['status' => $status, 'msg' => $msg];
                $this->grav['assets']->addCss('plugin://ingrid-grav-utils/assets/admin/rss/rss.css');
                $this->grav['assets']->addJs('plugin://ingrid-grav-utils/assets/admin/rss/rss.js');
            } catch (\Exception $e) {
                $this->grav['log']->error($e->getMessage());
            }
        }
    }
}
