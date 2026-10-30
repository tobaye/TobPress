<?php
/**
 * Comments template: native WordPress comment system (list + form).
 * Ajax submission and the emoji panel are layered on top by
 * assets/js/tobpress.js; without JS everything still works natively.
 *
 * @package TobPress
 */

if ( post_password_required() ) {
	return;
}

if ( ! function_exists( 'tobpress_comment_callback' ) ) :
	/**
	 * Single comment markup for wp_list_comments().
	 */
	function tobpress_comment_callback( $comment, $args, $depth ) {
		if ( 'pingback' === $comment->comment_type || 'trackback' === $comment->comment_type ) :
			?>
			<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'tb-comment tb-pingback' ); ?>>
				<?php esc_html_e( '引用：', 'tobpress' ); ?> <?php comment_author_link( $comment ); ?> <?php edit_comment_link( __( '编辑', 'tobpress' ), '<span class="edit-link">', '</span>' ); ?>
			<?php
			return;
		endif;
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'tb-comment' ); ?>>
			<article class="tb-comment-inner">
				<header class="tb-comment-meta">
					<?php
					if ( 0 !== (int) $args['avatar_size'] ) {
						echo get_avatar( $comment, $args['avatar_size'] );
					}
					?>
					<cite class="tb-comment-author"><?php echo get_comment_author_link( $comment ); // phpcs:ignore WordPress.Security.EscapeOutput -- core escapes author link parts. ?></cite>
					<?php
					$tobpress_post_author = (int) get_post_field( 'post_author', $comment->comment_post_ID );
					if ( (int) $comment->user_id > 0 && (int) $comment->user_id === $tobpress_post_author ) :
						?>
						<span class="tb-badge-author"><?php esc_html_e( '博主', 'tobpress' ); ?></span>
					<?php endif; ?>
					<?php
					$tobpress_ua = function_exists( 'tobpress_comment_ua' ) ? tobpress_comment_ua( $comment->comment_agent ) : '';
					if ( $tobpress_ua ) :
						?>
						<span class="tb-comment-ua"><?php echo esc_html( $tobpress_ua ); ?></span>
					<?php endif; ?>
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php
							printf(
								/* translators: 1: comment date, 2: comment time. */
								esc_html__( '%1$s 于 %2$s', 'tobpress' ),
								esc_html( get_comment_date( '', $comment ) ),
								esc_html( get_comment_time() )
							);
							?>
						</time>
					</a>
					<?php edit_comment_link( __( '编辑', 'tobpress' ), '<span class="edit-link">', '</span>' ); ?>
				</header>

				<?php if ( '0' === $comment->comment_approved ) : ?>
					<p class="tb-comment-awaiting"><?php esc_html_e( '您的评论正在等待审核。', 'tobpress' ); ?></p>
				<?php endif; ?>

				<div class="tb-comment-body">
					<?php comment_text(); ?>
				</div>

				<div class="tb-comment-foot">
					<?php if ( function_exists( 'tobpress_comment_like_button' ) ) : ?>
						<?php tobpress_comment_like_button( $comment->comment_ID ); ?>
					<?php endif; ?>
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'before'    => '<span class="tb-comment-reply">',
								'after'     => '</span>',
							)
						)
					);
					?>
				</div>
			</article>
		<?php
	}
endif;
?>
<section id="comments" class="tb-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="tb-section-title">
			<?php
			$tobpress_count = get_comments_number();
			printf(
				/* translators: %s: number of comments. */
				esc_html__( '共 %s 条评论', 'tobpress' ),
				esc_html( number_format_i18n( $tobpress_count ) )
			);
			?>
		</h2>

		<ol class="tb-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'format'      => 'html5',
					'short_ping'  => true,
					'avatar_size' => 44,
					'callback'    => 'tobpress_comment_callback',
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => __( '← 较早的评论', 'tobpress' ),
				'next_text' => __( '较新的评论 →', 'tobpress' ),
				'class'     => 'tb-pagination',
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="tb-comment-notice"><?php esc_html_e( '评论已关闭。', 'tobpress' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'           => 'tb-comment-form',
			'title_reply'          => __( '发表评论', 'tobpress' ),
			'title_reply_before'   => '<h2 class="tb-section-title">',
			'title_reply_after'    => '</h2>',
			'label_submit'         => __( '提交评论', 'tobpress' ),
			'comment_notes_before' => '',
		)
	);
	?>
</section>
