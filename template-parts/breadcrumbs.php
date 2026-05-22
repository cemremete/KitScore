<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<nav class="gs-breadcrumbs gs-container" aria-label="<?php esc_attr_e('Breadcrumb', 'kitscore'); ?>">
    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'kitscore'); ?></a>
    <?php if (is_singular('product_review')) : ?>
        <?php $terms = get_the_terms(get_the_ID(), 'sport_category'); ?>
        <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
            <span> / </span><a href="<?php echo esc_url(get_term_link($terms[0])); ?>"><?php echo esc_html($terms[0]->name); ?></a>
        <?php endif; ?>
        <span> / </span><span><?php the_title(); ?></span>
    <?php elseif (is_tax()) : ?>
        <span> / </span><span><?php single_term_title(); ?></span>
    <?php elseif (is_post_type_archive('product_review')) : ?>
        <span> / </span><span><?php esc_html_e('Reviews', 'kitscore'); ?></span>
    <?php elseif (is_page()) : ?>
        <span> / </span><span><?php the_title(); ?></span>
    <?php endif; ?>
</nav>
