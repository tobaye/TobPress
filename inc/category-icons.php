<?php
/**
 * Category icons: every category gets a small colorful SVG icon and an
 * accent color, shown in the header nav, the home category chips, post
 * cards, breadcrumbs and category archive headers.
 *
 * Resolution order per term: term meta (_tobpress_cat_icon /
 * _tobpress_cat_color, editable on the category edit screen) → built-in
 * keyword defaults matched against the term name/slug → generic tag icon
 * with a palette color. Zero external assets, inline SVG only.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Built-in icon set: key => label + inner SVG markup.
 * Stroke icons inherit the category color via currentColor; the cube
 * icon (fill) carries its own Rubik's-cube colors.
 *
 * @return array<string,array{label:string,svg:string,fill?:bool}>
 */
function tobpress_cat_icons() {
	$icons = array(
		'app'    => array(
			'label' => '应用网格',
			'svg'   => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
		),
		'code'   => array(
			'label' => '代码',
			'svg'   => '<path d="m8 6-6 6 6 6"/><path d="m16 6 6 6-6 6"/>',
		),
		'gift'   => array(
			'label' => '礼物盒',
			'svg'   => '<path d="M20 12v10H4V12"/><rect x="2" y="7" width="20" height="5"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z"/>',
		),
		'chat'   => array(
			'label' => '对话气泡',
			'svg'   => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
		),
		'cube'   => array(
			'label' => '魔方',
			'fill'  => true,
			'svg'   => '<rect x="1.8" y="1.8" width="6" height="6" rx="1.6" fill="#f43f5e"/><rect x="9" y="1.8" width="6" height="6" rx="1.6" fill="#f59e0b"/><rect x="16.2" y="1.8" width="6" height="6" rx="1.6" fill="#facc15"/><rect x="1.8" y="9" width="6" height="6" rx="1.6" fill="#22c55e"/><rect x="9" y="9" width="6" height="6" rx="1.6" fill="#38bdf8"/><rect x="16.2" y="9" width="6" height="6" rx="1.6" fill="#6366f1"/><rect x="1.8" y="16.2" width="6" height="6" rx="1.6" fill="#a855f7"/><rect x="9" y="16.2" width="6" height="6" rx="1.6" fill="#ec4899"/><rect x="16.2" y="16.2" width="6" height="6" rx="1.6" fill="#fb923c"/>',
		),
		'folder' => array(
			'label' => '文件夹',
			'svg'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
		),
		'book'   => array(
			'label' => '书本',
			'svg'   => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
		),
		'pen'    => array(
			'label' => '笔',
			'svg'   => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
		),
		'music'  => array(
			'label' => '音乐',
			'svg'   => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
		),
		'camera' => array(
			'label' => '相机',
			'svg'   => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
		),
		'star'   => array(
			'label' => '星星',
			'svg'   => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21 7 14.2 2 9.3l6.9-1z"/>',
		),
		'tag'    => array(
			'label' => '标签',
			'svg'   => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.3" fill="currentColor" stroke="none"/>',
		),
	);

	return apply_filters( 'tobpress_cat_icons', $icons );
}

/**
 * Keyword defaults: [keywords, icon key, color]. First match wins,
 * matched against "name slug" lowercased.
 *
 * @return array<int,array{0:string[],1:string,2:string}>
 */
function tobpress_cat_default_rules() {
	return array(
		array( array( '魔方', 'mofang', 'rubik', 'cube' ), 'cube', '#8b5cf6' ),
		array( array( '软件', '应用', 'app', 'soft' ), 'app', '#ff8a3d' ),
		array( array( '技术', '教程', '开发', '编程', 'tech', 'code' ), 'code', '#2f7fe0' ),
		array( array( '资源', '下载', 'resource', 'download' ), 'gift', '#1faa6b' ),
		array( array( '说说', '随笔', '日常', '日记', 'shuoshuo', 'talk' ), 'chat', '#ff6b9d' ),
		array( array( '音乐', 'music' ), 'music', '#e05252' ),
		array( array( '摄影', '照片', '相册', 'photo', 'camera' ), 'camera', '#0ea5e9' ),
		array( array( '读书', '阅读', '书', 'book' ), 'book', '#a16207' ),
		array( array( '收藏', '精选', '推荐', 'star' ), 'star', '#f59e0b' ),
		array( array( '文件', '归档', 'file', 'folder' ), 'folder', '#64748b' ),
		array( array( '写作', '文字', 'pen' ), 'pen', '#0d9488' ),
	);
}

/**
 * Fallback palette for categories no rule matches.
 *
 * @return string[]
 */
function tobpress_cat_palette() {
	return array( '#2f7fe0', '#1faa6b', '#ff8a3d', '#ff6b9d', '#8b5cf6', '#0ea5e9', '#e05252' );
}

/**
 * The default (icon, color) pair a term resolves to without meta.
 *
 * @param WP_Term $term Category term.
 * @return array{icon:string,color:string}
 */
function tobpress_cat_defaults( $term ) {
	$haystack = mb_strtolower( $term->name . ' ' . $term->slug );
	foreach ( tobpress_cat_default_rules() as $rule ) {
		foreach ( $rule[0] as $keyword ) {
			if ( '' !== $keyword && false !== mb_strpos( $haystack, mb_strtolower( $keyword ) ) ) {
				return array(
					'icon'  => $rule[1],
					'color' => $rule[2],
				);
			}
		}
	}
	$palette = tobpress_cat_palette();

	return array(
		'icon'  => 'tag',
		'color' => $palette[ ( (int) $term->term_id ) % count( $palette ) ],
	);
}

/**
 * Effective icon key for a category (meta overrides defaults).
 *
 * @param WP_Term $term Category term.
 */
function tobpress_cat_icon_key( $term ) {
	$icons = tobpress_cat_icons();
	$meta  = (string) get_term_meta( $term->term_id, '_tobpress_cat_icon', true );
	if ( '' !== $meta && isset( $icons[ $meta ] ) ) {
		return $meta;
	}
	$defaults = tobpress_cat_defaults( $term );

	return isset( $icons[ $defaults['icon'] ] ) ? $defaults['icon'] : 'tag';
}

/**
 * Effective accent color for a category (meta overrides defaults).
 *
 * @param WP_Term $term Category term.
 */
function tobpress_cat_color( $term ) {
	$meta = (string) get_term_meta( $term->term_id, '_tobpress_cat_color', true );
	if ( '' !== $meta && sanitize_hex_color( $meta ) ) {
		return $meta;
	}
	$defaults = tobpress_cat_defaults( $term );

	return $defaults['color'];
}

/**
 * Icon markup for a category: a span carrying --tb-cat-c and the SVG.
 *
 * @param WP_Term $term        Category term.
 * @param string  $extra_class Extra class for size variants.
 */
function tobpress_cat_icon( $term, $extra_class = '' ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}
	$icons = tobpress_cat_icons();
	$key   = tobpress_cat_icon_key( $term );
	$def   = $icons[ $key ];
	$color = tobpress_cat_color( $term );
	if ( ! empty( $def['fill'] ) ) {
		$svg = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $def['svg'] . '</svg>';
	} else {
		$svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $def['svg'] . '</svg>';
	}

	return '<span class="tb-cat-ic' . ( '' !== $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" style="--tb-cat-c:' . esc_attr( $color ) . '" aria-hidden="true">' . $svg . '</span>';
}

/**
 * Full category link with icon and accent color variable.
 *
 * @param WP_Term $term  Category term.
 * @param string  $class Link class.
 */
function tobpress_cat_link( $term, $class = '' ) {
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}

	return '<a' . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' )
		. ' href="' . esc_url( get_category_link( $term ) ) . '"'
		. ' style="--tb-cat-c:' . esc_attr( tobpress_cat_color( $term ) ) . '">'
		. tobpress_cat_icon( $term, 'tb-cat-ic-sm' )
		. '<span>' . esc_html( $term->name ) . '</span></a>';
}

/**
 * Prepend the category icon to category items in nav menus
 * (header nav and the mobile drawer share the primary menu).
 *
 * @param string  $title Menu item title.
 * @param WP_Post $item  Menu item.
 */
function tobpress_cat_nav_menu_icon( $title, $item ) {
	if ( ! $item || 'category' !== $item->object || empty( $item->object_id ) ) {
		return $title;
	}
	$term = get_term( (int) $item->object_id, 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		return $title;
	}

	return tobpress_cat_icon( $term ) . '<span>' . $title . '</span>';
}
add_filter( 'nav_menu_item_title', 'tobpress_cat_nav_menu_icon', 10, 2 );

/* ---------------------------------------------------------------------
 * Admin: icon / color fields on the category add & edit screens.
 * ------------------------------------------------------------------- */

/**
 * Fields on the "add category" form.
 */
function tobpress_cat_add_form_fields() {
	?>
	<div class="form-field term-tobpress-icon-wrap">
		<label for="tobpress_cat_icon"><?php esc_html_e( '分类图标（TobPress）', 'tobpress' ); ?></label>
		<select name="tobpress_cat_icon" id="tobpress_cat_icon">
			<option value=""><?php esc_html_e( '自动（按分类名匹配）', 'tobpress' ); ?></option>
			<?php foreach ( tobpress_cat_icons() as $key => $def ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $def['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<p><?php esc_html_e( '主题会在导航、首页分类条、文章卡片等处显示这个彩色小图标。', 'tobpress' ); ?></p>
	</div>
	<div class="form-field term-tobpress-color-wrap">
		<label for="tobpress_cat_color"><?php esc_html_e( '分类颜色（TobPress）', 'tobpress' ); ?></label>
		<input type="text" name="tobpress_cat_color" id="tobpress_cat_color" value="" placeholder="#2f7fe0" pattern="^#([0-9a-fA-F]{6})$">
		<p><?php esc_html_e( '十六进制颜色，如 #ff8a3d；留空则按分类名自动配色。', 'tobpress' ); ?></p>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'tobpress_cat_add_form_fields' );

/**
 * Fields on the "edit category" form.
 *
 * @param WP_Term $term Category term.
 */
function tobpress_cat_edit_form_fields( $term ) {
	$icons    = tobpress_cat_icons();
	$icon     = (string) get_term_meta( $term->term_id, '_tobpress_cat_icon', true );
	$color    = (string) get_term_meta( $term->term_id, '_tobpress_cat_color', true );
	$defaults = tobpress_cat_defaults( $term );
	?>
	<tr class="form-field term-tobpress-icon-wrap">
		<th scope="row"><label for="tobpress_cat_icon"><?php esc_html_e( '分类图标（TobPress）', 'tobpress' ); ?></label></th>
		<td>
			<select name="tobpress_cat_icon" id="tobpress_cat_icon">
				<option value="" <?php selected( $icon, '' ); ?>><?php echo esc_html( sprintf( __( '自动（当前：%s）', 'tobpress' ), $icons[ $defaults['icon'] ]['label'] ) ); ?></option>
				<?php foreach ( $icons as $key => $def ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icon, $key ); ?>><?php echo esc_html( $def['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( '导航、首页分类条、文章卡片等处显示的彩色小图标。', 'tobpress' ); ?></p>
		</td>
	</tr>
	<tr class="form-field term-tobpress-color-wrap">
		<th scope="row"><label for="tobpress_cat_color"><?php esc_html_e( '分类颜色（TobPress）', 'tobpress' ); ?></label></th>
		<td>
			<input type="text" name="tobpress_cat_color" id="tobpress_cat_color" value="<?php echo esc_attr( $color ); ?>" placeholder="<?php echo esc_attr( $defaults['color'] ); ?>" pattern="^#([0-9a-fA-F]{6})$">
			<?php echo tobpress_cat_icon( $term ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts. ?>
			<p class="description"><?php esc_html_e( '十六进制颜色；留空则按分类名自动配色。右侧为当前生效效果。', 'tobpress' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'tobpress_cat_edit_form_fields' );

/**
 * Save the icon / color term meta.
 *
 * @param int $term_id Term ID.
 */
function tobpress_cat_save_fields( $term_id ) {
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- core term forms carry their own nonce, verified before these actions fire.
	if ( isset( $_POST['tobpress_cat_icon'] ) ) {
		$icons = tobpress_cat_icons();
		$icon  = sanitize_key( wp_unslash( $_POST['tobpress_cat_icon'] ) );
		if ( '' === $icon || ! isset( $icons[ $icon ] ) ) {
			delete_term_meta( $term_id, '_tobpress_cat_icon' );
		} else {
			update_term_meta( $term_id, '_tobpress_cat_icon', $icon );
		}
	}
	if ( isset( $_POST['tobpress_cat_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['tobpress_cat_color'] ) );
		if ( $color ) {
			update_term_meta( $term_id, '_tobpress_cat_color', $color );
		} else {
			delete_term_meta( $term_id, '_tobpress_cat_color' );
		}
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
}
add_action( 'created_category', 'tobpress_cat_save_fields' );
add_action( 'edited_category', 'tobpress_cat_save_fields' );
