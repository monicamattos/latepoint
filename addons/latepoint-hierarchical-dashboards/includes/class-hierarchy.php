<?php
namespace LatepointHierarchicalDashboards;

if (!defined('ABSPATH')) {
    exit;
}

class Hierarchy
{
    public static function get_menu_for_user(\WP_User $user): array
    {
        $roles = (array) $user->roles;
        $menu = [];

        if (self::user_can($user, 'latepoint_view_dashboards')) {
            $menu[] = [
                'id' => 'dashboard',
                'label' => __('Dashboard', 'latepoint-hierarchical-dashboards'),
                'slug' => 'dashboard',
            ];
        }

        if (self::user_can($user, 'latepoint_manage_agenda')) {
            $menu[] = [
                'id' => 'agenda',
                'label' => __('Agenda', 'latepoint-hierarchical-dashboards'),
                'slug' => 'agenda',
            ];
        }

        if (self::user_can($user, 'latepoint_manage_clients')) {
            $menu[] = [
                'id' => 'clients',
                'label' => __('Clients', 'latepoint-hierarchical-dashboards'),
                'slug' => 'clients',
            ];
        }

        if (in_array('latepoint_supervisor', $roles, true)) {
            $menu[] = [
                'id' => 'providers',
                'label' => __('Providers', 'latepoint-hierarchical-dashboards'),
                'slug' => 'providers',
            ];
        }

        if (self::user_can($user, 'latepoint_view_commissions')) {
            $menu[] = [
                'id' => 'commissions',
                'label' => __('Commissions', 'latepoint-hierarchical-dashboards'),
                'slug' => 'commissions',
            ];
        }

        if (self::user_can($user, 'latepoint_manage_payments')) {
            $menu[] = [
                'id' => 'payments',
                'label' => __('Payments', 'latepoint-hierarchical-dashboards'),
                'slug' => 'payments',
            ];
        }

        if (self::user_can($user, 'latepoint_view_reports')) {
            $menu[] = [
                'id' => 'reports',
                'label' => __('Reports', 'latepoint-hierarchical-dashboards'),
                'slug' => 'reports',
            ];
        }

        if (self::user_can($user, 'latepoint_manage_loyalty')) {
            $menu[] = [
                'id' => 'loyalty',
                'label' => __('Loyalty', 'latepoint-hierarchical-dashboards'),
                'slug' => 'loyalty',
            ];
        }

        if (self::user_can($user, 'latepoint_manage_contracts')) {
            $menu[] = [
                'id' => 'contracts',
                'label' => __('Contracts', 'latepoint-hierarchical-dashboards'),
                'slug' => 'contracts',
            ];
        }

        return $menu;
    }

    public static function user_can(\WP_User $user, string $cap): bool
    {
        return user_can($user, $cap);
    }

    public static function get_sections(): array
    {
        return [
            'dashboard' => [
                'title' => __('Overview', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-dashboard.php',
            ],
            'agenda' => [
                'title' => __('Agenda', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-agenda.php',
            ],
            'clients' => [
                'title' => __('Clients', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-clients.php',
            ],
            'providers' => [
                'title' => __('Service Providers', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-providers.php',
            ],
            'commissions' => [
                'title' => __('Commissions', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-commissions.php',
            ],
            'payments' => [
                'title' => __('Payments', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-payments.php',
            ],
            'reports' => [
                'title' => __('Reports', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-reports.php',
            ],
            'loyalty' => [
                'title' => __('Loyalty', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-loyalty.php',
            ],
            'contracts' => [
                'title' => __('Contracts', 'latepoint-hierarchical-dashboards'),
                'template' => 'section-contracts.php',
            ],
        ];
    }
}
