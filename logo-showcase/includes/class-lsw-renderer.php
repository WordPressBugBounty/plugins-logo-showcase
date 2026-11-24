<?php
/**
 * Front-end output for the shortcode, the block and the Elementor widget.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW_Renderer {

	private static $count = 0;

	public static function init() {
		add_shortcode( 'logo_showcase', array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'editor_assets' ) );
	}

	public static function register_assets() {
		if ( wp_style_is( 'lsw-frontend', 'registered' ) ) {
			return;
		}
		wp_register_style( 'lsw-frontend', LSW_URL . 'assets/css/frontend.css', array(), LSW_VERSION );
		wp_register_script( 'lsw-frontend', LSW_URL . 'assets/js/frontend.js', array(), LSW_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	/**
	 * Load the front-end CSS/JS inside the block editor (including its iframe).
	 */
	public static function editor_assets() {
		if ( ! is_admin() ) {
			return;
		}
		self::register_assets();
		wp_enqueue_style( 'lsw-frontend' );
		wp_enqueue_script( 'lsw-frontend' );
		do_action( 'lsw_enqueue_assets' );
		wp_add_inline_script( 'lsw-frontend', 'window.addEventListener("load",function(){var d=document;function go(){if(window.LSW&&LSW.init){LSW.init(d);}if(window.LSW_PRO&&LSW_PRO.init){LSW_PRO.init();}}new MutationObserver(function(){clearTimeout(window.__lswT);window.__lswT=setTimeout(go,120);}).observe(d.body,{childList:true,subtree:true});go();});' );
	}

	public static function enqueue_assets() {
		self::register_assets();
		wp_enqueue_style( 'lsw-frontend' );
		wp_enqueue_script( 'lsw-frontend' );
		do_action( 'lsw_enqueue_assets' );
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'logo_showcase' );
		return self::render( $atts['id'] );
	}

	/**
	 * Render a saved showcase.
	 */
	public static function render( $id, $args = array() ) {
		$id = absint( $id );
		if ( ! LSW::is_showcase( $id ) ) {
			return '';
		}
		if ( empty( $args['preview'] ) && 'publish' !== get_post_status( $id ) ) {
			return current_user_can( 'edit_post', $id ) ? '<!-- Logo Showcase: this showcase is a draft. -->' : '';
		}
		$data = LSW_Data::get( $id );
		return self::render_data( $id, get_the_title( $id ), $data['settings'], $data['logos'], $args );
	}

	/**
	 * Layout renderers: id => callable( $ctx ) returning the inner markup.
	 */
	public static function layouts() {
		return apply_filters(
			'lsw_layouts',
			array(
				'slider' => array( __CLASS__, 'layout_slider' ),
				'grid'   => array( __CLASS__, 'layout_grid' ),
			)
		);
	}

	public static function render_data( $id, $title, $settings, $logos, $args = array() ) {
		$layouts = self::layouts();
		$layout  = isset( $settings['layout'] ) ? $settings['layout'] : 'slider';
		if ( ! isset( $layouts[ $layout ] ) ) {
			$layout = 'slider';
		}

		$logos = self::prepare_logos( $logos, $settings );
		if ( ! $logos ) {
			return ( ! empty( $args['preview'] ) ) ? '<div class="lsw-empty">' . esc_html__( 'Add logos to see a preview.', 'logo-showcase' ) . '</div>' : '';
		}

		self::$count++;
		$ctx = array(
			'id'       => (int) $id,
			'uid'      => 'lsw-' . (int) $id . '-' . self::$count,
			'title'    => $title,
			'settings' => $settings,
			'logos'    => $logos,
			'layout'   => $layout,
			'preview'  => ! empty( $args['preview'] ),
		);

		if ( empty( $args['no_enqueue'] ) ) {
			self::enqueue_assets();
		}

		$s       = $settings;
		$classes = array(
			'lsw',
			'lsw-id-' . (int) $id,
			'lsw-layout-' . $layout,
			'lsw-hover-' . $s['style']['hover_effect'],
			'lsw-fx-' . $s['style']['image_effect'],
			'lsw-tip-' . $s['tooltip']['position'],
		);
		if ( $s['tooltip']['enabled'] ) {
			$classes[] = 'lsw-tips-on';
		}
		if ( 'slider' === $layout ) {
			// Reserve the space the script adds later so the page does not jump.
			if ( $s['slider']['nav'] && 'center' !== $s['slider']['nav_position'] ) {
				$classes[] = 'lsw-nav-top';
			}
			if ( $s['slider']['nav'] && $s['slider']['nav_hover'] ) {
				$classes[] = 'lsw-nav-hover';
			}
			if ( $s['slider']['dots'] || $s['slider']['autoplay'] ) {
				$classes[] = 'lsw-reserve-dots';
			}
		}
		$classes = apply_filters( 'lsw_wrapper_classes', $classes, $ctx );

		$attrs = array(
			'id'    => $ctx['uid'],
			'class' => implode( ' ', array_map( 'sanitize_html_class', $classes ) ),
			'style' => self::css_vars( $s ),
			'role'  => 'region',
			'aria-label' => $title ? $title : __( 'Logos', 'logo-showcase' ),
		);
		$dir = isset( $s['direction'] ) ? $s['direction'] : 'auto';
		if ( 'rtl' === $dir || ( 'auto' === $dir && is_rtl() ) ) {
			$attrs['dir'] = 'rtl';
		} elseif ( 'ltr' === $dir && is_rtl() ) {
			$attrs['dir'] = 'ltr';
		}
		if ( 'slider' === $layout ) {
			$attrs['data-lsw'] = wp_json_encode( self::slider_config( $s ) );
		}
		$attrs = apply_filters( 'lsw_wrapper_attrs', $attrs, $ctx );

		$html = '<div';
		foreach ( $attrs as $k => $v ) {
			$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}
		$html .= '>';
		$html .= call_user_func( $layouts[ $layout ], $ctx );
		$html .= '</div>';

		$css = isset( $s['advanced']['custom_css'] ) ? $s['advanced']['custom_css'] : '';
		if ( $css ) {
			$html .= '<style id="' . esc_attr( $ctx['uid'] ) . '-css">' . LSW_Schema::sanitize_css( $css ) . '</style>';
		}
		return apply_filters( 'lsw_output', $html, $ctx );
	}

	/**
	 * Order, limit and let Pro filter (schedule, tiers...).
	 */
	private static function prepare_logos( $logos, $s ) {
		$logos = array_values( array_filter( (array) $logos, function ( $l ) {
			return ! empty( $l['id'] );
		} ) );
		$logos = apply_filters( 'lsw_logos', $logos, $s );
		switch ( $s['order'] ) {
			case 'random':
				shuffle( $logos );
				break;
			case 'title':
				usort( $logos, function ( $a, $b ) {
					return strcasecmp( $a['title'], $b['title'] );
				} );
				break;
		}
		if ( (int) $s['limit'] > 0 ) {
			$logos = array_slice( $logos, 0, (int) $s['limit'] );
		}
		return $logos;
	}

	/** left/right become start/end so text alignment mirrors in RTL. */
	private static function logical_align( $a ) {
		return 'left' === $a ? 'start' : ( 'right' === $a ? 'end' : $a );
	}

	private static function css_vars( $s ) {
		$v = array(
			'--lsw-bg'          => $s['style']['item_bg'],
			'--lsw-bg-h'        => $s['style']['item_bg_hover'],
			'--lsw-bw'          => $s['style']['border_width'] . 'px',
			'--lsw-bs'          => $s['style']['border_style'],
			'--lsw-bc'          => $s['style']['border_color'],
			'--lsw-bc-h'        => $s['style']['border_hover'],
			'--lsw-pad'         => $s['style']['padding'] . 'px',
			'--lsw-radius'      => $s['style']['radius'] . 'px',
			'--lsw-title-size'  => $s['title']['size'] . 'px',
			'--lsw-title-color' => $s['title']['color'],
			'--lsw-title-align' => self::logical_align( $s['title']['align'] ),
			'--lsw-title-style' => $s['title']['style'],
			'--lsw-desc-size'   => $s['desc']['size'] . 'px',
			'--lsw-desc-color'  => $s['desc']['color'],
			'--lsw-desc-align'  => $s['desc']['align'],
			'--lsw-desc-style'  => $s['desc']['style'],
			'--lsw-tip-color'   => $s['tooltip']['color'],
			'--lsw-tip-bg'      => $s['tooltip']['bg'],
			'--lsw-nav-color'   => $s['slider']['nav_color'],
			'--lsw-nav-bg'      => $s['slider']['nav_bg'],
			'--lsw-nav-bg-h'    => $s['slider']['nav_hover_bg'],
			'--lsw-dot'         => $s['slider']['dots_color'],
			'--lsw-dot-active'  => $s['slider']['dots_active'],
			'--lsw-gap'         => $s['grid']['gap'] . 'px',
			'--lsw-pv-d'        => $s['slider']['per_view']['desktop'],
			'--lsw-pv-t'        => $s['slider']['per_view']['tablet'],
			'--lsw-pv-m'        => $s['slider']['per_view']['mobile'],
			'--lsw-sgap'        => $s['slider']['margin'] . 'px',
			'--lsw-cols-d'      => $s['grid']['columns']['desktop'],
			'--lsw-cols-t'      => $s['grid']['columns']['tablet'],
			'--lsw-cols-m'      => $s['grid']['columns']['mobile'],
		);
		$v   = apply_filters( 'lsw_css_vars', $v, $s );
		$out = '';
		foreach ( $v as $k => $val ) {
			$out .= $k . ':' . $val . ';';
		}
		return $out;
	}

	private static function slider_config( $s ) {
		$sl = $s['slider'];
		return array(
			'autoplay'   => (bool) $sl['autoplay'],
			'delay'      => (int) $sl['delay'] * 1000,
			'speed'      => (int) $sl['speed'],
			'pauseHover' => (bool) $sl['pause_hover'],
			'loop'       => (bool) $sl['loop'],
			'center'     => (bool) $sl['center'],
			'margin'     => (int) $sl['margin'],
			'perView'    => $sl['per_view'],
			'nav'        => (bool) $sl['nav'],
			'navPos'     => $sl['nav_position'],
			'dots'       => (bool) $sl['dots'],
			'dotsAlign'  => $sl['dots_align'],
			'dotsStyle'  => $sl['dots_style'],
			'navShape'   => $sl['nav_shape'],
			'i18n'       => array(
				'prev'  => __( 'Previous logos', 'logo-showcase' ),
				'next'  => __( 'Next logos', 'logo-showcase' ),
				'pause' => __( 'Pause autoplay', 'logo-showcase' ),
				'play'  => __( 'Start autoplay', 'logo-showcase' ),
				'go'    => __( 'Go to slide %d', 'logo-showcase' ),
			),
		);
	}

	public static function layout_slider( $ctx ) {
		$rows  = max( 1, (int) $ctx['settings']['slider']['rows'] );
		$html  = '<div class="lsw-viewport"><ul class="lsw-track">';
		foreach ( array_chunk( $ctx['logos'], $rows ) as $slide ) {
			$cells = '';
			foreach ( $slide as $logo ) {
				$cells .= $rows > 1 ? '<div class="lsw-cell">' . self::item( $logo, $ctx ) . '</div>' : self::item( $logo, $ctx );
			}
			$html .= '<li class="lsw-item">' . $cells . '</li>';
		}
		return $html . '</ul></div>';
	}

	public static function layout_grid( $ctx ) {
		$html = '<ul class="lsw-grid">';
		foreach ( $ctx['logos'] as $logo ) {
			$html .= '<li class="lsw-item">' . self::item( $logo, $ctx ) . '</li>';
		}
		return $html . '</ul>';
	}

	/**
	 * One logo card. Public so Pro layouts reuse it.
	 */
	public static function item( $logo, $ctx ) {
		$s    = $ctx['settings'];
		$img  = self::image( $logo, $s );
		if ( '' === $img ) {
			return '';
		}
		if ( ! empty( $logo['url'] ) ) {
			$rel = array( 'noopener' );
			if ( ! empty( $s['link']['nofollow'] ) ) {
				$rel[] = 'nofollow';
			}
			$attr = ' href="' . esc_url( $logo['url'] ) . '"';
			if ( '_blank' === $s['link']['target'] ) {
				$attr .= ' target="_blank"';
			}
			$attr .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
			$img   = '<a class="lsw-link"' . $attr . '>' . $img . '</a>';
		}
		$title = '';
		if ( $s['title']['show'] && '' !== $logo['title'] ) {
			$title = '<div class="lsw-title">' . esc_html( $logo['title'] ) . '</div>';
		}
		if ( ! empty( $s['desc']['show'] ) && '' !== $logo['desc'] ) {
			$title .= '<div class="lsw-desc">' . esc_html( $logo['desc'] ) . '</div>';
		}
		$title .= (string) apply_filters( 'lsw_item_after_title', '', $logo, $ctx );
		$tip = '';
		if ( $s['tooltip']['enabled'] && '' !== $logo['title'] ) {
			$tip = ' data-lsw-tip="' . esc_attr( $logo['title'] ) . '"';
		}
		$extra = apply_filters( 'lsw_item_attrs', '', $logo, $ctx );
		$inner = '<div class="lsw-card' . ( $tip ? ' lsw-has-tip' : '' ) . '"' . $tip . $extra . '><div class="lsw-thumb">' . $img . '</div>' . $title . '</div>';
		return apply_filters( 'lsw_item_html', $inner, $logo, $ctx );
	}

	private static function image( $logo, $s ) {
		$alt = '' !== $logo['alt'] ? $logo['alt'] : get_post_meta( $logo['id'], '_wp_attachment_image_alt', true );
		if ( '' === $alt ) {
			$alt = $logo['title'];
		}
		$attr = array(
			'class'    => 'lsw-img',
			'alt'      => $alt,
			'decoding' => 'async',
			'loading'  => $s['advanced']['lazy'] ? 'lazy' : 'eager',
		);
		return (string) wp_get_attachment_image( $logo['id'], $s['advanced']['image_size'], false, $attr );
	}
}
