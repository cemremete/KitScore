<?php
/**
 * Site footer.
 */

if (!defined('ABSPATH')) {
    exit;
}

$review_archive = get_post_type_archive_link('product_review') ?: home_url('/reviews/');
$footer_category_names = ['Football', 'Tennis', 'Running', 'Cycling'];
$footer_categories = [];

foreach ($footer_category_names as $category_name) {
    $term = get_term_by('name', $category_name, 'sport_category');
    if ($term) {
        $footer_categories[] = $term;
    }
}

if (count($footer_categories) < 4) {
    $fallback_terms = get_terms([
        'taxonomy' => 'sport_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'number' => 4 - count($footer_categories),
        'exclude' => wp_list_pluck($footer_categories, 'term_id'),
    ]);

    if (!is_wp_error($fallback_terms)) {
        $footer_categories = array_merge($footer_categories, $fallback_terms);
    }
}
?>
</main>
<footer class="gs-footer">
    <div class="gs-container gs-footer-grid">
        <div class="gs-footer-brand">
            <a class="gs-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('KitScore home', 'kitscore'); ?>">
                <span class="gs-logo-mark" aria-hidden="true"><?php kitscore_trophy_icon(); ?></span>
                <span>KitScore</span>
            </a>
            <p><?php esc_html_e('Expert sports equipment reviews and ratings', 'kitscore'); ?></p>
        </div>

        <div>
            <h2><?php esc_html_e('Categories', 'kitscore'); ?></h2>
            <ul class="gs-footer-links">
                <?php foreach ($footer_categories as $category) : ?>
                    <li><a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div>
            <h2><?php esc_html_e('Company', 'kitscore'); ?></h2>
            <ul class="gs-footer-links">
                <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/compare/')); ?>"><?php esc_html_e('Compare', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url($review_archive); ?>"><?php esc_html_e('Reviews', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact', 'kitscore'); ?></a></li>
            </ul>
        </div>

        <div>
            <h2><?php esc_html_e('Resources', 'kitscore'); ?></h2>
            <ul class="gs-footer-links">
                <li><a href="<?php echo esc_url(home_url('/review-guidelines/')); ?>"><?php esc_html_e('Review Guidelines', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/submit-product/')); ?>"><?php esc_html_e('Submit Product', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php esc_html_e('Blog', 'kitscore'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/help-center/')); ?>"><?php esc_html_e('Help Center', 'kitscore'); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="gs-footer-bottom">
        <div class="gs-container">
            <?php echo wp_kses_post('&copy; 2026 KitScore. All rights reserved.'); ?>
        </div>
    </div>
    <?php wp_footer(); ?>
</footer>
</body>
</html>
