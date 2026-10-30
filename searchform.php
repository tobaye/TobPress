<?php
/**
 * Search form (also used via get_search_form() in the header bar,
 * the mobile menu, search results and the 404 page).
 *
 * @package TobPress
 */
?>
<form role="search" method="get" class="tb-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="tb-search-field"><?php esc_html_e( '搜索：', 'tobpress' ); ?></label>
	<input type="search" id="tb-search-field" class="tb-search-field" placeholder="<?php esc_attr_e( '搜索文章…', 'tobpress' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="tb-search-submit"><?php esc_html_e( '搜索', 'tobpress' ); ?></button>
</form>
