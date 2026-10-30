<?php
/**
 * Template Name: 文章归档 (Archives)
 * Template Post Type: page
 *
 * All published posts grouped by year and month, with counts.
 *
 * @package TobPress
 */

get_header();

$tobpress_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

$tobpress_groups = array();
foreach ( $tobpress_posts as $tobpress_p ) {
	$tobpress_y = get_the_date( 'Y', $tobpress_p );
	$tobpress_m = get_the_date( 'm', $tobpress_p );
	$tobpress_groups[ $tobpress_y ][ $tobpress_m ][] = $tobpress_p;
}
?>
<div class="tb-wrap">
	<div class="tb-layout">
		<main class="tb-main" id="tb-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'tb-article' ); ?>>
					<?php tobpress_breadcrumb(); ?>
					<header class="tb-article-head">
						<h1 class="tb-article-title"><?php the_title(); ?></h1>
						<p class="tb-archive-desc">
							<?php
							printf(
								/* translators: %s: total number of published posts. */
								esc_html__( '%s posts in total', 'tobpress' ),
								esc_html( number_format_i18n( count( $tobpress_posts ) ) )
							);
							?>
						</p>
					</header>
					<div class="tb-content">
						<?php the_content(); ?>
					</div>

					<?php foreach ( $tobpress_groups as $tobpress_y => $tobpress_months ) : ?>
						<h2 class="tb-archive-year">
							<?php echo esc_html( $tobpress_y ); ?>
							<small class="tb-archive-desc">
								<?php
								$tobpress_year_count = 0;
								foreach ( $tobpress_months as $tobpress_list ) {
									$tobpress_year_count += count( $tobpress_list );
								}
								printf(
									/* translators: %s: number of posts in the year. */
									esc_html__( '(%s posts)', 'tobpress' ),
									esc_html( number_format_i18n( $tobpress_year_count ) )
								);
								?>
							</small>
						</h2>
						<?php foreach ( $tobpress_months as $tobpress_m => $tobpress_list ) : ?>
							<h3>
								<?php
								echo esc_html(
									date_i18n(
										'F',
										mktime( 0, 0, 0, (int) $tobpress_m, 1, (int) $tobpress_y )
									)
								);
								?>
							</h3>
							<ul class="tb-archive-list">
								<?php foreach ( $tobpress_list as $tobpress_p ) : ?>
									<li>
										<time datetime="<?php echo esc_attr( get_the_date( 'c', $tobpress_p ) ); ?>"><?php echo esc_html( get_the_date( 'm-d', $tobpress_p ) ); ?></time>
										<a href="<?php echo esc_url( get_permalink( $tobpress_p ) ); ?>"><?php echo esc_html( get_the_title( $tobpress_p ) ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</article>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
