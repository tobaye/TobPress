<?php
/**
 * Header template: document head, skip link, sticky site header,
 * mobile drawer menu.
 *
 * @package TobPress
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#tb-content"><?php esc_html_e( '跳到内容', 'tobpress' ); ?></a>
<?php if ( is_singular( 'post' ) ) : ?>
<div class="tb-progress" id="tb-progress" aria-hidden="true"></div>
<?php endif; ?>

<?php
$tobpress_notice_text = trim( (string) get_theme_mod( 'tobpress_notice_text', '' ) );
if ( '' !== $tobpress_notice_text ) :
	$tobpress_notice_url = trim( (string) get_theme_mod( 'tobpress_notice_url', '' ) );
	?>
<div class="tb-notice" id="tb-notice" data-notice="<?php echo esc_attr( md5( $tobpress_notice_text ) ); ?>" hidden>
	<div class="tb-wrap tb-notice-in">
		<span class="tb-notice-badge"><?php esc_html_e( '公告', 'tobpress' ); ?></span>
		<?php if ( '' !== $tobpress_notice_url ) : ?>
			<a class="tb-notice-text" href="<?php echo esc_url( $tobpress_notice_url ); ?>"><?php echo esc_html( $tobpress_notice_text ); ?></a>
		<?php else : ?>
			<span class="tb-notice-text"><?php echo esc_html( $tobpress_notice_text ); ?></span>
		<?php endif; ?>
		<button type="button" class="tb-notice-close" id="tb-notice-close" aria-label="<?php esc_attr_e( '关闭公告', 'tobpress' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
		</button>
	</div>
</div>
<?php endif; ?>

<header class="tb-header">
	<div class="tb-wrap tb-header-in">
		<?php if ( has_custom_logo() ) : ?>
			<span class="tb-brand"><?php the_custom_logo(); ?></span>
		<?php else : ?>
			<a class="tb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
		<?php endif; ?>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav class="tb-nav" aria-label="<?php esc_attr_e( '主导航', 'tobpress' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'tb-nav-list',
					'depth'          => 2,
				)
			);
			?>
		</nav>
		<?php endif; ?>

		<div class="tb-header-actions">
			<button type="button" class="tb-icon-btn tb-search-toggle" aria-expanded="false" aria-controls="tb-search-bar" aria-keyshortcuts="Control+K Meta+K" title="搜索（Ctrl+K）" aria-label="<?php esc_attr_e( '搜索', 'tobpress' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
			</button>
			<button type="button" class="tb-icon-btn tb-theme-toggle" aria-label="<?php esc_attr_e( '切换深色模式', 'tobpress' ); ?>">
				<svg class="tb-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				<svg class="tb-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
			</button>
			<button type="button" class="tb-icon-btn tb-menu-toggle" aria-expanded="false" aria-controls="tb-mobile-menu" aria-label="<?php esc_attr_e( '打开菜单', 'tobpress' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</div>
	<div class="tb-wrap tb-search-bar" id="tb-search-bar" hidden>
		<?php get_search_form(); ?>
	</div>
</header>

<div class="tb-scrim" id="tb-scrim" hidden></div>
<aside class="tb-mobile-menu" id="tb-mobile-menu" aria-label="<?php esc_attr_e( '移动菜单', 'tobpress' ); ?>" hidden>
	<?php get_search_form(); ?>
	<?php if ( has_nav_menu( 'primary' ) ) : ?>
	<nav aria-label="<?php esc_attr_e( '主导航', 'tobpress' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'tb-nav-list',
				'depth'          => 1,
			)
		);
		?>
	</nav>
	<?php endif; ?>
</aside>
