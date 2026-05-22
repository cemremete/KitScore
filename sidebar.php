<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_sport = get_query_var('sport_category');
?>
<aside class="gs-filter-sidebar" aria-label="<?php esc_attr_e('Review filters', 'kitscore'); ?>">
    <h2><?php esc_html_e('Filter Reviews', 'kitscore'); ?></h2>
    <form method="get">
        <?php if (!$current_sport) : ?>
            <div class="gs-filter-group">
                <label for="sport_category"><?php esc_html_e('Sport Type', 'kitscore'); ?></label>
                <?php wp_dropdown_categories([
                    'taxonomy' => 'sport_category',
                    'name' => 'sport_category',
                    'id' => 'sport_category',
                    'show_option_all' => __('All sports', 'kitscore'),
                    'hide_empty' => false,
                    'value_field' => 'slug',
                    'selected' => isset($_GET['sport_category']) ? sanitize_text_field(wp_unslash($_GET['sport_category'])) : '',
                ]); ?>
            </div>
        <?php endif; ?>
        <div class="gs-filter-group">
            <label for="price_range"><?php esc_html_e('Price Range', 'kitscore'); ?></label>
            <?php wp_dropdown_categories([
                'taxonomy' => 'price_range',
                'name' => 'price_range',
                'id' => 'price_range',
                'show_option_all' => __('All prices', 'kitscore'),
                'hide_empty' => false,
                'value_field' => 'slug',
                'selected' => isset($_GET['price_range']) ? sanitize_text_field(wp_unslash($_GET['price_range'])) : '',
            ]); ?>
        </div>
        <div class="gs-filter-group">
            <label for="brand"><?php esc_html_e('Brand', 'kitscore'); ?></label>
            <?php wp_dropdown_categories([
                'taxonomy' => 'brand',
                'name' => 'brand',
                'id' => 'brand',
                'show_option_all' => __('All brands', 'kitscore'),
                'hide_empty' => false,
                'value_field' => 'slug',
                'selected' => isset($_GET['brand']) ? sanitize_text_field(wp_unslash($_GET['brand'])) : '',
            ]); ?>
        </div>
        <div class="gs-filter-group">
            <label for="min_rating"><?php esc_html_e('Minimum Rating', 'kitscore'); ?></label>
            <input id="min_rating" name="min_rating" type="number" min="0" max="10" step="0.5" value="<?php echo isset($_GET['min_rating']) ? esc_attr((float) $_GET['min_rating']) : ''; ?>" placeholder="8.0">
        </div>
        <button class="gs-button" type="submit"><?php esc_html_e('Apply Filters', 'kitscore'); ?></button>
    </form>
</aside>
