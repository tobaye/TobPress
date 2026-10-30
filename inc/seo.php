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
		if ( $post ) {
			$custom_desc = trim( (string) get_post_meta( $post->ID, '_tobpress_seo_desc', true ) );
			if ( '' !== $custom_desc ) {
				return $custom_desc;
			}
		}
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

/* ------------------------------------------------------------------
 * Per-post SEO description: a small meta box on the post/page editor.
 * Stored as _tobpress_seo_desc; tobpress_seo_description() prefers it
 * over the excerpt and the auto-trimmed content.
 * ------------------------------------------------------------------ */

function tobpress_seo_meta_box() {
	foreach ( array( 'post', 'page' ) as $screen ) {
		add_meta_box(
			'tobpress_seo_desc',
			__( 'TobPress：SEO 描述', 'tobpress' ),
			'tobpress_seo_meta_box_html',
			$screen,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'tobpress_seo_meta_box' );

function tobpress_seo_meta_box_html( $post ) {
	wp_nonce_field( 'tobpress_seo_desc_save', 'tobpress_seo_desc_nonce' );
	$value = (string) get_post_meta( $post->ID, '_tobpress_seo_desc', true );
	?>
	<p>
		<label class="screen-reader-text" for="tobpress-seo-desc"><?php esc_html_e( 'SEO 描述', 'tobpress' ); ?></label>
		<textarea id="tobpress-seo-desc" name="tobpress_seo_desc" rows="3" class="large-text" maxlength="300" placeholder="<?php esc_attr_e( '留空则自动使用摘要或正文开头。建议 60–120 字，写清这篇讲什么。', 'tobpress' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	</p>
	<?php
}

function tobpress_seo_meta_box_save( $post_id ) {
	if ( ! isset( $_POST['tobpress_seo_desc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tobpress_seo_desc_nonce'] ) ), 'tobpress_seo_desc_save' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$value = isset( $_POST['tobpress_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tobpress_seo_desc'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( '' === $value ) {
		delete_post_meta( $post_id, '_tobpress_seo_desc' );
	} else {
		update_post_meta( $post_id, '_tobpress_seo_desc', $value );
	}
}
add_action( 'save_post', 'tobpress_seo_meta_box_save' );
