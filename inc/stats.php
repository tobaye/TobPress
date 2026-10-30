<?php
/**
 * Lightweight engagement stats: post view counter and a like button.
 *
 * Views are stored in the `tobpress_views` post meta and counted on
 * singular post views only (admins, previews and known bots skipped).
 * Likes are stored in `tobpress_likes`, incremented through Ajax and
 * guarded by a long-lived cookie so one browser counts once.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format a counter the way Chinese blogs usually show it:
 * 184 stays 184, 1630 becomes 1.6K+, 45000 becomes 45K+.
 */
function tobpress_format_count( $n ) {
	$n = (int) $n;
	if ( $n < 1000 ) {
		return (string) $n;
	}
	$k = $n / 1000;
	$s = ( $k >= 100 ) ? (string) round( $k ) : number_format( $k, 1 );
	if ( substr( $s, -2 ) === '.0' ) {
		$s = substr( $s, 0, -2 );
	}
	return $s . 'K+';
}

function tobpress_get_views( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (int) get_post_meta( $post_id, 'tobpress_views', true );
}

function tobpress_get_likes( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (int) get_post_meta( $post_id, 'tobpress_likes', true );
}

/**
 * Count one view per singular post request.
 */
function tobpress_track_views() {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
	foreach ( array( 'bot', 'spider', 'crawler', 'curl', 'wget', 'python-requests', 'httpclient', 'headless' ) as $needle ) {
		if ( '' !== $ua && false !== strpos( $ua, $needle ) ) {
			return;
		}
	}
	$post_id = get_queried_object_id();
	if ( $post_id ) {
		update_post_meta( $post_id, 'tobpress_views', tobpress_get_views( $post_id ) + 1 );
	}
}
add_action( 'wp_head', 'tobpress_track_views', 99 );

/**
 * Hand the Ajax endpoint + nonce to the front-end script.
 */
function tobpress_localize_stats() {
	if ( wp_script_is( 'tobpress-main', 'enqueued' ) ) {
		wp_localize_script(
			'tobpress-main',
			'tobpressData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'tobpress_like' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'tobpress_localize_stats', 20 );

/**
 * Render the like button for a post.
 */
function tobpress_like_button( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$liked   = isset( $_COOKIE[ 'tobpress_liked_' . $post_id ] );
	?>
	<button type="button" class="tb-like-btn<?php echo $liked ? ' is-liked' : ''; ?>" data-like="<?php echo esc_attr( $post_id ); ?>" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>">
		<svg viewBox="0 0 24 24" fill="<?php echo $liked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
		<span class="tb-like-label"><?php esc_html_e( '点赞', 'tobpress' ); ?></span>
		<span class="tb-like-count"><?php echo esc_html( tobpress_format_count( tobpress_get_likes( $post_id ) ) ); ?></span>
	</button>
	<?php
}

/**
 * Ajax: register one like.
 */
function tobpress_ajax_like() {
	check_ajax_referer( 'tobpress_like' );
	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'bad post' ), 400 );
	}
	$cookie = 'tobpress_liked_' . $post_id;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		wp_send_json_success(
			array(
				'count'     => tobpress_get_likes( $post_id ),
				'formatted' => tobpress_format_count( tobpress_get_likes( $post_id ) ),
				'liked'     => true,
			)
		);
	}
	$count = tobpress_get_likes( $post_id ) + 1;
	update_post_meta( $post_id, 'tobpress_likes', $count );
	setcookie( $cookie, '1', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	wp_send_json_success(
		array(
			'count'     => $count,
			'formatted' => tobpress_format_count( $count ),
			'liked'     => true,
		)
	);
}
add_action( 'wp_ajax_tobpress_like', 'tobpress_ajax_like' );
add_action( 'wp_ajax_nopriv_tobpress_like', 'tobpress_ajax_like' );

/* ------------------------------------------------------------------
 * Site statistics (cached) — used by the stats widget.
 * ---------------------------------------------------------------- */

function tobpress_site_stats() {
	$stats = get_transient( 'tobpress_site_stats' );
	if ( false !== $stats && is_array( $stats ) ) {
		return $stats;
	}
	global $wpdb;
	$posts = (int) wp_count_posts( 'post' )->publish;
	// Approximate total word count: characters of stripped post content.
	$chars = $wpdb->get_var( "SELECT SUM(CHAR_LENGTH(post_content)) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish'" ); // phpcs:ignore WordPress.DB
	$first = get_posts(
		array(
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$days = 0;
	if ( $first ) {
		$days = max( 0, (int) floor( ( current_time( 'timestamp' ) - (int) get_post_time( 'U', true, $first[0] ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) / DAY_IN_SECONDS ) );
	}
	$stats = array(
		'posts'    => $posts,
		'words'    => (int) $chars,
		'comments' => (int) wp_count_comments()->approved,
		'days'     => $days,
	);
	set_transient( 'tobpress_site_stats', $stats, HOUR_IN_SECONDS );
	return $stats;
}

/* ------------------------------------------------------------------
 * Comment likes (comment meta counter + cookie guard, Ajax).
 * ---------------------------------------------------------------- */

function tobpress_comment_likes( $comment_id ) {
	return (int) get_comment_meta( (int) $comment_id, 'tobpress_clikes', true );
}

function tobpress_comment_like_button( $comment_id ) {
	$comment_id = (int) $comment_id;
	$liked      = ! empty( $_COOKIE[ 'tobpress_cliked_' . $comment_id ] );
	echo '<button type="button" class="tb-clike-btn' . ( $liked ? ' is-liked' : '' ) . '" data-comment-id="' . esc_attr( $comment_id ) . '" aria-pressed="' . ( $liked ? 'true' : 'false' ) . '">';
	echo '<svg viewBox="0 0 24 24" width="13" height="13" fill="' . ( $liked ? 'currentColor' : 'none' ) . '" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>';
	echo '<span class="tb-clike-count">' . esc_html( tobpress_comment_likes( $comment_id ) ) . '</span>';
	echo '</button>';
}

function tobpress_ajax_comment_like() {
	check_ajax_referer( 'tobpress_like', 'nonce' );
	$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
	$comment    = $comment_id ? get_comment( $comment_id ) : null;
	if ( ! $comment || '1' !== (string) $comment->comment_approved ) {
		wp_send_json_error( array( 'message' => '评论不存在' ), 400 );
	}
	if ( ! empty( $_COOKIE[ 'tobpress_cliked_' . $comment_id ] ) ) {
		wp_send_json_success( array( 'count' => tobpress_comment_likes( $comment_id ), 'liked' => true ) );
	}
	$count = tobpress_comment_likes( $comment_id ) + 1;
	update_comment_meta( $comment_id, 'tobpress_clikes', $count );
	if ( ! headers_sent() ) {
		setcookie( 'tobpress_cliked_' . $comment_id, '1', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	}
	wp_send_json_success( array( 'count' => $count, 'liked' => true ) );
}
add_action( 'wp_ajax_tobpress_comment_like', 'tobpress_ajax_comment_like' );
add_action( 'wp_ajax_nopriv_tobpress_comment_like', 'tobpress_ajax_comment_like' );
