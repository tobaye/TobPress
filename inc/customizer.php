<?php
/**
 * Customizer settings for TobPress.
 *
 * @package TobPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tobpress_customize( $wp_customize ) {
	$wp_customize->add_section(
		'tobpress_options',
		array(
			'title'    => __( 'TobPress 设置', 'tobpress' ),
			'priority' => 30,
		)
	);

	// Primary colour.
	$wp_customize->add_setting(
		'tobpress_primary_color',
		array(
			'default'           => '#2f7fe0',
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'tobpress_primary_color',
			array(
				'label'   => __( '主色调', 'tobpress' ),
				'section' => 'tobpress_options',
			)
		)
	);

	// Layout.
	$wp_customize->add_setting(
		'tobpress_layout',
		array(
			'default'           => 'sidebar-right',
			'sanitize_callback' => 'tobpress_sanitize_layout',
		)
	);
	$wp_customize->add_control(
		'tobpress_layout',
		array(
			'label'   => __( '博客布局', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'select',
			'choices' => array(
				'sidebar-right' => __( '内容 + 右边栏', 'tobpress' ),
				'no-sidebar'    => __( '全宽内容（无边栏）', 'tobpress' ),
			),
		)
	);

	// Dark mode default.
	$wp_customize->add_setting(
		'tobpress_dark_default',
		array(
			'default'           => 'system',
			'sanitize_callback' => 'tobpress_sanitize_dark',
		)
	);
	$wp_customize->add_control(
		'tobpress_dark_default',
		array(
			'label'   => __( '默认配色模式', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'select',
			'choices' => array(
				'system' => __( '跟随系统', 'tobpress' ),
				'light'  => __( '浅色', 'tobpress' ),
				'dark'   => __( '深色', 'tobpress' ),
			),
		)
	);

	// Homepage carousel.
	$wp_customize->add_setting(
		'tobpress_show_carousel',
		array(
			'default'           => true,
			'sanitize_callback' => 'tobpress_sanitize_bool',
		)
	);
	$wp_customize->add_control(
		'tobpress_show_carousel',
		array(
			'label'   => __( '在首页显示精选轮播', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'checkbox',
		)
	);

	// Homepage welcome strip.
	$wp_customize->add_setting(
		'tobpress_show_welcome',
		array(
			'default'           => true,
			'sanitize_callback' => 'tobpress_sanitize_bool',
		)
	);
	$wp_customize->add_control(
		'tobpress_show_welcome',
		array(
			'label'   => __( '在首页显示欢迎条', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'tobpress_welcome_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'tobpress_welcome_text',
		array(
			'label'       => __( '欢迎条文案（留空使用站点副标题）', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'text',
		)
	);

	// Mobile bottom tab bar.
	$wp_customize->add_setting(
		'tobpress_mobile_tabbar',
		array(
			'default'           => true,
			'sanitize_callback' => 'tobpress_sanitize_bool',
		)
	);
	$wp_customize->add_control(
		'tobpress_mobile_tabbar',
		array(
			'label'   => __( '显示移动端底部导航栏', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'checkbox',
		)
	);

	// Site-wide notice bar (empty text hides the bar).
	$wp_customize->add_setting(
		'tobpress_notice_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'tobpress_notice_text',
		array(
			'label'       => __( '顶部公告文案（留空不显示公告条）', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'tobpress_notice_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'tobpress_notice_url',
		array(
			'label'       => __( '顶部公告链接（可空）', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'url',
		)
	);

	// Related posts count.
	$wp_customize->add_setting(
		'tobpress_related_count',
		array(
			'default'           => 3,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'tobpress_related_count',
		array(
			'label'       => __( '相关文章数量', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 6 ),
		)
	);

	// ICP filing text (footer).
	$wp_customize->add_setting(
		'tobpress_icp_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'tobpress_icp_text',
		array(
			'label'       => __( '备案号文字（页脚）', 'tobpress' ),
			'description' => __( '纯文本，例如：萌ICP备20221986号。留空则隐藏。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'text',
		)
	);
	$wp_customize->add_setting(
		'tobpress_icp_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'tobpress_icp_url',
		array(
			'label'   => __( '备案查询链接', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'url',
		)
	);

	// Post list style (home / archives / search).
	$wp_customize->add_setting(
		'tobpress_card_style',
		array(
			'default'           => 'list',
			'sanitize_callback' => 'tobpress_sanitize_card_style',
		)
	);
	$wp_customize->add_control(
		'tobpress_card_style',
		array(
			'label'   => __( '文章列表样式', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'select',
			'choices' => array(
				'list' => __( '横排列表（缩略图 + 文字）', 'tobpress' ),
				'card' => __( '卡片网格', 'tobpress' ),
			),
		)
	);

	// Built-in SEO output master switch.
	$wp_customize->add_setting(
		'tobpress_seo_enable',
		array(
			'default'           => true,
			'sanitize_callback' => 'tobpress_sanitize_bool',
		)
	);
	$wp_customize->add_control(
		'tobpress_seo_enable',
		array(
			'label'       => __( '启用内置 SEO 输出', 'tobpress' ),
			'description' => __( '输出 meta 描述、Open Graph、canonical 与 BlogPosting 结构化数据。装了 SEO 插件时请关闭（检测到常见 SEO 插件时 TobPress 也会自动让行）。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'checkbox',
		)
	);

	// Footer theme link (points to the theme intro article or repo).
	$wp_customize->add_setting(
		'tobpress_theme_url',
		array(
			'default'           => 'https://github.com/tobaye/TobPress',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'tobpress_theme_url',
		array(
			'label'       => __( '主题链接（页脚）', 'tobpress' ),
			'description' => __( '页脚「主题：TobPress」的指向，一般填主题介绍文章地址。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'url',
		)
	);

	// Immersive hero title on single posts (featured image as header background).
	$wp_customize->add_setting(
		'tobpress_hero_title',
		array(
			'default'           => false,
			'sanitize_callback' => 'tobpress_sanitize_bool',
		)
	);
	$wp_customize->add_control(
		'tobpress_hero_title',
		array(
			'label'       => __( '文章页沉浸式标题区', 'tobpress' ),
			'description' => __( '用文章特色图作标题区背景。开启后正文顶部不再重复显示特色图。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'checkbox',
		)
	);

	// Reward (tip) QR images; the reward button appears when at least one is set.
	$wp_customize->add_setting(
		'tobpress_reward_wx',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'tobpress_reward_wx',
		array(
			'label'       => __( '打赏：微信收款码图片链接', 'tobpress' ),
			'description' => __( '留空则不显示微信打赏。两个收款码都留空时，文章页不显示打赏按钮。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'url',
		)
	);
	$wp_customize->add_setting(
		'tobpress_reward_ali',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'tobpress_reward_ali',
		array(
			'label'   => __( '打赏：支付宝收款码图片链接', 'tobpress' ),
			'section' => 'tobpress_options',
			'type'    => 'url',
		)
	);

	// Copyright line template for single posts.
	$wp_customize->add_setting(
		'tobpress_copyright_text',
		array(
			'default'           => '版权声明：本文由 {author} 原创发表于 {year} 年，原文《{title}》，地址：{url}。转载请注明出处。',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'tobpress_copyright_text',
		array(
			'label'       => __( '版权声明（文章页）', 'tobpress' ),
			'description' => __( '附加在每篇文章末尾。占位符：{title} {url} {author} {year}。留空则隐藏。', 'tobpress' ),
			'section'     => 'tobpress_options',
			'type'        => 'textarea',
		)
	);
}
add_action( 'customize_register', 'tobpress_customize' );

function tobpress_sanitize_layout( $value ) {
	return in_array( $value, array( 'sidebar-right', 'no-sidebar' ), true ) ? $value : 'sidebar-right';
}

function tobpress_sanitize_dark( $value ) {
	return in_array( $value, array( 'system', 'light', 'dark' ), true ) ? $value : 'system';
}

function tobpress_sanitize_bool( $value ) {
	return (bool) $value;
}

function tobpress_sanitize_card_style( $value ) {
	return in_array( $value, array( 'list', 'card' ), true ) ? $value : 'list';
}
