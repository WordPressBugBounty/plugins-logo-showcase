<?php
/**
 * Elementor widget (loads only when Elementor is active).
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Elementor {

	public static function init() {
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register' ) );
	}

	public static function register( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}
		require_once LSW_PATH . 'includes/class-lsw-elementor-widget.php';
		$widgets_manager->register( new LSW_Elementor_Widget() );
	}

	public static function choices() {
		$out = array( '' => __( 'Select a showcase', 'logo-showcase' ) );
		$q   = new WP_Query( array( 'post_type' => LSW::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => 200, 'no_found_rows' => true, 'orderby' => 'title', 'order' => 'ASC' ) );
		foreach ( $q->posts as $p ) {
			$out[ $p->ID ] = $p->post_title . ' (#' . $p->ID . ')';
		}
		return $out;
	}
}
