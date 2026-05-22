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
