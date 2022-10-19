<?php
/**
 * WordPress debugging library.
 *
 * @since 2.5.3 - Extracted WP code into separate file
 */

/*
Installation instructions for WordPress:

1. Clone the debug-module to your ABSPATH directory
2. Create the file in "/wp-content/mu-plugins/debug.php" with the following
   content:

-----

<?php
require_once ABSPATH . 'wp-debug/debug.php';

-----
 */


/**
 * Collect debug details about all WP hooks that fire in the current request.
 *
 * WP Only
 *
 * @since 2.5.2
 */
function debug_all_hooks( $enabled = true, $patterns = [], $is_whitelist = false ) {
	call_user_func_array(
		[ Evr_Debug::inst(), 'debug_hooks' ],
		func_get_args()
	);
}

/**
 * List cron entries with time remaining till next run.
 *
 * WP Only
 *
 * @since 2.5.2
 */
function debug_cron() {
	$cron = _get_cron_array();

	echo '<pre>';

	$offset = get_option( 'gmt_offset' ) * 3600;

	foreach ( $cron as $time => $entry ) {
		$when = '<strong>In ' . human_time_diff( $time ) . '</strong> (' . $time . ' ' . date_i18n( DATE_RSS, $time + $offset ) . ')';
		echo "<br />&gt;&gt;&gt;&gt;&gt;\t{$when}<br />";

		foreach ( array_keys( $entry ) as $function ) {
			echo "\t{$function}<br />";
			debug_hooks( $function );
		}
	}

	echo '</pre>';
}

/**
 * List hooks as currently defined
 *
 * WP Only
 *
 * @since 2.5.2
 *
 * @param bool|string $filter limit to matching names
 */
function debug_hooks( $filter = false ) {
	/** @type WP_Hook[] $wp_filter */
	global $wp_filter;

	$skip_filter = empty( $filter );
	$hooks       = $wp_filter;
	ksort( $hooks );

	foreach ( $hooks as $tag => $hook ) {
		if ( $skip_filter || false !== strpos( $tag, $filter ) ) {
			Evr_Debug::inst()->dump_hook( $tag, $hook );
		}
	}
}

/**
 * List active plugins
 *
 * WP Only
 *
 * @since 2.5.2
 */
function debug_plugins() {
	Evr_Debug::inst()->dump( get_option( 'active_plugins' ) );
}

/**
 * List post's fields, custom fields, and terms
 *
 * WP Only
 *
 * @since 2.5.2
 *
 * @param int $post_id
 */
function debug_post( $post_id = null ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	Evr_Debug::inst()->dump(
		get_post( $post_id ),
		get_post_custom( $post_id ),
		wp_get_post_terms( $post_id, get_post_taxonomies( $post_id ) )
	);
}

/**
 * List performed MySQL queries
 *
 * WP Only
 *
 * @since 2.5.2
 */
function debug_queries() {
	/** @type wpdb $wpdb */
	global $wpdb;

	if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
		trigger_error( 'SAVEQUERIES needs to be defined', E_USER_NOTICE );

		return;
	}

	echo '<pre>';

	foreach ( $wpdb->queries as $query ) {
		[ $request, $duration, $backtrace ] = $query;

		$duration  = sprintf( '%f', $duration );
		$backtrace = explode( ',', $backtrace );
		$backtrace = trim( array_pop( $backtrace ) );

		if ( 'get_option' == $backtrace ) {

			preg_match_all( '/\option_name.*?=.*?\'(.+?)\'/', $request, $matches );
			$backtrace .= "({$matches[1][0]})";
		}

		echo "<br /><code>{$request}</code><br />{$backtrace} in {$duration}s<br />";
	}

	echo '<br /></pre>';
}

/**
 * Run EXPLAIN on provided MySQL query or last query performed.
 *
 * WP Only
 *
 * @since 2.5.2
 *
 * @param string $query
 */
function debug_sql_query( $query = '' ) {
	/** @type wpdb $wpdb */
	global $wpdb;

	if ( empty( $query ) ) {
		$query = $wpdb->last_query;
	}

	Evr_Debug::inst()->dump(
		$query,
		$wpdb->get_results( 'EXPLAIN EXTENDED ' . $query ),
		$wpdb->get_results( 'SHOW WARNINGS' )
	);
}

if ( _EVR_DEBUG_ON ) {
	/**
	 * Debug WordPress redirects, when the debug module is enabled
	 */
	if ( defined( 'ABSPATH' ) ) {
		add_filter( 'wp_redirect', [ Evr_Debug::inst(), 'redirect_headers' ], 9999 );

		add_filter(
			'nocache_headers',
			function ( $headers ) {
				Evr_Debug::inst()->header_trace( 'nocache' );

				return $headers;
			},
			9999
		);

		add_action( 'shutdown', [ Evr_Debug::inst(), 'flush' ], 9999 );

		// Enable JS debugging to non WordPress projects.
		add_action( 'wp_enqueue_scripts', [ Evr_Debug::inst(), 'js_debug' ] );
		add_action( 'admin_enqueue_scripts', [ Evr_Debug::inst(), 'js_debug' ] );
	} else {
		// Enable JS debugging to non WordPress projects.
		Evr_Debug::inst()->js_debug();
	}
}
