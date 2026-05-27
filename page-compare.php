<?php
get_header();

if (!function_exists('kitscore_compare_field_text')) {
  function kitscore_compare_field_text($value): string {
    if (is_array($value)) {
      $parts = [];
      array_walk_recursive($value, static function ($item) use (&$parts): void {
        if (is_scalar($item)) {
          $parts[] = (string) $item;
        }
      });
      return trim(implode(' ', $parts));
    }

    return trim((string) $value);
  }
}
?>

<div class="gs-container" style="padding: 48px 0 80px;">
  <h1 style="font-size:2rem;font-weight:700;color:var(--gs-text);margin-bottom:8px;">Compare Products</h1>
  <p style="color:var(--gs-muted);margin-bottom:40px;">Select 2 or 3 products to compare side by side.</p>

  <!-- Step 1: Category filter -->
  <div style="margin-bottom:32px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--gs-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Step 1 &mdash; Choose a Sport Category</label>
    <select id="gs-cat-filter" onchange="filterByCategory(this.value)" style="width:100%;max-width:400px;padding:12px 16px;border:1px solid var(--gs-border);border-radius:8px;font-size:15px;color:var(--gs-text);background:var(--gs-card);cursor:pointer;">
      <option value="">&mdash; All Categories &mdash;</option>
      <?php
      $cats = get_terms(['taxonomy'=>'sport_category','hide_empty'=>true,'orderby'=>'name','order'=>'ASC']);
      foreach($cats as $cat):
        $selected_cat = isset($_GET['cat']) ? $_GET['cat'] : '';
      ?>
      <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($selected_cat, $cat->slug); ?>><?php echo esc_html($cat->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Step 2: Product selectors -->
  <div style="margin-bottom:12px;">
    <label style="display:block;font-size:13px;font-weight:600;color:var(--gs-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:16px;">Step 2 &mdash; Select Products to Compare</label>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
      <?php
      $selected_cat = isset($_GET['cat']) ? sanitize_text_field($_GET['cat']) : '';
      $product_args = [
        'post_type' => 'product_review',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
      ];
      if($selected_cat) {
        $product_args['tax_query'] = [[
          'taxonomy' => 'sport_category',
          'field' => 'slug',
          'terms' => $selected_cat,
        ]];
      }
      $all_products = get_posts($product_args);
      for($i = 1; $i <= 3; $i++):
        $selected_id = isset($_GET['p'.$i]) ? intval($_GET['p'.$i]) : 0;
      ?>
      <div>
        <label style="display:block;font-size:13px;font-weight:600;color:var(--gs-muted);margin-bottom:8px;">Product <?php echo $i; ?><?php echo $i === 3 ? ' (optional)' : ''; ?></label>
        <select id="product-<?php echo $i; ?>" class="gs-compare-select" style="width:100%;padding:10px 14px;border:1px solid var(--gs-border);border-radius:8px;font-size:14px;color:var(--gs-text);background:var(--gs-card);cursor:pointer;">
          <option value="">&mdash; Select a product &mdash;</option>
          <?php foreach($all_products as $p): ?>
          <option value="<?php echo $p->ID; ?>" <?php selected($selected_id, $p->ID); ?>><?php echo esc_html($p->post_title); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <div style="margin-bottom:48px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
    <button onclick="compareNow()" style="background:#0082C3;color:white;border:none;padding:12px 32px;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;">Compare Now</button>
    <?php if(!empty($selected_cat)): ?>
    <span style="font-size:14px;color:var(--gs-muted);">Showing products from: <strong style="color:var(--gs-text);"><?php echo esc_html(get_term_by('slug',$selected_cat,'sport_category')->name ?? $selected_cat); ?></strong> &mdash; <a href="?">Clear filter</a></span>
    <?php endif; ?>
  </div>

  <!-- STEP 2: Comparison table (only shown when products selected) -->
  <div id="gs-compare-result">
  <?php
  $p1 = isset($_GET['p1']) ? intval($_GET['p1']) : 0;
  $p2 = isset($_GET['p2']) ? intval($_GET['p2']) : 0;
  $p3 = isset($_GET['p3']) ? intval($_GET['p3']) : 0;
  $selected = array_filter([$p1, $p2, $p3]);

  if(count($selected) >= 2):
    $products = [];
    foreach($selected as $pid) {
      $post = get_post($pid);
      if($post) {
        $fields = function_exists('get_fields') ? (array) get_fields($pid) : [];
        $products[] = ['post' => $post, 'fields' => $fields];
      }
    }
    if(count($products) >= 2):
  ?>
  <div style="overflow-x:auto;">
  <table class="gs-compare-table" style="width:100%;border-collapse:collapse;background:var(--gs-card);border-radius:12px;overflow:hidden;box-shadow:var(--gs-shadow);">
    <thead>
      <tr style="background:#EBF5FB;">
        <th style="padding:16px 20px;text-align:left;font-size:14px;font-weight:700;color:var(--gs-text);width:140px;">Feature</th>
        <?php foreach($products as $product): ?>
        <th style="padding:16px 20px;text-align:left;font-size:15px;font-weight:700;color:var(--gs-text);"><?php echo esc_html($product['post']->post_title); ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <!-- Score row -->
      <tr style="border-top:1px solid var(--gs-border);">
        <td style="padding:16px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Score</td>
        <?php foreach($products as $product):
          $score = isset($product['fields']['score']) ? floatval($product['fields']['score']) : floatval($product['fields']['gs_score'] ?? 0);
          $color = $score >= 8 ? '#27AE60' : ($score >= 6 ? '#F39C12' : '#E74C3C');
        ?>
        <td style="padding:16px 20px;">
          <div style="width:56px;height:56px;border-radius:50%;background:white;border:3px solid <?php echo $color; ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <span style="font-size:18px;font-weight:700;color:<?php echo $color; ?>;line-height:1;"><?php echo $score; ?></span>
            <span style="font-size:10px;color:var(--gs-muted);line-height:1;">/10</span>
          </div>
        </td>
        <?php endforeach; ?>
      </tr>
      <!-- Brand -->
      <tr style="background:#F8FAFC;border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Brand</td>
        <?php foreach($products as $product): ?>
        <td style="padding:14px 20px;color:var(--gs-text);"><?php echo esc_html($product['fields']['brand'] ?? $product['fields']['gs_brand'] ?? '-'); ?></td>
        <?php endforeach; ?>
      </tr>
      <!-- Price -->
      <tr style="border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Price</td>
        <?php foreach($products as $product): ?>
        <td style="padding:14px 20px;color:var(--gs-text);"><?php echo esc_html($product['fields']['price'] ?? $product['fields']['gs_price'] ?? '-'); ?></td>
        <?php endforeach; ?>
      </tr>
      <!-- Best For -->
      <tr style="background:#F8FAFC;border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Best For</td>
        <?php foreach($products as $product): ?>
        <td style="padding:14px 20px;color:var(--gs-muted);font-size:14px;"><?php echo esc_html(wp_trim_words(kitscore_compare_field_text($product['fields']['best_for'] ?? $product['fields']['gs_best_for'] ?? $product['post']->post_excerpt), 20)); ?></td>
        <?php endforeach; ?>
      </tr>
      <!-- Pros -->
      <tr style="border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Pros</td>
        <?php foreach($products as $product):
          $pros = $product['fields']['pros'] ?? $product['fields']['gs_pros'] ?? [];
          if(is_string($pros)) $pros = explode("\n", $pros);
        ?>
        <td style="padding:14px 20px;">
          <ul style="margin:0;padding:0;list-style:none;">
            <?php foreach(array_slice((array)$pros, 0, 3) as $pro): ?>
            <?php $pro = kitscore_compare_field_text($pro); if ($pro === '') { continue; } ?>
            <li style="font-size:14px;color:var(--gs-text);padding:3px 0;padding-left:20px;position:relative;"><span style="position:absolute;left:0;color:#27AE60;font-weight:700;">&#10003;</span><?php echo esc_html($pro); ?></li>
            <?php endforeach; ?>
          </ul>
        </td>
        <?php endforeach; ?>
      </tr>
      <!-- Cons -->
      <tr style="background:#F8FAFC;border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Cons</td>
        <?php foreach($products as $product):
          $cons = $product['fields']['cons'] ?? $product['fields']['gs_cons'] ?? [];
          if(is_string($cons)) $cons = explode("\n", $cons);
        ?>
        <td style="padding:14px 20px;">
          <ul style="margin:0;padding:0;list-style:none;">
            <?php foreach(array_slice((array)$cons, 0, 3) as $con): ?>
            <?php $con = kitscore_compare_field_text($con); if ($con === '') { continue; } ?>
            <li style="font-size:14px;color:var(--gs-text);padding:3px 0;padding-left:20px;position:relative;"><span style="position:absolute;left:0;color:#E74C3C;font-weight:700;">&#10007;</span><?php echo esc_html($con); ?></li>
            <?php endforeach; ?>
          </ul>
        </td>
        <?php endforeach; ?>
      </tr>
      <!-- Buy link -->
      <tr style="border-top:1px solid var(--gs-border);">
        <td style="padding:14px 20px;font-weight:700;color:var(--gs-text);font-size:14px;">Link</td>
        <?php foreach($products as $product):
          $url = $product['fields']['affiliate_link'] ?? $product['fields']['gs_affiliate_link'] ?? $product['fields']['decathlon_url'] ?? '';
        ?>
        <td style="padding:14px 20px;">
          <?php if($url): ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" style="display:inline-block;background:var(--gs-blue);color:white;padding:8px 18px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">View on Decathlon</a>
          <?php else: ?>
          <span style="color:var(--gs-muted);font-size:14px;">&mdash;</span>
          <?php endif; ?>
        </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>
  </div>

  <!-- Reviews Comparison Section -->
  <?php
  function kitscore_get_product_reviews_for_compare(int $pid): array {
    $reviews = [];
    if (function_exists('have_rows') && have_rows('user_reviews', $pid)) {
      while (have_rows('user_reviews', $pid)) {
        the_row();
        $reviews[] = [
          'name'     => get_sub_field('reviewer_name') ?: get_sub_field('name') ?: 'Anonymous',
          'rating'   => (int) (get_sub_field('rating') ?: get_sub_field('stars') ?: 5),
          'date'     => get_sub_field('date') ?: get_sub_field('review_date') ?: '',
          'verified' => (bool) (get_sub_field('verified') ?: false),
          'text'     => get_sub_field('review_text') ?: get_sub_field('text') ?: get_sub_field('content') ?: '',
        ];
      }
    }
    if (empty($reviews)) {
      $wp_comments = get_comments(['post_id' => $pid, 'status' => 'approve']);
      foreach ($wp_comments as $c) {
        $rating    = (int) get_comment_meta($c->comment_ID, 'rating', true);
        $reviews[] = [
          'name'     => $c->comment_author ?: 'Anonymous',
          'rating'   => $rating ?: 5,
          'date'     => $c->comment_date,
          'verified' => false,
          'text'     => $c->comment_content,
        ];
      }
    }
    return array_slice($reviews, 0, 3);
  }

  if (!empty($products)):
    $any_reviews = false;
    foreach ($products as $product) {
      if (!empty(kitscore_get_product_reviews_for_compare($product['post']->ID))) {
        $any_reviews = true;
        break;
      }
    }
    if ($any_reviews):
  ?>
  <div style="margin-top:48px;">
    <h2 style="font-family:Poppins,Inter,sans-serif;font-size:24px;font-weight:700;color:var(--gs-text);margin-bottom:24px;padding-left:16px;border-left:3px solid #0082C3;">Reviews Comparison</h2>
    <p style="color:var(--gs-muted);margin-bottom:28px;font-size:15px;">What actual buyers said about each product.</p>
    <div style="display:grid;grid-template-columns:repeat(<?php echo count($products); ?>,1fr);gap:24px;align-items:start;">
      <?php foreach ($products as $product):
        $pid          = $product['post']->ID;
        $prod_reviews = kitscore_get_product_reviews_for_compare($pid);
      ?>
      <div>
        <h3 style="font-family:Poppins,Inter,sans-serif;font-size:16px;font-weight:700;color:var(--gs-text);margin:0 0 16px;padding:12px 16px;background:var(--gs-card);border:1px solid var(--gs-border);border-radius:8px;"><?php echo esc_html($product['post']->post_title); ?></h3>
        <?php if (empty($prod_reviews)): ?>
        <p style="color:var(--gs-muted);font-size:14px;padding:16px;text-align:center;">No reviews yet.</p>
        <?php else: ?>
        <div style="display:grid;gap:12px;">
          <?php foreach ($prod_reviews as $rev):
            $name     = esc_html($rev['name']);
            $initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''));
            $rating   = max(1, min(5, (int) $rev['rating']));
            $stars    = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            $date_str = $rev['date'] ? date_i18n(get_option('date_format'), is_numeric($rev['date']) ? $rev['date'] : strtotime($rev['date'])) : '';
          ?>
          <div style="background:var(--gs-card);border:1px solid var(--gs-border);border-radius:10px;padding:16px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
              <div style="flex-shrink:0;width:36px;height:36px;border-radius:50%;background:#0082C3;color:#fff;display:flex;align-items:center;justify-content:center;font-family:Poppins,Inter,sans-serif;font-size:13px;font-weight:700;"><?php echo esc_html($initials); ?></div>
              <div style="flex:1;min-width:0;">
                <span style="display:block;color:var(--gs-text);font-size:14px;font-weight:700;line-height:1.3;"><?php echo $name; ?></span>
                <?php if ($date_str): ?>
                <span style="color:var(--gs-muted);font-size:12px;"><?php echo esc_html($date_str); ?></span>
                <?php endif; ?>
              </div>
              <?php if ($rev['verified']): ?>
              <span style="display:inline-flex;align-items:center;height:20px;padding:0 8px;background:#E6F9F1;color:#16A34A;border-radius:999px;font-size:11px;font-weight:700;">Verified</span>
              <?php endif; ?>
            </div>
            <div style="color:#FF6B00;font-size:14px;letter-spacing:1px;margin-bottom:6px;"><?php echo esc_html($stars); ?></div>
            <p style="margin:0;color:var(--gs-text);font-size:13px;line-height:1.6;"><?php echo esc_html($rev['text']); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; endif; ?>

  <?php endif; else: ?>
  <div style="text-align:center;padding:60px 20px;color:var(--gs-muted);">
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 16px;display:block;opacity:0.4;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    <p style="font-size:16px;">Select at least 2 products above and click Compare Now.</p>
  </div>
  <?php endif; ?>
  </div>
</div>

<script>
function filterByCategory(slug) {
  var url = window.location.pathname + (slug ? '?cat=' + slug : '');
  window.location.href = url;
}

function compareNow() {
  var p1 = document.getElementById('product-1').value;
  var p2 = document.getElementById('product-2').value;
  var p3 = document.getElementById('product-3').value;
  var cat = document.getElementById('gs-cat-filter').value;
  if (!p1 || !p2) {
    alert('Please select at least 2 products to compare.');
    return;
  }
  var url = window.location.pathname + '?p1=' + p1 + '&p2=' + p2 + (p3 ? '&p3=' + p3 : '') + (cat ? '&cat=' + cat : '');
  window.location.href = url;
}
</script>

<?php get_footer(); ?>
