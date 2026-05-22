<?php
get_header();
get_template_part('template-parts/breadcrumbs');
?>

<section class="gs-section">
    <div class="gs-container">
        <div class="gs-section-title">
            <h1><?php single_term_title(); ?></h1>
            <a href="<?php echo esc_url(home_url('/compare/')); ?>"><?php esc_html_e('Compare products', 'kitscore'); ?></a>
        </div>
        <div class="gs-layout gs-layout-with-sidebar">
            <?php get_sidebar(); ?>
            <div class="gs-grid gs-product-grid">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <?php kitscore_render_product_card(get_the_ID()); ?>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p><?php esc_html_e('No reviews match the selected filters.', 'kitscore'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
