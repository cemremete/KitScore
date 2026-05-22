<?php
get_header();
get_template_part('template-parts/breadcrumbs');
?>

<?php while (have_posts()) : the_post(); ?>
    <section class="gs-section">
        <div class="gs-container">
            <article class="gs-review-section">
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>
