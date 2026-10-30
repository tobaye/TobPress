<?php
/**
 * Template Name: 说说时间线
 * Template Post Type: page
 *
 * Timeline of short posts from the "shuoshuo" category （说说）.
 *
 * @package TobPress
 */

get_header();

$tobpress_cat = get_category_by_slug( 'shuoshuo' );
if ( ! $tobpress_cat ) {
	$tobpress_terms = get_categories( array( 'search' => '说说', 'hide_empty' => false ) );
	$tobpress_cat   = $tobpress_terms ? $tobpress_terms[0] : null;
}
?>
<div class="tb-wrap">
	<main class="tb-main tb-main-wide" id="tb-content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<header class="tb-page-head">
				<h1 class="tb-article-title"><?php the_title(); ?></h1>
			</header>

			<?php if ( get_the_content() ) : ?>
				<div class="tb-content"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $tobpress_cat ) : ?>
				<?php
				$tobpress_tl = new WP_Query(
					array(
						'cat'                 => $tobpress_cat->term_id,
						'posts_per_page'      => 50,
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					)
				);
				if ( $tobpress_tl->have_posts() ) :
					?>
					<ol class="tb-timeline">
						<?php
						while ( $tobpress_tl->have_posts() ) :
							$tobpress_tl->the_post();
							?>
							<li class="tb-tl-item">
								<time class="tb-tl-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?></time>
								<div class="tb-tl-body">
									<?php echo wp_kses_post( wpautop( wp_strip_all_tags( get_the_content() ) ) ); ?>
									<a class="tb-tl-link" href="<?php the_permalink(); ?>"><?php esc_html_e( '查看详情', 'tobpress' ); ?></a>
								</div>
							</li>
						<?php endwhile; ?>
					</ol>
					<?php
					wp_reset_postdata();
				else :
					?>
					<p class="tb-tl-empty"><?php esc_html_e( '这个分类下还没有内容。', 'tobpress' ); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<p class="tb-tl-empty"><?php esc_html_e( '未找到「说说」分类（别名 shuoshuo）。请先创建该分类并把短内容发在里面。', 'tobpress' ); ?></p>
			<?php endif; ?>
		<?php endwhile; ?>
	</main>
</div>
<?php
get_footer();
