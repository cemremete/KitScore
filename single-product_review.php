<?php
get_header();
get_template_part('template-parts/breadcrumbs');

$post_id = get_the_ID();
$score = (float) kitscore_get_field_value('gs_score', $post_id, 0);
$brand = kitscore_get_field_value('gs_brand', $post_id, '');
$price = kitscore_get_field_value('gs_price', $post_id, '');
$affiliate = kitscore_get_field_value('gs_affiliate_link', $post_id, '');
$pros = kitscore_get_list_field('gs_pros', $post_id);
$cons = kitscore_get_list_field('gs_cons', $post_id);
?>

<?php while (have_posts()) : the_post(); ?>
    <section class="gs-container gs-review-hero">
        <div>
            <h1><?php the_title(); ?></h1>
            <div class="gs-product-meta">
                <?php if ($brand) : ?><span><?php echo esc_html($brand); ?></span><?php endif; ?>
                <?php if ($price) : ?><span><?php echo esc_html($price); ?></span><?php endif; ?>
            </div>
            <div class="gs-review-image">
                <img src="<?php echo esc_url(kitscore_product_image_url($post_id)); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
            </div>
        </div>
        <aside class="gs-panel gs-review-summary">
            <?php kitscore_score_badge($score, __('KitScore rating', 'kitscore')); ?>
            <p><?php echo esc_html(get_the_excerpt()); ?></p>
            <?php if ($affiliate) : ?>
                <a class="gs-button" href="<?php echo esc_url($affiliate); ?>" rel="nofollow sponsored noopener" target="_blank"><?php esc_html_e('View Product', 'kitscore'); ?></a>
            <?php endif; ?>
        </aside>
    </section>

    <section class="gs-section">
        <div class="gs-container">
            <article class="gs-review-section">
                <h2><?php esc_html_e('Review', 'kitscore'); ?></h2>
                <?php the_content(); ?>
            </article>

            <section class="gs-review-section">
                <h2><?php esc_html_e('Pros and Cons', 'kitscore'); ?></h2>
                <div class="gs-pros-cons">
                    <div>
                        <h3><?php esc_html_e('Pros', 'kitscore'); ?></h3>
                        <ul class="gs-list">
                            <?php foreach ($pros as $pro) : ?><li><?php echo esc_html($pro); ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Cons', 'kitscore'); ?></h3>
                        <ul class="gs-list">
                            <?php foreach ($cons as $con) : ?><li><?php echo esc_html($con); ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="gs-review-section">
                <h2><?php esc_html_e('Comparison Snapshot', 'kitscore'); ?></h2>
                <?php echo do_shortcode('[kitscore_comparison ids="' . esc_attr($post_id) . '"]'); ?>
            </section>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>
