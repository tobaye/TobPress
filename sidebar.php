<?php
/**
 * Main sidebar. Renders nothing when the layout is set to
 * no-sidebar or the sidebar has no widgets.
 *
 * @package TobPress
 */

if ( 'no-sidebar' === get_theme_mod( 'tobpress_layout', 'sidebar-right' ) ) {
	return;
}
if ( ! is_active_sidebar( 'sidebar-main' ) ) {
	return;
}
?>
<aside class="tb-sidebar" aria-label="<?php esc_attr_e( '边栏', 'tobpress' ); ?>">
	<?php dynamic_sidebar( 'sidebar-main' ); ?>
</aside>
