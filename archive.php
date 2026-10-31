<?php
/**
 * Archive template (categories, tags, dates, authors).
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
			<?php tobpress_breadcrumb(); ?>
			<header class="tb-archive-head">
				<?php if ( is_category() ) : ?>
					<?php $tobpress_term = get_queried_object(); ?>
					<h1 class="tb-archive-title tb-archive-title-cat"><?php echo tobpress_cat_icon( $tobpress_term, 'tb-cat-ic-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?><span><?php echo esc_html( $tobpress_term->name ); ?></span></h1>
				<?php else : ?>
					<?php the_archive_title( '<h1 class="tb-archive-title">', '</h1>' ); ?>
				<?php endif; ?>
				<?php the_archive_description( '<div class="tb-archive-desc">', '</div>' ); ?>
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
