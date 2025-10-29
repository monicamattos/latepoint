<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<nav class="latepoint-hierarchy-menu">
    <ul>
        <?php foreach ($items as $item) : ?>
            <li>
                <a href="#<?php echo esc_attr($item['slug']); ?>" data-section="<?php echo esc_attr($item['slug']); ?>">
                    <?php echo esc_html($item['label']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
