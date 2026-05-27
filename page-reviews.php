<?php
/**
 * Template Name: Reviews
 */

get_header();

/* Helper: get review rows from user_reviews meta only. */
if (!function_exists('kitscore_get_review_rows')) {
    function kitscore_get_review_rows(int $post_id): array
    {
        // 1. ACF repeater
        $rows = function_exists('get_field') ? get_field('user_reviews', $post_id) : [];
        if (!is_array($rows)) {
            $rows = [];
        }

        if ($rows) {
            $normalized = [];
            foreach ($rows as $row) {
                $normalized[] = [
                    'reviewer_name'   => trim((string) ($row['reviewer_name'] ?? '')),
                    'reviewer_rating' => (int) ($row['reviewer_rating'] ?? 0),
                    'review_date'     => (string) ($row['review_date'] ?? ''),
                    'review_text'     => (string) ($row['review_text'] ?? ''),
                    'verified'        => !empty($row['verified_purchase']) || !empty($row['verified']),
                ];
            }
            $rows = $normalized;
        } else {
            $stored_reviews = get_post_meta($post_id, 'user_reviews', true);

            if (is_array($stored_reviews)) {
                foreach ($stored_reviews as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $rows[] = [
                        'reviewer_name'   => trim((string) ($row['reviewer_name'] ?? '')),
                        'reviewer_rating' => (int) ($row['reviewer_rating'] ?? 0),
                        'review_date'     => (string) ($row['review_date'] ?? ''),
                        'review_text'     => (string) ($row['review_text'] ?? ''),
                        'verified'        => !empty($row['verified_purchase']) || !empty($row['verified']),
                    ];
                }
            } else {
                // 2. ACF repeater row meta
                $count = (int) $stored_reviews;
                for ($i = 0; $i < $count; $i++) {
                    $rows[] = [
                        'reviewer_name'   => (string) get_post_meta($post_id, "user_reviews_{$i}_reviewer_name", true),
                        'reviewer_rating' => (int) get_post_meta($post_id, "user_reviews_{$i}_reviewer_rating", true),
                        'review_date'     => (string) get_post_meta($post_id, "user_reviews_{$i}_review_date", true),
                        'review_text'     => (string) get_post_meta($post_id, "user_reviews_{$i}_review_text", true),
                        'verified'        => (bool) get_post_meta($post_id, "user_reviews_{$i}_verified_purchase", true),
                    ];
                }
            }
        }

        return array_values(array_filter($rows, static fn($r) => is_array($r) && !empty($r['review_text'])));
    }
}

if (!function_exists('kitscore_rev_initials')) {
    function kitscore_rev_initials(string $name): string
    {
        $parts    = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach (array_slice(array_filter((array) $parts), 0, 2) as $p) {
            $initials .= strtoupper(substr($p, 0, 1));
        }
        return $initials ?: 'KS';
    }
}

if (!function_exists('kitscore_rev_stars_html')) {
    function kitscore_rev_stars_html(int $rating): string
    {
        $r = max(1, min(5, $rating));
        return str_repeat('&#9733;', $r) . str_repeat('&#9734;', 5 - $r);
    }
}

/* ── Data ── */
$selected_category = isset($_GET['sport_category']) ? sanitize_text_field(wp_unslash($_GET['sport_category'])) : '';
$selected_product  = isset($_GET['product_id'])     ? absint($_GET['product_id']) : 0;
$reviews_action    = get_post_type_archive_link('product_review') ?: home_url('/reviews/');

$categories = get_terms(['taxonomy' => 'sport_category', 'hide_empty' => true, 'orderby' => 'name']);
if (is_wp_error($categories)) {
    $categories = [];
}

/* PHP-rendered products map (used for fast initial render) */
$all_products = get_posts([
    'post_type'      => 'product_review',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);

$products_by_category = [];
foreach ($all_products as $prod) {
    $terms = get_the_terms($prod->ID, 'sport_category');
    if (is_wp_error($terms) || !$terms) {
        continue;
    }
    foreach ($terms as $term) {
        $products_by_category[$term->slug][] = ['id' => $prod->ID, 'title' => get_the_title($prod)];
    }
}

/* Reviews for selected product */
$reviews       = $selected_product ? kitscore_get_review_rows($selected_product) : [];
$review_count  = count($reviews);
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$rating_total  = 0;

foreach ($reviews as $r) {
    $star = max(1, min(5, (int) ($r['reviewer_rating'] ?? 0)));
    $rating_counts[$star]++;
    $rating_total += $star;
}

$average_rating = $review_count ? $rating_total / $review_count : 0;
$product_title  = $selected_product ? get_the_title($selected_product) : '';
?>

<div class="gs-container gs-reviews-page">

    <!-- Page header -->
    <div class="gs-reviews-page-header">
        <h1 class="gs-reviews-page-title"><?php esc_html_e('Product Reviews', 'kitscore'); ?></h1>
        <p class="gs-reviews-page-subtitle"><?php esc_html_e('Select a category and product to read reviews.', 'kitscore'); ?></p>
    </div>

    <!-- Two-step selector card -->
    <div class="gs-reviews-selector-card">
        <form class="gs-reviews-form" method="get" action="<?php echo esc_url($reviews_action); ?>">
            <div class="gs-reviews-steps">
                <div class="gs-reviews-step">
                    <label for="ks-cat-select" class="gs-reviews-step-label">
                        <span class="gs-step-num" aria-hidden="true">1</span>
                        <?php esc_html_e('Sport Category', 'kitscore'); ?>
                    </label>
                    <select id="ks-cat-select" name="sport_category" class="gs-reviews-select">
                        <option value=""><?php esc_html_e('Select a sport…', 'kitscore'); ?></option>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($selected_category, $cat->slug); ?>>
                                <?php echo esc_html($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="gs-reviews-step">
                    <label for="ks-prod-select" class="gs-reviews-step-label">
                        <span class="gs-step-num" aria-hidden="true">2</span>
                        <?php esc_html_e('Product', 'kitscore'); ?>
                    </label>
                    <select id="ks-prod-select" name="product_id" class="gs-reviews-select">
                        <option value=""><?php esc_html_e('Select a product…', 'kitscore'); ?></option>
                    </select>
                </div>

                <div class="gs-reviews-submit-wrap">
                    <button type="submit" class="gs-reviews-load-btn">
                        <?php esc_html_e('Load Reviews', 'kitscore'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($selected_product && $review_count) : ?>

        <!-- Summary card -->
        <section class="gs-rev-summary-card" aria-label="<?php esc_attr_e('Review summary', 'kitscore'); ?>">
            <?php if ($product_title) : ?>
                <h2 class="gs-rev-summary-product">
                    <a href="<?php echo esc_url(get_permalink($selected_product)); ?>"><?php echo esc_html($product_title); ?></a>
                </h2>
            <?php endif; ?>
            <div class="gs-rev-summary-inner">
                <div class="gs-rev-summary-score">
                    <div class="gs-rev-avg-num" aria-label="<?php echo esc_attr(number_format($average_rating, 1) . ' out of 5'); ?>">
                        <?php echo esc_html(number_format($average_rating, 1)); ?>
                    </div>
                    <div class="gs-rev-avg-stars" aria-hidden="true">
                        <?php echo kitscore_rev_stars_html((int) round($average_rating)); ?>
                    </div>
                    <div class="gs-rev-avg-count">
                        <?php echo esc_html(sprintf(_n('%d review', '%d reviews', $review_count, 'kitscore'), $review_count)); ?>
                    </div>
                </div>
                <div class="gs-rev-rating-bars" aria-label="<?php esc_attr_e('Rating breakdown', 'kitscore'); ?>">
                    <?php foreach ([5, 4, 3, 2, 1] as $star) :
                        $pct = $review_count ? round(($rating_counts[$star] / $review_count) * 100) : 0; ?>
                        <button class="gs-rev-bar-row" type="button" data-star-filter="<?php echo esc_attr($star); ?>" aria-pressed="false">
                            <span class="gs-rev-bar-label"><?php echo esc_html($star); ?> &#9733;</span>
                            <span class="gs-rev-bar-track" role="progressbar" aria-valuenow="<?php echo esc_attr($pct); ?>" aria-valuemin="0" aria-valuemax="100">
                                <span class="gs-rev-bar-fill" style="width:<?php echo esc_attr($pct); ?>%"></span>
                            </span>
                            <span class="gs-rev-bar-pct">
                                <?php echo esc_html($pct); ?>%
                                <span class="gs-rev-clear-filter"><?php esc_html_e('Clear filter', 'kitscore'); ?></span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Review cards grid -->
        <p class="gs-rev-filter-status" hidden></p>
        <p class="gs-rev-no-filter-results" hidden><?php esc_html_e('No reviews with this rating yet.', 'kitscore'); ?></p>
        <div class="gs-rev-grid" role="list" aria-label="<?php esc_attr_e('User reviews', 'kitscore'); ?>">
            <?php foreach ($reviews as $rev) :
                $text = (string) ($rev['review_text'] ?? '');
                if (empty(trim($text))) { continue; }
                $name     = trim((string) ($rev['reviewer_name'] ?? __('Verified Reviewer', 'kitscore')));
                $rating   = max(1, min(5, (int) ($rev['reviewer_rating'] ?? 4)));
                $raw_date = (string) ($rev['review_date'] ?? '');
                $date_fmt = $raw_date ? date_i18n(get_option('date_format'), strtotime($raw_date)) : '';
                $verified = !empty($rev['verified']);
            ?>
                <article class="gs-rev-card" role="listitem" data-rating="<?php echo esc_attr($rating); ?>">
                    <div class="gs-rev-card-top">
                        <span class="gs-rev-avatar" aria-hidden="true"><?php echo esc_html(kitscore_rev_initials($name)); ?></span>
                        <div class="gs-rev-meta">
                            <strong class="gs-rev-name"><?php echo esc_html($name); ?></strong>
                            <?php if ($date_fmt) : ?>
                                <span class="gs-rev-date"><?php echo esc_html($date_fmt); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($verified) : ?>
                            <span class="gs-rev-verified"><?php esc_html_e('Verified', 'kitscore'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="gs-rev-stars-row" aria-label="<?php echo esc_attr(sprintf(__('%d out of 5 stars', 'kitscore'), $rating)); ?>">
                        <?php echo kitscore_rev_stars_html($rating); ?>
                    </div>
                    <p class="gs-rev-text"><?php echo esc_html($text); ?></p>
                </article>
            <?php endforeach; ?>
        </div>

    <?php elseif ($selected_product) : ?>

        <!-- Empty state -->
        <div class="gs-rev-empty">
            <div class="gs-rev-empty-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <line x1="9" y1="10" x2="9.01" y2="10"/>
                    <line x1="12" y1="10" x2="12.01" y2="10"/>
                    <line x1="15" y1="10" x2="15.01" y2="10"/>
                </svg>
            </div>
            <p class="gs-rev-empty-msg"><?php esc_html_e('No reviews available for this product yet.', 'kitscore'); ?></p>
            <p class="gs-rev-empty-note"><?php esc_html_e('Reviews are sourced from Decathlon product pages.', 'kitscore'); ?></p>
        </div>

    <?php endif; ?>

</div>

<script>
(function () {
  var catSel  = document.getElementById('ks-cat-select');
  var prodSel = document.getElementById('ks-prod-select');
  var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
  /* Pre-loaded map for instant first render */
  var localMap = <?php echo wp_json_encode($products_by_category); ?>;
  var preselect = String(<?php echo (int) $selected_product; ?>);

  function renderOptions(products) {
    prodSel.innerHTML = '<option value=""><?php echo esc_js(__('Select a product…', 'kitscore')); ?></option>';
    products.forEach(function (p) {
      var o = document.createElement('option');
      o.value = String(p.id);
      o.textContent = p.title;
      o.selected = o.value === preselect;
      prodSel.appendChild(o);
    });
  }

  function loadFromAjax(catSlug) {
    fetch(ajaxUrl + '?action=ks_get_products_by_cat&cat=' + encodeURIComponent(catSlug))
      .then(function (r) { return r.json(); })
      .then(renderOptions)
      .catch(function () { renderOptions(localMap[catSlug] || []); });
  }

  /* Initial populate from local map (no network round-trip) */
  renderOptions(localMap[catSel.value] || []);

  /* On category change: fetch via AJAX */
  catSel.addEventListener('change', function () {
    preselect = '';
    var slug = catSel.value;
    if (!slug) {
      renderOptions([]);
      return;
    }
    loadFromAjax(slug);
  });

  var ratingRows = [].slice.call(document.querySelectorAll('.gs-rev-bar-row[data-star-filter]'));
  var reviewCards = [].slice.call(document.querySelectorAll('.gs-rev-card[data-rating]'));
  var filterStatus = document.querySelector('.gs-rev-filter-status');
  var noResults = document.querySelector('.gs-rev-no-filter-results');
  var activeRating = null;

  function updateRatingFilter(nextRating) {
    activeRating = activeRating === nextRating ? null : nextRating;

    var visibleCount = 0;
    reviewCards.forEach(function (card) {
      var isVisible = !activeRating || card.getAttribute('data-rating') === activeRating;
      card.hidden = !isVisible;
      if (isVisible) {
        visibleCount++;
      }
    });

    ratingRows.forEach(function (row) {
      var isActive = row.getAttribute('data-star-filter') === activeRating;
      row.classList.toggle('is-active', isActive);
      row.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    if (filterStatus) {
      if (activeRating) {
        filterStatus.hidden = false;
        filterStatus.textContent = 'Showing ' + visibleCount + ' ' + (visibleCount === 1 ? 'review' : 'reviews') + ' with ' + activeRating + ' ' + (activeRating === '1' ? 'star' : 'stars');
      } else {
        filterStatus.hidden = true;
        filterStatus.textContent = '';
      }
    }

    if (noResults) {
      noResults.hidden = !activeRating || visibleCount > 0;
      noResults.style.display = activeRating && visibleCount === 0 ? 'block' : 'none';
    }
  }

  ratingRows.forEach(function (row) {
    row.addEventListener('click', function () {
      updateRatingFilter(row.getAttribute('data-star-filter'));
    });
  });

  /* Ensure product_id is present before form submits */
  var reviewsForm = document.querySelector('.gs-reviews-form');
  if (reviewsForm) {
    reviewsForm.addEventListener('submit', function (e) {
      var pid = prodSel.value;
      if (!pid) {
        e.preventDefault();
        prodSel.focus();
        prodSel.classList.add('gs-input-error');
        return;
      }
      prodSel.classList.remove('gs-input-error');
    });
  }

  prodSel.addEventListener('change', function () {
    prodSel.classList.remove('gs-input-error');
  });
})();
</script>

<?php get_footer(); ?>
