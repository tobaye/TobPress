<?php
/**
 * Footer template: footer widget columns, copyright bar with the
 * ICP filing line, back-to-top button.
 *
 * @package TobPress
 */

$tobpress_icp_text = trim( (string) get_theme_mod( 'tobpress_icp_text', '' ) );
$tobpress_icp_url  = trim( (string) get_theme_mod( 'tobpress_icp_url', '' ) );

// Copyright year range: the site's first published year through today.
$tobpress_year_now = (int) date_i18n( 'Y' );
$tobpress_first    = get_posts(
	array(
		'numberposts' => 1,
		'order'       => 'ASC',
		'orderby'     => 'date',
		'post_status' => 'publish',
		'fields'      => 'ids',
	)
);
$tobpress_year_start = $tobpress_first ? (int) get_the_date( 'Y', $tobpress_first[0] ) : $tobpress_year_now;
$tobpress_years      = ( $tobpress_year_start < $tobpress_year_now ) ? $tobpress_year_start . '–' . $tobpress_year_now : (string) $tobpress_year_now;
?>
<footer class="tb-footer">
	<div class="tb-wrap">
		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
		<div class="tb-footer-cols">
			<div><?php dynamic_sidebar( 'footer-1' ); ?></div>
			<div><?php dynamic_sidebar( 'footer-2' ); ?></div>
			<div><?php dynamic_sidebar( 'footer-3' ); ?></div>
		</div>
		<?php endif; ?>

		<div class="tb-footer-bar">
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="tb-footer-nav" aria-label="<?php esc_attr_e( '页脚', 'tobpress' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
			<?php endif; ?>

			<p class="tb-footer-main">
				<span class="tb-copyright">&copy; <?php echo esc_html( $tobpress_years ); ?> <a class="tb-site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></span>
				<?php if ( '' !== $tobpress_icp_text ) : ?>
				<span class="tb-sep" aria-hidden="true">·</span>
				<span class="tb-icp">
					<?php if ( '' !== $tobpress_icp_url ) : ?>
						<a href="<?php echo esc_url( $tobpress_icp_url ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( $tobpress_icp_text ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $tobpress_icp_text ); ?>
					<?php endif; ?>
				</span>
				<?php endif; ?>
			</p>

			<p class="tb-footer-credit">
				<?php
				$tobpress_theme_url = trim( (string) get_theme_mod( 'tobpress_theme_url', 'https://github.com/tobaye/TobPress' ) );
				printf(
					/* translators: %s: theme name link. */
					esc_html__( '由 WordPress 强力驱动 · 主题：%s', 'tobpress' ),
					$tobpress_theme_url ? '<a href="' . esc_url( $tobpress_theme_url ) . '" target="_blank" rel="noopener">TobPress</a>' : 'TobPress' // phpcs:ignore WordPress.Security.EscapeOutput -- URL escaped above.
				);
				?>
			</p>
		</div>
	</div>
</footer>

<button type="button" class="tb-icon-btn tb-to-top" id="tb-to-top" aria-label="<?php esc_attr_e( '回到顶部', 'tobpress' ); ?>">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<?php if ( get_theme_mod( 'tobpress_mobile_tabbar', true ) ) : ?>
<nav class="tb-tabbar" aria-label="<?php esc_attr_e( '移动端快捷导航', 'tobpress' ); ?>">
	<a class="tb-tab<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11.2 12 4l9 7.2"/><path d="M5.8 9.8V20h12.4V9.8"/></svg>
		<span><?php esc_html_e( '首页', 'tobpress' ); ?></span>
	</a>
	<button type="button" class="tb-tab" data-tab-proxy=".tb-search-toggle">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20.5 20.5-4.6-4.6"/></svg>
		<span><?php esc_html_e( '搜索', 'tobpress' ); ?></span>
	</button>
	<button type="button" class="tb-tab" data-tab-proxy=".tb-theme-toggle">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13.6A8.4 8.4 0 1 1 10.4 4a6.8 6.8 0 0 0 9.6 9.6z"/></svg>
		<span><?php esc_html_e( '深色', 'tobpress' ); ?></span>
	</button>
	<button type="button" class="tb-tab" data-tab-proxy=".tb-menu-toggle">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
		<span><?php esc_html_e( '菜单', 'tobpress' ); ?></span>
	</button>
	<button type="button" class="tb-tab" data-tab-proxy="#tb-to-top">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
		<span><?php esc_html_e( '顶部', 'tobpress' ); ?></span>
	</button>
</nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
