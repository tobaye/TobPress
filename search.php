<?php
/**
 * Search results template.
 *
 * @package TobPress
 */

get_header();

$tobpress_has_sidebar = is_active_sidebar( 'sidebar-main' ) && 'no-sidebar' !== get_theme_mod( 'tobpress_layout', 'sidebar-right' );
$tobpress_card_style  = (string) get_theme_mod( 'tobpress_card_style', 'list' );
?>
<div class="tb-wrap">
	<div class="tb-layout<?php echo $tobpress_has_sidebar ? ' has-sidebar' : ''; ?>">
		<main class="tb-main" id="tb-content">
			<header class="tb-archive-head">
				<h1 class="tb-archive-title">
					<?php
					printf(
						/* translators: %s: search query. */
						esc_html__( '「%s」的搜索结果', 'tobpress' ),
						esc_html( get_search_query() )
					);
					?>
				</h1>
				<?php get_search_form(); ?>
			</header>

			<?php if ( have_posts() ) : ?>
				<div class="tb-card-list style-<?php echo esc_attr( $tobpress_card_style ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					?>
				</div>
				<div class="tb-pagination">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => __( '← 更新', 'tobpress' ),
							'next_text' => __( '更早 →', 'tobpress' ),
						)
					);
					?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</main>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
