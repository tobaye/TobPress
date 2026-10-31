<?php
/**
 * Template helper functions for TobPress.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estimated reading time in minutes for a post.
 */
function tobpress_reading_time( $post_id = 0 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return 1;
	}
	$text = wp_strip_all_tags( $post->post_content );
	$len  = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
	return max( 1, (int) round( $len / 400 ) );
}

/**
 * Post meta line: date · comments · reading time.
 */
function tobpress_post_meta( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$parts   = array();
	$author  = (int) get_post_field( 'post_author', $post_id );
	if ( $author ) {
		$parts[] = '<span class="tb-meta-author">' . tobpress_avatar( $author, 22, array( 'class' => 'tb-meta-avatar' ) ) . '<a href="' . esc_url( get_author_posts_url( $author ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author ) ) . '</a></span>';
	}
	$tobpress_ts = get_post_time( 'U', true, $post_id );
	if ( $tobpress_ts && ( time() - $tobpress_ts ) < 30 * DAY_IN_SECONDS && ( time() - $tobpress_ts ) > 0 ) {
		/* translators: %s: human readable time difference. */
		$parts[] = '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '" title="' . esc_attr( get_the_date( '', $post_id ) ) . '">' . esc_html( sprintf( __( '%s前', 'tobpress' ), human_time_diff( $tobpress_ts, time() ) ) ) . '</time>';
	} else {
		$parts[] = '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . esc_html( get_the_date( '', $post_id ) ) . '</time>';
	}
	if ( function_exists( 'tobpress_get_views' ) ) {
		$parts[] = sprintf( __( '浏览 %s', 'tobpress' ), tobpress_format_count( tobpress_get_views( $post_id ) ) );
	}
	if ( comments_open( $post_id ) ) {
		$count   = get_comments_number( $post_id );
		$parts[] = '<a href="' . esc_url( get_comments_link( $post_id ) ) . '">' . sprintf( __( '%s 条评论', 'tobpress' ), number_format_i18n( $count ) ) . '</a>';
	}
	if ( function_exists( 'tobpress_get_likes' ) ) {
		$parts[] = sprintf( __( '点赞 %s', 'tobpress' ), tobpress_format_count( tobpress_get_likes( $post_id ) ) );
	}
	$parts[] = sprintf( __( '阅读约 %d 分钟', 'tobpress' ), tobpress_reading_time( $post_id ) );
	echo '<div class="tb-meta">' . implode( '<span class="tb-meta-sep">·</span>', $parts ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Thumbnail for list cards: featured image, else first content image,
 * else a gradient placeholder carrying the title's first character.
 */
function tobpress_card_thumb( $post_id = 0, $size = 'tobpress-thumb' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, $size, array( 'class' => 'tb-card-img', 'loading' => 'lazy', 'alt' => '' ) );
		return;
	}
	$post = get_post( $post_id );
	if ( $post && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $m ) ) {
		echo '<img class="tb-card-img" loading="lazy" src="' . esc_url( $m[1] ) . '" alt="">';
		return;
	}
	$title = get_the_title( $post_id );
	$char  = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
	echo '<span class="tb-card-img tb-card-placeholder" aria-hidden="true">' . esc_html( $char ) . '</span>';
}

/**
 * Breadcrumb trail for singular and archive views.
 */
function tobpress_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . __( '首页', 'tobpress' ) . '</a>' );
	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = tobpress_cat_link( $cats[0], 'tb-crumb-cat' );
		}
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = '<span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span aria-current="page">' . esc_html__( '搜索结果', 'tobpress' ) . '</span>';
	} elseif ( is_page() ) {
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	}
	echo '<nav class="tb-breadcrumb" aria-label="breadcrumb">' . implode( '<span class="tb-crumb-sep">/</span>', $items ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Add id anchors to h2/h3 headings in single-post content and
 * remember them so the template can render a table of contents.
 */
function tobpress_add_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$toc  = array();
	$i    = 0;
	$content = preg_replace_callback(
		'/<(h[23])(\s[^>]*)?>(.*?)<\/\1>/is',
		function ( $m ) use ( &$toc, &$i ) {
			$i++;
			$text = trim( wp_strip_all_tags( $m[3] ) );
			if ( '' !== $text ) {
				$toc[] = array(
					'level' => (int) substr( $m[1], 1 ),
					'text'  => $text,
					'id'    => 'tb-h-' . $i,
				);
			}
			$attrs       = isset( $m[2] ) ? $m[2] : '';
			return '<' . $m[1] . $attrs . ' id="tb-h-' . $i . '">' . $m[3] . '</' . $m[1] . '>';
		},
		$content
	);
	$GLOBALS['tobpress_toc'] = $toc;
	return $content;
}
add_filter( 'the_content', 'tobpress_add_heading_ids', 9 );

/**
 * Render the table of contents collected by tobpress_add_heading_ids().
 */
function tobpress_render_toc() {
	$toc = isset( $GLOBALS['tobpress_toc'] ) ? $GLOBALS['tobpress_toc'] : array();
	if ( count( $toc ) < 3 ) {
		return;
	}
	echo '<nav class="tb-toc" aria-label="' . esc_attr__( '文章目录', 'tobpress' ) . '">';
	echo '<h3 class="tb-widget-title">' . esc_html__( '目录', 'tobpress' ) . '</h3><ul>';
	foreach ( $toc as $item ) {
		echo '<li class="tb-toc-l' . (int) $item['level'] . '"><a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['text'] ) . '</a></li>';
	}
	echo '</ul></nav>';
}

/**
 * Related posts: same primary category, newest first.
 */
function tobpress_related_posts( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$count   = (int) get_theme_mod( 'tobpress_related_count', 3 );
	$cats    = wp_get_post_categories( $post_id );
	if ( ! $cats || $count < 1 ) {
		return;
	}
	$q = new WP_Query(
		array(
			'post__not_in'        => array( $post_id ),
			'category__in'        => $cats,
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $q->have_posts() ) {
		return;
	}
	echo '<section class="tb-related"><h2 class="tb-section-title">' . esc_html__( '相关文章', 'tobpress' ) . '</h2><div class="tb-related-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		echo '<a class="tb-related-card" href="' . esc_url( get_permalink() ) . '">';
		tobpress_card_thumb( get_the_ID(), 'tobpress-thumb' );
		echo '<span class="tb-related-card-title">' . esc_html( get_the_title() ) . '</span>';
		echo '<span class="tb-related-card-date">' . esc_html( get_the_date() ) . '</span></a>';
	}
	echo '</div></section>';
	wp_reset_postdata();
}

/**
 * Short browser / OS label from a user-agent string (for comments).
 */
function tobpress_comment_ua( $ua ) {
	$ua      = (string) $ua;
	$browser = '';
	$os      = '';
	if ( preg_match( '/Edg\/[0-9.]+/', $ua ) ) {
		$browser = 'Edge';
	} elseif ( preg_match( '/OPR\/[0-9.]+/', $ua ) ) {
		$browser = 'Opera';
	} elseif ( preg_match( '/MicroMessenger\/[0-9.]+/', $ua ) ) {
		$browser = '微信';
	} elseif ( preg_match( '/QQ\/[0-9.]+/', $ua ) ) {
		$browser = 'QQ';
	} elseif ( preg_match( '/Chrome\/[0-9.]+/', $ua ) ) {
		$browser = 'Chrome';
	} elseif ( preg_match( '/Firefox\/[0-9.]+/', $ua ) ) {
		$browser = 'Firefox';
	} elseif ( preg_match( '/Version\/[0-9.]+.*Safari/', $ua ) ) {
		$browser = 'Safari';
	}
	if ( preg_match( '/Windows NT 10/', $ua ) ) {
		$os = 'Windows 10/11';
	} elseif ( preg_match( '/Windows NT/', $ua ) ) {
		$os = 'Windows';
	} elseif ( preg_match( '/Android/', $ua ) ) {
		$os = 'Android';
	} elseif ( preg_match( '/iPhone|iPad/', $ua ) ) {
		$os = 'iOS';
	} elseif ( preg_match( '/Mac OS X/', $ua ) ) {
		$os = 'macOS';
	} elseif ( preg_match( '/Linux/', $ua ) ) {
		$os = 'Linux';
	}
	$parts = array_filter( array( $browser, $os ) );
	return $parts ? implode( ' · ', $parts ) : '';
}

/**
 * Share row for single posts: copy link, Weibo, QQ, WeChat QR, email.
 */
function tobpress_share_row( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = get_the_title( $post_id );
	?>
	<div class="tb-share" aria-label="<?php esc_attr_e( '分享本文', 'tobpress' ); ?>">
		<span class="tb-share-label"><?php esc_html_e( '分享：', 'tobpress' ); ?></span>
		<button type="button" class="tb-share-btn" data-share-copy="<?php echo esc_url( $url ); ?>"><?php esc_html_e( '复制链接', 'tobpress' ); ?></button>
		<a class="tb-share-btn" target="_blank" rel="noopener" href="<?php echo esc_url( 'https://service.weibo.com/share/share.php?url=' . rawurlencode( $url ) . '&title=' . rawurlencode( $title ) ); ?>"><?php esc_html_e( '微博', 'tobpress' ); ?></a>
		<a class="tb-share-btn" target="_blank" rel="noopener" href="<?php echo esc_url( 'https://connect.qq.com/widget/shareqq/index.html?url=' . rawurlencode( $url ) . '&title=' . rawurlencode( $title ) ); ?>"><?php esc_html_e( 'QQ', 'tobpress' ); ?></a>
		<span class="tb-share-wx">
			<button type="button" class="tb-share-btn" data-share-wx aria-expanded="false"><?php esc_html_e( '微信', 'tobpress' ); ?></button>
			<span class="tb-share-qr" hidden>
				<img loading="lazy" alt="<?php esc_attr_e( '扫码在微信分享', 'tobpress' ); ?>" data-qr-src="<?php echo esc_url( 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . rawurlencode( $url ) ); ?>">
				<span><?php esc_html_e( '扫码分享到微信', 'tobpress' ); ?></span>
			</span>
		</span>
		<a class="tb-share-btn" href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $url ) ); ?>"><?php esc_html_e( '邮件', 'tobpress' ); ?></a>
	</div>
	<?php
}

/**
 * Whether a registered user has a real Gravatar (not the site
 * default silhouette). Probed once via Gravatar's d=404 endpoint
 * and cached in a transient for a week; on any network error we
 * assume "has avatar" so the fallback never misfires.
 */
function tobpress_user_has_gravatar( $user ) {
	$email = strtolower( trim( (string) $user->user_email ) );
	if ( '' === $email ) {
		return true;
	}
	$key    = 'tobpress_grav_' . md5( $email );
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return '1' === $cached;
	}
	$resp = wp_remote_head( 'https://secure.gravatar.com/avatar/' . md5( $email ) . '?d=404&s=80', array( 'timeout' => 2 ) );
	if ( is_wp_error( $resp ) ) {
		set_transient( $key, '1', HOUR_IN_SECONDS );
		return true;
	}
	$has = 200 === (int) wp_remote_retrieve_response_code( $resp );
	set_transient( $key, $has ? '1' : '0', WEEK_IN_SECONDS );
	return $has;
}

/**
 * Avatar with a letter fallback: when a registered user has no real
 * Gravatar, render the first character of their display name on a
 * theme gradient instead of the grey default silhouette.
 */
function tobpress_avatar( $id_or_email, $size, $args = array() ) {
	$user = null;
	if ( is_numeric( $id_or_email ) ) {
		$user = get_userdata( (int) $id_or_email );
	} elseif ( $id_or_email instanceof WP_User ) {
		$user = $id_or_email;
	}
	if ( $user && ! tobpress_user_has_gravatar( $user ) ) {
		$name    = (string) $user->display_name;
		$initial = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
		if ( preg_match( '/^[a-z]$/', $initial ) ) {
			$initial = strtoupper( $initial );
		}
		$class = isset( $args['class'] ) ? (string) $args['class'] : '';
		return '<span class="' . esc_attr( trim( $class . ' tb-letter-avatar' ) ) . '" style="width:' . (int) $size . 'px;height:' . (int) $size . 'px;font-size:' . max( 10, (int) round( $size * 0.45 ) ) . 'px" aria-hidden="true">' . esc_html( $initial ) . '</span>';
	}
	return get_avatar( $id_or_email, $size, '', '', $args );
}
