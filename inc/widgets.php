<?php
/**
 * TobPress custom widgets: About This Site, Hot Posts, Latest Comments.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TobPress_About_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'tobpress_about',
			__( 'TobPress：关于本站', 'tobpress' ),
			array( 'description' => __( '站点标志、名称与简短介绍。', 'tobpress' ) )
		);
	}

	public function widget( $args, $instance ) {
		$text = isset( $instance['text'] ) ? $instance['text'] : '';
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="tb-about">';
		if ( has_custom_logo() ) {
			the_custom_logo();
		}
		echo '<div class="tb-about-name">' . esc_html( get_bloginfo( 'name' ) ) . '</div>';
		if ( $text ) {
			echo '<p class="tb-about-text">' . esc_html( $text ) . '</p>';
		} elseif ( get_bloginfo( 'description' ) ) {
			echo '<p class="tb-about-text">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
		}
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function form( $instance ) {
		$text = isset( $instance['text'] ) ? $instance['text'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( '介绍文字：', 'tobpress' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" rows="4"><?php echo esc_textarea( $text ); ?></textarea>
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array( 'text' => sanitize_textarea_field( isset( $new['text'] ) ? $new['text'] : '' ) );
	}
}

class TobPress_Hot_Posts_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'tobpress_hot',
			__( 'TobPress：热门文章', 'tobpress' ),
			array( 'description' => __( '评论最多的文章。', 'tobpress' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = isset( $instance['title'] ) && $instance['title'] ? $instance['title'] : __( '热门文章', 'tobpress' );
		$count = isset( $instance['count'] ) ? max( 1, (int) $instance['count'] ) : 5;
		$q     = new WP_Query(
			array(
				'posts_per_page'      => $count,
				'orderby'             => 'comment_count',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( ! $q->have_posts() ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<ul class="tb-mini-list">';
		while ( $q->have_posts() ) {
			$q->the_post();
			echo '<li><a href="' . esc_url( get_permalink() ) . '">';
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( array( 96, 96 ), array( 'class' => 'tb-mini-thumb', 'loading' => 'lazy', 'alt' => '' ) );
			}
			echo '<span class="tb-mini-title">' . esc_html( get_the_title() ) . '</span>';
			echo '<span class="tb-mini-date">' . esc_html( get_the_date() ) . '</span>';
			echo '</a></li>';
		}
		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( '标题：', 'tobpress' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( '文章数量：', 'tobpress' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="10" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( isset( $new['title'] ) ? $new['title'] : '' ),
			'count' => max( 1, min( 10, (int) ( isset( $new['count'] ) ? $new['count'] : 5 ) ) ),
		);
	}
}

class TobPress_Recent_Comments_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'tobpress_recent_comments',
			__( 'TobPress：最新评论', 'tobpress' ),
			array( 'description' => __( '带头像的最新已通过评论。', 'tobpress' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title    = isset( $instance['title'] ) && $instance['title'] ? $instance['title'] : __( '最新评论', 'tobpress' );
		$count    = isset( $instance['count'] ) ? max( 1, (int) $instance['count'] ) : 5;
		$comments = get_comments(
			array(
				'number' => $count,
				'status' => 'approve',
				'type'   => 'comment',
			)
		);
		if ( ! $comments ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<ul class="tb-mini-list tb-comments-list">';
		foreach ( $comments as $c ) {
			$url = get_comment_link( $c );
			echo '<li><a href="' . esc_url( $url ) . '">';
			echo get_avatar( $c, 32, '', '', array( 'class' => 'tb-mini-avatar' ) );
			echo '<span class="tb-mini-title">' . esc_html( $c->comment_author ) . ': ' . esc_html( wp_trim_words( $c->comment_content, 12 ) ) . '</span>';
			echo '<span class="tb-mini-date">' . esc_html( get_the_title( $c->comment_post_ID ) ) . '</span>';
			echo '</a></li>';
		}
		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( '标题：', 'tobpress' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( '评论数量：', 'tobpress' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="10" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( isset( $new['title'] ) ? $new['title'] : '' ),
			'count' => max( 1, min( 10, (int) ( isset( $new['count'] ) ? $new['count'] : 5 ) ) ),
		);
	}
}

function tobpress_register_widgets() {
	register_widget( 'TobPress_About_Widget' );
	register_widget( 'TobPress_Hot_Posts_Widget' );
	register_widget( 'TobPress_Recent_Comments_Widget' );
	register_widget( 'TobPress_Stats_Widget' );
}
add_action( 'widgets_init', 'tobpress_register_widgets' );

class TobPress_Stats_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'tobpress_stats',
			__( 'TobPress：站点数据', 'tobpress' ),
			array( 'description' => __( '文章数、总字数、评论数与建站天数。', 'tobpress' ) )
		);
	}

	public function widget( $args, $instance ) {
		if ( ! function_exists( 'tobpress_site_stats' ) ) {
			return;
		}
		$title = isset( $instance['title'] ) && $instance['title'] ? $instance['title'] : __( '站点数据', 'tobpress' );
		$s     = tobpress_site_stats();
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="tb-stats-grid">';
		echo '<div class="tb-stat"><span class="tb-stat-num">' . esc_html( number_format_i18n( $s['posts'] ) ) . '</span><span class="tb-stat-label">' . esc_html__( '文章', 'tobpress' ) . '</span></div>';
		echo '<div class="tb-stat"><span class="tb-stat-num">' . esc_html( tobpress_format_count( $s['words'] ) ) . '</span><span class="tb-stat-label">' . esc_html__( '总字数', 'tobpress' ) . '</span></div>';
		echo '<div class="tb-stat"><span class="tb-stat-num">' . esc_html( number_format_i18n( $s['comments'] ) ) . '</span><span class="tb-stat-label">' . esc_html__( '评论', 'tobpress' ) . '</span></div>';
		echo '<div class="tb-stat"><span class="tb-stat-num">' . esc_html( number_format_i18n( $s['days'] ) ) . '</span><span class="tb-stat-label">' . esc_html__( '运行天数', 'tobpress' ) . '</span></div>';
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( '标题：', 'tobpress' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array( 'title' => sanitize_text_field( isset( $new['title'] ) ? $new['title'] : '' ) );
	}
}
