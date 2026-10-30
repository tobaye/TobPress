<?php
/**
 * Shown when a loop has no posts.
 *
 * @package TobPress
 */
?>
<section class="tb-empty">
	<h2><?php esc_html_e( '没有找到', 'tobpress' ); ?></h2>
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( '抱歉，没有与搜索匹配的文章。换个关键词试试：', 'tobpress' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( '这里暂时还没有内容。', 'tobpress' ); ?></p>
	<?php endif; ?>
</section>
