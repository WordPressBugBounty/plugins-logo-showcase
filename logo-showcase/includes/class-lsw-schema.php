<?php
/**
 * Settings registry: defaults, validation and Free/Pro locking in one place.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Schema {

	/** @var array|null */
	private static $fields = null;

	/**
	 * Field definitions keyed by dot path.
	 * type: bool|int|color|enum|text|css
	 * pro_values: enum values that need Pro. pro: whole field needs Pro. pro_max: int above this needs Pro.
	 */
	public static function fields() {
		if ( null !== self::$fields ) {
			return self::$fields;
		}
		$f = array(
			'layout'                  => array( 'type' => 'enum', 'default' => 'slider', 'options' => array( 'slider', 'grid', 'list', 'ticker', 'table', 'filter', 'tiers' ), 'pro_values' => array( 'list', 'ticker', 'table', 'filter', 'tiers' ) ),
			'direction'               => array( 'type' => 'enum', 'default' => 'auto', 'options' => array( 'auto', 'rtl', 'ltr' ) ),
			'order'                   => array( 'type' => 'enum', 'default' => 'manual', 'options' => array( 'manual', 'random', 'title' ) ),
			'limit'                   => array( 'type' => 'int', 'default' => 0, 'min' => 0, 'max' => 1000 ),

			'style.item_bg'           => array( 'type' => 'color', 'default' => '#ffffff' ),
			'style.item_bg_hover'     => array( 'type' => 'color', 'default' => '#ffffff' ),
			'style.border_width'      => array( 'type' => 'int', 'default' => 1, 'min' => 0, 'max' => 20 ),
			'style.border_style'      => array( 'type' => 'enum', 'default' => 'solid', 'options' => array( 'solid', 'dashed', 'dotted', 'double', 'none' ) ),
			'style.border_color'      => array( 'type' => 'color', 'default' => '#e5e7eb' ),
			'style.border_hover'      => array( 'type' => 'color', 'default' => '#c7d2fe' ),
			'style.padding'           => array( 'type' => 'int', 'default' => 12, 'min' => 0, 'max' => 80 ),
			'style.radius'            => array( 'type' => 'int', 'default' => 8, 'min' => 0, 'max' => 80 ),
			'style.image_effect'      => array( 'type' => 'enum', 'default' => 'none', 'options' => array( 'none', 'grayscale', 'blur', 'blur-grayscale' ), 'pro_values' => array( 'grayscale', 'blur', 'blur-grayscale' ) ),
			'style.hover_effect'      => array( 'type' => 'enum', 'default' => 'none', 'options' => array( 'none', 'zoom-in', 'zoom-out', 'lift' ), 'pro_values' => array( 'zoom-out', 'lift' ) ),

			'title.show'              => array( 'type' => 'bool', 'default' => true ),
			'title.align'             => array( 'type' => 'enum', 'default' => 'center', 'options' => array( 'left', 'center', 'right' ) ),
			'title.size'              => array( 'type' => 'int', 'default' => 14, 'min' => 8, 'max' => 48 ),
			'title.style'             => array( 'type' => 'enum', 'default' => 'normal', 'options' => array( 'normal', 'italic' ) ),
			'title.color'             => array( 'type' => 'color', 'default' => '#333333' ),

			'desc.show'               => array( 'type' => 'bool', 'default' => false ),
			'desc.align'              => array( 'type' => 'enum', 'default' => 'center', 'options' => array( 'left', 'center', 'right' ) ),
			'desc.size'               => array( 'type' => 'int', 'default' => 13, 'min' => 8, 'max' => 36 ),
			'desc.style'              => array( 'type' => 'enum', 'default' => 'normal', 'options' => array( 'normal', 'italic' ) ),
			'desc.color'              => array( 'type' => 'color', 'default' => '#556274' ),

			'tooltip.enabled'         => array( 'type' => 'bool', 'default' => true ),
			'tooltip.position'        => array( 'type' => 'enum', 'default' => 'top', 'options' => array( 'top', 'bottom', 'left', 'right' ), 'pro_values' => array( 'bottom', 'left', 'right' ) ),
			'tooltip.color'           => array( 'type' => 'color', 'default' => '#ffffff' ),
			'tooltip.bg'              => array( 'type' => 'color', 'default' => '#1f2937' ),

			'link.target'             => array( 'type' => 'enum', 'default' => '_self', 'options' => array( '_self', '_blank' ) ),
			'link.nofollow'           => array( 'type' => 'bool', 'default' => false ),

			'slider.autoplay'         => array( 'type' => 'bool', 'default' => true ),
			'slider.delay'            => array( 'type' => 'int', 'default' => 3, 'min' => 1, 'max' => 10, 'pro_max' => 3 ),
			'slider.speed'            => array( 'type' => 'int', 'default' => 450, 'min' => 100, 'max' => 3000 ),
			'slider.pause_hover'      => array( 'type' => 'bool', 'default' => true ),
			'slider.loop'             => array( 'type' => 'bool', 'default' => true ),
			'slider.center'           => array( 'type' => 'bool', 'default' => false, 'pro' => true ),
			'slider.margin'           => array( 'type' => 'int', 'default' => 16, 'min' => 0, 'max' => 80 ),
			'slider.per_view.desktop' => array( 'type' => 'int', 'default' => 4, 'min' => 1, 'max' => 10 ),
			'slider.per_view.tablet'  => array( 'type' => 'int', 'default' => 3, 'min' => 1, 'max' => 10 ),
			'slider.per_view.mobile'  => array( 'type' => 'int', 'default' => 2, 'min' => 1, 'max' => 10 ),
			'slider.rows'             => array( 'type' => 'int', 'default' => 1, 'min' => 1, 'max' => 4, 'pro_max' => 1 ),
			'slider.nav'              => array( 'type' => 'bool', 'default' => true ),
			'slider.nav_hover'        => array( 'type' => 'bool', 'default' => false ),
			'slider.nav_position'     => array( 'type' => 'enum', 'default' => 'top-right', 'options' => array( 'top-right', 'top-left', 'center' ), 'pro_values' => array( 'top-left', 'center' ) ),
			'slider.nav_shape'        => array( 'type' => 'enum', 'default' => 'square', 'options' => array( 'square', 'round' ), 'pro_values' => array( 'round' ) ),
			'slider.nav_color'        => array( 'type' => 'color', 'default' => '#ffffff' ),
			'slider.nav_bg'           => array( 'type' => 'color', 'default' => '#4f46e5' ),
			'slider.nav_hover_bg'     => array( 'type' => 'color', 'default' => '#3730a3' ),
			'slider.dots'             => array( 'type' => 'bool', 'default' => true ),
			'slider.dots_style'       => array( 'type' => 'enum', 'default' => 'round', 'options' => array( 'round', 'square', 'line' ), 'pro_values' => array( 'square', 'line' ) ),
			'slider.dots_align'       => array( 'type' => 'enum', 'default' => 'center', 'options' => array( 'left', 'center', 'right' ), 'pro_values' => array( 'left', 'right' ) ),
			'slider.dots_color'       => array( 'type' => 'color', 'default' => '#c3c9d6' ),
			'slider.dots_active'      => array( 'type' => 'color', 'default' => '#4f46e5' ),

			'grid.columns.desktop'    => array( 'type' => 'int', 'default' => 4, 'min' => 1, 'max' => 10 ),
			'grid.columns.tablet'     => array( 'type' => 'int', 'default' => 3, 'min' => 1, 'max' => 10 ),
			'grid.columns.mobile'     => array( 'type' => 'int', 'default' => 2, 'min' => 1, 'max' => 10 ),
			'grid.gap'                => array( 'type' => 'int', 'default' => 16, 'min' => 0, 'max' => 80 ),

			'advanced.custom_css'     => array( 'type' => 'css', 'default' => '' ),
			'advanced.lazy'           => array( 'type' => 'bool', 'default' => true ),
			'advanced.image_size'     => array( 'type' => 'enum', 'default' => 'medium', 'options' => array( 'thumbnail', 'medium', 'large', 'full' ) ),
		);

		/**
		 * Pro (or add-ons) may register extra fields.
		 */
		self::$fields = apply_filters( 'lsw_schema_fields', $f );
		return self::$fields;
	}

	public static function defaults() {
		$out = array();
		foreach ( self::fields() as $path => $def ) {
			self::set( $out, $path, $def['default'] );
		}
		return $out;
	}

	public static function get( array $arr, $path, $fallback = null ) {
		foreach ( explode( '.', $path ) as $part ) {
			if ( ! is_array( $arr ) || ! array_key_exists( $part, $arr ) ) {
				return $fallback;
			}
			$arr = $arr[ $part ];
		}
		return $arr;
	}

	public static function set( array &$arr, $path, $value ) {
		$ref = &$arr;
		foreach ( explode( '.', $path ) as $part ) {
			if ( ! isset( $ref[ $part ] ) || ! is_array( $ref[ $part ] ) ) {
				$ref[ $part ] = array();
			}
			$ref = &$ref[ $part ];
		}
		$ref = $value;
	}

	/**
	 * Does this value need Pro (and Pro is not active)?
	 */
	public static function is_locked( $path, $value ) {
		$fields = self::fields();
		if ( ! isset( $fields[ $path ] ) || LSW::is_pro() ) {
			return false;
		}
		$def = $fields[ $path ];
		if ( ! empty( $def['pro'] ) && $value ) {
			return true;
		}
		if ( ! empty( $def['pro_values'] ) && in_array( $value, $def['pro_values'], true ) ) {
			return true;
		}
		if ( isset( $def['pro_max'] ) && (int) $value > $def['pro_max'] ) {
			return true;
		}
		return false;
	}

	public static function sanitize_value( $def, $value ) {
		switch ( $def['type'] ) {
			case 'bool':
				if ( is_string( $value ) ) {
					return in_array( strtolower( $value ), array( '1', 'true', 'yes', 'on' ), true );
				}
				return (bool) $value;
			case 'int':
				if ( ! is_numeric( $value ) ) {
					return $def['default'];
				}
				return (int) max( $def['min'], min( $def['max'], (int) $value ) );
			case 'color':
				$c = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : null;
				return $c ? $c : $def['default'];
			case 'enum':
				return in_array( $value, $def['options'], true ) ? $value : $def['default'];
			case 'css':
				return is_string( $value ) ? self::sanitize_css( $value ) : '';
			default:
				return is_string( $value ) ? sanitize_text_field( $value ) : $def['default'];
		}
	}

	public static function sanitize_css( $css ) {
		$css = wp_strip_all_tags( (string) $css );
		$css = preg_replace( '/expression\s*\(|javascript\s*:|@import|behavior\s*:|-moz-binding/i', '', $css );
		return substr( trim( $css ), 0, 20000 );
	}

	/**
	 * Merge $in over defaults, validate every known field, and reset locked values.
	 */
	public static function sanitize( $in ) {
		$in  = is_array( $in ) ? $in : array();
		$out = array();
		foreach ( self::fields() as $path => $def ) {
			$value = self::get( $in, $path, $def['default'] );
			$value = self::sanitize_value( $def, $value );
			if ( 'advanced.custom_css' === $path && $value && ! current_user_can( 'manage_options' ) && ! self::is_internal() ) {
				$value = '';
			}
			if ( self::is_locked( $path, $value ) ) {
				$value = $def['default'];
			}
			self::set( $out, $path, $value );
		}
		return $out;
	}

	/** True while rendering/migrating data that was already stored (no current-user check). */
	private static $internal = 0;

	public static function is_internal() {
		return self::$internal > 0;
	}

	public static function internal( $on ) {
		self::$internal += $on ? 1 : -1;
	}


	/**
	 * Admin sections (left rail). `layouts` limits a section to those layouts.
	 */
	public static function sections() {
		$s = array(
			array( 'id' => 'logos', 'label' => __( 'Logos', 'logo-showcase' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'logo-showcase' ) ),
			array( 'id' => 'style', 'label' => __( 'Style', 'logo-showcase' ) ),
			array( 'id' => 'slider', 'label' => __( 'Slider', 'logo-showcase' ), 'layouts' => array( 'slider' ) ),
			array( 'id' => 'grid', 'label' => __( 'Grid', 'logo-showcase' ), 'layouts' => array( 'grid', 'filter' ) ),
			array( 'id' => 'tooltip', 'label' => __( 'Tooltips', 'logo-showcase' ) ),
			array( 'id' => 'links', 'label' => __( 'Links', 'logo-showcase' ) ),
			array( 'id' => 'advanced', 'label' => __( 'Advanced', 'logo-showcase' ) ),
		);
		return apply_filters( 'lsw_sections', $s );
	}

	/**
	 * How each field is shown in the admin: section, label, control and choices.
	 */
	public static function ui() {
		$ui = array(
			'layout'                  => array( 'section' => 'layout', 'ctl' => 'layout', 'label' => __( 'Layout', 'logo-showcase' ) ),
			'direction'               => array( 'section' => 'layout', 'ctl' => 'select', 'label' => __( 'Text direction', 'logo-showcase' ), 'help' => __( 'Automatic follows the site language and works with Arabic, Hebrew, Persian and Urdu sites, including multilingual sites. Slide direction, arrows and text alignment are mirrored in RTL.', 'logo-showcase' ), 'choices' => array( 'auto' => __( 'Automatic (follow site language)', 'logo-showcase' ), 'rtl' => __( 'Right to left (RTL)', 'logo-showcase' ), 'ltr' => __( 'Left to right (LTR)', 'logo-showcase' ) ) ),
			'order'                   => array( 'section' => 'layout', 'ctl' => 'segmented', 'label' => __( 'Order', 'logo-showcase' ), 'choices' => array( 'manual' => __( 'Manual', 'logo-showcase' ), 'random' => __( 'Random', 'logo-showcase' ), 'title' => __( 'A to Z', 'logo-showcase' ) ) ),
			'limit'                   => array( 'section' => 'layout', 'ctl' => 'number', 'label' => __( 'Maximum logos shown', 'logo-showcase' ), 'help' => __( '0 shows every logo.', 'logo-showcase' ) ),

			'style.item_bg'           => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Card background', 'logo-showcase' ) ),
			'style.item_bg_hover'     => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Card background on hover', 'logo-showcase' ) ),
			'style.border_width'      => array( 'section' => 'style', 'ctl' => 'range', 'label' => __( 'Border width', 'logo-showcase' ), 'unit' => 'px' ),
			'style.border_style'      => array( 'section' => 'style', 'ctl' => 'select', 'label' => __( 'Border style', 'logo-showcase' ), 'choices' => array( 'solid' => __( 'Solid', 'logo-showcase' ), 'dashed' => __( 'Dashed', 'logo-showcase' ), 'dotted' => __( 'Dotted', 'logo-showcase' ), 'double' => __( 'Double', 'logo-showcase' ), 'none' => __( 'None', 'logo-showcase' ) ) ),
			'style.border_color'      => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Border color', 'logo-showcase' ) ),
			'style.border_hover'      => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Border color on hover', 'logo-showcase' ) ),
			'style.padding'           => array( 'section' => 'style', 'ctl' => 'range', 'label' => __( 'Card padding', 'logo-showcase' ), 'unit' => 'px' ),
			'style.radius'            => array( 'section' => 'style', 'ctl' => 'range', 'label' => __( 'Corner radius', 'logo-showcase' ), 'unit' => 'px' ),
			'style.image_effect'      => array( 'section' => 'style', 'ctl' => 'select', 'label' => __( 'Logo effect', 'logo-showcase' ), 'choices' => array( 'none' => __( 'Normal', 'logo-showcase' ), 'grayscale' => __( 'Grayscale', 'logo-showcase' ), 'blur' => __( 'Blur', 'logo-showcase' ), 'blur-grayscale' => __( 'Blur and grayscale', 'logo-showcase' ) ) ),
			'style.hover_effect'      => array( 'section' => 'style', 'ctl' => 'select', 'label' => __( 'Hover animation', 'logo-showcase' ), 'choices' => array( 'none' => __( 'None', 'logo-showcase' ), 'zoom-in' => __( 'Zoom in', 'logo-showcase' ), 'zoom-out' => __( 'Zoom out', 'logo-showcase' ), 'lift' => __( 'Lift up', 'logo-showcase' ) ) ),

			'title.show'              => array( 'section' => 'style', 'ctl' => 'switch', 'label' => __( 'Show logo titles', 'logo-showcase' ), 'group' => 'title' ),
			'title.align'             => array( 'section' => 'style', 'ctl' => 'segmented', 'label' => __( 'Title alignment', 'logo-showcase' ), 'group' => 'title', 'show_if' => array( 'title.show' => true ), 'choices' => array( 'left' => __( 'Left', 'logo-showcase' ), 'center' => __( 'Center', 'logo-showcase' ), 'right' => __( 'Right', 'logo-showcase' ) ) ),
			'title.size'              => array( 'section' => 'style', 'ctl' => 'range', 'label' => __( 'Title size', 'logo-showcase' ), 'unit' => 'px', 'group' => 'title', 'show_if' => array( 'title.show' => true ) ),
			'title.style'             => array( 'section' => 'style', 'ctl' => 'segmented', 'label' => __( 'Title style', 'logo-showcase' ), 'group' => 'title', 'show_if' => array( 'title.show' => true ), 'choices' => array( 'normal' => __( 'Normal', 'logo-showcase' ), 'italic' => __( 'Italic', 'logo-showcase' ) ) ),
			'title.color'             => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Title color', 'logo-showcase' ), 'group' => 'title', 'show_if' => array( 'title.show' => true ) ),
			'desc.show'               => array( 'section' => 'style', 'ctl' => 'switch', 'label' => __( 'Show logo descriptions', 'logo-showcase' ), 'help' => __( 'Shows the description you wrote for each logo under its title.', 'logo-showcase' ), 'group' => 'desc' ),
			'desc.align'              => array( 'section' => 'style', 'ctl' => 'segmented', 'label' => __( 'Description alignment', 'logo-showcase' ), 'group' => 'desc', 'show_if' => array( 'desc.show' => true ), 'choices' => array( 'left' => __( 'Left', 'logo-showcase' ), 'center' => __( 'Center', 'logo-showcase' ), 'right' => __( 'Right', 'logo-showcase' ) ) ),
			'desc.size'               => array( 'section' => 'style', 'ctl' => 'range', 'label' => __( 'Description size', 'logo-showcase' ), 'unit' => 'px', 'group' => 'desc', 'show_if' => array( 'desc.show' => true ) ),
			'desc.style'              => array( 'section' => 'style', 'ctl' => 'segmented', 'label' => __( 'Description style', 'logo-showcase' ), 'group' => 'desc', 'show_if' => array( 'desc.show' => true ), 'choices' => array( 'normal' => __( 'Normal', 'logo-showcase' ), 'italic' => __( 'Italic', 'logo-showcase' ) ) ),
			'desc.color'              => array( 'section' => 'style', 'ctl' => 'color', 'label' => __( 'Description color', 'logo-showcase' ), 'group' => 'desc', 'show_if' => array( 'desc.show' => true ) ),

			'tooltip.enabled'         => array( 'section' => 'tooltip', 'ctl' => 'switch', 'label' => __( 'Show tooltip on hover', 'logo-showcase' ), 'help' => __( 'Shows the logo title above the card.', 'logo-showcase' ) ),
			'tooltip.position'        => array( 'section' => 'tooltip', 'ctl' => 'segmented', 'label' => __( 'Position', 'logo-showcase' ), 'show_if' => array( 'tooltip.enabled' => true ), 'choices' => array( 'top' => __( 'Top', 'logo-showcase' ), 'bottom' => __( 'Bottom', 'logo-showcase' ), 'left' => __( 'Left', 'logo-showcase' ), 'right' => __( 'Right', 'logo-showcase' ) ) ),
			'tooltip.color'           => array( 'section' => 'tooltip', 'ctl' => 'color', 'label' => __( 'Text color', 'logo-showcase' ), 'show_if' => array( 'tooltip.enabled' => true ) ),
			'tooltip.bg'              => array( 'section' => 'tooltip', 'ctl' => 'color', 'label' => __( 'Background', 'logo-showcase' ), 'show_if' => array( 'tooltip.enabled' => true ) ),

			'link.target'             => array( 'section' => 'links', 'ctl' => 'segmented', 'label' => __( 'Open links in', 'logo-showcase' ), 'choices' => array( '_self' => __( 'Same tab', 'logo-showcase' ), '_blank' => __( 'New tab', 'logo-showcase' ) ) ),
			'link.nofollow'           => array( 'section' => 'links', 'ctl' => 'switch', 'label' => __( 'Add rel="nofollow"', 'logo-showcase' ), 'help' => __( 'Recommended for paid sponsor links.', 'logo-showcase' ) ),

			'slider.autoplay'         => array( 'section' => 'slider', 'heading' => __( 'Movement', 'logo-showcase' ), 'ctl' => 'switch', 'label' => __( 'Autoplay', 'logo-showcase' ), 'help' => __( 'Move to the next logo automatically. Visitors who prefer reduced motion never see autoplay.', 'logo-showcase' ) ),
			'slider.delay'            => array( 'section' => 'slider', 'ctl' => 'range', 'label' => __( 'Time between slides', 'logo-showcase' ), 'unit' => 's', 'show_if' => array( 'slider.autoplay' => true ) ),
			'slider.pause_hover'      => array( 'section' => 'slider', 'ctl' => 'switch', 'label' => __( 'Pause on hover', 'logo-showcase' ), 'help' => __( 'Stop moving while the pointer is over the slider.', 'logo-showcase' ), 'show_if' => array( 'slider.autoplay' => true ) ),
			'slider.loop'             => array( 'section' => 'slider', 'ctl' => 'switch', 'label' => __( 'Loop', 'logo-showcase' ), 'help' => __( 'Return to the first logo after the last.', 'logo-showcase' ) ),
			'slider.center'           => array( 'section' => 'slider', 'ctl' => 'switch', 'label' => __( 'Center mode', 'logo-showcase' ), 'help' => __( 'Highlight the logo in the middle.', 'logo-showcase' ) ),
			'slider.speed'            => array( 'section' => 'slider', 'ctl' => 'range', 'label' => __( 'Slide animation speed', 'logo-showcase' ), 'unit' => 'ms', 'step' => 50 ),
			'slider.margin'           => array( 'section' => 'slider', 'heading' => __( 'Size and spacing', 'logo-showcase' ), 'ctl' => 'range', 'label' => __( 'Space between logos', 'logo-showcase' ), 'unit' => 'px' ),
			'slider.rows'             => array( 'section' => 'slider', 'ctl' => 'number', 'label' => __( 'Rows', 'logo-showcase' ), 'help' => __( 'Stack logos in rows inside each slide. 1 shows a single row.', 'logo-showcase' ) ),
			'slider.per_view.desktop' => array( 'section' => 'slider', 'ctl' => 'device', 'device' => 'desktop', 'group' => 'slider.per_view', 'label' => __( 'Logos visible at once', 'logo-showcase' ) ),
			'slider.per_view.tablet'  => array( 'section' => 'slider', 'ctl' => 'device', 'device' => 'tablet', 'group' => 'slider.per_view', 'label' => __( 'Logos visible at once', 'logo-showcase' ) ),
			'slider.per_view.mobile'  => array( 'section' => 'slider', 'ctl' => 'device', 'device' => 'mobile', 'group' => 'slider.per_view', 'label' => __( 'Logos visible at once', 'logo-showcase' ) ),

			'slider.nav'              => array( 'section' => 'slider', 'heading' => __( 'Arrows', 'logo-showcase' ), 'ctl' => 'switch', 'label' => __( 'Show arrows', 'logo-showcase' ), 'group' => 'nav' ),
			'slider.nav_hover'        => array( 'section' => 'slider', 'ctl' => 'switch', 'label' => __( 'Show arrows only on hover', 'logo-showcase' ), 'help' => __( 'Arrows fade in when the pointer is over the slider. Touch screens always show them.', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ) ),
			'slider.nav_position'     => array( 'section' => 'slider', 'ctl' => 'segmented', 'label' => __( 'Arrow position', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ), 'choices' => array( 'top-right' => __( 'Top right', 'logo-showcase' ), 'top-left' => __( 'Top left', 'logo-showcase' ), 'center' => __( 'Centered', 'logo-showcase' ) ) ),
			'slider.nav_shape'        => array( 'section' => 'slider', 'ctl' => 'segmented', 'label' => __( 'Arrow shape', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ), 'choices' => array( 'square' => __( 'Square', 'logo-showcase' ), 'round' => __( 'Round', 'logo-showcase' ) ) ),
			'slider.nav_color'        => array( 'section' => 'slider', 'ctl' => 'color', 'label' => __( 'Arrow color', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ) ),
			'slider.nav_bg'           => array( 'section' => 'slider', 'ctl' => 'color', 'label' => __( 'Arrow background', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ) ),
			'slider.nav_hover_bg'     => array( 'section' => 'slider', 'ctl' => 'color', 'label' => __( 'Arrow hover background', 'logo-showcase' ), 'group' => 'nav', 'show_if' => array( 'slider.nav' => true ) ),
			'slider.dots'             => array( 'section' => 'slider', 'heading' => __( 'Dots', 'logo-showcase' ), 'ctl' => 'switch', 'label' => __( 'Show dots', 'logo-showcase' ), 'group' => 'dots' ),
			'slider.dots_style'       => array( 'section' => 'slider', 'ctl' => 'segmented', 'label' => __( 'Dot style', 'logo-showcase' ), 'group' => 'dots', 'show_if' => array( 'slider.dots' => true ), 'choices' => array( 'round' => __( 'Round', 'logo-showcase' ), 'square' => __( 'Square', 'logo-showcase' ), 'line' => __( 'Line', 'logo-showcase' ) ) ),
			'slider.dots_align'       => array( 'section' => 'slider', 'ctl' => 'segmented', 'label' => __( 'Dot alignment', 'logo-showcase' ), 'group' => 'dots', 'show_if' => array( 'slider.dots' => true ), 'choices' => array( 'left' => __( 'Left', 'logo-showcase' ), 'center' => __( 'Center', 'logo-showcase' ), 'right' => __( 'Right', 'logo-showcase' ) ) ),
			'slider.dots_color'       => array( 'section' => 'slider', 'ctl' => 'color', 'label' => __( 'Dot color', 'logo-showcase' ), 'group' => 'dots', 'show_if' => array( 'slider.dots' => true ) ),
			'slider.dots_active'      => array( 'section' => 'slider', 'ctl' => 'color', 'label' => __( 'Active dot color', 'logo-showcase' ), 'group' => 'dots', 'show_if' => array( 'slider.dots' => true ) ),

			'grid.columns.desktop'    => array( 'section' => 'grid', 'ctl' => 'device', 'device' => 'desktop', 'group' => 'grid.columns', 'label' => __( 'Columns', 'logo-showcase' ) ),
			'grid.columns.tablet'     => array( 'section' => 'grid', 'ctl' => 'device', 'device' => 'tablet', 'group' => 'grid.columns', 'label' => __( 'Columns', 'logo-showcase' ) ),
			'grid.columns.mobile'     => array( 'section' => 'grid', 'ctl' => 'device', 'device' => 'mobile', 'group' => 'grid.columns', 'label' => __( 'Columns', 'logo-showcase' ) ),
			'grid.gap'                => array( 'section' => 'grid', 'ctl' => 'range', 'label' => __( 'Space between logos', 'logo-showcase' ), 'unit' => 'px' ),

			'advanced.image_size'     => array( 'section' => 'advanced', 'ctl' => 'select', 'label' => __( 'Image size', 'logo-showcase' ), 'help' => __( 'Smaller sizes load faster. Use Full only for large logos.', 'logo-showcase' ), 'choices' => array( 'thumbnail' => __( 'Thumbnail', 'logo-showcase' ), 'medium' => __( 'Medium', 'logo-showcase' ), 'large' => __( 'Large', 'logo-showcase' ), 'full' => __( 'Full', 'logo-showcase' ) ) ),
			'advanced.lazy'           => array( 'section' => 'advanced', 'ctl' => 'switch', 'label' => __( 'Lazy-load images', 'logo-showcase' ), 'help' => __( 'Load logos only when they are about to appear.', 'logo-showcase' ) ),
			'advanced.custom_css'     => array( 'section' => 'advanced', 'ctl' => 'css', 'label' => __( 'Custom CSS', 'logo-showcase' ), 'help' => __( 'Applies to this showcase only. Start selectors with .lsw-id-{ID}.', 'logo-showcase' ) ),
		);
		return apply_filters( 'lsw_schema_ui', $ui );
	}

	/**
	 * Schema description for the admin app.
	 */
	public static function describe() {
		$out = array();
		foreach ( self::fields() as $path => $def ) {
			$d = $def;
			if ( isset( $d['options'] ) ) {
				$d['options'] = array_values( $d['options'] );
			}
			$ui = self::ui();
			if ( isset( $ui[ $path ] ) ) {
				$d['ui'] = $ui[ $path ];
			}
			$out[ $path ] = $d;
		}
		return $out;
	}
}
