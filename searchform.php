<form role="search" method="get" class="gs-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text" for="gs-search-field"><?php esc_html_e('Search reviews', 'kitscore'); ?></label>
    <input id="gs-search-field" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search gear, brands, sports...', 'kitscore'); ?>">
    <input type="hidden" name="post_type" value="product_review">
    <button type="submit"><?php esc_html_e('Search', 'kitscore'); ?></button>
</form>
