<?php
/**
 * Template Name: 友情链接 (Links)
 * Template Post Type: page
 *
 * Card-style friend-links page built on the native WordPress link
 * manager (get_bookmarks), grouped by link category.
 *
 * @package TobPress
 */

get_header();
?>
<div class="tb-wrap">
	<div class="tb-layout">
		<main class="tb-main" id="tb-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'tb-article' ); ?>>
					<?php tobpress_breadcrumb(); ?>
					<header class="tb-article-head">
						<h1 class="tb-article-title"><?php the_title(); ?></h1>
					</header>
					<div class="tb-content">
						<?php the_content(); ?>
					</div>

					<?php
					$tobpress_link_cats = get_terms(
						array(
							'taxonomy'   => 'link_category',
							'hide_empty' => true,
						)
					);
					if ( is_wp_error( $tobpress_link_cats ) ) {
						$tobpress_link_cats = array();
					}
					$tobpress_rendered = false;
					foreach ( $tobpress_link_cats as $tobpress_cat ) :
						$tobpress_links = get_bookmarks(
							array(
								'category' => $tobpress_cat->term_id,
								'orderby'  => 'name',
							)
						);
						if ( ! $tobpress_links ) {
							continue;
						}
						$tobpress_rendered = true;
						?>
						<h2 class="tb-section-title"><?php echo esc_html( $tobpress_cat->name ); ?></h2>
						<ul class="tb-link-grid">
							<?php foreach ( $tobpress_links as $tobpress_link ) : ?>
								<li class="tb-link-card">
									<a href="<?php echo esc_url( $tobpress_link->link_url ); ?>" target="_blank" rel="noopener">
										<span class="tb-link-name"><?php echo esc_html( $tobpress_link->link_name ); ?></span>
										<?php if ( $tobpress_link->link_description ) : ?>
											<span class="tb-link-desc"><?php echo esc_html( $tobpress_link->link_description ); ?></span>
										<?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>

					<?php if ( ! $tobpress_rendered ) : ?>
						<p class="tb-archive-desc">
							<?php
							esc_html_e( '暂无链接。在后台「链接」中添加后（链接管理器如被关闭需先重新启用），会按分类分组显示在这里。', 'tobpress' );
							?>
						</p>
					<?php endif; ?>
				</article>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
