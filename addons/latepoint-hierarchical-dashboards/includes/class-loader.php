<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class Loader
{
    public const VERSION = '0.1.0';

    public static function init(): void
    {
        Roles::init();
        DashboardRoutes::init();
        Assets::init();
        Shortcodes::init();
    }
}

require_once __DIR__ . '/class-roles.php';
require_once __DIR__ . '/class-dashboard-routes.php';
require_once __DIR__ . '/class-assets.php';
require_once __DIR__ . '/class-shortcodes.php';
