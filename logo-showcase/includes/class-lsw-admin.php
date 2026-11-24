<?php
/**
 * Admin screen: one top-level menu that hosts the single-page app.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Admin {

	const SLUG = 'logo-showcase';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'removable_query_args', array( __CLASS__, 'noop' ) );
	}

	public static function noop( $args ) {
		return $args;
	}

	public static function menu() {
		add_menu_page(
			__( 'Logo Showcase', 'logo-showcase' ),
			__( 'Logo Showcase', 'logo-showcase' ),
			'edit_posts',
			self::SLUG,
			array( __CLASS__, 'page' ),
			'dashicons-images-alt2',
			26
		);
	}

	private static function is_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && 'toplevel_page_' . self::SLUG === $screen->id;
	}

	public static function body_class( $classes ) {
		return self::is_screen() ? $classes . ' lsw-admin-page' : $classes;
	}

	public static function page() {
		echo '<div id="lsw-app" class="lsw-app"><noscript>' . esc_html__( 'Logo Showcase needs JavaScript to run its dashboard.', 'logo-showcase' ) . '</noscript></div>';
	}

	public static function assets() {
		if ( ! self::is_screen() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'lsw-admin', LSW_URL . 'build/admin.css', array(), LSW_VERSION );
		wp_enqueue_script( 'lsw-admin', LSW_URL . 'build/admin.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ), LSW_VERSION, true );
		wp_set_script_translations( 'lsw-admin', 'logo-showcase' );
		$boot = array(
			'adminUrl'   => admin_url( 'admin.php?page=' . self::SLUG ),
			'canManage'  => current_user_can( 'manage_options' ),
			'logoUrl'    => LSW_URL . 'assets/img/icon.svg',
			'canUpload'  => current_user_can( 'upload_files' ),
		);
		wp_add_inline_script( 'lsw-admin', 'window.lswBoot=' . wp_json_encode( $boot ) . ';', 'before' );
		do_action( 'lsw_admin_assets' );
	}
}
