<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class DashboardRoutes
{
    private const DASHBOARD_SLUG = 'latepoint-hub';

    public static function init(): void
    {
        add_action('init', [self::class, 'register_rewrite']);
        add_filter('query_vars', [self::class, 'register_query_var']);
        add_action('template_redirect', [self::class, 'intercept_dashboard']);
        add_action('latepoint/admin/menu', [self::class, 'register_admin_menu']);
        register_activation_hook(LATEPOINT_HIERARCHICAL_DASHBOARDS_FILE, [self::class, 'activate']);
        register_deactivation_hook(LATEPOINT_HIERARCHICAL_DASHBOARDS_FILE, [self::class, 'deactivate']);
    }

    public static function register_rewrite(): void
    {
        add_rewrite_endpoint(self::DASHBOARD_SLUG, EP_ROOT | EP_PAGES);
    }

    public static function register_query_var(array $vars): array
    {
        $vars[] = 'latepoint_dashboard';
        return $vars;
    }

    public static function intercept_dashboard(): void
    {
        global $wp_query;
        if (!isset($wp_query->query_vars[self::DASHBOARD_SLUG])) {
            return;
        }

        if (!is_user_logged_in()) {
            auth_redirect();
        }

        status_header(200);
        include LATEPOINT_HIERARCHICAL_DASHBOARDS_PATH . 'includes/templates/dashboard.php';
        exit;
    }

    public static function register_admin_menu(array $menu): array
    {
        $menu['account_hierarchy'] = [
            'label' => __('Account Hierarchy', 'latepoint-hierarchical-dashboards'),
            'icon' => 'dashicons-networking',
            'url' => admin_url('admin.php?page=latepoint-hierarchy-settings'),
        ];

        return $menu;
    }

    public static function get_dashboard_slug(): string
    {
        return self::DASHBOARD_SLUG;
    }

    public static function activate(): void
    {
        self::register_rewrite();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
