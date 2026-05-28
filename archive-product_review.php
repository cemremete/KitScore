<?php
/**
 * Product review archive.
 */

get_header();
get_template_part('template-parts/breadcrumbs');

$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = [
    'post_type'      => 'product_review',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'meta_value_num',
    'meta_key'       => 'score',
    'order'          => 'DESC',
];
$reviews_query = new WP_Query($args);
$category_count = wp_count_terms('sport_category');
if (is_wp_error($category_count)) {
    $category_count = 0;
}
?>

<div class="gs-container" style="padding: 40px 0 80px;">
    <div style="margin-bottom:32px;">
        <h1 style="font-size:2rem;font-weight:700;color:var(--gs-text);margin-bottom:8px;"><?php esc_html_e('All Reviews', 'kitscore'); ?></h1>
        <p style="color:var(--gs-muted);font-size:15px;">
            <?php
            echo esc_html(sprintf(
                _n(
                    '%1$d product reviewed across %2$d sport categories',
                    '%1$d products reviewed across %2$d sport categories',
                    (int) $reviews_query->found_posts,
                    'kitscore'
                ),
                (int) $reviews_query->found_posts,
                (int) $category_count
            ));
            ?>
        </p>
    </div>

    <div class="gs-products-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;">
        <?php if ($reviews_query->have_posts()) : ?>
            <?php while ($reviews_query->have_posts()) : $reviews_query->the_post(); ?>
                <?php kitscore_render_product_card(get_the_ID()); ?>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php esc_html_e('No reviews found.', 'kitscore'); ?></p>
        <?php endif; ?>
    </div>

    <div style="margin-top:40px;text-align:center;">
        <?php
        echo paginate_links([
            'total'     => $reviews_query->max_num_pages,
            'current'   => $paged,
            'prev_text' => __('&larr; Previous', 'kitscore'),
            'next_text' => __('Next &rarr;', 'kitscore'),
        ]);
        ?>
    </div>
</div>

<?php get_footer(); ?>
