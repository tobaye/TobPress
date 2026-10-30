<?php
/**
 * Admin tool: export / import all TobPress theme mods as JSON.
 * Lives under Tools → TobPress Settings Migration. Guarded by the
 * manage_options capability and nonces on both directions.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tobpress_tools_menu() {
	add_management_page(
		__( 'TobPress Settings Migration', 'tobpress' ),
		__( 'TobPress Settings', 'tobpress' ),
		'manage_options',
		'tobpress-tools',
		'tobpress_tools_page'
	);
}
add_action( 'admin_menu', 'tobpress_tools_menu' );

/**
 * Export: GET with a nonce streams a JSON download of all theme mods.
 */
function tobpress_tools_export() {
	if ( ! isset( $_GET['tobpress_export'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verified below.
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'tobpress_export' );

	$mods = get_theme_mods();
	if ( ! is_array( $mods ) ) {
		$mods = array();
	}
	$payload = array(
		'theme'    => 'tobpress',
		'version'  => TOBPRESS_VERSION,
		'exported' => gmdate( 'c' ),
		'mods'     => $mods,
	);

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="tobpress-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
	echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'admin_init', 'tobpress_tools_export' );

/**
 * Import: POST of a JSON payload restores the mods it contains.
 */
function tobpress_tools_import() {
	if ( ! isset( $_POST['tobpress_import'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verified below.
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'tobpress_import' );

	$count = 0;
	$raw   = isset( $_POST['tobpress_import_json'] ) ? wp_unslash( $_POST['tobpress_import_json'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- JSON payload, decoded and type-checked below.
	$data  = json_decode( (string) $raw, true );
	if ( is_array( $data ) && isset( $data['mods'] ) && is_array( $data['mods'] ) ) {
		foreach ( $data['mods'] as $key => $value ) {
			if ( ! is_string( $key ) || '' === $key ) {
				continue;
			}
			if ( is_scalar( $value ) || is_array( $value ) ) {
				set_theme_mod( $key, $value );
				$count++;
			}
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'            => 'tobpress-tools',
				'tobpress_notice' => $count > 0 ? 'imported' : 'failed',
				'count'           => $count,
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'tobpress_tools_import' );

function tobpress_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'tobpress' ) );
	}
	$export_url = wp_nonce_url(
		add_query_arg(
			array(
				'page'            => 'tobpress-tools',
				'tobpress_export' => '1',
			),
			admin_url( 'tools.php' )
		),
		'tobpress_export'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'TobPress Settings Migration', 'tobpress' ); ?></h1>

		<?php if ( isset( $_GET['tobpress_notice'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification -- display-only notice. ?>
			<?php if ( 'imported' === sanitize_key( wp_unslash( $_GET['tobpress_notice'] ) ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					printf(
						/* translators: %s: number of settings restored. */
						esc_html__( 'Import complete: %s setting(s) restored.', 'tobpress' ),
						esc_html( isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0 ) // phpcs:ignore WordPress.Security.NonceVerification -- display only.
					);
					?>
				</p></div>
			<?php else : ?>
				<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Import failed: the pasted content is not a valid TobPress settings JSON.', 'tobpress' ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Export', 'tobpress' ); ?></h2>
		<p><?php esc_html_e( 'Download every TobPress Customizer setting (theme mods) as a JSON file. Nav menus, widgets and site content are not included — those belong to WordPress itself, not the theme.', 'tobpress' ); ?></p>
		<p><a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Download settings JSON', 'tobpress' ); ?></a></p>

		<hr>

		<h2><?php esc_html_e( 'Import', 'tobpress' ); ?></h2>
		<p><?php esc_html_e( 'Paste a previously exported TobPress settings JSON below. Existing settings with the same keys will be overwritten; settings not present in the file are left untouched.', 'tobpress' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'tobpress_import' ); ?>
			<p>
				<label class="screen-reader-text" for="tobpress-import-json"><?php esc_html_e( 'Settings JSON', 'tobpress' ); ?></label>
				<textarea id="tobpress-import-json" name="tobpress_import_json" rows="12" class="large-text code" placeholder='{"theme":"tobpress","mods":{…}}'></textarea>
			</p>
			<p>
				<button type="submit" name="tobpress_import" value="1" class="button"><?php esc_html_e( 'Import settings', 'tobpress' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}
