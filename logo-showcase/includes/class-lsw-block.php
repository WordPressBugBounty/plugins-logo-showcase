<?php
/**
 * Gutenberg block: a thin, server-rendered wrapper around a saved showcase.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Block {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		register_block_type(
			LSW_PATH . 'blocks/showcase',
			array(
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array( 'showcaseId' => array( 'type' => 'number', 'default' => 0 ) ),
			)
		);
	}

	public static function render( $attrs ) {
		$id = isset( $attrs['showcaseId'] ) ? absint( $attrs['showcaseId'] ) : 0;
		if ( ! $id ) {
			return '';
		}
		$args = array();
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// Editor preview: show drafts to people who can edit them.
			$args['preview'] = current_user_can( 'edit_post', $id );
		}
		return '<div ' . get_block_wrapper_attributes() . '>' . LSW_Renderer::render( $id, $args ) . '</div>';
	}
}
