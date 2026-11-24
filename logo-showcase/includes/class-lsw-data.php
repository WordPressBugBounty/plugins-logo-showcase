<?php
/**
 * Showcase storage: one versioned meta value per showcase, with lazy migration
 * from the pre-5.0 layout (about 70 separate meta keys).
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Data {

	const META    = '_lsw_data';
	const VERSION = 5;

	/** @var array */
	private static $cache = array();

	/**
	 * @return array{settings:array,logos:array}
	 */
	public static function get( $id ) {
		$id = absint( $id );
		if ( isset( self::$cache[ $id ] ) ) {
			return self::$cache[ $id ];
		}
		$raw = get_post_meta( $id, self::META, true );
		if ( ! is_array( $raw ) || ! isset( $raw['settings'] ) ) {
			$raw = self::from_legacy( $id );
		}
		LSW_Schema::internal( true );
		$data = array(
			'settings' => LSW_Schema::sanitize( isset( $raw['settings'] ) ? $raw['settings'] : array() ),
			'logos'    => self::sanitize_logos( isset( $raw['logos'] ) ? $raw['logos'] : array() ),
		);
		LSW_Schema::internal( false );
		self::$cache[ $id ] = $data;
		return $data;
	}

	public static function save( $id, $settings, $logos ) {
		$id   = absint( $id );
		$data = array(
			'version'  => self::VERSION,
			'settings' => LSW_Schema::sanitize( $settings ),
			'logos'    => self::sanitize_logos( $logos ),
		);
		update_post_meta( $id, self::META, $data );
		unset( self::$cache[ $id ] );
		return self::get( $id );
	}

	public static function forget( $id ) {
		unset( self::$cache[ absint( $id ) ] );
	}

	public static function sanitize_logos( $logos ) {
		$out = array();
		if ( ! is_array( $logos ) ) {
			return $out;
		}
		foreach ( $logos as $logo ) {
			if ( ! is_array( $logo ) ) {
				continue;
			}
			$image = isset( $logo['id'] ) ? absint( $logo['id'] ) : 0;
			if ( ! $image ) {
				continue;
			}
			$item = array(
				'id'    => $image,
				'title' => isset( $logo['title'] ) ? sanitize_text_field( $logo['title'] ) : '',
				'desc'  => isset( $logo['desc'] ) ? sanitize_textarea_field( $logo['desc'] ) : '',
				'url'   => isset( $logo['url'] ) ? esc_url_raw( trim( $logo['url'] ) ) : '',
				'alt'   => isset( $logo['alt'] ) ? sanitize_text_field( $logo['alt'] ) : '',
				'tags'  => isset( $logo['tags'] ) ? self::clean_tags( $logo['tags'] ) : '',
			);
			/** Pro adds fields such as start/end dates and tier. */
			$out[] = apply_filters( 'lsw_sanitize_logo', $item, $logo );
		}
		return $out;
	}

	private static function clean_tags( $tags ) {
		if ( is_array( $tags ) ) {
			$tags = implode( ',', $tags );
		}
		$parts = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( (string) $tags ) ) ) );
		return implode( ', ', array_unique( $parts ) );
	}

	/**
	 * Build the 5.x structure from legacy post meta. Does not write anything.
	 */
	public static function from_legacy( $id ) {
		$m = function ( $key, $default = '' ) use ( $id ) {
			$v = get_post_meta( $id, $key, true );
			return ( '' === $v || false === $v || null === $v ) ? $default : $v;
		};
		$s = LSW_Schema::defaults();

		$layouts = array( 'theme1' => 'slider', 'theme2' => 'grid', 'theme3' => 'list', 'theme4' => 'ticker', 'theme5' => 'table' );
		$s['layout'] = isset( $layouts[ $m( 'logo_showcase_columns_post_themes', 'theme1' ) ] ) ? $layouts[ $m( 'logo_showcase_columns_post_themes', 'theme1' ) ] : 'slider';

		$map_color = array(
			'style.item_bg'       => 'logo_showcase_items_background_color',
			'style.item_bg_hover' => 'logo_showcase_items_hover_background',
			'style.border_color'  => 'logo_showcase_columns_border_color',
			'style.border_hover'  => 'logo_showcase_columns_border_hover_color',
			'title.color'         => 'logo_showcase_columns_title_font_color',
			'tooltip.color'       => 'logo_showcase_tooltips_color',
			'tooltip.bg'          => 'logo_showcase_tooltips_bgcolor',
			'slider.nav_color'    => 'logo_showcase_navigation_text_color',
			'slider.nav_bg'       => 'logo_showcase_navigation_bg_color',
			'slider.nav_hover_bg' => 'logo_showcase_navigation_hover_bg_color',
			'slider.dots_color'   => 'logo_showcase_pagination_bg_color',
			'slider.dots_active'  => 'logo_showcase_pagination_active_bg_color',
		);
		foreach ( $map_color as $path => $key ) {
			$v = $m( $key );
			if ( $v ) {
				LSW_Schema::set( $s, $path, $v );
			}
		}
		$map_int = array(
			'style.border_width'      => 'logo_showcase_item_roderwidth',
			'style.padding'           => 'logo_showcase_item_padding',
			'style.radius'            => 'logo_showcase_columns_radius',
			'title.size'              => 'logo_showcase_columns_title_font_size',
			'slider.margin'           => 'margin',
			'slider.per_view.desktop' => 'logo_showcase_columns_show_items',
			'slider.per_view.tablet'  => 'itemsdesktopsmall',
			'slider.per_view.mobile'  => 'itemsmobile',
			'grid.columns.desktop'    => 'grid_normal_column',
			'grid.columns.tablet'     => 'columndesktopsmall',
			'grid.columns.mobile'     => 'columnmobile',
			'grid.gap'                => 'grid_column_pleft',
			'slider.speed'            => 'logo_showcase_columns_show_slide_speed',
		);
		foreach ( $map_int as $path => $key ) {
			$v = $m( $key );
			if ( '' !== $v && is_numeric( $v ) ) {
				LSW_Schema::set( $s, $path, (int) $v );
			}
		}
		$timeout = $m( 'autoplaytimeout' );
		if ( is_numeric( $timeout ) ) {
			LSW_Schema::set( $s, 'slider.delay', (int) round( $timeout / 1000 ) );
		}

		$bool = function ( $key, $default ) use ( $m ) {
			$v = $m( $key, '' );
			if ( '' === $v ) {
				return $default;
			}
			return in_array( (string) $v, array( '1', 'true' ), true );
		};
		$s['title']['show']        = $bool( 'logo_showcase_free_show_title_hide', true );
		$s['slider']['autoplay']   = $bool( 'logo_showcase_columns_show_auto_play', true );
		$s['slider']['pause_hover'] = $bool( 'stop_hover_play', true );
		$s['slider']['loop']       = $bool( 'loop', true );
		$s['slider']['center']     = $bool( 'slide_center_mode', false );
		$s['slider']['nav']        = $bool( 'logo_showcase_navigation', true );
		$s['slider']['dots']       = $bool( 'logo_showcase_pagination', true );
		$tip                       = $m( 'logo_showcase_columns_show_hide_tooltips', '1' );
		$s['tooltip']['enabled']   = ( '2' !== (string) $tip && '0' !== (string) $tip );

		$s['title']['align']    = $m( 'logo_showcase_columns_title_position', 'center' );
		$s['title']['style']    = $m( 'logo_showcase_free_title_font_style', 'normal' );
		$s['tooltip']['position'] = $m( 'logo_showcase_tooltips_positions', 'top' );
		$s['link']['target']    = $m( 'logo_showcase_free_open_link', '_self' );

		$nav_pos = array( 'topright' => 'top-right', 'topleft' => 'top-left', 'centred' => 'center' );
		$s['slider']['nav_position'] = isset( $nav_pos[ $m( 'logo_showcase_navigation_position', 'topright' ) ] ) ? $nav_pos[ $m( 'logo_showcase_navigation_position', 'topright' ) ] : 'top-right';
		$s['slider']['nav_shape']    = ( '50' === (string) $m( 'logo_showcase_navigation_style', '0' ) ) ? 'round' : 'square';
		$dot_style                   = array( '1' => 'round', '2' => 'square', '3' => 'line' );
		$s['slider']['dots_style']   = isset( $dot_style[ (string) $m( 'logo_showcase_pagination_style', '1' ) ] ) ? $dot_style[ (string) $m( 'logo_showcase_pagination_style', '1' ) ] : 'round';
		$s['slider']['dots_align']   = $m( 'logo_showcase_pagination_position', 'center' );

		$fx = array( '1' => 'none', '2' => 'grayscale', '3' => 'blur', '4' => 'blur-grayscale' );
		$s['style']['image_effect'] = isset( $fx[ (string) $m( 'logo_showcase_columns_image_effect', '1' ) ] ) ? $fx[ (string) $m( 'logo_showcase_columns_image_effect', '1' ) ] : 'none';
		$hv = array( '0' => 'none', '1' => 'zoom-in', '2' => 'zoom-out', '3' => 'lift' );
		$s['style']['hover_effect'] = isset( $hv[ (string) $m( 'logo_showcase_columns_hover_effect', '0' ) ] ) ? $hv[ (string) $m( 'logo_showcase_columns_hover_effect', '0' ) ] : 'none';

		$logos  = array();
		$legacy = get_post_meta( $id, 'logo_showcase_columns', false );
		if ( is_array( $legacy ) ) {
			foreach ( $legacy as $row ) {
				if ( ! is_array( $row ) || empty( $row['logo_showcase_uploader'] ) ) {
					continue;
				}
				$logos[] = array(
					'id'    => absint( $row['logo_showcase_uploader'] ),
					'title' => isset( $row['logo_showcase_title'] ) ? $row['logo_showcase_title'] : '',
					'desc'  => isset( $row['logo_showcase_desc'] ) ? $row['logo_showcase_desc'] : '',
					'url'   => isset( $row['logo_showcase_link_url'] ) ? $row['logo_showcase_link_url'] : '',
				);
			}
		}
		return array( 'settings' => $s, 'logos' => $logos );
	}
}
