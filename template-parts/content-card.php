<?php
/**
 * Post card used by home, archives and search results.
 * Style (list / card grid) follows the tobpress_card_style mod.
 *
 * @package TobPress
 */

$tobpress_card_size = ( 'card' === get_theme_mod( 'tobpress_card_style', 'list' ) ) ? 'tobpress-card' : 'tobpress-thumb';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'tb-card' ); ?>>
	<a class="tb-card-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php tobpress_card_thumb( get_the_ID(), $tobpress_card_size ); ?>
		<?php if ( is_sticky() ) : ?>
			<span class="tb-badge-sticky"><?php esc_html_e( '置顶', 'tobpress' ); ?></span>
		<?php endif; ?>
	</a>
	<div class="tb-card-body">
		<?php
		$tobpress_cats = get_the_category();
		if ( $tobpress_cats ) :
			?>
			<span class="tb-card-cats"><?php foreach ( $tobpress_cats as $tobpress_cat ) { echo tobpress_cat_link( $tobpress_cat, 'tb-card-cat' ); } // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?></span>
		<?php endif; ?>
		<h2 class="tb-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php tobpress_post_meta(); ?>
		<p class="tb-card-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
	</div>
</article>
