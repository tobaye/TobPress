<?php
/**
 * Single post: breadcrumb, headline block, featured image, table of
 * contents, content, copyright line, prev/next, related posts,
 * comments.
 *
 * The TOC is collected by a the_content filter while the content is
 * rendered, so the content is captured in a buffer first and the TOC
 * is printed above it.
 *
 * @package TobPress
 */

get_header();

$tobpress_has_sidebar = is_active_sidebar( 'sidebar-main' ) && 'no-sidebar' !== get_theme_mod( 'tobpress_layout', 'sidebar-right' );
?>
<div class="tb-wrap">
	<div class="tb-layout<?php echo $tobpress_has_sidebar ? ' has-sidebar' : ''; ?>">
		<main class="tb-main" id="tb-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'tb-article' ); ?>>
					<?php tobpress_breadcrumb(); ?>

					<?php $tobpress_hero = get_theme_mod( 'tobpress_hero_title', false ) && has_post_thumbnail(); ?>
					<header class="tb-article-head<?php echo $tobpress_hero ? ' tb-hero' : ''; ?>"<?php echo $tobpress_hero ? ' style="background-image:url(' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ) . ')"' : ''; ?>>
						<h1 class="tb-article-title"><?php the_title(); ?></h1>
						<?php tobpress_post_meta(); ?>
					</header>

					<?php if ( has_post_thumbnail() && ! $tobpress_hero ) : ?>
						<figure class="tb-featured">
							<?php the_post_thumbnail( 'large', array( 'fetchpriority' => 'high', 'alt' => '' ) ); ?>
						</figure>
					<?php endif; ?>

					<?php
					$tobpress_age = current_time( 'timestamp' ) - get_post_modified_time( 'U' );
					if ( $tobpress_age > 180 * DAY_IN_SECONDS ) :
						?>
						<div class="tb-stale" role="note">
							<?php
							printf(
								/* translators: 1: last updated date, 2: days since update. */
								esc_html__( '本文最后更新于 %1$s，已有 %2$s 天未更新，部分内容可能已经过时，请结合实际情况参考。', 'tobpress' ),
								esc_html( get_the_modified_date() ),
								esc_html( number_format_i18n( floor( $tobpress_age / DAY_IN_SECONDS ) ) )
							);
							?>
						</div>
					<?php endif; ?>

					<div class="tb-content">
						<?php
						ob_start();
						the_content();
						$tobpress_body = ob_get_clean();
						tobpress_render_toc();
						echo $tobpress_body; // phpcs:ignore WordPress.Security.EscapeOutput -- post content, filtered by WordPress.
						?>
					</div>

					<?php
					wp_link_pages(
						array(
							'before' => '<div class="tb-pagination"><div class="nav-links">',
							'after'  => '</div></div>',
						)
					);
					?>

					<?php
					$tobpress_reward_wx  = trim( (string) get_theme_mod( 'tobpress_reward_wx', '' ) );
					$tobpress_reward_ali = trim( (string) get_theme_mod( 'tobpress_reward_ali', '' ) );
					?>
					<div class="tb-actions">
						<?php if ( function_exists( 'tobpress_like_button' ) ) : ?>
							<?php tobpress_like_button( get_the_ID() ); ?>
						<?php endif; ?>
						<?php if ( $tobpress_reward_wx || $tobpress_reward_ali ) : ?>
							<button type="button" class="tb-reward-btn" data-reward-open><?php esc_html_e( '打赏', 'tobpress' ); ?></button>
						<?php endif; ?>
					</div>

					<?php tobpress_share_row( get_the_ID() ); ?>

					<?php
					$tobpress_tags = get_the_tag_list( '', '' );
					if ( $tobpress_tags ) :
						?>
						<div class="tb-tags"><?php echo $tobpress_tags; // phpcs:ignore WordPress.Security.EscapeOutput -- core returns escaped links. ?></div>
					<?php endif; ?>

					<?php
					$tobpress_copyright = trim( (string) get_theme_mod( 'tobpress_copyright_text', '版权声明：本文由 {author} 原创发表于 {year} 年，原文《{title}》，地址：{url}。转载请注明出处。' ) );
					if ( '' !== $tobpress_copyright ) :
						$tobpress_copyright = strtr(
							$tobpress_copyright,
							array(
								'{title}'  => get_the_title(),
								'{url}'    => get_permalink(),
								'{author}' => get_the_author_meta( 'display_name' ),
								'{year}'   => get_the_date( 'Y' ),
							)
						);
						?>
						<div class="tb-copyright"><?php echo esc_html( $tobpress_copyright ); ?></div>
					<?php endif; ?>

					<div class="tb-author-card">
						<?php echo tobpress_avatar( get_the_author_meta( 'ID' ), 72, array( 'class' => 'tb-author-avatar' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- avatar markup is core-generated or a plain letter span. ?>
						<div class="tb-author-main">
							<p class="tb-author-name">
								<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
								<span class="tb-author-count"><?php echo esc_html( sprintf( /* translators: %s: post count. */ __( '共 %s 篇文章', 'tobpress' ), number_format_i18n( (int) count_user_posts( get_the_author_meta( 'ID' ) ) ) ) ); ?></span>
							</p>
							<p class="tb-author-bio"><?php echo esc_html( (string) ( get_the_author_meta( 'description' ) ?: get_bloginfo( 'description' ) ) ); ?></p>
						</div>
						<a class="tb-author-more" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php esc_html_e( '全部文章', 'tobpress' ); ?></a>
					</div>

					<?php
					$tobpress_prev = get_previous_post();
					$tobpress_next = get_next_post();
					if ( $tobpress_prev || $tobpress_next ) :
						?>
						<nav class="tb-post-nav" aria-label="<?php esc_attr_e( '文章导航', 'tobpress' ); ?>">
							<?php if ( $tobpress_prev ) : ?>
								<a class="tb-nav-card prev" href="<?php echo esc_url( get_permalink( $tobpress_prev ) ); ?>"<?php echo has_post_thumbnail( $tobpress_prev ) ? ' style="background-image:url(' . esc_url( get_the_post_thumbnail_url( $tobpress_prev, 'tobpress-card' ) ) . ')"' : ''; ?>>
									<span class="tb-nav-label"><?php esc_html_e( '← 上一篇', 'tobpress' ); ?></span>
									<span class="tb-nav-title"><?php echo esc_html( get_the_title( $tobpress_prev ) ); ?></span>
								</a>
							<?php else : ?>
								<span class="tb-nav-card empty"></span>
							<?php endif; ?>
							<?php if ( $tobpress_next ) : ?>
								<a class="tb-nav-card next" href="<?php echo esc_url( get_permalink( $tobpress_next ) ); ?>"<?php echo has_post_thumbnail( $tobpress_next ) ? ' style="background-image:url(' . esc_url( get_the_post_thumbnail_url( $tobpress_next, 'tobpress-card' ) ) . ')"' : ''; ?>>
									<span class="tb-nav-label"><?php esc_html_e( '下一篇 →', 'tobpress' ); ?></span>
									<span class="tb-nav-title"><?php echo esc_html( get_the_title( $tobpress_next ) ); ?></span>
								</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>

					<?php if ( $tobpress_reward_wx || $tobpress_reward_ali ) : ?>
						<dialog class="tb-reward" id="tb-reward" aria-label="<?php esc_attr_e( '打赏作者', 'tobpress' ); ?>">
							<button type="button" class="tb-dialog-close" data-reward-close aria-label="<?php esc_attr_e( '关闭', 'tobpress' ); ?>">✕</button>
							<p class="tb-reward-text"><?php esc_html_e( '如果这篇文章对你有帮助，欢迎打赏支持。', 'tobpress' ); ?></p>
							<div class="tb-reward-codes">
								<?php if ( $tobpress_reward_wx ) : ?>
									<figure><img loading="lazy" src="<?php echo esc_url( $tobpress_reward_wx ); ?>" alt="<?php esc_attr_e( '微信收款码', 'tobpress' ); ?>"><figcaption><?php esc_html_e( '微信', 'tobpress' ); ?></figcaption></figure>
								<?php endif; ?>
								<?php if ( $tobpress_reward_ali ) : ?>
									<figure><img loading="lazy" src="<?php echo esc_url( $tobpress_reward_ali ); ?>" alt="<?php esc_attr_e( '支付宝收款码', 'tobpress' ); ?>"><figcaption><?php esc_html_e( '支付宝', 'tobpress' ); ?></figcaption></figure>
								<?php endif; ?>
							</div>
						</dialog>
					<?php endif; ?>
				</article>

				<?php tobpress_related_posts(); ?>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			<?php endwhile; ?>
		</main>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
