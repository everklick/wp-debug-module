<?php
/**
 * Debug module for PHP.
 *
 * Version: 2.5.4
 * Author:  Philipp Stracker (stracker.phil@gmail.com)
 * Website: https://github.com/everklick/wp-debug-module
 */

if ( ! defined( 'EVR_NOCONFLICT_DEBUG' ) ) {
	define( 'EVR_NOCONFLICT_DEBUG', false );
}
if ( ! defined( 'EVR_DEBUG_WITH_IP' ) ) {
	define( 'EVR_DEBUG_WITH_IP', false );
}
if ( ! defined( 'EVR_DEBUG_WITH_COOKIE' ) ) {
	define( 'EVR_DEBUG_WITH_COOKIE', false );
}

// Flag to check, if this is a WordPress site.
define(
	'EVR_DEBUG_IS_WORDPRESS',
	defined( 'ABSPATH' ) && function_exists( 'wp' )
);

if ( EVR_DEBUG_IS_WORDPRESS ) {
	if ( ! defined( 'WP_DEBUG' ) ) {
		define( 'WP_DEBUG', true );
	}
	if ( ! defined( 'WP_DEBUG_LOG' ) ) {
		define( 'WP_DEBUG_LOG', true );
	}
	if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
		define( 'WP_DEBUG_DISPLAY', true );
	}
	if ( ! defined( 'EVR_DEBUG' ) ) {
		define( 'EVR_DEBUG', WP_DEBUG );
	}
} else {
	if ( ! defined( 'EVR_DEBUG' ) ) {
		define( 'EVR_DEBUG', true );
	}
}

/**
 * Disable output of JS debugging functions.
 *
 * @see   @Evr_Debug::js_debug()
 * @since 2.5.0
 */
if ( ! defined( 'EVR_DEBUG_JS' ) ) {
	define( 'EVR_DEBUG_JS', true );
}

// EVR_DEBUG_WITH_* flags have the power to override EVR_DEBUG.
if ( ! EVR_DEBUG && EVR_DEBUG_WITH_IP ) {
	$ip_list = array_map( 'trim', explode( ',', EVR_DEBUG_WITH_IP ) );
	define( '_EVR_DEBUG_ON', in_array( $_SERVER['REMOTE_ADDR'], $ip_list ) );
	unset( $ip_list );
} elseif ( ! EVR_DEBUG && EVR_DEBUG_WITH_COOKIE ) {
	define( '_EVR_DEBUG_ON', ! empty( $_COOKIE[ EVR_DEBUG_WITH_COOKIE ] ) );
} else {
	define( '_EVR_DEBUG_ON', EVR_DEBUG );
}

if ( ! defined( 'EVR_DEBUG_LOG_IMPORTANT' ) ) {
	define( 'EVR_DEBUG_LOG_IMPORTANT', true );
}

require_once __DIR__ . '/class-evr-debug.php';


// ----------------------------------------------------------------------------

/**
 * Don't log crap logic: Disable notices, strict-warnings and deprecation
 * information from log output.
 *
 * @since 2.5.3
 */
if ( EVR_DEBUG_LOG_IMPORTANT ) {
	error_reporting( E_ALL & ~( E_NOTICE | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED ) );
}

// ----------------------------------------------------------------------------


/**
 * Dump all params on the screen.
 */
$GLOBALS['_evr_fn_debug'] = function () {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	call_user_func_array(
		[ Evr_Debug::inst(), 'dump' ],
		func_get_args()
	);
};

if ( EVR_NOCONFLICT_DEBUG ) {
	$code = sprintf(
		'function %s(){
			call_user_func_array( $GLOBALS["_evr_fn_debug"], func_get_args() );
		}',
		EVR_NOCONFLICT_DEBUG
	);
	eval( $code );
} else {
	function debug() {
		call_user_func_array( $GLOBALS['_evr_fn_debug'], func_get_args() );
	}
}

/**
 * Output a styled hash-value of the input variable.
 * The hash-value and color offer a quick way to recognize changes in the object
 * or identify different Ajax responses (use debug_marker in the ajax response).
 */
function debug_marker() {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	$marker = call_user_func_array(
		[ Evr_Debug::inst(), 'marker_html' ],
		func_get_args()
	);
	echo $marker->html;
}

/**
 * Same as debug_marker, but return the hash-value instead of outputting it.
 */
function debug_get_marker() {
	if ( ! _EVR_DEBUG_ON ) {
		return null;
	}

	return call_user_func_array(
		[ Evr_Debug::inst(), 'marker_html' ],
		func_get_args()
	);
}

/**
 * Dump all params to the screen and exit the request.
 */
function debug_and_die() {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	call_user_func_array(
		[ Evr_Debug::inst(), 'dump' ],
		func_get_args()
	);
	exit;
}

/**
 * Outputs all dumps that were collected via `debug()`.
 * In WordPress, this is called automatically during the `shutdown` action.
 */
function debug_show() {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	call_user_func_array(
		[ Evr_Debug::inst(), 'flush' ],
		func_get_args()
	);
}

/**
 * Dump all params to the logfile.
 * Always works, even when debugging is disabled.
 */
function debug_log() {
	call_user_func_array(
		[ Evr_Debug::inst(), 'log' ],
		func_get_args()
	);
}

/**
 * Add redirect debugging.
 * Always works, even when debugging is disabled.
 */
function debug_redirect( $location, $stop_redirect = null ) {
	call_user_func_array(
		[ Evr_Debug::inst(), 'redirect_headers' ],
		func_get_args()
	);
}

/**
 * Write a back-trace to log-file.
 * Always works, even when debugging is disabled.
 */
function debug_log_trace() {
	call_user_func_array(
		[ Evr_Debug::inst(), 'log_trace' ],
		func_get_args()
	);
}

/**
 * Output all passed parameters as HTTP response headers.
 * Non-scalar params are simply json_encoded and not dumped as in debug().
 */
function debug_header() {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	call_user_func_array(
		[ Evr_Debug::inst(), 'header' ],
		func_get_args()
	);
}

/**
 * Alias for log_slack()
 */
function debug_slack() {
	call_user_func_array(
		'log_slack',
		func_get_args()
	);
}

/**
 * Output a message to slack.
 * Always works, even when debugging is disabled.
 */
function log_slack() {
	call_user_func_array(
		[ Evr_Debug::inst(), 'slack' ],
		func_get_args()
	);
}

/**
 * Set or return a debug flag.
 * Always works, even when debugging is disabled.
 */
function debug_flag( $key, $value = null ) {
	return Evr_Debug::inst()->flag( $key, $value );
}

/**
 * List defined constants
 *
 * @since 2.5.2
 *
 * @param bool|string $filter limit to matching names or values
 */
function debug_constants( $filter = false ) {
	$constants = get_defined_constants();

	if ( false !== $filter ) {
		$temp = [];

		foreach ( $constants as $key => $constant ) {
			if ( false !== stripos( $key, $filter ) || false !== stripos( $constant, $filter ) ) {
				$temp[ $key ] = $constant;
			}
		}

		$constants = $temp;
	}

	Evr_Debug::inst()->dump( $constants );
}


/**
 * Dumps the current memory usage into a JSON file.
 *
 * Requires the `meminfo`{@see https://github.com/BitOne/php-meminfo} extension
 * to be installed and active.
 *
 * @param string $file Name of the dump file. The placeholder "##" is
 *                     replaced with the current timestamp.
 *
 * @return void
 */
function debug_memory( string $file = 'memdump-##' ) {
	$file = str_replace( '##', time(), $file );
	if ( EVR_DEBUG_IS_WORDPRESS ) {
		$path = WP_CONTENT_DIR . '/' . $file;
	} else {
		$path = dirname( __DIR__ ) . '/' . $file;
	}

	if ( function_exists( 'memprof_dump_callgrind' ) && memprof_enabled() ) {
		memprof_dump_callgrind( fopen( "$path.cache", 'w' ) );
		// memprof_dump_pprof( fopen( "$path.heap", 'w' ) );
		// file_put_contents( "$path.json", json_encode( memprof_dump_array(), JSON_PRETTY_PRINT ) );
	} elseif ( function_exists( 'meminfo_dump' ) ) {
		meminfo_dump( fopen( "$path.json", 'w' ) );
	}
}

if ( EVR_DEBUG_IS_WORDPRESS ) {
	require_once __DIR__ . '/debug-wp.php';
}


// ----------------------------------------------------------------------------


/**
 * In WordPress, we automatically add a back-trace to all redirects.
 */
Evr_Debug::inst();

// This indicates, that debugging is active.
return true;
