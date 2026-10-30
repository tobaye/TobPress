<?php
/**
 * Static page template.
 *
 * @package TobPress
 */

get_header();

$tobpress_has_sidebar = is_active_sidebar( 'sidebar-main' ) && 'no-sidebar' !== get_theme_mod( 'tobpress_layout', 'sidebar-right' );
?>
<div class="tb-wrap">
	<div class="tb-layout<?php echo $tobpress_has_sidebar ? ' has-sidebar' : ''; ?>">
		<main class="tb-main" id="tb-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'tb-article' ); ?>>
					<?php tobpress_breadcrumb(); ?>
					<header class="tb-article-head">
						<h1 class="tb-article-title"><?php the_title(); ?></h1>
					</header>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="tb-featured">
							<?php the_post_thumbnail( 'large', array( 'alt' => '' ) ); ?>
						</figure>
					<?php endif; ?>
					<div class="tb-content">
						<?php the_content(); ?>
					</div>
					<?php
					wp_link_pages(
						array(
							'before' => '<div class="tb-pagination"><div class="nav-links">',
							'after'  => '</div></div>',
						)
					);
					?>
				</article>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			<?php endwhile; ?>
		</main>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
