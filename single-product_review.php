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
$current_post_id = get_the_ID();
$current_terms = get_the_terms($current_post_id, 'sport_category');
$term_ids = [];
if ($current_terms && !is_wp_error($current_terms)) {
    foreach ($current_terms as $t) {
        $term_ids[] = $t->term_id;
    }
}
$comp_query_args = [
    'post_type'      => 'product_review',
    'posts_per_page' => 2,
    'post__not_in'   => [$current_post_id],
    'orderby'        => 'meta_value_num',
    'meta_key'       => 'score',
    'order'          => 'DESC',
];
if (!empty($term_ids)) {
    $comp_query_args['tax_query'] = [[
        'taxonomy' => 'sport_category',
        'field'    => 'term_id',
        'terms'    => $term_ids,
        'operator' => 'IN',
    ]];
}
$comp_query = new WP_Query($comp_query_args);
$comp_post_ids = wp_list_pluck($comp_query->posts, 'ID');
$comparison_ids = array_merge([$post_id], $comp_post_ids);
$has_comparison_peers = !empty($comp_post_ids);
wp_reset_postdata();
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
                <?php if ($has_comparison_peers) : ?>
                    <?php echo do_shortcode('[kitscore_comparison ids="' . esc_attr(implode(',', $comparison_ids)) . '"]'); ?>
                <?php else : ?>
                    <p style="color:var(--gs-muted);font-size:14px;padding:16px 0;">No other products in this category to compare yet.</p>
                <?php endif; ?>
            </section>

            <?php
            /* ── Customer Reviews ── */
            $acf_reviews = [];
            $has_acf_reviews = false;

            if (function_exists('have_rows') && have_rows('user_reviews', $post_id)) {
                $has_acf_reviews = true;
                while (have_rows('user_reviews', $post_id)) {
                    the_row();
                    $rev_text = get_sub_field('review_text') ?: get_sub_field('text') ?: get_sub_field('content') ?: '';
                    if (empty(trim((string) $rev_text))) {
                        continue;
                    }
                    $acf_reviews[] = [
                        'name'     => get_sub_field('reviewer_name') ?: get_sub_field('name') ?: 'Anonymous',
                        'rating'   => (int) (get_sub_field('rating') ?: get_sub_field('stars') ?: 5),
                        'date'     => get_sub_field('date') ?: get_sub_field('review_date') ?: '',
                        'verified' => (bool) (get_sub_field('verified') ?: false),
                        'text'     => $rev_text,
                    ];
                }
            }

            $wp_comments = [];
            if (!$has_acf_reviews) {
                $wp_comments = get_comments(['post_id' => $post_id, 'status' => 'approve']);
                foreach ($wp_comments as $c) {
                    $content = trim($c->comment_content);
                    if (empty($content) || strlen($content) <= 10) {
                        continue;
                    }
                    $rating = (int) get_comment_meta($c->comment_ID, 'rating', true);
                    $acf_reviews[] = [
                        'name'     => $c->comment_author ?: 'Anonymous',
                        'rating'   => $rating ?: 5,
                        'date'     => $c->comment_date,
                        'verified' => false,
                        'text'     => $c->comment_content,
                    ];
                }
            }

            ?>
            <section class="gs-review-section gs-customer-reviews-section" id="customer-reviews">
                <h2><?php esc_html_e('Customer Reviews', 'kitscore'); ?></h2>
            <?php if (!empty($acf_reviews)) :
                /* Summary stats */
                $total     = count($acf_reviews);
                $sum       = array_sum(array_column($acf_reviews, 'rating'));
                $avg       = $total > 0 ? round($sum / $total, 1) : 0;
                $dist      = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                foreach ($acf_reviews as $r) {
                    $star = max(1, min(5, (int) $r['rating']));
                    $dist[$star]++;
                }
            ?>

                <!-- Summary block -->
                <div style="display:grid;grid-template-columns:160px 1fr;gap:32px;align-items:center;background:var(--gs-card);border-radius:12px;padding:24px;margin-bottom:28px;">
                    <div style="text-align:center;">
                        <div style="color:#0082C3;font-family:Poppins,Inter,sans-serif;font-size:52px;font-weight:700;line-height:1;"><?php echo esc_html($avg); ?></div>
                        <div style="color:#FF6B00;font-size:20px;letter-spacing:2px;margin-top:4px;">
                            <?php
                            $full  = floor($avg / 1);
                            $stars_out = '';
                            for ($s = 1; $s <= 5; $s++) {
                                $stars_out .= $s <= round($avg) ? '★' : '☆';
                            }
                            echo esc_html($stars_out);
                            ?>
                        </div>
                        <div style="color:#667088;font-size:13px;margin-top:6px;"><?php echo esc_html(sprintf(_n('%d review', '%d reviews', $total, 'kitscore'), $total)); ?></div>
                    </div>
                    <div style="display:grid;gap:8px;">
                        <?php for ($s = 5; $s >= 1; $s--) :
                            $pct = $total > 0 ? round(($dist[$s] / $total) * 100) : 0;
                        ?>
                        <div style="display:grid;grid-template-columns:52px 1fr 42px;gap:10px;align-items:center;font-size:13px;color:#667088;">
                            <span><?php echo $s; ?> star</span>
                            <div style="height:8px;background:#E2E8F0;border-radius:999px;overflow:hidden;">
                                <div style="height:100%;width:<?php echo $pct; ?>%;background:#0082C3;border-radius:inherit;"></div>
                            </div>
                            <span style="text-align:right;"><?php echo $pct; ?>%</span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Review cards -->
                <div style="display:grid;gap:16px;">
                    <?php foreach ($acf_reviews as $review) :
                        $name     = esc_html($review['name']);
                        $initials = strtoupper(substr($name, 0, 1) . (strpos($name, ' ') !== false ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                        $rating   = max(1, min(5, (int) $review['rating']));
                        $stars    = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                        $date_str = $review['date'] ? date_i18n(get_option('date_format'), is_numeric($review['date']) ? $review['date'] : strtotime($review['date'])) : '';
                    ?>
                    <div style="background:var(--gs-card);border:1px solid #E2E8F0;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                            <div style="flex-shrink:0;width:44px;height:44px;border-radius:50%;background:#0082C3;color:#fff;display:flex;align-items:center;justify-content:center;font-family:Poppins,Inter,sans-serif;font-size:15px;font-weight:700;"><?php echo esc_html($initials); ?></div>
                            <div style="flex:1;min-width:0;">
                                <span style="display:block;color:#1A1A2E;font-size:15px;font-weight:700;line-height:1.3;"><?php echo $name; ?></span>
                                <?php if ($date_str) : ?>
                                <span style="display:block;color:#667088;font-size:13px;"><?php echo esc_html($date_str); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($review['verified']) : ?>
                            <span style="display:inline-flex;align-items:center;height:24px;padding:0 10px;background:#E6F9F1;color:#16A34A;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap;"><?php esc_html_e('Verified', 'kitscore'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-bottom:8px;color:#FF6B00;font-size:16px;letter-spacing:1px;"><?php echo esc_html($stars); ?></div>
                        <p style="margin:0;color:#444;font-size:15px;line-height:1.65;"><?php echo esc_html($review['text']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div style="text-align:center;padding:48px 24px;color:var(--gs-muted);">
                    <p style="font-size:15px;margin:0 0 8px;">No customer reviews yet for this product.</p>
                    <p style="font-size:13px;margin:0;">Reviews are sourced from verified purchases.</p>
                </div>
            <?php endif; ?>
            </section>

        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>
