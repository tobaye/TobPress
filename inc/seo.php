<?php
/**
 * Lightweight SEO output for TobPress: canonical, meta description,
 * Open Graph basics and BlogPosting structured data on single posts.
 *
 * Controlled by the tobpress_seo_enable Customizer switch (default
 * on). TobPress also steps aside automatically when a known SEO
 * plugin is active, so tags are never printed twice.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the theme's own SEO output should run.
 */
function tobpress_seo_enabled() {
	if ( ! get_theme_mod( 'tobpress_seo_enable', true ) ) {
		return false;
	}
	// Yield to dedicated SEO plugins.
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return false;
	}
	return true;
}

/**
 * Canonical URL for the current view ('' when none applies).
 */
function tobpress_seo_canonical() {
	if ( is_singular() ) {
		$url = get_permalink();
		return $url ? $url : '';
	}
	if ( is_front_page() || is_home() ) {
		return home_url( '/' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) ) {
			$url = get_term_link( $term );
			return is_wp_error( $url ) ? '' : $url;
		}
	}
	if ( is_author() ) {
		return get_author_posts_url( get_queried_object_id() );
	}
	if ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( get_query_var( 'post_type' ) );
		return $url ? $url : '';
	}
	return '';
}

function tobpress_seo_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_excerpt ) ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 40, '' );
		}
		if ( $post ) {
			return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 40, '' );
		}
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_trim_words( wp_strip_all_tags( $desc ), 40, '' );
		}
	}
	return get_bloginfo( 'description' );
}

function tobpress_seo_output() {
	if ( ! tobpress_seo_enabled() ) {
		return;
	}

	// Take over the canonical from core to avoid printing it twice.
	remove_action( 'wp_head', 'rel_canonical' );
	$canonical = tobpress_seo_canonical();
	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	}

	$desc = tobpress_seo_description();
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}

	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( '/' );
	$type  = is_singular( 'post' ) ? 'article' : 'website';
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$img = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
		if ( $img ) {
			echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		}
	}

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		$data = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'mainEntityOfPage' => get_permalink( $post ),
		);
		if ( $desc ) {
			$data['description'] = $desc;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'tobpress_seo_output', 5 );
