<?php
/**
 * KitScore child theme functions.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KITSCORE_THEME_VERSION', '1.0.0');

add_action('wp_enqueue_scripts', 'kitscore_enqueue_assets');
function kitscore_enqueue_assets(): void
{
    wp_enqueue_style('kitscore-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@600;700;800&display=swap', [], null);
    wp_enqueue_style('kitscore-style', get_stylesheet_uri(), ['kitscore-fonts'], KITSCORE_THEME_VERSION);
    wp_enqueue_script('kitscore-main', get_stylesheet_directory_uri() . '/assets/js/main.js', [], KITSCORE_THEME_VERSION, true);
}

add_action('after_setup_theme', 'kitscore_theme_setup');
function kitscore_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'kitscore'),
    ]);
}

add_action('widgets_init', 'kitscore_register_widget_areas');
function kitscore_register_widget_areas(): void
{
    register_sidebar([
        'name' => __('Sidebar', 'kitscore'),
        'id' => 'sidebar-1',
        'before_widget' => '<section class="widget gs-panel">',
        'after_widget' => '</section>',
        'before_title' => '<h2>',
        'after_title' => '</h2>',
    ]);

    register_sidebar([
        'name' => __('Homepage Featured', 'kitscore'),
        'id' => 'homepage-featured',
        'before_widget' => '<section class="widget gs-panel">',
        'after_widget' => '</section>',
        'before_title' => '<h2>',
        'after_title' => '</h2>',
    ]);

    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name' => sprintf(__('Footer Column %d', 'kitscore'), $i),
            'id' => 'footer-' . $i,
            'before_widget' => '<section class="widget">',
            'after_widget' => '</section>',
            'before_title' => '<h2>',
            'after_title' => '</h2>',
        ]);
    }
}

add_action('init', 'kitscore_register_shortcode_aliases', 20);
function kitscore_register_shortcode_aliases(): void
{
    if (shortcode_exists('kitscore_comparison')) {
        return;
    }

    $legacy_shortcode = 'gear' . 'score_comparison';
    if (!shortcode_exists($legacy_shortcode)) {
        return;
    }

    add_shortcode('kitscore_comparison', static function ($atts = [], $content = null) use ($legacy_shortcode): string {
        global $shortcode_tags;

        if (empty($shortcode_tags[$legacy_shortcode]) || !is_callable($shortcode_tags[$legacy_shortcode])) {
            return '';
        }

        return (string) call_user_func($shortcode_tags[$legacy_shortcode], $atts, $content, $legacy_shortcode);
    });
}

add_action('acf/init', 'kitscore_register_user_reviews_field_group');
function kitscore_register_user_reviews_field_group(): void
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_kitscore_user_reviews',
        'title' => 'user_reviews',
        'fields' => [
            [
                'key' => 'field_kitscore_user_reviews',
                'label' => 'User Reviews',
                'name' => 'user_reviews',
                'type' => 'repeater',
                'button_label' => 'Add Review',
                'layout' => 'row',
                'sub_fields' => [
                    ['key' => 'field_kitscore_reviewer_name', 'label' => 'Reviewer Name', 'name' => 'reviewer_name', 'type' => 'text'],
                    ['key' => 'field_kitscore_reviewer_rating', 'label' => 'Reviewer Rating', 'name' => 'reviewer_rating', 'type' => 'number', 'min' => 1, 'max' => 5, 'step' => 1],
                    ['key' => 'field_kitscore_review_date', 'label' => 'Review Date', 'name' => 'review_date', 'type' => 'date_picker', 'display_format' => 'Y-m-d', 'return_format' => 'Y-m-d'],
                    ['key' => 'field_kitscore_review_text', 'label' => 'Review Text', 'name' => 'review_text', 'type' => 'textarea', 'rows' => 4],
                    ['key' => 'field_kitscore_verified_purchase', 'label' => 'Verified Purchase', 'name' => 'verified_purchase', 'type' => 'true_false', 'ui' => 1],
                ],
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'product_review']]],
        'position' => 'normal',
        'style' => 'default',
        'active' => true,
        'show_in_rest' => 1,
    ]);
}

add_action('wp_head', 'kitscore_inline_critical_css', 1);
function kitscore_inline_critical_css(): void
{
    ?>
    <style id="kitscore-critical-css">
        body{margin:0;background:#F5F7FA;color:#333333;font-family:Inter,system-ui,sans-serif}.gs-site-header{position:sticky;top:0;z-index:50;background:#1A1A2E}.gs-container{width:min(100% - 32px,1180px);margin:0 auto}.gs-hero{position:relative;min-height:500px;background:#1A1A2E;color:#fff}.gs-hero h1{margin:0 0 18px;color:#fff;font-family:Poppins,Inter,sans-serif;font-size:52px;line-height:1.08}
    </style>
    <?php
}

function kitscore_trophy_icon(): void
{
    ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
        <path d="M4 22h16"/>
        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
    </svg>
    <?php
}

function kitscore_score_class(float $score): string
{
    if ($score >= 8) {
        return 'gs-score-blue';
    }

    if ($score >= 5) {
        return 'gs-score-yellow';
    }

    return 'gs-score-red';
}

function kitscore_get_field_value(string $key, ?int $post_id = null, $default = '')
{
    $post_id = $post_id ?: get_the_ID();

    if (function_exists('get_field')) {
        $value = get_field($key, $post_id);
        if ($value !== null && $value !== false && $value !== '') {
            return $value;
        }
    }

    $value = get_post_meta($post_id, $key, true);
    return ($value !== '' && $value !== null) ? $value : $default;
}

function kitscore_get_list_field(string $key, ?int $post_id = null): array
{
    $raw = kitscore_get_field_value($key, $post_id, []);

    if (!is_array($raw)) {
        return array_filter(array_map('trim', explode("\n", (string) $raw)));
    }

    $items = [];
    foreach ($raw as $row) {
        if (is_array($row)) {
            $items[] = trim((string) reset($row));
        } else {
            $items[] = trim((string) $row);
        }
    }

    return array_values(array_filter($items));
}

function kitscore_score_meta_key(): string
{
    static $meta_key = null;

    if ($meta_key !== null) {
        return $meta_key;
    }

    $score_posts = get_posts([
        'post_type' => 'product_review',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => 'score',
        'no_found_rows' => true,
    ]);

    $meta_key = $score_posts ? 'score' : 'gs_score';

    return $meta_key;
}

function kitscore_product_image_url(?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();

    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'large');
    }

    if (function_exists('get_field')) {
        $acf_img = get_field('image_url', $post_id);
        if ($acf_img) {
            return (string) $acf_img;
        }
    }

    return (string) kitscore_get_field_value('gs_product_image', $post_id, 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=1200&q=80');
}

function kitscore_score_badge($score, string $label = 'Score'): void
{
    $score = (float) $score;
    ?>
    <span class="gs-score-badge <?php echo esc_attr(kitscore_score_class($score)); ?>" aria-label="<?php echo esc_attr($label . ' ' . number_format($score, 1)); ?>">
        <span><?php echo esc_html(number_format($score, 1)); ?><small>/10</small></span>
    </span>
    <?php
}

function kitscore_category_images(): array
{
    return [
        'running' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
        'cycling' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=900&q=80',
        'swimming' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=900&q=80',
        'hiking' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
        'team-sports' => 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=900&q=80',
    ];
}

function kitscore_category_icon(int $index = 0): void
{
    $icons = [
        '<path d="M12 3a9 9 0 1 0 9 9"/><path d="M12 3a9 9 0 0 1 9 9"/><path d="M3.6 8h16.8"/><path d="M3.6 16h16.8"/><path d="M8 3.6c2.1 2.4 3 5.2 3 8.4s-.9 6-3 8.4"/><path d="M16 3.6c-2.1 2.4-3 5.2-3 8.4s.9 6 3 8.4"/>',
        '<path d="M6 3v18"/><path d="M18 3v18"/><path d="M6 8h12"/><path d="M6 16h12"/><path d="M3 12h18"/>',
        '<circle cx="12" cy="12" r="9"/><path d="M8 8l8 8"/><path d="M16 8l-8 8"/>',
        '<path d="M6 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M18 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M8.5 9 12 14l3.5-5"/><path d="M12 14h-2"/><path d="M14 14h-2"/>',
    ];

    echo '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icons[$index % count($icons)] . '</svg>';
}

function kitscore_render_product_card(int $post_id): void
{
    $score = (float) kitscore_get_field_value('gs_score', $post_id, 0);
    $price = kitscore_get_field_value('gs_price', $post_id, '');
    $brand = kitscore_get_field_value('gs_brand', $post_id, '');
    $compare_url = add_query_arg('products[]', $post_id, home_url('/compare/'));
    ?>
    <article class="gs-product-card">
        <a class="gs-product-media" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <img src="<?php echo esc_url(kitscore_product_image_url($post_id)); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy">
        </a>
        <a class="gs-button gs-quick-compare" href="<?php echo esc_url($compare_url); ?>"><?php esc_html_e('Compare', 'kitscore'); ?></a>
        <div class="gs-product-body">
            <div class="gs-card-top">
                <div>
                    <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
                    <div class="gs-product-meta">
                        <?php if ($brand) : ?><span><?php echo esc_html($brand); ?></span><?php endif; ?>
                        <?php if ($price) : ?><span><?php echo esc_html($price); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php kitscore_score_badge($score); ?>
            </div>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 18)); ?></p>
        </div>
    </article>
    <?php
}

function kitscore_get_total_review_count(int $post_id): int
{
    $acf_review_count = 0;

    if (function_exists('have_rows')) {
        while (have_rows('user_reviews', $post_id)) {
            the_row();
            $acf_review_count++;
        }

        if (function_exists('reset_rows')) {
            reset_rows();
        }
    }

    return $acf_review_count + (int) get_comments_number($post_id);
}

function kitscore_render_home_product_card(int $post_id, ?int $review_count = null): void
{
    $score = (float) kitscore_get_field_value(kitscore_score_meta_key(), $post_id, 0);
    $terms = get_the_terms($post_id, 'sport_category');
    $category = (!is_wp_error($terms) && !empty($terms)) ? $terms[0] : null;
    $review_count = $review_count ?? kitscore_get_total_review_count($post_id);
    ?>
    <article class="gs-product-card gs-home-product-card">
        <a class="gs-product-media" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <img src="<?php echo esc_url(kitscore_product_image_url($post_id)); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy">
            <span class="gs-home-score-badge" aria-label="<?php echo esc_attr(sprintf(__('Score %s', 'kitscore'), number_format($score, 1))); ?>">
                <strong><?php echo esc_html(number_format($score, 1)); ?></strong>
                <span><?php esc_html_e('Score', 'kitscore'); ?></span>
            </span>
        </a>
        <div class="gs-product-body">
            <?php if ($category) : ?>
                <a class="gs-product-category" href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
            <?php endif; ?>
            <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
            <div class="gs-stars" aria-label="<?php esc_attr_e('Four out of five stars', 'kitscore'); ?>">
                <span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
                <?php if ($review_count > 0) : ?>
                    <small><?php echo esc_html(sprintf(_n('%d review', '%d reviews', $review_count, 'kitscore'), $review_count)); ?></small>
                <?php else : ?>
                    <small class="gs-no-reviews"><?php esc_html_e('Be the first to review', 'kitscore'); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}

add_action('wp_ajax_ks_get_products_by_cat', 'kitscore_ajax_products_by_cat');
add_action('wp_ajax_nopriv_ks_get_products_by_cat', 'kitscore_ajax_products_by_cat');
function kitscore_ajax_products_by_cat(): void
{
    $slug = isset($_GET['cat']) ? sanitize_text_field(wp_unslash($_GET['cat'])) : '';
    $products = [];

    if ($slug) {
        $posts = get_posts([
            'post_type'      => 'product_review',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'tax_query'      => [[
                'taxonomy' => 'sport_category',
                'field'    => 'slug',
                'terms'    => $slug,
            ]],
        ]);

        foreach ($posts as $post) {
            $products[] = ['id' => $post->ID, 'title' => get_the_title($post)];
        }
    }

    wp_send_json($products);
}

add_action('wp_ajax_ks_load_product_reviews', 'kitscore_ajax_load_product_reviews');
add_action('wp_ajax_nopriv_ks_load_product_reviews', 'kitscore_ajax_load_product_reviews');
function kitscore_ajax_load_product_reviews(): void
{
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

    error_log('[KitScore] ks_load_product_reviews received product_id=' . $product_id);

    if (!$product_id) {
        wp_send_json_error(['message' => 'No product_id received'], 400);
        return;
    }

    $post = get_post($product_id);
    if (!$post || $post->post_type !== 'product_review' || $post->post_status !== 'publish') {
        error_log('[KitScore] ks_load_product_reviews: invalid product for id=' . $product_id);
        wp_send_json_error(['message' => 'Invalid product'], 404);
        return;
    }

    $reviews = function_exists('kitscore_get_review_rows') ? kitscore_get_review_rows($product_id) : [];

    error_log('[KitScore] ks_load_product_reviews: returning ' . count($reviews) . ' reviews for product_id=' . $product_id);

    wp_send_json_success([
        'product_id'    => $product_id,
        'product_title' => get_the_title($post),
        'post_link'     => get_permalink($product_id),
        'image_url'     => kitscore_product_image_url($product_id),
        'reviews'       => $reviews,
        'count'         => count($reviews),
    ]);
}

add_action('admin_init', 'kitscore_cleanup_anon_comments_once');
function kitscore_cleanup_anon_comments_once(): void
{
    if (get_option('kitscore_anon_cleanup_done')) {
        return;
    }
    global $wpdb;
    $ids = $wpdb->get_col(
        "SELECT comment_ID FROM {$wpdb->comments}
         WHERE (comment_author = 'Anonymous' OR comment_content = '')
         AND LENGTH(comment_content) < 20"
    );
    foreach ($ids as $id) {
        wp_delete_comment((int) $id, true);
    }
    update_option('kitscore_anon_cleanup_done', true);
}

add_action('pre_get_posts', 'kitscore_apply_product_filters');
function kitscore_apply_product_filters(WP_Query $query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!$query->is_post_type_archive('product_review') && !$query->is_tax('sport_category')) {
        return;
    }

    $tax_query = (array) $query->get('tax_query');

    foreach (['sport_category', 'brand', 'price_range'] as $taxonomy) {
        if (!empty($_GET[$taxonomy])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => sanitize_text_field(wp_unslash($_GET[$taxonomy])),
            ];
        }
    }

    if ($tax_query) {
        $query->set('tax_query', $tax_query);
    }

    if (!empty($_GET['min_rating'])) {
        $query->set('meta_query', [[
            'key' => 'gs_score',
            'value' => (float) $_GET['min_rating'],
            'compare' => '>=',
            'type' => 'NUMERIC',
        ]]);
    }
}
