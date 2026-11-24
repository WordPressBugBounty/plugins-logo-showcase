<?php
/**
 * Core helpers.
 *
 * @package LogoShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LSW {

	const POST_TYPE = 'tplogoshowcase';

	/**
	 * Whether Pro features are unlocked. The Pro build hooks the `lsw_is_pro` filter.
	 */
	public static function is_pro() {
		return (bool) apply_filters( 'lsw_is_pro', false );
	}

	/**
	 * Whether the Pro code is present at all (Pro build). Used to hide upsell UI.
	 */
	public static function has_pro_code() {
		return (bool) apply_filters( 'lsw_has_pro_code', false );
	}

	public static function upgrade_url() {
		return apply_filters( 'lsw_upgrade_url', 'https://themepoints.com/logoshowcase/' );
	}

	public static function docs_url() {
		return 'https://themepoints.com/logoshowcase/documentation/';
	}

	public static function shortcode( $id ) {
		return sprintf( '[logo_showcase id="%d"]', (int) $id );
	}

	/**
	 * Is $id a showcase post the current context may render?
	 */
	public static function is_showcase( $id ) {
		$id = absint( $id );
		return $id && get_post_type( $id ) === self::POST_TYPE;
	}
}
