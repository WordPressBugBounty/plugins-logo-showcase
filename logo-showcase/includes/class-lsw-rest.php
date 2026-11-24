<?php
/**
 * REST API used by the admin app (namespace logo-showcase/v1).
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_REST {

	const NS = 'logo-showcase/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	/**
	 * WordPress passes three arguments to validate callbacks, so built-ins such as is_numeric() cannot be used directly (fatal on PHP 8).
	 */
	public static function valid_id( $value ) {
		return is_numeric( $value );
	}

	public static function can_list() {
		return current_user_can( 'edit_posts' );
	}

	public static function can_item( $request ) {
		$id = (int) $request['id'];
		return LSW::is_showcase( $id ) && current_user_can( 'edit_post', $id );
	}

	public static function can_delete( $request ) {
		$id = (int) $request['id'];
		return LSW::is_showcase( $id ) && current_user_can( 'delete_post', $id );
	}

	public static function can_admin() {
		return current_user_can( 'manage_options' );
	}

	public static function routes() {
		$id = array( 'id' => array( 'validate_callback' => array( __CLASS__, 'valid_id' ) ) );
		register_rest_route( self::NS, '/meta', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'meta' ), 'permission_callback' => array( __CLASS__, 'can_list' ) ) );
		register_rest_route( self::NS, '/showcases', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'list_items' ), 'permission_callback' => array( __CLASS__, 'can_list' ) ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'create' ), 'permission_callback' => array( __CLASS__, 'can_list' ) ),
		) );
		register_rest_route( self::NS, '/showcases/(?P<id>\d+)', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'get_item' ), 'permission_callback' => array( __CLASS__, 'can_item' ), 'args' => $id ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'update' ), 'permission_callback' => array( __CLASS__, 'can_item' ), 'args' => $id ),
			array( 'methods' => 'DELETE', 'callback' => array( __CLASS__, 'delete' ), 'permission_callback' => array( __CLASS__, 'can_delete' ), 'args' => $id ),
		) );
		register_rest_route( self::NS, '/showcases/(?P<id>\d+)/duplicate', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'duplicate' ), 'permission_callback' => array( __CLASS__, 'can_item' ), 'args' => $id ) );
		register_rest_route( self::NS, '/preview', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'preview' ), 'permission_callback' => array( __CLASS__, 'can_list' ) ) );
		register_rest_route( self::NS, '/settings', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'get_global' ), 'permission_callback' => array( __CLASS__, 'can_admin' ) ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_global' ), 'permission_callback' => array( __CLASS__, 'can_admin' ) ),
		) );
	}

	public static function meta() {
		$layouts = array(
			array( 'id' => 'slider', 'label' => __( 'Slider', 'logo-showcase' ) ),
			array( 'id' => 'grid', 'label' => __( 'Grid', 'logo-showcase' ) ),
			array( 'id' => 'list', 'label' => __( 'List', 'logo-showcase' ) ),
			array( 'id' => 'ticker', 'label' => __( 'Ticker', 'logo-showcase' ) ),
			array( 'id' => 'table', 'label' => __( 'Table', 'logo-showcase' ) ),
			array( 'id' => 'filter', 'label' => __( 'Filter grid', 'logo-showcase' ) ),
			array( 'id' => 'tiers', 'label' => __( 'Sponsor tiers', 'logo-showcase' ) ),
		);
		$layouts = apply_filters( 'lsw_layout_choices', $layouts );
		$registered = LSW_Renderer::layouts();
		foreach ( $layouts as &$l ) {
			$l['pro'] = ! isset( $registered[ $l['id'] ] );
		}
		unset( $l );
		return rest_ensure_response( array(
			'version'     => LSW_VERSION,
			'isPro'       => LSW::is_pro(),
			'hasProCode'  => LSW::has_pro_code(),
			'upgradeUrl'  => LSW::upgrade_url(),
			'docsUrl'     => LSW::docs_url(),
			'fields'      => LSW_Schema::describe(),
			'sections'    => LSW_Schema::sections(),
			'defaults'    => LSW_Schema::defaults(),
			'layouts'     => $layouts,
			'templates'   => LSW_Templates::all(),
			'features'    => apply_filters( 'lsw_features', array() ),
			'logoFields'  => apply_filters( 'lsw_logo_fields', array() ),
			'canCss'      => current_user_can( 'manage_options' ),
			'frontend'    => array( 'css' => LSW_URL . 'assets/css/frontend.css?ver=' . LSW_VERSION, 'js' => LSW_URL . 'assets/js/frontend.js?ver=' . LSW_VERSION, 'extra' => apply_filters( 'lsw_preview_assets', array() ) ),
			'rtl'         => is_rtl(),
		) );
	}

	private static function format( $id, $full = true ) {
		$post = get_post( $id );
		$data = LSW_Data::get( $id );
		$out  = array(
			'id'        => (int) $id,
			'title'     => get_the_title( $id ),
			'status'    => $post ? $post->post_status : 'draft',
			'modified'  => $post ? mysql2date( 'c', $post->post_modified_gmt, false ) : '',
			'layout'    => $data['settings']['layout'],
			'count'     => count( $data['logos'] ),
			'shortcode' => LSW::shortcode( $id ),
			'thumbs'    => array(),
		);
		foreach ( array_slice( $data['logos'], 0, 4 ) as $logo ) {
			$u = wp_get_attachment_image_url( $logo['id'], 'thumbnail' );
			if ( $u ) {
				$out['thumbs'][] = $u;
			}
		}
		if ( $full ) {
			$logos = array();
			foreach ( $data['logos'] as $logo ) {
				$logo['thumb'] = (string) wp_get_attachment_image_url( $logo['id'], 'medium' );
				$logos[]       = apply_filters( 'lsw_rest_logo', $logo, $id );
			}
			$out['settings'] = $data['settings'];
			$out['logos']    = $logos;
		}
		return $out;
	}

	public static function list_items( $request ) {
		$q = new WP_Query( array(
			'post_type'      => LSW::POST_TYPE,
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			's'              => sanitize_text_field( (string) $request->get_param( 'search' ) ),
			'no_found_rows'  => true,
		) );
		$items = array();
		foreach ( $q->posts as $p ) {
			if ( current_user_can( 'edit_post', $p->ID ) ) {
				$items[] = self::format( $p->ID, false );
			}
		}
		return rest_ensure_response( array( 'items' => $items ) );
	}

	public static function get_item( $request ) {
		return rest_ensure_response( self::format( (int) $request['id'] ) );
	}

	private static function apply_status( $id, $status ) {
		if ( in_array( $status, array( 'publish', 'draft' ), true ) ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => $status ) );
		}
	}

	public static function create( $request ) {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$id    = wp_insert_post( array(
			'post_type'   => LSW::POST_TYPE,
			'post_status' => 'publish',
			'post_title'  => '' !== $title ? $title : __( 'Untitled showcase', 'logo-showcase' ),
		), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$settings = LSW_Schema::defaults();
		$tpl      = LSW_Templates::find( (string) $request->get_param( 'template' ) );
		if ( $tpl && ! empty( $tpl['settings'] ) ) {
			$settings = array_replace_recursive( $settings, $tpl['settings'] );
		}
		LSW_Data::save( $id, $settings, array() );
		return rest_ensure_response( self::format( $id ) );
	}

	public static function update( $request ) {
		$id = (int) $request['id'];
		$p  = array( 'ID' => $id );
		if ( null !== $request->get_param( 'title' ) ) {
			$title          = sanitize_text_field( (string) $request->get_param( 'title' ) );
			$p['post_title'] = '' !== $title ? $title : __( 'Untitled showcase', 'logo-showcase' );
		}
		if ( count( $p ) > 1 ) {
			wp_update_post( $p );
		}
		self::apply_status( $id, (string) $request->get_param( 'status' ) );
		$settings = $request->get_param( 'settings' );
		$logos    = $request->get_param( 'logos' );
		if ( is_array( $settings ) || is_array( $logos ) ) {
			$cur = LSW_Data::get( $id );
			LSW_Data::save( $id, is_array( $settings ) ? $settings : $cur['settings'], is_array( $logos ) ? $logos : $cur['logos'] );
		}
		return rest_ensure_response( self::format( $id ) );
	}

	public static function delete( $request ) {
		$id = (int) $request['id'];
		wp_trash_post( $id );
		return rest_ensure_response( array( 'deleted' => true, 'id' => $id ) );
	}

	public static function duplicate( $request ) {
		$id   = (int) $request['id'];
		$data = LSW_Data::get( $id );
		/* translators: %s: showcase name */
		$new = wp_insert_post( array( 'post_type' => LSW::POST_TYPE, 'post_status' => 'publish', 'post_title' => sprintf( __( '%s (copy)', 'logo-showcase' ), get_the_title( $id ) ) ), true );
		if ( is_wp_error( $new ) ) {
			return $new;
		}
		LSW_Data::save( $new, $data['settings'], $data['logos'] );
		return rest_ensure_response( self::format( $new ) );
	}

	public static function preview( $request ) {
		$settings = LSW_Schema::sanitize( (array) $request->get_param( 'settings' ) );
		$logos    = LSW_Data::sanitize_logos( (array) $request->get_param( 'logos' ) );
		$title    = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$html     = LSW_Renderer::render_data( 0, $title, $settings, $logos, array( 'preview' => true, 'no_enqueue' => true ) );
		return rest_ensure_response( array( 'html' => $html ) );
	}

	public static function global_settings() {
		return wp_parse_args( get_option( 'lsw_global', array() ), array( 'remove_data' => false ) );
	}

	public static function get_global() {
		return rest_ensure_response( self::global_settings() );
	}

	public static function save_global( $request ) {
		update_option( 'lsw_global', array( 'remove_data' => (bool) $request->get_param( 'remove_data' ) ), false );
		return rest_ensure_response( self::global_settings() );
	}
}
