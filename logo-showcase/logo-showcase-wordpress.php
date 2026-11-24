<?php

/**
 * Plugin Name: Logo Showcase
 * Plugin URI:  https://themepoints.com/logoshowcase/
 * Description: Display client, sponsor, partner or brand logos as a slider or grid. Build and manage showcases from a modern dashboard with live preview.
 * Version:     5.0.0
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Author:      Themepoints
 * Author URI:  https://themepoints.com
 * Text Domain: logo-showcase
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 *
 * @package LogoShowcase
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
define( 'LSW_VERSION', '5.0.0' );
define( 'LSW_FILE', __FILE__ );
define( 'LSW_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSW_URL', plugin_dir_url( __FILE__ ) );
// Kept for add-ons written against the 4.x constants.
define( 'LOGO_SHOWCASE_VERSION', LSW_VERSION );
define( 'LOGO_SHOWCASE_WP_PLUGIN_PATH', LSW_URL );
// >>> FREEMIUS (Pro build only; removed from the wordpress.org build by build-zips.sh)
if ( !defined( 'LSW_SKIP_FREEMIUS' ) && !function_exists( 'lslogo_fs' ) && file_exists( dirname( __FILE__ ) . '/freemius/start.php' ) ) {
    // Create a helper function for easy SDK access.
    function lslogo_fs() {
        global $lslogo_fs;
        if ( !isset( $lslogo_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $lslogo_fs = fs_dynamic_init( array(
                'id'               => '14279',
                'slug'             => 'logo-showcase',
                'type'             => 'plugin',
                'public_key'       => 'pk_2eaa84768f994f84e2e831ae45a93',
                'is_premium'       => false,
                'premium_suffix'   => 'Individuals',
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'trial'            => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'             => array(
                    'slug'       => 'logo-showcase',
                    'first-path' => 'admin.php?page=logo-showcase',
                    'support'    => false,
                ),
                'is_live'          => true,
                'is_org_compliant' => true,
            ) );
        }
        return $lslogo_fs;
    }

    // Init Freemius.
    lslogo_fs();
    // Signal that SDK was initiated.
    do_action( 'lslogo_fs_loaded' );
    /**
     * Uninstall cleanup for the Pro build (Freemius runs it on uninstall instead of uninstall.php).
     * Data is removed only if the admin opted in under Logo Showcase > Settings.
     */
    function lsw_fs_uninstall_cleanup() {
        $opts = get_option( 'lsw_global', array() );
        if ( empty( $opts['remove_data'] ) ) {
            return;
        }
        $ids = get_posts( array(
            'post_type'   => 'tplogoshowcase',
            'post_status' => 'any',
            'numberposts' => -1,
            'fields'      => 'ids',
        ) );
        foreach ( $ids as $id ) {
            wp_delete_post( $id, true );
        }
        delete_option( 'lsw_global' );
        delete_option( 'tlsw_logoshowcase_activation_time' );
    }

    lslogo_fs()->add_action( 'after_uninstall', 'lsw_fs_uninstall_cleanup' );
}
// <<< FREEMIUS
foreach ( array(
    'class-lsw',
    'class-lsw-schema',
    'class-lsw-data',
    'class-lsw-post-type',
    'class-lsw-renderer',
    'class-lsw-templates',
    'class-lsw-rest',
    'class-lsw-block',
    'class-lsw-elementor',
    'class-lsw-admin'
) as $lsw_file ) {
    if ( file_exists( LSW_PATH . 'includes/' . $lsw_file . '.php' ) ) {
        require_once LSW_PATH . 'includes/' . $lsw_file . '.php';
    }
}
unset($lsw_file);
// The Pro build ships a /pro folder; the wordpress.org build does not.
if ( file_exists( LSW_PATH . 'pro/loader.php' ) ) {
    require_once LSW_PATH . 'pro/loader.php';
}
LSW_Post_Type::init();
LSW_Renderer::init();
if ( class_exists( 'LSW_REST' ) ) {
    LSW_REST::init();
}
if ( class_exists( 'LSW_Block' ) ) {
    LSW_Block::init();
}
if ( class_exists( 'LSW_Elementor' ) ) {
    LSW_Elementor::init();
}
if ( is_admin() && class_exists( 'LSW_Admin' ) ) {
    LSW_Admin::init();
}
register_activation_hook( __FILE__, function () {
    if ( !get_option( 'tlsw_logoshowcase_activation_time' ) ) {
        update_option( 'tlsw_logoshowcase_activation_time', time() );
    }
} );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
    $extra = array();
    $extra[] = '<a href="' . esc_url( admin_url( 'admin.php?page=logo-showcase' ) ) . '">' . esc_html__( 'Showcases', 'logo-showcase' ) . '</a>';
    if ( !LSW::has_pro_code() ) {
        $extra[] = '<a href="' . esc_url( LSW::upgrade_url() ) . '" target="_blank" rel="noopener" style="color:#4f46e5;font-weight:600;">' . esc_html__( 'Upgrade to Pro', 'logo-showcase' ) . '</a>';
    }
    return array_merge( $extra, $links );
} );