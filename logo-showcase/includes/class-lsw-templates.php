<?php
/**
 * Starter templates shown on the dashboard. Pro adds its own through `lsw_templates`.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Templates {

	public static function all() {
		$list = array(
			array(
				'id'       => 'trusted-by',
				'name'     => __( 'Trusted by', 'logo-showcase' ),
				'note'     => __( 'Single row under a hero section', 'logo-showcase' ),
				'pro'      => false,
				'settings' => array(
					'layout' => 'slider',
					'style'  => array( 'border_width' => 0, 'item_bg' => '#ffffff', 'item_bg_hover' => '#ffffff', 'padding' => 8, 'hover_effect' => 'zoom-in' ),
					'title'  => array( 'show' => false ),
					'slider' => array( 'per_view' => array( 'desktop' => 5, 'tablet' => 4, 'mobile' => 2 ), 'dots' => false, 'nav' => false, 'autoplay' => true ),
				),
			),
			array(
				'id'       => 'sponsor-wall',
				'name'     => __( 'Sponsor wall', 'logo-showcase' ),
				'note'     => __( 'Large logos in a centered grid', 'logo-showcase' ),
				'pro'      => false,
				'settings' => array(
					'layout' => 'grid',
					'style'  => array( 'padding' => 24, 'radius' => 12 ),
					'title'  => array( 'show' => true ),
					'grid'   => array( 'columns' => array( 'desktop' => 3, 'tablet' => 2, 'mobile' => 1 ), 'gap' => 24 ),
				),
			),
			array(
				'id'       => 'partner-strip',
				'name'     => __( 'Partner strip', 'logo-showcase' ),
				'note'     => __( 'Compact, auto-scrolling', 'logo-showcase' ),
				'pro'      => false,
				'settings' => array(
					'layout' => 'slider',
					'style'  => array( 'border_width' => 1, 'padding' => 10, 'radius' => 6 ),
					'title'  => array( 'show' => false ),
					'tooltip' => array( 'enabled' => true ),
					'slider' => array( 'per_view' => array( 'desktop' => 6, 'tablet' => 4, 'mobile' => 3 ), 'delay' => 2, 'dots' => false, 'margin' => 12 ),
				),
			),
			array( 'id' => 'ticker-strip', 'name' => __( 'Ticker strip', 'logo-showcase' ), 'note' => __( 'Continuous marquee scroll', 'logo-showcase' ), 'pro' => true, 'settings' => array() ),
			array( 'id' => 'filter-grid', 'name' => __( 'Filterable grid', 'logo-showcase' ), 'note' => __( 'Category buttons above a grid', 'logo-showcase' ), 'pro' => true, 'settings' => array() ),
			array( 'id' => 'sponsor-tiers', 'name' => __( 'Sponsor tiers', 'logo-showcase' ), 'note' => __( 'Gold, Silver and Partner groups', 'logo-showcase' ), 'pro' => true, 'settings' => array() ),
		);
		$list = apply_filters( 'lsw_templates', $list );
		if ( LSW::is_pro() ) {
			// Pro replaces the placeholders; keep the last definition per id.
			$by = array();
			foreach ( $list as $t ) {
				$by[ $t['id'] ] = $t;
			}
			$list = array_values( $by );
		}
		return $list;
	}

	public static function find( $id ) {
		foreach ( self::all() as $t ) {
			if ( $t['id'] === $id && ( ! $t['pro'] || LSW::is_pro() ) ) {
				return $t;
			}
		}
		return null;
	}
}
