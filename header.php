<?php
/**
 * Site header.
 */

if (!defined('ABSPATH')) {
    exit;
}

$reviews_url = get_post_type_archive_link('product_review') ?: home_url('/reviews/');
$categories_url = get_terms([
    'taxonomy' => 'sport_category',
    'hide_empty' => false,
    'number' => 1,
]);

if (!is_wp_error($categories_url) && !empty($categories_url)) {
    $term_link = get_term_link($categories_url[0]);
    $categories_url = is_wp_error($term_link) ? $reviews_url : $term_link;
} else {
    $categories_url = $reviews_url;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
    (function() {
      function kitscoreWantsDark() {
        try {
          var saved = localStorage.getItem('ks-dark');
          var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
          return saved !== null ? saved === 'true' : prefersDark;
        } catch (error) {
          return false;
        }
      }

      window.kitscoreWantsDark = kitscoreWantsDark;
      if (kitscoreWantsDark()) {
        document.documentElement.classList.add('dark-mode');
      }
    })();
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<script>
(function() {
  if (window.kitscoreWantsDark && window.kitscoreWantsDark()) {
    document.body.classList.add('dark-mode');
  }
})();
</script>
<?php wp_body_open(); ?>
<header class="gs-site-header">
    <div class="gs-container gs-header-inner">
        <a class="gs-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('KitScore home', 'kitscore'); ?>">
            <span class="gs-logo-mark" aria-hidden="true"><?php kitscore_trophy_icon(); ?></span>
            <span>KitScore</span>
        </a>
        <div class="gs-nav-search-wrap">
            <form role="search" method="get" class="gs-nav-search" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="screen-reader-text" for="gs-header-search"><?php esc_html_e('Search reviews', 'kitscore'); ?></label>
                <span class="gs-search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </span>
                <input id="gs-header-search" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search gear, brands, sports...', 'kitscore'); ?>">
                <input type="hidden" name="post_type" value="product_review">
            </form>
        </div>
        <nav class="gs-main-nav" aria-label="<?php esc_attr_e('Primary navigation', 'kitscore'); ?>">
            <ul class="gs-nav-row">
                <li><a class="<?php echo is_post_type_archive('product_review') || is_singular('product_review') ? 'is-active' : ''; ?>" href="<?php echo esc_url($reviews_url); ?>"><?php esc_html_e('Reviews', 'kitscore'); ?></a></li>
                <li><a class="<?php echo is_tax('sport_category') ? 'is-active' : ''; ?>" href="<?php echo esc_url($categories_url); ?>"><?php esc_html_e('Categories', 'kitscore'); ?></a></li>
                <li><a class="gs-nav-cta <?php echo is_page('compare') ? 'is-active' : ''; ?>" href="<?php echo esc_url(home_url('/compare/')); ?>"><?php esc_html_e('Compare Now', 'kitscore'); ?></a></li>
                <li>
                    <button id="gs-dark-toggle" aria-label="Toggle dark mode" style="background:transparent;border:1.5px solid rgba(255,255,255,0.3);border-radius:8px;width:40px;height:40px;cursor:pointer;color:white;display:flex;align-items:center;justify-content:center;margin-left:12px;flex-shrink:0;transition:border-color 0.2s;">
                      <span id="gs-icon-light" style="display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                      </span>
                      <span id="gs-icon-dark" style="display:none;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                      </span>
                    </button>
                    <script>
                    (function() {
                      document.addEventListener('DOMContentLoaded', function() {
                        var toggle = document.getElementById('gs-dark-toggle');
                        var iconLight = document.getElementById('gs-icon-light');
                        var iconDark = document.getElementById('gs-icon-dark');
                        var isDark = window.kitscoreWantsDark ? window.kitscoreWantsDark() : document.body.classList.contains('dark-mode');

                        if (!toggle || !iconLight || !iconDark) {
                          return;
                        }

                        function applyDark(dark) {
                          document.documentElement.classList.toggle('dark-mode', dark);
                          document.body.classList.toggle('dark-mode', dark);
                          iconLight.style.display = dark ? 'none' : 'flex';
                          iconDark.style.display = dark ? 'flex' : 'none';
                        }

                        applyDark(isDark);

                        toggle.addEventListener('click', function() {
                          isDark = !isDark;
                          localStorage.setItem('ks-dark', isDark);
                          applyDark(isDark);
                        });
                      });
                    })();
                    </script>
                </li>
            </ul>
        </nav>
    </div>
</header>
<main id="content">
