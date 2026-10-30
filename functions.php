<?php
/**
 * TobPress — a light, modern WordPress theme for personal blogs.
 *
 * Original code. Feature design inspired by the GPL-licensed CorePress
 * theme and the feature set of AeroCore; no third-party theme code is
 * included. TobPress is released under GPL-2.0-or-later.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOBPRESS_VERSION', '1.0.1' );

/**
 * Theme setup.
 */
function tobpress_setup() {
	load_theme_textdomain( 'tobpress', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 360,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'tobpress-thumb', 480, 300, true );
	add_image_size( 'tobpress-card', 720, 405, true );

	register_nav_menus(
		array(
			'primary' => __( '主菜单', 'tobpress' ),
			'footer'  => __( '页脚菜单', 'tobpress' ),
		)
	);
}
add_action( 'after_setup_theme', 'tobpress_setup' );

/**
 * Cache-busting asset version: theme version + file modification time,
 * so same-version hotfixes still invalidate browser and CDN caches.
 */
function tobpress_asset_ver( $relative_path ) {
	$abs = get_template_directory() . $relative_path;
	$mt  = file_exists( $abs ) ? filemtime( $abs ) : 0;
	return $mt ? TOBPRESS_VERSION . '.' . $mt : TOBPRESS_VERSION;
}

/**
 * Enqueue styles and scripts.
 */
function tobpress_enqueue() {
	wp_enqueue_style(
		'tobpress-style',
		get_stylesheet_uri(),
		array(),
		tobpress_asset_ver( '/style.css' )
	);
	wp_enqueue_script(
		'tobpress-main',
		get_template_directory_uri() . '/assets/js/tobpress.js',
		array(),
		tobpress_asset_ver( '/assets/js/tobpress.js' ),
		true
	);
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	tobpress_maybe_enqueue_prism();
}

/**
 * Prism syntax highlighting is loaded only on singular views whose
 * content actually contains code, so plain pages pay nothing for it.
 */
function tobpress_maybe_enqueue_prism() {
	if ( ! is_singular() ) {
		return;
	}
	$tb_post = get_queried_object();
	if ( ! $tb_post instanceof WP_Post ) {
		return;
	}
	$content = (string) $tb_post->post_content;
	if ( false === strpos( $content, '<code' ) && false === strpos( $content, 'language-' ) ) {
		return;
	}
	wp_enqueue_style(
		'tobpress-prism',
		get_template_directory_uri() . '/assets/vendor/prism/prism-tobpress.css',
		array(),
		tobpress_asset_ver( '/assets/vendor/prism/prism-tobpress.css' )
	);
	wp_enqueue_script(
		'tobpress-prism',
		get_template_directory_uri() . '/assets/vendor/prism/prism.min.js',
		array(),
		tobpress_asset_ver( '/assets/vendor/prism/prism.min.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'tobpress_enqueue' );

/**
 * Print the primary colour as CSS variables, plus the no-flash
 * dark-mode boot script, early in <head>.
 */
function tobpress_head_bits() {
	$color = sanitize_hex_color( get_theme_mod( 'tobpress_primary_color', '#2f7fe0' ) );
	if ( ! $color ) {
		$color = '#2f7fe0';
	}
	echo '<style id="tobpress-vars">:root{--tb-primary:' . esc_html( $color ) . ';}</style>' . "\n";

	$default = get_theme_mod( 'tobpress_dark_default', 'system' );
	echo '<script>(function(){try{var t=localStorage.getItem("tobpress-theme");'
		. 'if(!t){t="' . esc_js( $default ) . '";}'
		. 'if(t==="system"){t=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";}'
		. 'document.documentElement.setAttribute("data-theme",t);}catch(e){}})();</script>' . "\n";
}
add_action( 'wp_head', 'tobpress_head_bits', 1 );

/**
 * Register widget areas.
 */
function tobpress_sidebars() {
	register_sidebar(
		array(
			'name'          => __( '主边栏', 'tobpress' ),
			'id'            => 'sidebar-main',
			'description'   => __( '显示在文章与归档页右侧。', 'tobpress' ),
			'before_widget' => '<section id="%1$s" class="tb-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="tb-widget-title">',
			'after_title'   => '</h3>',
		)
	);
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array(
				'name'          => sprintf( __( '页脚栏目 %d', 'tobpress' ), $i ),
				'id'            => 'footer-' . $i,
				'before_widget' => '<section id="%1$s" class="tb-widget tb-footer-widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="tb-widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'tobpress_sidebars' );

/**
 * Excerpt tweaks.
 */
function tobpress_excerpt_length( $length ) {
	return 42;
}
add_filter( 'excerpt_length', 'tobpress_excerpt_length' );

function tobpress_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'tobpress_excerpt_more' );

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/shortcodes.php';
require get_template_directory() . '/inc/tools.php';
require get_template_directory() . '/inc/stats.php';
