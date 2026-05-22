<?php
get_header();
get_template_part('template-parts/breadcrumbs');
?>

<section class="gs-section">
    <div class="gs-container">
        <div class="gs-section-title">
            <h1><?php echo esc_html(sprintf(__('Search results for "%s"', 'kitscore'), get_search_query())); ?></h1>
        </div>
        <div class="gs-grid gs-product-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (get_post_type() === 'product_review') : ?>
                        <?php kitscore_render_product_card(get_the_ID()); ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e('No matching gear reviews found.', 'kitscore'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
