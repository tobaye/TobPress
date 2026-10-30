<?php
/**
 * TobPress shortcodes (core subset):
 *
 * [tobpress_alert type="info|success|warning|error"]…[/tobpress_alert]
 * [tobpress_collapse title="…"]…[/tobpress_collapse]
 * [tobpress_button url="…"]label[/tobpress_button]
 * [tobpress_download url="…" name="…"]
 * [tobpress_login]…[/tobpress_login]   content for logged-in users
 * [tobpress_reply]…[/tobpress_reply]   content after commenting
 *
 * Original implementations on the native shortcode API.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tobpress_shortcode_alert( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'type' => 'info' ), $atts, 'tobpress_alert' );
	$type = in_array( $atts['type'], array( 'info', 'success', 'warning', 'error' ), true ) ? $atts['type'] : 'info';
	return '<div class="tb-alert tb-alert-' . esc_attr( $type ) . '">' . do_shortcode( wpautop( (string) $content ) ) . '</div>';
}
add_shortcode( 'tobpress_alert', 'tobpress_shortcode_alert' );

function tobpress_shortcode_collapse( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'title' => __( '详情', 'tobpress' ) ), $atts, 'tobpress_collapse' );
	$title = sanitize_text_field( $atts['title'] );
	return '<details class="tb-collapse"><summary>' . esc_html( $title ) . '</summary><div class="tb-collapse-body">'
		. do_shortcode( wpautop( (string) $content ) ) . '</div></details>';
}
add_shortcode( 'tobpress_collapse', 'tobpress_shortcode_collapse' );

function tobpress_shortcode_button( $atts, $content = '' ) {
	$atts  = shortcode_atts(
		array(
			'url'   => '',
			'style' => '',
		),
		$atts,
		'tobpress_button'
	);
	$label = '' !== trim( (string) $content ) ? wp_strip_all_tags( do_shortcode( (string) $content ) ) : __( '按钮', 'tobpress' );
	$class = 'tb-btn' . ( 'ghost' === $atts['style'] ? ' tb-btn-ghost' : '' );
	if ( '' === $atts['url'] ) {
		return '<span class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>';
	}
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $atts['url'] ) . '">' . esc_html( $label ) . '</a>';
}
add_shortcode( 'tobpress_button', 'tobpress_shortcode_button' );

function tobpress_shortcode_download( $atts ) {
	$atts = shortcode_atts(
		array(
			'url'  => '',
			'name' => '',
		),
		$atts,
		'tobpress_download'
	);
	if ( '' === $atts['url'] ) {
		return '';
	}
	$name = '' !== $atts['name'] ? sanitize_text_field( $atts['name'] ) : __( '下载', 'tobpress' );
	return '<p><a class="tb-download" href="' . esc_url( $atts['url'] ) . '" rel="nofollow noopener">'
		. '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M6 11l6 6 6-6M4 21h16"/></svg>'
		. esc_html( $name ) . '</a></p>';
}
add_shortcode( 'tobpress_download', 'tobpress_shortcode_download' );

function tobpress_shortcode_login( $atts, $content = '' ) {
	if ( is_user_logged_in() ) {
		return do_shortcode( (string) $content );
	}
	$redirect = is_singular() ? get_permalink() : home_url( '/' );
	return '<div class="tb-hidden-note">'
		. sprintf(
			/* translators: %s: login URL. */
			__( '此内容仅对登录用户可见。<a href="%s">登录</a>', 'tobpress' ),
			esc_url( wp_login_url( $redirect ) )
		)
		. '</div>';
}
add_shortcode( 'tobpress_login', 'tobpress_shortcode_login' );

/**
 * Has the current visitor (account or remembered commenter e-mail)
 * already commented on the current post?
 */
function tobpress_visitor_has_commented( $post_id ) {
	if ( is_user_logged_in() ) {
		$found = get_comments(
			array(
				'post_id' => $post_id,
				'user_id' => get_current_user_id(),
				'number'  => 1,
				'count'   => true,
				'status'  => 'all',
			)
		);
		return (int) $found > 0;
	}
	$cookie = 'comment_author_email_' . COOKIEHASH;
	if ( isset( $_COOKIE[ $cookie ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized on the next line.
		$email = sanitize_email( wp_unslash( $_COOKIE[ $cookie ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( $email ) {
			$found = get_comments(
				array(
					'post_id'      => $post_id,
					'author_email' => $email,
					'number'       => 1,
					'count'        => true,
					'status'       => 'all',
				)
			);
			return (int) $found > 0;
		}
	}
	return false;
}

function tobpress_shortcode_reply( $atts, $content = '' ) {
	if ( ! is_singular() ) {
		return '';
	}
	$post_id = get_the_ID();
	if ( $post_id && tobpress_visitor_has_commented( $post_id ) ) {
		return do_shortcode( (string) $content );
	}
	return '<div class="tb-hidden-note">'
		. esc_html__( '此内容已隐藏。在本文下发表评论后即可查看。', 'tobpress' )
		. '</div>';
}
add_shortcode( 'tobpress_reply', 'tobpress_shortcode_reply' );

/* ------------------------------------------------------------------
 * Password-visible content: [tobpress_pass pwd="1234"]...[/tobpress_pass]
 * The unlock form posts back to the same article; tobpress_pass_unlock()
 * verifies against every password used on that post and sets an
 * HMAC cookie per post, so several blocks can share one unlock.
 * ------------------------------------------------------------------ */

function tobpress_pass_token( $post_id, $pwd ) {
	return hash_hmac( 'sha256', (int) $post_id . '|' . (string) $pwd, wp_salt() );
}

function tobpress_shortcode_pass( $atts, $content = '' ) {
	if ( ! is_singular() ) {
		return '';
	}
	$atts    = shortcode_atts( array( 'pwd' => '' ), $atts, 'tobpress_pass' );
	$pwd     = (string) $atts['pwd'];
	$post_id = (int) get_the_ID();
	if ( '' === $pwd || ! $post_id ) {
		return '';
	}
	$cookie = 'tobpress_pass_' . $post_id;
	if ( isset( $_COOKIE[ $cookie ] ) && is_string( $_COOKIE[ $cookie ] ) && hash_equals( tobpress_pass_token( $post_id, $pwd ), $_COOKIE[ $cookie ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return do_shortcode( (string) $content );
	}
	$failed = isset( $_GET['tb_pass_err'] ) && '1' === (string) $_GET['tb_pass_err']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$html   = '<form class="tb-pass-form" method="post" action="' . esc_url( get_permalink( $post_id ) ) . '">'
		. '<p class="tb-pass-hint">' . esc_html__( '此内容需密码查看，请输入密码：', 'tobpress' ) . '</p>'
		. '<p class="tb-pass-row">'
		. '<input type="password" name="tobpress_pass_try" autocomplete="off" required>'
		. '<input type="hidden" name="tobpress_pass_post" value="' . $post_id . '">'
		. '<button type="submit">' . esc_html__( '查看', 'tobpress' ) . '</button>'
		. '</p>';
	if ( $failed ) {
		$html .= '<p class="tb-pass-error">' . esc_html__( '密码不正确，请重试。', 'tobpress' ) . '</p>';
	}
	$html .= '</form>';
	return $html;
}
add_shortcode( 'tobpress_pass', 'tobpress_shortcode_pass' );

function tobpress_pass_unlock() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['tobpress_pass_post'] ) || ! isset( $_POST['tobpress_pass_try'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return;
	}
	$post_id = (int) $_POST['tobpress_pass_post']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$try     = sanitize_text_field( wp_unslash( $_POST['tobpress_pass_try'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$post    = get_post( $post_id );
	if ( ! $post ) {
		return;
	}
	$target = get_permalink( $post_id );
	if ( preg_match_all( '/\[tobpress_pass[^\]]*?pwd=["\']([^"\']+)["\']/', (string) $post->post_content, $m ) ) {
		foreach ( $m[1] as $pwd ) {
			if ( hash_equals( (string) $pwd, $try ) ) {
				setcookie( 'tobpress_pass_' . $post_id, tobpress_pass_token( $post_id, $pwd ), time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				wp_safe_redirect( $target );
				exit;
			}
		}
	}
	wp_safe_redirect( add_query_arg( 'tb_pass_err', '1', $target ) );
	exit;
}
add_action( 'init', 'tobpress_pass_unlock' );
