<?php
/**
 * Template Name: About KitScore
 */

get_header();
get_template_part('template-parts/breadcrumbs');
?>

<section class="gs-about-hero">
    <div class="gs-container">
        <h1><?php esc_html_e('About KitScore', 'kitscore'); ?></h1>
        <p><?php esc_html_e('An independent UX portfolio project built on WordPress', 'kitscore'); ?></p>
    </div>
</section>

<section class="gs-section gs-about-intro">
    <div class="gs-container gs-about-grid">
        <div>
            <h2><?php esc_html_e('What is KitScore?', 'kitscore'); ?></h2>
            <p><?php esc_html_e('KitScore is a WordPress portfolio project demonstrating UX design, information architecture, content migration, and brand-aligned visual systems. The site reviews sports equipment across 15 categories using a structured scoring methodology.', 'kitscore'); ?></p>
        </div>
        <aside class="gs-about-stats">
            <div><strong>465</strong><span><?php esc_html_e('Products', 'kitscore'); ?></span></div>
            <div><strong>15</strong><span><?php esc_html_e('Categories', 'kitscore'); ?></span></div>
            <div><strong>2-Click</strong><span><?php esc_html_e('Navigation', 'kitscore'); ?></span></div>
        </aside>
    </div>
</section>

<section class="gs-section gs-about-process">
    <div class="gs-container">
        <div class="gs-section-title">
            <h2><?php esc_html_e('How It Works', 'kitscore'); ?></h2>
        </div>
        <div class="gs-grid gs-process-grid">
            <article class="gs-process-card">
                <span>1</span>
                <h3><?php esc_html_e('Choose a sport', 'kitscore'); ?></h3>
                <p><?php esc_html_e('Start from a focused category and browse gear built for that activity.', 'kitscore'); ?></p>
            </article>
            <article class="gs-process-card">
                <span>2</span>
                <h3><?php esc_html_e('Review the score', 'kitscore'); ?></h3>
                <p><?php esc_html_e('Compare products using consistent criteria, clear specs, and concise review notes.', 'kitscore'); ?></p>
            </article>
            <article class="gs-process-card">
                <span>3</span>
                <h3><?php esc_html_e('Compare gear', 'kitscore'); ?></h3>
                <p><?php esc_html_e('Shortlist products side by side and make a confident choice faster.', 'kitscore'); ?></p>
            </article>
        </div>
    </div>
</section>

<section class="gs-section gs-about-tech">
    <div class="gs-container">
        <div class="gs-section-title">
            <h2><?php esc_html_e('Tech Stack', 'kitscore'); ?></h2>
        </div>
        <div class="gs-tech-grid">
            <?php foreach (['WordPress', 'Advanced Custom Fields', 'PHP', 'MySQL', 'LocalWP', 'CSS3'] as $tool) : ?>
                <span><?php echo esc_html($tool); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="gs-section gs-about-disclaimer">
    <div class="gs-container">
        <div class="gs-disclaimer-box">
            <h2><?php esc_html_e('Disclaimer', 'kitscore'); ?></h2>
            <p><?php esc_html_e('This project is independent and is not affiliated with, sponsored by, or endorsed by Decathlon. Product data is sourced from publicly available information on decathlon.com for portfolio demonstration purposes only.', 'kitscore'); ?></p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
