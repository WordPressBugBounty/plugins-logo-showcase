<?php
/**
 * Elementor widget class. Only loaded by LSW_Elementor once Elementor exists.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LSW_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'logo_showcase';
	}

	public function get_title() {
		return __( 'Logo Showcase', 'logo-showcase' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'logo', 'slider', 'carousel', 'sponsors', 'clients' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Showcase', 'logo-showcase' ) ) );
		$this->add_control(
			'showcase_id',
			array(
				'label'   => __( 'Showcase', 'logo-showcase' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => LSW_Elementor::choices(),
				'default' => '',
			)
		);
		$this->add_control(
			'manage',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => '<a href="' . esc_url( admin_url( 'admin.php?page=logo-showcase' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Manage showcases', 'logo-showcase' ) . '</a>',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$id = absint( $this->get_settings_for_display( 'showcase_id' ) );
		if ( ! $id ) {
			echo '<div class="lsw-empty">' . esc_html__( 'Choose a showcase in the widget settings.', 'logo-showcase' ) . '</div>';
			return;
		}
		$preview = class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
		echo LSW_Renderer::render( $id, array( 'preview' => $preview ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
	}
}
