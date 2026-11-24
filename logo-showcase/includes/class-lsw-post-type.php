<?php
/**
 * Showcase post type. Kept as `tplogoshowcase` so existing showcases and shortcodes keep working.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Post_Type {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'load-edit.php', array( __CLASS__, 'redirect_list' ) );
		add_action( 'load-post.php', array( __CLASS__, 'redirect_edit' ) );
		add_action( 'load-post-new.php', array( __CLASS__, 'redirect_new' ) );
		add_action( 'before_delete_post', array( 'LSW_Data', 'forget' ) );
	}

	public static function register() {
		register_post_type(
			LSW::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => _x( 'Logo Showcase', 'post type general name', 'logo-showcase' ),
					'singular_name' => _x( 'Logo Showcase', 'post type singular name', 'logo-showcase' ),
					'menu_name'     => _x( 'Logo Showcase', 'admin menu', 'logo-showcase' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'capability_type'     => 'post',
				'supports'            => array( 'title' ),
			)
		);
	}

	private static function app_url( $hash = '' ) {
		return admin_url( 'admin.php?page=logo-showcase' ) . $hash;
	}

	public static function redirect_list() {
		if ( isset( $_GET['post_type'] ) && LSW::POST_TYPE === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( self::app_url() );
			exit;
		}
	}

	public static function redirect_new() {
		if ( isset( $_GET['post_type'] ) && LSW::POST_TYPE === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( self::app_url( '#/new' ) );
			exit;
		}
	}

	public static function redirect_edit() {
		$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $id && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && LSW::POST_TYPE === get_post_type( $id ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( self::app_url( '#/edit/' . $id ) );
			exit;
		}
	}
}
