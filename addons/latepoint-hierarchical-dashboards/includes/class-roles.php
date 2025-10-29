<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class Roles
{
    private const CAPABILITIES = [
        'latepoint_view_dashboards',
        'latepoint_manage_clients',
        'latepoint_manage_agenda',
        'latepoint_view_commissions',
        'latepoint_view_reports',
        'latepoint_manage_contracts',
        'latepoint_manage_payments',
        'latepoint_manage_loyalty',
    ];

    private const ROLES = [
        'latepoint_account_admin' => [
            'label' => 'LatePoint Account Admin',
            'caps' => [
                'latepoint_view_dashboards' => true,
                'latepoint_manage_clients' => true,
                'latepoint_manage_agenda' => true,
                'latepoint_view_commissions' => true,
                'latepoint_view_reports' => true,
                'latepoint_manage_contracts' => true,
                'latepoint_manage_payments' => true,
                'latepoint_manage_loyalty' => true,
                'read' => true,
            ],
        ],
        'latepoint_supervisor' => [
            'label' => 'LatePoint Supervisor',
            'caps' => [
                'latepoint_view_dashboards' => true,
                'latepoint_manage_clients' => true,
                'latepoint_manage_agenda' => true,
                'latepoint_view_commissions' => true,
                'latepoint_view_reports' => true,
                'read' => true,
            ],
        ],
        'latepoint_service_provider' => [
            'label' => 'LatePoint Service Provider',
            'caps' => [
                'latepoint_view_dashboards' => true,
                'latepoint_manage_agenda' => true,
                'latepoint_view_commissions' => true,
                'latepoint_view_reports' => true,
                'read' => true,
            ],
        ],
        'latepoint_client_pf' => [
            'label' => 'LatePoint Customer (Individual)',
            'caps' => [
                'latepoint_view_dashboards' => true,
                'latepoint_manage_agenda' => true,
                'latepoint_manage_payments' => true,
                'latepoint_manage_loyalty' => true,
                'read' => true,
            ],
        ],
        'latepoint_client_pj' => [
            'label' => 'LatePoint Customer (Business)',
            'caps' => [
                'latepoint_view_dashboards' => true,
                'latepoint_manage_agenda' => true,
                'latepoint_manage_payments' => true,
                'latepoint_manage_loyalty' => true,
                'latepoint_manage_contracts' => true,
                'latepoint_view_reports' => true,
                'read' => true,
            ],
        ],
    ];

    public static function init(): void
    {
        register_activation_hook(LATEPOINT_HIERARCHICAL_DASHBOARDS_FILE, [self::class, 'activate']);
        register_deactivation_hook(LATEPOINT_HIERARCHICAL_DASHBOARDS_FILE, [self::class, 'deactivate']);
        add_action('init', [self::class, 'register_caps']);
    }

    public static function activate(): void
    {
        self::register_caps();
        foreach (self::ROLES as $role => $data) {
            add_role($role, $data['label'], $data['caps']);
        }
    }

    public static function deactivate(): void
    {
        foreach (self::ROLES as $role => $data) {
            remove_role($role);
        }

        $wp_roles = wp_roles();
        foreach (self::CAPABILITIES as $cap) {
            foreach ($wp_roles->roles as $role_key => $role) {
                if (isset($role['capabilities'][$cap])) {
                    $wp_roles->remove_cap($role_key, $cap);
                }
            }
        }
    }

    public static function register_caps(): void
    {
        $admin = get_role('administrator');
        if (!$admin) {
            return;
        }

        foreach (self::CAPABILITIES as $cap) {
            if (!$admin->has_cap($cap)) {
                $admin->add_cap($cap);
            }
        }
    }
}
