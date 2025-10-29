<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class Assets
{
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin']);
    }

    public static function enqueue_frontend(): void
    {
        if (!self::should_enqueue()) {
            return;
        }

        wp_enqueue_style(
            'latepoint-hierarchical-dashboards',
            LATEPOINT_HIERARCHICAL_DASHBOARDS_URL . 'assets/css/frontend.css',
            [],
            Loader::VERSION
        );

        wp_enqueue_script(
            'latepoint-hierarchical-dashboards',
            LATEPOINT_HIERARCHICAL_DASHBOARDS_URL . 'assets/js/frontend.js',
            ['jquery'],
            Loader::VERSION,
            true
        );

        wp_localize_script(
            'latepoint-hierarchical-dashboards',
            'LatepointHierarchy',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('latepoint_hierarchy'),
            ]
        );
    }

    public static function enqueue_admin(): void
    {
        wp_enqueue_style(
            'latepoint-hierarchical-dashboards-admin',
            LATEPOINT_HIERARCHICAL_DASHBOARDS_URL . 'assets/css/admin.css',
            [],
            Loader::VERSION
        );
    }

    private static function should_enqueue(): bool
    {
        if (is_admin()) {
            return false;
        }

        global $wp_query;
        $slug = DashboardRoutes::get_dashboard_slug();
        return isset($wp_query->query_vars[$slug]);
    }
}
