<?php
get_header();

$reviews_url = get_post_type_archive_link('product_review') ?: home_url('/reviews/');
$categories = get_terms([
    'taxonomy' => 'sport_category',
    'hide_empty' => false,
    'orderby' => 'name',
]);

if (is_wp_error($categories)) {
    $categories = [];
}

$sport_icons = [
    'basketball' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93c4.08 4.08 10.07 4.08 14.14 0"/><path d="M4.93 19.07c4.08-4.08 10.07-4.08 14.14 0"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>',
    'camping-outdoors' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l4-8 4 4 4-6 4 10"/><path d="M3 17h18"/><circle cx="18" cy="6" r="2"/></svg>',
    'cycling' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
    'fitness-gym' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11"/><path d="M6.5 17.5h11"/><path d="M3 9.5h3v5H3z"/><path d="M18 9.5h3v5h-3z"/><path d="M6.5 12h11"/></svg>',
    'football-soccer' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    'golf' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 18V2l7 4-7 4"/><circle cx="12" cy="21" r="1"/><line x1="12" y1="18" x2="12" y2="20"/></svg>',
    'hiking' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M10.5 8.5l-3 9 2.5-1 2 4 2-4 2.5 1-3-9"/><path d="M8 20l-2 2M16 20l2 2"/></svg>',
    'martial-arts-boxing' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 11V6a2 2 0 0 0-4 0v0"/><path d="M14 10V4a2 2 0 0 0-4 0v2"/><path d="M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/></svg>',
    'running' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="13" cy="4" r="2"/><path d="M6 20l2-4 3 2 2-4 4 4"/><path d="M7.5 13l2-3.5L12 11l2.5-4.5"/></svg>',
    'skiing-snow-sports' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M3 20l5-5 3 3 5-8 3 4"/><path d="M3 20h18"/></svg>',
    'swimming' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M2 16c2-2 4 0 6 0s4-2 6 0 4 2 6 0"/><circle cx="17" cy="6" r="2"/><path d="M13 6l-3 6"/></svg>',
    'team-sports' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="2"/><circle cx="15" cy="7" r="2"/><path d="M6 20v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M3 20v-2a2 2 0 0 1 2-2"/><path d="M21 20v-2a2 2 0 0 1-2-2"/></svg>',
    'tennis-racket-sports' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16" y1="16" x2="22" y2="22"/><line x1="8" y1="11" x2="14" y2="11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>',
    'water-sports' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12c2-4 6-4 8 0s6 4 8 0"/><path d="M2 17c2-4 6-4 8 0s6 4 8 0"/><path d="M2 7c2-4 6-4 8 0s6 4 8 0"/></svg>',
    'yoga-pilates' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M5 20l4-8 3 4 3-4 4 8"/><path d="M8 12l-3 2M16 12l3 2"/></svg>',
];
$default_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
?>

<section class="gs-hero gs-home-hero" aria-label="<?php esc_attr_e('KitScore hero', 'kitscore'); ?>">
  <!-- Slideshow images — all stacked absolutely, only the active one is visible -->
  <div class="gs-hero-slides" aria-hidden="true">
    <img class="gs-hero-slide is-active" src="https://images.unsplash.com/photo-1606925797300-0b35e9d1794e?w=1600&q=80" alt="">
    <img class="gs-hero-slide" src="https://images.unsplash.com/photo-1560090995-01632a28895b?w=1600&q=80" alt="">
    <img class="gs-hero-slide" src="https://images.unsplash.com/photo-1547347298-4074fc3086f0?w=1600&q=80" alt="">
    <img class="gs-hero-slide" src="https://images.unsplash.com/photo-1534787238916-9ba6764efd4f?w=1600&q=80" alt="">
    <img class="gs-hero-slide" src="https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=1600&q=80" alt="">
  </div>
  <div class="gs-hero-overlay" aria-hidden="true"></div>
  <div class="gs-container gs-hero-inner">
    <div class="gs-hero-content">
      <h1>Expert Reviews for Every Athlete</h1>
      <p><?php esc_html_e('In-depth analysis and scoring of the latest sports equipment to help you perform at your best', 'kitscore'); ?></p>
      <div class="gs-hero-actions">
        <a class="gs-button" href="<?php echo esc_url($reviews_url); ?>"><?php esc_html_e('Browse Reviews', 'kitscore'); ?></a>
        <a class="gs-button gs-button-secondary" href="<?php echo esc_url(get_permalink(get_page_by_path('compare'))); ?>"><?php esc_html_e('Compare', 'kitscore'); ?></a>
      </div>
    </div>
  </div>
  <!-- Dot indicators -->
  <div class="gs-hero-dots" role="tablist" aria-label="<?php esc_attr_e('Slideshow navigation', 'kitscore'); ?>">
    <button class="gs-hero-dot is-active" role="tab" aria-selected="true"  aria-label="<?php esc_attr_e('Slide 1 of 5', 'kitscore'); ?>" data-idx="0"></button>
    <button class="gs-hero-dot"           role="tab" aria-selected="false" aria-label="<?php esc_attr_e('Slide 2 of 5', 'kitscore'); ?>" data-idx="1"></button>
    <button class="gs-hero-dot"           role="tab" aria-selected="false" aria-label="<?php esc_attr_e('Slide 3 of 5', 'kitscore'); ?>" data-idx="2"></button>
    <button class="gs-hero-dot"           role="tab" aria-selected="false" aria-label="<?php esc_attr_e('Slide 4 of 5', 'kitscore'); ?>" data-idx="3"></button>
    <button class="gs-hero-dot"           role="tab" aria-selected="false" aria-label="<?php esc_attr_e('Slide 5 of 5', 'kitscore'); ?>" data-idx="4"></button>
  </div>
</section>

<script>
(function () {
  var slides  = [].slice.call(document.querySelectorAll('.gs-hero-slide'));
  var dots    = [].slice.call(document.querySelectorAll('.gs-hero-dot'));
  if (!slides.length) return;
  var current = 0, timer;

  function goTo(i) {
    slides[current].classList.remove('is-active');
    dots[current].classList.remove('is-active');
    dots[current].setAttribute('aria-selected', 'false');
    current = ((i % slides.length) + slides.length) % slides.length;
    slides[current].classList.add('is-active');
    dots[current].classList.add('is-active');
    dots[current].setAttribute('aria-selected', 'true');
  }

  function next() { goTo(current + 1); }

  timer = setInterval(next, 4000);

  dots.forEach(function (dot, idx) {
    dot.addEventListener('click', function () {
      clearInterval(timer);
      goTo(idx);
      timer = setInterval(next, 4000);
    });
  });
})();
</script>

<section class="gs-section gs-featured-categories" id="browse-by-sport">
    <div class="gs-container">
        <div class="gs-section-title">
            <h2><?php esc_html_e('Browse by Sport', 'kitscore'); ?></h2>
        </div>
        <div class="gs-grid gs-category-grid">
            <?php foreach ($categories as $index => $category) : ?>
                <?php
                $fresh_category = get_term($category->term_id, 'sport_category');
                $review_count = (!is_wp_error($fresh_category) && $fresh_category) ? (int) $fresh_category->count : (int) $category->count;
                ?>
                <a class="gs-category-card" href="<?php echo esc_url(get_term_link($category)); ?>">
                    <span class="gs-cat-icon <?php echo $index % 2 ? 'is-orange' : 'is-blue'; ?>" aria-hidden="true">
                        <?php echo $sport_icons[$category->slug] ?? $default_icon; ?>
                    </span>
                    <h3><?php echo esc_html($category->name); ?></h3>
                    <span class="gs-category-subtitle"><?php esc_html_e('Explore reviews', 'kitscore'); ?></span>
                    <span class="gs-category-count"><?php echo esc_html(sprintf(_n('%d review', '%d reviews', $review_count, 'kitscore'), $review_count)); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="gs-section gs-top-rated" id="top-rated-equipment">
    <div class="gs-container">
        <div class="gs-section-title">
            <h2><?php esc_html_e('Top Rated Equipment', 'kitscore'); ?></h2>
            <a class="gs-view-all" href="<?php echo esc_url($reviews_url); ?>"><?php esc_html_e('View All', 'kitscore'); ?> <span aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="gs-grid gs-product-grid">
            <?php
            $top_rated = new WP_Query([
                'post_type' => 'product_review',
                'posts_per_page' => 4,
                'no_found_rows' => true,
                'meta_key' => kitscore_score_meta_key(),
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
            ]);

            while ($top_rated->have_posts()) :
                $top_rated->the_post();
                kitscore_render_home_product_card(get_the_ID());
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
