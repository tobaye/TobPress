<?php
/**
 * 404 template: brief message, search form, latest posts.
 *
 * @package TobPress
 */

get_header();
?>
<div class="tb-wrap">
	<div class="tb-layout">
		<main class="tb-main" id="tb-content">
			<section class="tb-empty">
				<h1 class="tb-archive-title">404</h1>
				<p><?php esc_html_e( '你要找的页面不存在，或者已经被移动。', 'tobpress' ); ?></p>
				<?php get_search_form(); ?>
				<h2 class="tb-section-title"><?php esc_html_e( '最新文章', 'tobpress' ); ?></h2>
				<ul class="tb-archive-list" style="text-align:start; display:inline-block;">
					<?php
					$tobpress_recent = wp_get_recent_posts(
						array(
							'numberposts' => 5,
							'post_status' => 'publish',
						)
					);
					foreach ( $tobpress_recent as $tobpress_item ) :
						?>
						<li><a href="<?php echo esc_url( get_permalink( $tobpress_item['ID'] ) ); ?>"><?php echo esc_html( $tobpress_item['post_title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		</main>
	</div>
</div>
<?php
get_footer();
