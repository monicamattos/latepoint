<?php
use LatepointHierarchicalDashboards\Hierarchy;

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$menu_items = Hierarchy::get_menu_for_user($current_user);
$sections = Hierarchy::get_sections();
?>
<div class="latepoint-hierarchy-wrapper">
    <div class="latepoint-hierarchy-grid">
        <aside class="latepoint-hierarchy-menu">
            <div class="latepoint-hierarchy-user">
                <strong><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></strong>
                <p class="latepoint-hierarchy-user-role">
                    <?php echo esc_html(implode(', ', $current_user->roles)); ?>
                </p>
            </div>
            <ul>
                <?php foreach ($menu_items as $item) : ?>
                    <li>
                        <a href="#<?php echo esc_attr($item['slug']); ?>"
                           data-section="<?php echo esc_attr($item['slug']); ?>"
                           class="<?php echo esc_attr($item === reset($menu_items) ? 'is-active' : ''); ?>">
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <main class="latepoint-hierarchy-content">
            <?php foreach ($menu_items as $item) :
                $section = $sections[$item['slug']] ?? null;
                if (!$section) {
                    continue;
                }
                $template = LATEPOINT_HIERARCHICAL_DASHBOARDS_PATH . 'includes/templates/' . $section['template'];
                ?>
                <section class="latepoint-hierarchy-section" data-section="<?php echo esc_attr($item['slug']); ?>">
                    <h2><?php echo esc_html($section['title']); ?></h2>
                    <?php if (file_exists($template)) {
                        include $template;
                    } else : ?>
                        <div class="latepoint-hierarchy-placeholder">
                            <?php esc_html_e('Content placeholder. Replace this template with your LatePoint integration.', 'latepoint-hierarchical-dashboards'); ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
</div>
