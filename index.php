<?php
/**
 * Blog home / post index: optional featured carousel, card list,
 * pagination, sidebar.
 *
 * @package TobPress
 */

get_header();

$tobpress_has_sidebar = is_active_sidebar( 'sidebar-main' ) && 'no-sidebar' !== get_theme_mod( 'tobpress_layout', 'sidebar-right' );
$tobpress_card_style  = (string) get_theme_mod( 'tobpress_card_style', 'list' );
?>
<div class="tb-wrap">
	<div class="tb-layout<?php echo $tobpress_has_sidebar ? ' has-sidebar' : ''; ?>">
		<main class="tb-main" id="tb-content">

			<?php
			// Welcome strip: front page only, first page only.
			if ( is_front_page() && ! is_paged() && get_theme_mod( 'tobpress_show_welcome', true ) ) :
				$tobpress_welcome_text = trim( (string) get_theme_mod( 'tobpress_welcome_text', '' ) );
				if ( '' === $tobpress_welcome_text ) {
					$tobpress_welcome_text = (string) get_bloginfo( 'description' );
				}
				$tobpress_welcome_logo = (string) get_site_icon_url( 96 );
				if ( '' === $tobpress_welcome_logo ) {
					$tobpress_custom_logo = (int) get_theme_mod( 'custom_logo' );
					if ( $tobpress_custom_logo ) {
						$tobpress_welcome_logo = (string) wp_get_attachment_image_url( $tobpress_custom_logo, 'thumbnail' );
					}
				}
				$tobpress_welcome_stats = function_exists( 'tobpress_site_stats' ) ? tobpress_site_stats() : null;
				?>
				<section class="tb-welcome" aria-label="<?php esc_attr_e( '欢迎', 'tobpress' ); ?>">
					<?php if ( '' !== $tobpress_welcome_logo ) : ?>
						<img class="tb-welcome-avatar" src="<?php echo esc_url( $tobpress_welcome_logo ); ?>" alt="" width="56" height="56" loading="lazy">
					<?php endif; ?>
					<div class="tb-welcome-main">
						<h2 class="tb-welcome-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
						<?php if ( '' !== $tobpress_welcome_text ) : ?>
							<p class="tb-welcome-text"><?php echo esc_html( $tobpress_welcome_text ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( is_array( $tobpress_welcome_stats ) ) : ?>
						<div class="tb-welcome-chips">
							<span class="tb-chip"><?php echo esc_html( sprintf( /* translators: %s: post count. */ __( '文章 %s', 'tobpress' ), number_format_i18n( $tobpress_welcome_stats['posts'] ) ) ); ?></span>
							<span class="tb-chip"><?php echo esc_html( sprintf( /* translators: %s: days count. */ __( '运行 %s 天', 'tobpress' ), number_format_i18n( $tobpress_welcome_stats['days'] ) ) ); ?></span>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php
			// Featured carousel: front page only, first page only.
			if ( is_front_page() && ! is_paged() && get_theme_mod( 'tobpress_show_carousel', true ) ) :
				$tobpress_slides = get_posts(
					array(
						'post__in'            => get_option( 'sticky_posts', array() ),
						'posts_per_page'      => 5,
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
					)
				);
				if ( ! $tobpress_slides ) {
					$tobpress_slides = get_posts(
						array(
							'posts_per_page' => 5,
							'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
							'no_found_rows'  => true,
						)
					);
				}
				if ( $tobpress_slides ) :
					?>
					<section class="tb-carousel" aria-label="<?php esc_attr_e( '精选文章', 'tobpress' ); ?>">
						<div class="tb-carousel-track" id="tb-carousel-track">
							<?php
							$tobpress_i = 0;
							foreach ( $tobpress_slides as $tobpress_slide ) :
								$tobpress_i++;
								?>
								<article class="tb-slide">
									<?php if ( has_post_thumbnail( $tobpress_slide ) ) : ?>
										<?php
										echo get_the_post_thumbnail(
											$tobpress_slide,
											'tobpress-card',
											array(
												'alt'           => '',
												'loading'       => 1 === $tobpress_i ? 'eager' : 'lazy',
												'fetchpriority' => 1 === $tobpress_i ? 'high' : 'auto',
											)
										);
										?>
									<?php else : ?>
										<span class="tb-slide-placeholder" aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( get_the_title( $tobpress_slide ), 0, 1 ) : substr( get_the_title( $tobpress_slide ), 0, 1 ) ); ?></span>
									<?php endif; ?>
									<div class="tb-slide-body">
										<h2 class="tb-slide-title"><a href="<?php echo esc_url( get_permalink( $tobpress_slide ) ); ?>"><?php echo esc_html( get_the_title( $tobpress_slide ) ); ?></a></h2>
										<p class="tb-slide-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $tobpress_slide->post_excerpt ? $tobpress_slide->post_excerpt : $tobpress_slide->post_content ), 24, '…' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
						<?php if ( count( $tobpress_slides ) > 1 ) : ?>
							<button type="button" class="tb-icon-btn tb-carousel-btn tb-carousel-prev" aria-label="<?php esc_attr_e( '上一张', 'tobpress' ); ?>">
								<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg>
							</button>
							<button type="button" class="tb-icon-btn tb-carousel-btn tb-carousel-next" aria-label="<?php esc_attr_e( '下一张', 'tobpress' ); ?>">
								<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg>
							</button>
						<?php endif; ?>
					</section>
					<?php
				endif;
			endif;
			?>

			<?php
			if ( is_front_page() && ! is_paged() ) :
				$tobpress_cats = get_categories(
					array(
						'orderby'    => 'count',
						'order'      => 'DESC',
						'number'     => 8,
						'hide_empty' => true,
					)
				);
				if ( $tobpress_cats ) :
					?>
					<nav class="tb-cat-chips" aria-label="<?php esc_attr_e( '按分类浏览', 'tobpress' ); ?>">
						<?php foreach ( $tobpress_cats as $tobpress_cat ) : ?>
							<a class="tb-chip" style="--tb-cat-c:<?php echo esc_attr( tobpress_cat_color( $tobpress_cat ) ); ?>" href="<?php echo esc_url( get_category_link( $tobpress_cat ) ); ?>"><?php echo tobpress_cat_icon( $tobpress_cat ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?><span><?php echo esc_html( $tobpress_cat->name ); ?></span><span class="tb-chip-count"><?php echo esc_html( number_format_i18n( $tobpress_cat->count ) ); ?></span></a>
						<?php endforeach; ?>
					</nav>
					<?php
				endif;
			endif;
			?>

			<?php if ( have_posts() ) : ?>
				<div class="tb-card-list style-<?php echo esc_attr( $tobpress_card_style ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					?>
				</div>
				<div class="tb-pagination">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => __( '← 更新', 'tobpress' ),
							'next_text' => __( '更早 →', 'tobpress' ),
						)
					);
					?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>

		</main>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
