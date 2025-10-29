<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class Shortcodes
{
    public static function init(): void
    {
        add_shortcode('latepoint_hierarchy_dashboard', [self::class, 'render_dashboard']);
        add_shortcode('latepoint_hierarchy_menu', [self::class, 'render_menu']);
    }

    public static function render_dashboard(array $atts, string $content = ''): string
    {
        if (!is_user_logged_in()) {
            return wp_login_form(['echo' => false]);
        }

        ob_start();
        include LATEPOINT_HIERARCHICAL_DASHBOARDS_PATH . 'includes/templates/dashboard.php';
        return ob_get_clean();
    }

    public static function render_menu(array $atts, string $content = ''): string
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $current_user = wp_get_current_user();
        $items = Hierarchy::get_menu_for_user($current_user);

        ob_start();
        include LATEPOINT_HIERARCHICAL_DASHBOARDS_PATH . 'includes/templates/menu.php';
        return ob_get_clean();
    }
}

require_once __DIR__ . '/class-hierarchy.php';
