<?php
/**
 * Plugin Name: LatePoint Hierarchical Dashboards
 * Description: Adds multi-level account dashboards and role management on top of LatePoint.
 * Version: 0.1.0
 * Author: OpenAI Assistant
 * Text Domain: latepoint-hierarchical-dashboards
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LATEPOINT_HIERARCHICAL_DASHBOARDS_FILE', __FILE__);
define('LATEPOINT_HIERARCHICAL_DASHBOARDS_PATH', plugin_dir_path(__FILE__));
define('LATEPOINT_HIERARCHICAL_DASHBOARDS_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/includes/class-loader.php';

\LatepointHierarchicalDashboards\Loader::init();
