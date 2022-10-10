<?php
/**
 * Debug module for PHP.
 *
 * Version: 2.5.2
 * Author:  Philipp Stracker (stracker.phil@gmail.com)
 * Website: https://bitbucket.org/everklick/debug-module
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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', ! defined( 'ABSPATH' ) );
}
if ( ! defined( 'EVR_DEBUG' ) ) {
	define( 'EVR_DEBUG', WP_DEBUG );
}

/**
 * Do not use the JS Debugger in irrelevant requests:
 * Ajax calls, cron jobs, CLI requests, REST/API, iframe content or in Headless
 * Chrome (which is used for automated testing).
 *
 * @since 2.5.1
 */
$ua = empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : $_SERVER['HTTP_USER_AGENT'];

if (
	defined( 'DOING_AJAX' )
	|| defined( 'DOING_CRON' )
	|| defined( 'WP_CLI' )
	|| defined( 'DIVIMODE_AJAX' )
	|| defined( 'DIVIMODE_IFRAME' )
	|| defined( 'XMLRPC_REQUEST' )
	|| defined( 'IFRAME_REQUEST' )
	|| wp_is_json_request()
	|| strpos( $ua, 'HeadlessChrome' )
) {
	define( 'EVR_DEBUG_JS', false );
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

/**
 * Dump all params on the screen.
 */
$GLOBALS['_evr_fn_debug'] = function () {
	if ( ! _EVR_DEBUG_ON ) {
		return;
	}
	call_user_func_array(
		[ $GLOBALS['_evr_debugger'], 'dump' ],
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
		[ $GLOBALS['_evr_debugger'], 'marker_html' ],
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
		[ $GLOBALS['_evr_debugger'], 'marker_html' ],
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
		[ $GLOBALS['_evr_debugger'], 'dump' ],
		func_get_args()
	);
	if ( function_exists( 'wp_die' ) ) {
		wp_die();
	}
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
		[ $GLOBALS['_evr_debugger'], 'flush' ],
		func_get_args()
	);
}

/**
 * Dump all params to the logfile.
 * Always works, even when debugging is disabled.
 */
function debug_log() {
	call_user_func_array(
		[ $GLOBALS['_evr_debugger'], 'log' ],
		func_get_args()
	);
}

/**
 * Add redirect debugging.
 * Always works, even when debugging is disabled.
 */
function debug_redirect( $location, $stop_redirect = null ) {
	call_user_func_array(
		[ $GLOBALS['_evr_debugger'], 'redirect_headers' ],
		func_get_args()
	);
}

/**
 * Collect debug details about all WP hooks that fire in the current request.
 */
function debug_hooks( $enabled = true, $patterns = [], $is_whitelist = false ) {
	call_user_func_array(
		[ $GLOBALS['_evr_debugger'], 'debug_hooks' ],
		func_get_args()
	);
}

/**
 * Write a back-trace to log-file.
 * Always works, even when debugging is disabled.
 */
function debug_log_trace() {
	call_user_func_array(
		[ $GLOBALS['_evr_debugger'], 'log_trace' ],
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
		[ $GLOBALS['_evr_debugger'], 'header' ],
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
		[ $GLOBALS['_evr_debugger'], 'slack' ],
		func_get_args()
	);
}

/**
 * Set or return a debug flag.
 * Always works, even when debugging is disabled.
 */
function debug_flag( $key, $value = null ) {
	return $GLOBALS['_evr_debugger']->flag( $key, $value );
}

/**
 * In WordPress, we automatically add a back-trace to all redirects.
 */
$GLOBALS['_evr_debugger'] = new Evr_Debug( $_SERVER );

if ( _EVR_DEBUG_ON ) {
	// WordPress specific code.
	if ( defined( 'ABSPATH' ) ) {
		add_filter( 'wp_redirect', [ $GLOBALS['_evr_debugger'], 'redirect_headers' ], 9999 );

		add_filter(
			'nocache_headers',
			function ( $headers ) {
				$GLOBALS['_evr_debugger']->header_trace( 'nocache' );

				return $headers;
			},
			9999
		);

		add_action( 'shutdown', [ $GLOBALS['_evr_debugger'], 'flush' ], 9999 );

		// Enable JS debugging to non WordPress projects.
		add_action( 'wp_enqueue_scripts', [ $GLOBALS['_evr_debugger'], 'js_debug' ] );
		add_action( 'admin_enqueue_scripts', [ $GLOBALS['_evr_debugger'], 'js_debug' ] );
	} else {
		// Enable JS debugging to non WordPress projects.
		$GLOBALS['_evr_debugger']->js_debug();
	}
}

// -----

/**
 * Debugging class
 */
class Evr_Debug {

	/**
	 * Current module version.
	 */
	const VERSION = '2.5.1';

	/**
	 * Timestamp of request start.
	 * This is set in the constructor, i.e. when this file is loaded
	 *
	 * @var float
	 */
	protected $start_stamp = 0;

	/**
	 * Logfile output destination, absolute path.
	 *
	 * @var string
	 */
	protected $log_file = '';

	/**
	 * List of public flags:
	 *
	 * - enabled
	 *     (boolean)
	 *     If set to true or false it will override the EVR_DEBUG value
	 *     If set to null the EVR_DEBUG and EVR_DEBUG values are used.
	 *
	 * - format
	 *     (html | text)
	 *     Toggles the plain-text / HTML output of the debug.
	 *     All Ajax requests will ignore this flag and use plain-text format.
	 *
	 * - sort
	 *     (boolean)
	 *     Toggles the alphabetical sorting of array/object keys in the dump.
	 *
	 * - mark_fields
	 *     (array / comma separated string)
	 *     Defines, which array/object fields are highlighted as "primary key"
	 *     fields in the debug dump.
	 *
	 * - show_trace
	 *     (boolean)
	 *     If set to true each debug output will contain a stack-trace.
	 *     Otherwise, only the variable will be dumped. Default: true.
	 *
	 * - show_request
	 *     (boolean)
	 *     Whether to show request details (like GET/POST/COOKIE values) at the
	 *     end of every debug dump. Default: true.
	 *
	 * - show_stats
	 *     (boolean)
	 *     Whether to show request stats like memory usage and php version at
	 *     the end of every debug dump. Default: true.
	 *
	 * - stop_redirect
	 *     (boolean)
	 *     WordPress specific flag. Whether to prevent wp_redirect from
	 *     redirecting users. If set to true, a link with the target URL is
	 *     displayed instead of automatic redirection.
	 *
	 * - - - - - - - - - -
	 *
	 * Internal flags:
	 *
	 * - header_count
	 *     (integer)
	 *     Counter of already sent header-trace lines.
	 *
	 * @var array
	 */
	protected $flags = [];

	/**
	 * Copy of the $_SERVER array.
	 *
	 * @since 2.5.0
	 * @var array
	 */
	protected $_server = [];

	/**
	 * Internal collection used by `dump()`.
	 *
	 * @since 2.5.1
	 * @var array
	 */
	protected $dumps = [];

	/**
	 * List of hook names to ignore.
	 *
	 * @since 2.5.2
	 * @var array
	 */
	protected $hook_patterns = [];

	/**
	 * Whether the hook-patterns are a whitelist or blacklist
	 *
	 * @since 2.5.2
	 * @var bool
	 */
	protected $hook_pattern_whitelist = false;

	/**
	 * Collection of all hooks that were called by the request.
	 *
	 * @since 2.5.2
	 * @var array
	 */
	protected $hooks = [];

	/**
	 * Counts, which hook is fired how often.
	 *
	 * @since 2.5.2
	 * @var array
	 */
	protected $hook_count = [];

	/**
	 * Singleton class.
	 *
	 * @since 1.0.0
	 *
	 * @param array $_server The $_SERVER array (since 2.5.0).
	 */
	public function __construct( $_server = [] ) {
		$this->_server = $_server;

		if ( isset( $this->_server['REQUEST_TIME_FLOAT'] ) ) {
			// Since php 5.4 we find the real start time in the Server global.
			$this->start_stamp = $this->_server['REQUEST_TIME_FLOAT'];
		} else {
			// If not set (e.g. called via CLI) we use current time.
			$this->start_stamp = microtime( true );
		}

		$debug_default = WP_DEBUG;

		if ( ! defined( 'EVR_LOG_DIR' ) ) {
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				define( 'EVR_LOG_DIR', WP_CONTENT_DIR );
			} else {
				define( 'EVR_LOG_DIR', dirname( __FILE__ ) );
			}
		}

		if ( ! defined( 'EVR_LOG_FILE' ) ) {
			define( 'EVR_LOG_FILE', 'debug-info.log' );
		}

		if ( ! defined( '_EVR_DEBUG_ON' ) ) {
			define( '_EVR_DEBUG_ON', $debug_default );
		}

		if ( ! defined( 'EVR_DEBUG_SORT' ) ) {
			define( 'EVR_DEBUG_SORT', true );
		}
		if ( ! defined( 'EVR_DEBUG_MARK_FIELDS' ) ) {
			define( 'EVR_DEBUG_MARK_FIELDS', 'ID,id' );
		}

		$this->reset();

		// Initialize the log-file path.
		$this->set_log_file();
	}

	/**
	 * Resets all debug-output flags.
	 *
	 * @since  1.1.4
	 * @api
	 */
	public function reset() {
		$this->flags['enabled']       = _EVR_DEBUG_ON;
		$this->flags['format']        = 'html';
		$this->flags['stop_redirect'] = false;

		$this->flags['show_trace']   = true;
		$this->flags['show_request'] = true;
		$this->flags['show_stats']   = true;

		$this->flags['log_file'] = EVR_LOG_FILE;
		$this->flags['log_dir']  = EVR_LOG_DIR;

		$this->flags['sort']        = EVR_DEBUG_SORT;
		$this->flags['mark_fields'] = explode( ',', EVR_DEBUG_MARK_FIELDS );
		$this->flags['mark_fields'] = array_map( 'trim', $this->flags['mark_fields'] );

		if ( ! isset( $this->flags['header_count'] ) ) {
			$this->flags['header_count'] = 0;
		}

		$this->reset_slack();
	}

	/**
	 * Reset the slack settings
	 *
	 * @since  2.4.0
	 */
	protected function reset_slack() {
		$this->flags['slack_hook']    = '';
		$this->flags['slack_name']    = '';
		$this->flags['slack_icon']    = '';
		$this->flags['slack_channel'] = '';

		if ( defined( 'EVR_SLACK_HOOK' ) ) {
			$this->flags['slack_hook'] = EVR_SLACK_HOOK;
		}
		if ( defined( 'EVR_SLACK_NAME' ) ) {
			$this->flags['slack_name'] = EVR_SLACK_NAME;
		}
		if ( defined( 'EVR_SLACK_ICON' ) ) {
			$this->flags['slack_icon'] = EVR_SLACK_ICON;
		}
		if ( defined( 'EVR_SLACK_CHANNEL' ) ) {
			$this->flags['slack_channel'] = EVR_SLACK_CHANNEL;
		}
	}

	/**
	 * Get and optionally set a module flag.
	 *
	 * @since  2.4.0
	 *
	 * @param string $key       The flag key.
	 * @param mixed  $new_value Optional. New value to assign to the flag.
	 *
	 * @return mixed  The original value of the specified flag, before the
	 *                optional new value was assigned.
	 * @api
	 */
	public function flag( $key, $new_value = null ) {
		if ( isset( $this->flags[ $key ] ) ) {
			$orig_value = $this->flags[ $key ];
		} else {
			$orig_value = null;
		}

		if ( null !== $new_value ) {
			// Parse array values
			$array_fields = [ 'mark_fields' ];
			if ( in_array( $key, $array_fields ) ) {
				$new_value = explode( ',', $new_value );
				$new_value = array_map( 'trim', $new_value );
			}

			$this->flags[ $key ] = $new_value;
			$this->set_log_file();
		}

		return $orig_value;
	}

	/**
	 * Set the log file to a valid path using the flags
	 * `log_dir` and `log_file`
	 *
	 * @since 2.4.6
	 */
	protected function set_log_file() {
		$dir  = $this->flags['log_dir'];
		$file = $this->flags['log_file'];

		if ( ! $dir ) {
			$dir = EVR_LOG_DIR;
		}
		if ( ! $file ) {
			$file = EVR_LOG_FILE;
		}

		$dir = str_replace(
			[ '//', '/', '\\' ],
			DIRECTORY_SEPARATOR,
			rtrim( $dir, '/\ ' )
		);

		if ( ! in_array( $dir[0], [ '.', DIRECTORY_SEPARATOR ] ) ) {
			$dir = DIRECTORY_SEPARATOR . $dir;
		}

		$file = str_replace(
			[ '//', '/', '\\' ],
			DIRECTORY_SEPARATOR,
			trim( $file, '/ ' )
		);

		$this->log_file = $dir . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Returns the debugging status. False means no debug output is made.
	 *
	 * @since  2.0.0
	 * @return bool
	 * @api
	 */
	public function is_enabled() {
		$enabled = $this->flags['enabled'];

		// SPECIFIC FOR WordPress ONLY: Detect ajax calls.
		if ( defined( 'EVR_AJAX_DEBUG' ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$enabled = EVR_AJAX_DEBUG;
			}
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				$enabled = EVR_AJAX_DEBUG;
			}
		}

		return $enabled;
	}

	/**
	 * Whether stacktrace should be included in all debug outputs.
	 *
	 * @since  2.0.0
	 * @return bool
	 */
	public function show_trace() {
		$enabled = $this->flags['show_trace'];

		if ( defined( 'EVR_DEBUG_TRACE' ) ) {
			$enabled = EVR_DEBUG_TRACE;
		}

		return $enabled;
	}

	/**
	 * Whether request details should be included in the debug output.
	 * This includes GET/POST/COOKIE details.
	 *
	 * @since  2.4.0
	 * @return bool
	 */
	public function show_request() {
		$enabled = $this->flags['show_request'];

		if ( defined( 'EVR_DEBUG_REQUEST' ) ) {
			$enabled = EVR_DEBUG_REQUEST;
		}

		return $enabled;
	}

	/**
	 * Whether request stats should be included in the debug output.
	 * This includes memory usage, elapsed time and php version.
	 *
	 * @since  2.4.0
	 * @return bool
	 */
	public function show_stats() {
		$enabled = $this->flags['show_stats'];

		if ( defined( 'EVR_DEBUG_STATS' ) ) {
			$enabled = EVR_DEBUG_STATS;
		}

		return $enabled;
	}

	/**
	 * SPECIFIC FOR WordPress ONLY.
	 *
	 * Whether redirect attepts from WordPress should be prevented. Instead of
	 * redirecting the user, a link to the destination page is displayed
	 * together with a stacktrace if show_trace()
	 *
	 * @since  2.0.0
	 * @return bool
	 */
	public function is_redirect_stopped() {
		$res = $this->flags['stop_redirect'];

		if ( defined( 'EVR_STOP_REDIRECT' ) ) {
			$res = EVR_STOP_REDIRECT;
		}

		return $res;
	}

	/**
	 * Determines if the debug output should be made in plain text.
	 *
	 * @since  2.0.0
	 * @return bool
	 * @api
	 */
	public function is_format( $check = 'text' ) {
		$res = ( $check == $this->flags['format'] );

		$is_ajax = false;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$is_ajax = true;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$is_ajax = true;
		}

		// Ajax requests always have format 'text'.
		if ( $is_ajax ) {
			$res = ( 'text' == $check );
		}

		return $res;
	}

	/**
	 * Returns the current OS username or IP.
	 *
	 * @since  2.3.6
	 * @return string The OS username or IP address.
	 */
	public function get_current_user() {
		if ( empty( $this->flags['current_user'] ) ) {
			if ( @getenv( 'username' ) ) {
				$user = getenv( 'username' );
			} elseif ( @getenv( 'user' ) ) {
				$user = getenv( 'user' );
			} elseif ( ! empty( $this->_server['AUTH_USER'] ) ) {
				$user = $this->_server['AUTH_USER'];
			} elseif ( ! empty( $this->_server['HTTP_AUTH_USER'] ) ) {
				$user = $this->_server['HTTP_AUTH_USER'];
			} elseif ( ! empty( $this->_server['REMOTE_USER'] ) ) {
				$user = $this->_server['REMOTE_USER'];
			} elseif ( ! empty( $this->_server['HTTP_REMOTE_USER'] ) ) {
				$user = $this->_server['HTTP_REMOTE_USER'];
			} elseif ( @get_current_user() ) {
				$user = get_current_user();
			} elseif ( @posix_getuid() && @posix_getpwuid( posix_getuid() ) ) {
				$pwuid = posix_getpwuid( posix_getuid() );
				$user  = $pwuid['name'];
			} else {
				$user = $this->_server['REMOTE_ADDR'];
			}
			$this->flag( 'current_user', $user );
		}

		return $this->flags['current_user'];
	}

	/**
	 * Write debug information to error log file.
	 *
	 * @since  2.0.0
	 *
	 * @param mixed $first_arg Each param will be dumped.
	 *
	 * @api
	 */
	public function log( $first_arg ) {
		$orig_format = $this->flag( 'format', 'text' );
		$time        = date( "Y-m-d\tH:i:s\t" );

		foreach ( func_get_args() as $param ) {
			if ( is_scalar( $param ) ) {
				$dump = $param;
			} else {
				$dump = var_export( $param, true );
			}
			touch( $this->log_file );

			if ( ! file_exists( $this->log_file ) ) {
				error_log( 'Log file could not be created: ' . $this->log_file );
			}
			error_log( $time . $dump . "\n", 3, $this->log_file );
		}

		$this->flag( 'format', $orig_format );
	}

	/**
	 * Write stacktrace information to error log file.
	 *
	 * @since  2.0.0
	 * @api
	 */
	public function log_trace() {
		$orig_format = $this->flag( 'format', 'text' );

		// Display the backtrace.
		$trace = $this->get_full_trace( false );
		error_log( $trace, 3, $this->log_file );

		$this->flag( 'format', $orig_format );
	}

	/**
	 * Adds a log-message to the HTTP response header.
	 * This is very useful to debug Ajax requests or redirects.
	 *
	 * @since  2.0.3
	 *
	 * @param string $message The debug message
	 */
	public function header( $message ) {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$messages = func_get_args();

		$this->flags['header_count'] += 1;
		$headers_sent                = headers_sent();

		foreach ( $messages as $ind => $message ) {
			if ( ! is_scalar( $message ) ) {
				$message = json_encode( $message );
			} elseif ( is_bool( $message ) ) {
				$message = ( $message ? '[bool] true' : '[bool] false' );
			} elseif ( is_null( $message ) ) {
				$message = '[null] null';
			}

			$num = $this->flags['header_count'];
			if ( $headers_sent ) {
				// HTTP Headers already sent, so add the response as HTML comment.
				$message = str_replace( '-->', '--/>', $message );
				printf( "<!-- X-Debug[%d.%d]: %s -->\n", $num, $ind, $message );
			} else {
				// No output was sent yet so add the message to the HTTP headers.
				$message = str_replace( [ "\n", "\r" ], ' ', $message );
				header( "X-Debug-$num.$ind: $message", false );
			}
		}

		// Add the backtrace.
		if ( $this->show_trace() ) {
			$this->header_trace();
		}
	}

	/**
	 * Send a debug message to a slack channel.
	 * In order to use this function the setting EVR_SLACK_HOOK needs to be
	 * defined:
	 *     define( 'EVR_SLACK_HOOK', 'T00000000/B11111111/q22222222222222222222222' );
	 *
	 * @since  2.3
	 *
	 * @param string $message      Either the message as string or a structure
	 *                             with slack-notification params. For a list of
	 *                             supported params see the `$valid_params` array.
	 * @param int    $trace_lines  Optional. How many lines of stack-trace should
	 *                             be added to the message? Default is 1 line.
	 * @param array  $channels     Optional. List of recipients.
	 *                             Format: @user/#channel/D123/C123.
	 * @param bool   $is_private   Optional. Only send to specified $channels,
	 *                             not to the internal/predefined channels.
	 */
	public function slack( $message, $trace_lines = 1, $channels = false, $is_private = false ) {
		// Only allow the following fields in the payload.
		$valid_params = [
			'fallback'    => false,
			'color'       => false,
			'text'        => false,
			'pretext'     => false,
			'fields'      => false,
			'author_name' => false,
			'author_link' => false,
			'title'       => false,
			'title_link'  => false,
			'image_url'   => false,
			'thumb_url'   => false,
			'footer'      => false,
			'footer_icon' => false,
			'mrkdwn_in'   => false,
			'ts'          => false,
		];

		// Initialize slack settings, if not already set.
		if ( empty( $this->flags['slack_hook'] ) ) {
			$this->reset_slack();
		}

		if ( empty( $this->flags['slack_hook'] ) ) {
			return false;
		}
		if ( empty( $message ) ) {
			return false;
		}

		$hook_url = 'https://hooks.slack.com/services/' . $this->flags['slack_hook'];

		if ( ! $channels ) {
			$channels = [];
		} elseif ( ! is_array( $channels ) ) {
			$channels = explode( ',', $channels );
		}

		// Validate the recipients.
		foreach ( $channels as $ind => $channel ) {
			$ch_type = substr( $channel, 0, 1 );
			if ( ! in_array( $ch_type, [ '@', 'D', '#', 'C' ] ) ) {
				unset( $channels[ $ind ] );
			} else {
				$channels[ $ind ] = trim( $channel );
			}
		}

		// Add default reporting channels, if message is not private
		if ( ! $is_private || empty( $channels ) ) {
			if ( $this->flags['slack_channel'] ) {
				$public_channels = explode( ',', $this->flags['slack_channel'] );
				foreach ( $public_channels as $channel ) {
					$channels[] = $channel;
				}
			}
		}
		$channels = array_unique( $channels );
		if ( empty( $channels ) ) {
			$channels = [ false ];
		}

		// Build the message attachment.
		if ( is_scalar( $message ) ) {
			$message = [
				'text' => (string) $message,
			];
		}
		if ( is_array( $message['text'] ) ) {
			$textlines = [];
			foreach ( $message['text'] as $key => $value ) {
				$textlines[] = sprintf( '%s: `%s`', $key, $value );
			}
			$message['text'] = implode( "\n", $textlines );
		}
		if ( empty( $message['fallback'] ) ) {
			$fallback = [];
			if ( ! empty( $message['pretext'] ) ) {
				$fallback[] = $message['pretext'];
			}
			if ( ! empty( $message['text'] ) ) {
				$fallback[] = $message['text'];
			}
			$message['fallback'] = implode( ' - ', $fallback );
		}
		if ( ! isset( $message['mrkdwn_in'] ) ) {
			$message['mrkdwn_in'] = [ 'text', 'pretext', 'fields' ];
		}
		if ( isset( $message['fields'] ) ) {
			$fields            = $message['fields'];
			$message['fields'] = [];

			if ( ! is_array( $fields ) ) {
				unset( $message['fields'] );
			} else {
				foreach ( $fields as $label => $value ) {
					$f_title  = $label;
					$f_value  = $value;
					$is_small = true;
					if ( is_array( $value ) && ! empty( $value['title'] ) ) {
						$f_title = $value['title'];
					}
					if ( is_array( $value ) && ! empty( $value['value'] ) ) {
						$f_value = $value['value'];
					}
					if ( is_array( $f_value ) ) {
						$lines = [];
						foreach ( $f_value as $line_label => $line_value ) {
							if ( empty( $line_value ) ) {
								continue;
							}
							if ( ! is_scalar( $line_value ) ) {
								$line_value = json_encode( $line_value );
							}
							if ( is_numeric( $line_label ) ) {
								$lines[] = (string) $line_value;
							} else {
								$lines[] = sprintf(
									'%s: %s',
									$line_label,
									(string) $line_value
								);
							}
						}
						$f_value = implode( "\n", $lines );
					}
					if ( empty( $f_value ) ) {
						continue;
					}

					if ( isset( $message['field_size'] ) ) {
						if ( isset( $message['field_size'][ $f_title ] ) ) {
							$is_small = in_array(
								$message['field_size'][ $f_title ],
								[ 'short', 'small', 's' ]
							);
						}
					}

					$message['fields'][] = [
						'title' => $f_title,
						'value' => $f_value,
						'short' => $is_small,
					];
				}
			}
		}

		if ( $trace_lines > 0 ) {
			$caller = $this->calling_function();

			$remote_user  = $this->_server['REMOTE_ADDR'];
			$local_user   = $this->get_current_user();
			$local_server = false;
			if ( ! empty( $this->_server['SERVER_NAME'] ) ) {
				$local_server = $this->_server['SERVER_NAME'];
			} elseif ( ! empty( $this->_server['HOST'] ) ) {
				$local_server = $this->_server['HOST'];
			}

			$who_is_it = [ $remote_user, $local_user, $local_server ];
			$who_is_it = array_filter( $who_is_it );

			$message['footer'] = sprintf(
				'%s:%d • %s',
				$caller['file_short'],
				$caller['line'],
				implode( ' | ', $who_is_it )
			);
			$trace_lines       -= 1;
		}

		if ( $trace_lines > 0 ) {
			$full_trace = $this->get_simple_trace();
			$trace      = [];

			if ( count( $full_trace ) <= $trace_lines ) {
				$trace = $full_trace;
			} else {
				for ( ; $trace_lines > 0; $trace_lines -= 1 ) {
					if ( empty( $full_trace ) ) {
						break;
					}
					$trace[] = array_shift( $full_trace );
				}
			}

			if ( empty( $message['fields'] ) ) {
				$message['fields'] = [];
			}
			$message['fields'][] = [
				'title' => 'Stacktrace',
				'value' => implode( "\n", $trace ),
				'short' => false,
			];
		}

		$message = array_intersect_key( $message, $valid_params );
		$message = array_filter( $message );

		// Build the payload --------------------------------------------------
		$payload = [
			'attachments' => [ $message ],
			'mrkdwn'      => true,
		];
		if ( $this->flags['slack_name'] ) {
			$payload['username'] = str_replace(
				'@USER',
				$this->get_current_user(),
				$this->flags['slack_name']
			);
		}
		if ( $this->flags['slack_icon'] ) {
			$payload['icon_emoji'] = $this->flags['slack_icon'];
		}

		// Send the message to all recipients ---------------------------------
		$ch = curl_init();
		foreach ( $channels as $recipient ) {
			if ( $recipient ) {
				$payload['channel'] = $recipient;
			} else {
				unset( $payload['channel'] );
			}
			$data = json_encode( $payload );

			curl_setopt( $ch, CURLOPT_URL, $hook_url );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			$response = curl_exec( $ch );
		}
		curl_close( $ch );
	}

	/**
	 * Add a stack-trace to the HTTP response headers.
	 *
	 * @since  2.0.0
	 *
	 * @param string $group  Optional. Prepended to the header to identify the
	 *                       source or reason for the debug output.
	 *
	 * @api
	 */
	public function header_trace( $group = '' ) {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->flags['header_count'] += 1;
		$num                         = $this->flags['header_count'];

		$trace = $this->get_simple_trace();
		if ( $group ) {
			array_unshift( $trace, 'Trace ' . ucwords( $group ) );
		}

		if ( ! headers_sent() ) {
			foreach ( $trace as $ind => $line ) {
				header( "X-Debug-$num.$ind-Trace: $line", false );
			}
		} else {
			echo "\n";
			foreach ( $trace as $ind => $line ) {
				echo "<!-- Debug-Trace-$num.$ind: $line -->\n";
			}
		}
	}

	/**
	 * Displays a debug message at the current position on the page.
	 *
	 * @since  2.5.1
	 *
	 * @param mixed <dynamic> Each param will be dumped.
	 *
	 * @api
	 */
	public function get_dump( $first_arg ) {
		if ( ! $this->is_enabled() ) {
			return '';
		}
		$result     = [];
		$plain_text = $this->is_format( 'text' );

		if ( ! $plain_text ) {
			$this->add_scripts();
			$block_id = 'wp-debug-' . md5( rand() );

			if ( is_scalar( $first_arg ) && ! empty( $first_arg ) ) {
				$block_label = substr( strip_tags( (string) $first_arg ), 0, 30 );
			} else {
				$block_label = 'DEBUG';
			}

			$result[] = sprintf(
				'
					<div class="wp-debug">
					<span class="wp-debug-label" onclick="toggleBlock(\'%1$s\')">
						%2$s
					</span>
					<div class="%1$s">
					<table cellspacing="0" cellpadding="0" width="100%%" border="0" class="wp-debug-dump">
				',
				$block_id,
				$block_label
			);

			foreach ( func_get_args() as $data ) {
				$this->dumped = []; // used to detect recursion.
				$result[]     = $this->_dump_var( $data );
			}
			unset( $this->dumped );

			$result[] = '</table>';
		} else {
			foreach ( func_get_args() as $data ) {
				$this->dumped = []; // used to detect recursion.
				$result[]     = $this->_dump_var( $data );
			}
			unset( $this->dumped );
		}

		// Display the backtrace.
		if ( $this->show_trace() ) {
			$result[] = $this->get_wp_hook( false );
			$result[] = $this->get_full_trace( false );
		}
		if ( $this->show_request() ) {
			$result[] = $this->http_request( false );
		}
		if ( $this->show_stats() ) {
			$result[] = $this->report_stats( false );
		}

		if ( ! $plain_text ) {
			$result[] = '</div><div class="wp-debug-clear"></div></div>';
		}

		return implode( "\n", $result );
	}

	/**
	 * Displays a debug message at the current position on the page.
	 *
	 * @since  1.0.14
	 *
	 * @param mixed <dynamic> Each param will be dumped.
	 *
	 * @api
	 */
	public function dump( $first_arg ) {
		$this->dumps[] = call_user_func_array( [ $this, 'get_dump' ], func_get_args() );
	}

	/**
	 * Outputs all debug messages that were generated via `dump()`.
	 * This function also clears the internal "dump" collection; i.e. calling
	 * `flush()` multiple times will only output the dumps once.
	 *
	 * @since  2.5.1
	 * @api
	 */
	public function flush() {
		echo implode( "\n\n", $this->dumps );

		if ( $this->hooks ) {
			$this->add_scripts();

			$id = md5( rand() . time() );

			printf(
				'<div class="wp-debug evr-hooks"><span class="wp-debug-label" onclick="toggleBlock(\'wp-hooks-%1$s\')">Hooks</span><div class="wp-hooks-%1$s"><ol><li>%2$s</ol></div></div>',
				$id,
				implode( "<li>", $this->hooks )
			);

			arsort( $this->hook_count );

			printf(
				'<div class="wp-debug evr-hooks"><span class="wp-debug-label" onclick="toggleBlock(\'wp-hook-count-%1$s\')">Hook Count</span><div class="wp-hook-count-%1$s"><table>',
				$id
			);

			foreach ( $this->hook_count as $hook => $count ) {
				printf( '<tr><td><span class="dev-item" style="color:#800000">%d</span></td><td>%s</td></tr>', $count, $hook );
			}
			echo '</table></div></div>';
		}

		$this->hooks      = [];
		$this->hook_count = [];
		$this->dumps      = [];
	}

	/**
	 * Adds some redirect headers with debugging information to the response.
	 *
	 * @since  1.0.0
	 */
	public function redirect_headers( $location, $stop_redirect = null ) {
		if ( ! $this->is_enabled() ) {
			return $location;
		}
		if ( ! $location ) {
			return $location;
		}
		if ( null === $stop_redirect ) {
			$stop_redirect = $this->is_redirect_stopped();
		}

		if ( $stop_redirect ) {
			if ( ! headers_sent() ) {
				ob_end_clean();
				header( '200 OK', true, 200 );
				echo '<!doctype html><html>';
			}

			echo '<center><h3>The website wants to redirect to this URL:</h3></center>';
			printf( '<center><a href="%s">%s</a></center><hr>', $location, $location );

			if ( $this->show_trace() ) {
				$this->get_full_trace();
			}
			exit;
		} else {
			$this->header_trace( 'redirect' );
		}

		return $location;
	}

	/**
	 * Starts or stops global WP hooks debugging.
	 *
	 * @since 2.5.2
	 *
	 * @param bool     $enabled
	 * @param string[] $patterns
	 * @param bool     $is_whitelist
	 *
	 * @return void
	 */
	public function debug_hooks( $enabled = true, $patterns = [], bool $is_whitelist = false ) {
		$this->hook_patterns          = (array) $patterns;
		$this->hook_pattern_whitelist = $is_whitelist;

		if ( ! $is_whitelist ) {
			$this->hook_patterns[] = 'alloptions';
			$this->hook_patterns[] = '/^n?gettext.*/';
			$this->hook_patterns[] = '/^sanitize_.*/';
			$this->hook_patterns[] = '/^esc_.*/';
			$this->hook_patterns[] = 'attribute_escape';
		}

		remove_action( 'all', [ $this, '_on_any_hook' ] );
		if ( $enabled ) {
			add_action( 'all', [ $this, '_on_any_hook' ] );
		}
	}

	/**
	 * Internal hook callback that collects a trace of all WP hooks that fired.
	 *
	 * @since 2.5.2
	 *
	 * @param $filter
	 *
	 * @return mixed Returns the unmodified $filter value, to keep WP filters intact.
	 */
	public function _on_any_hook( $filter ) {
		$hook     = current_filter();
		$is_match = ! $this->hook_pattern_whitelist;

		foreach ( $this->hook_patterns as $pattern ) {
			if ( '/' === $pattern[0] ) {
				$match = preg_match( $pattern, $hook );
			} else {
				$match = $pattern === $hook;
			}

			if ( $match ) {
				$is_match = $this->hook_pattern_whitelist;
				break;
			}
		}

		if ( $is_match ) {
			if ( isset( $this->hook_count[ $hook ] ) ) {
				$this->hook_count[ $hook ] ++;
			} else {
				$this->hook_count[ $hook ] = 1;
			}

			$this->hooks[] = $hook;
		}

		return $filter;
	}

	/**
	 * Generates an array of stack-trace information. Each array item is a
	 * simple string that can be directly output.
	 *
	 * @since  1.0.0
	 * @return array Trace information
	 */
	public function get_simple_trace() {
		$result = [];

		$trace       = debug_backtrace();
		$trace_count = count( $trace );
		$_num        = 0;
		$start_at    = 0;

		// Skip the first 4 trace lines (filter call inside wp_redirect)
		if ( $trace_count > 4 ) {
			$start_at = 4;
		}

		for ( $i = $start_at; $i < $trace_count; $i += 1 ) {
			$trace_info = $trace[ $i ];
			$line_info  = $trace_info;
			$j          = $i;

			while ( empty( $line_info['line'] ) && $j < $trace_count ) {
				$line_info = $trace[ $j ];
				$j         += 1;
			}

			$_file     = empty( $line_info['file'] ) ? '' : $line_info['file'];
			$_line     = empty( $line_info['line'] ) ? '' : $line_info['line'];
			$_args     = empty( $trace_info['args'] ) ? [] : $trace_info['args'];
			$_class    = empty( $trace_info['class'] ) ? '' : $trace_info['class'];
			$_type     = empty( $trace_info['type'] ) ? '' : $trace_info['type'];
			$_function = empty( $trace_info['function'] ) ? '' : $trace_info['function'];

			$_num        += 1;
			$_arg_string = '';
			$_args_arr   = [];

			if ( $i > 0 && is_array( $_args ) && count( $_args ) ) {
				foreach ( $_args as $arg ) {
					if ( is_scalar( $arg ) ) {
						if ( is_bool( $arg ) ) {
							$_args_arr[] = ( $arg ? 'true' : 'false' );
						} elseif ( is_string( $arg ) ) {
							$arg = str_replace(
								[ "\n", "\r", "\t" ],
								[ '', '', ' ' ],
								$arg
							);
							if ( strlen( $arg ) > 20 ) {
								$arg = substr( $arg, 0, 20 ) . '...';
							}
							$_args_arr[] = '"' . $arg . '"';
						} else {
							$_args_arr[] = $arg;
						}
					} elseif ( is_array( $arg ) ) {
						$_args_arr[] = '[Array]';
					} elseif ( is_object( $arg ) ) {
						$_args_arr[] = '[' . get_class( $arg ) . ']';
					} elseif ( is_null( $arg ) ) {
						$_args_arr[] = 'NULL';
					} else {
						$_args_arr[] = '[?]';
					}
				}

				$_arg_string = implode( ', ', $_args_arr );
			}

			if ( strlen( $_file ) > 80 ) {
				$_file = '...' . substr( $_file, - 77 );
			} else {
				$_file = str_pad( $_file, 80, ' ', STR_PAD_RIGHT );
			}

			$_num_str    = str_pad( $_num, 2, '0', STR_PAD_LEFT );
			$result_item = sprintf(
				'%d# %s:%s %s(%s)',
				$_num_str,
				$_file,
				str_pad( $_line, 5, ' ', STR_PAD_LEFT ),
				$_class . $_type . $_function,
				$_arg_string
			);

			$result[ $_num_str ] = $result_item;
		}

		return $result;
	}

	/**
	 * Outputs information on the current WP hook.
	 *
	 * @since 2.5.1
	 *
	 * @param bool      $outpt      Optional. Whether to output the debug information to
	 *                              STDOUT or return it as string.
	 * @param bool|null $plain_text Optional. When true, the debug output is formatted as
	 *                              HTML, when false it's plain text.
	 *
	 * @return void|string Depending on the $output flag, returns void or debug details.
	 */
	public function get_wp_hook( $output = true, $plain_text = null ) {
		if ( null === $plain_text ) {
			$plain_text = $this->is_format( 'text' );
		}

		$trace_str = [];

		if ( defined( 'ABSPATH' ) && ! empty( $GLOBALS['wp_current_filter'] ) ) {
			if ( ! $plain_text ) {
				$this->add_scripts();
				$block_id    = 'wp-debug-' . md5( 'hooks' . rand() );
				$trace_str[] = sprintf(
					'<span class="wdev-trace-toggle" onclick="toggleBlock(\'%1$s-hooks\')">
					<b>WP Hooks</b>
				</span>
				<div class="%1$s-hooks" style="display:none">
				<table class="wdev-trace" width="100%%" cellspacing="0" cellpadding="3" border="1">
				',
					$block_id
				);
			}

			$last = count( $GLOBALS['wp_current_filter'] ) - 1;
			foreach ( $GLOBALS['wp_current_filter'] as $line => $hook ) {
				$filter = $GLOBALS['wp_filter'][ $hook ];
				$prio   = $filter->current_priority();

				$trace_str[] = sprintf(
					"<tr><td class='trc-num' onclick='_m(this)'>%s</td><td>%s%s</td><td>priority %s</td></tr>\r\n",
					$line + 1,
					$line === $last ? '→ ' : '',
					$hook,
					$prio
				);
			}
		}

		if ( $plain_text ) {
			$trace_str[] = "\r\n-----\r\n";
		} else {
			$trace_str[] = '</table>';
			$trace_str[] = '</div>';
		}

		$result = implode( '', $trace_str );
		if ( $output ) {
			echo $result;
		}

		return $result;
	}


	/**
	 * Output a stack-trace.
	 *
	 * @since  2.0.0
	 *
	 * @param bool      $output     Optional. If false then the trace will be returned
	 *                              instead of echo'ed. Default: true (echo).
	 * @param bool|null $plain_text Optional. When true, the debug output is formatted as
	 *                              HTML, when false it's plain text.
	 *
	 * @return string Returns the stack-trace contents.
	 * @api
	 */
	public function get_full_trace( $output = true, $plain_text = null ) {
		if ( null === $plain_text ) {
			$plain_text = $this->is_format( 'text' );
		}

		$trace_str = [];

		if ( ! $plain_text ) {
			$this->add_scripts();
			$block_id    = 'wp-debug-' . md5( 'trace' . rand() );
			$trace_str[] = sprintf(
				'<span class="wdev-trace-toggle" onclick="toggleBlock(\'%1$s-trace\')">
					<b>Back-Trace</b>
				</span>
				<div class="%1$s-trace" style="display:none">
				<table class="wdev-trace" width="100%%" cellspacing="0" cellpadding="3" border="1">
				',
				$block_id
			);
		}

		$trace     = debug_backtrace();
		$trace_num = count( $trace );
		$line      = 0;

		for ( $i = 0; $i < $trace_num; $i += 1 ) {
			$item      = $trace[ $i ];
			$line_item = $item;
			$j         = $i;

			while ( empty( $line_item['line'] ) && $j < $trace_num ) {
				$line_item = $trace[ $j ];
				$j         += 1;
			}

			$line_item = $this->get_trace_details( $line_item );
			$item      = $this->get_trace_details( $item );

			if ( 0 === strpos( $line_item['file'], __FILE__ ) ) {
				continue;
			}
			if ( 0 === strpos( $item['file'], __FILE__ ) ) {
				continue;
			}

			$line  += 1;
			$args  = '';
			$dummy = [];

			if ( $i > 0 && is_array( $item['args'] ) && count( $item['args'] ) ) {
				foreach ( $item['args'] as $arg ) {
					if ( is_scalar( $arg ) ) {
						if ( is_bool( $arg ) ) {
							$dummy[] = ( $arg ? 'true' : 'false' );
						} elseif ( is_string( $arg ) ) {
							$dummy[] = '"' . htmlspecialchars( $arg ) . '"';
						} else {
							$dummy[] = htmlspecialchars( $arg );
						}
					} elseif ( is_array( $arg ) ) {
						$dummy[] = '<i>[Array]</i>';
					} elseif ( is_object( $arg ) ) {
						$dummy[] = '<i>[' . get_class( $arg ) . ']</i>';
					} elseif ( is_null( $arg ) ) {
						$dummy[] = '<i>NULL</i>';
					} else {
						$dummy[] = '<i>[???]</i>';
					}
				}

				$args = implode( '</font></span><span class="trc-param"><font>', $dummy );
				$args = '<span class="trc-param"><font>' . $args . '</font></span>';
			}

			if ( $plain_text ) {
				$file = $line_item['file'];
				if ( strlen( $file ) > 80 ) {
					$file = '...' . substr( $line_item['file'], - 77 );
				} else {
					$file = str_pad( $file, 80, ' ', STR_PAD_RIGHT );
				}

				$trace_str[] = sprintf(
					"\r\n  %s. \t %s \t by %s",
					str_pad( $line, 2, ' ', STR_PAD_LEFT ),
					$file . ': ' . str_pad( $line_item['line'], 5, ' ', STR_PAD_LEFT ),
					$item['class'] . $item['type'] . $item['function'] . '(' . strip_tags( $args ) . ')'
				);
			} else {
				$dir      = dirname( $line_item['file'] );
				$file     = basename( $line_item['file'] );
				$the_file = sprintf(
					'%s<span class="trc-file">%s</span>',
					$dir . DIRECTORY_SEPARATOR,
					$file
				);

				if ( 1 == $line ) {
					$trace_str[] = sprintf(
						"<tr><td class='trc-num' onclick='_m(this)'>%s</td><td class='trc-loc' colspan='2'><span class='trc-line'>%s</span>%s</td></tr>\r\n",
						$line,
						':' . $line_item['line'],
						$the_file
					);
				} else {
					$trace_str[] = sprintf(
						"<tr><td class='trc-num' onclick='_m(this)'>%s</td><td class='trc-loc'><span class='trc-line'>%s</span>%s</td><td class='trc-arg'>%s</td></tr>\r\n",
						$line,
						':' . $line_item['line'],
						$the_file,
						$item['class'] . $item['type'] . $item['function'] . '(' . $args . ')'
					);
				}
			}
		}

		if ( $plain_text ) {
			$trace_str[] = "\r\n-----\r\n";
		} else {
			$trace_str[] = '</table>';
			$trace_str[] = '</div>';
		}

		$result = implode( '', $trace_str );
		if ( $output ) {
			echo $result;
		}

		return $result;
	}

	/**
	 * Returns an HTML element that displays a colored label. By default the
	 * label is a random/unique MD5 hash.
	 * This marker is intended for debugging to identify changes in objects
	 * that are loaded via ajax.
	 *
	 * @since  2.0.1
	 *
	 * @param string $label  Optional. The label to display. Default is a
	 *                       random MD5 string.
	 * @param array  $styles Optional. Array of CSS styles to apply.
	 *
	 * @return object {
	 *         Marker details
	 *         $html
	 *         $hash
	 *         $text
	 *         $color
	 * }
	 * @api
	 */
	public function marker_html( $data = null, $styles = [] ) {
		$hash = md5( rand( 1000, 9999 ) . time() );

		if ( null === $data ) {
			$label = $hash;
		} else {
			if ( ! is_string( $data ) && is_callable( $data ) ) {
				$type = 'Callable';
			} else {
				$type = ucfirst( gettype( $data ) );
			}

			$hash = md5( json_encode( $data ) );

			if ( is_scalar( $data ) ) {
				$label = $type . ': ' . (string) $data;
			} else {
				$label = $type . ': ' . $hash;
			}
		}

		$color        = substr( $hash, 0, 3 ) . substr( $hash, - 3 );
		$block_styles = [
			'font-size'      => '12px',
			'text-transform' => 'uppercase',
			'font-family'    => 'monospace',
			'text-align'     => 'center',
			'margin'         => '5px auto',
		];
		$def_styles   = [
			'display'        => 'inline-block',
			'background'     => '#' . $color,
			'color'          => '#fff',
			'min-width'      => '250px',
			'max-width'      => '400px',
			'font-size'      => '12px',
			'text-transform' => 'uppercase',
			'font-family'    => 'monospace',
			'text-align'     => 'center',
			'margin'         => '5px auto',
			'border-radius'  => '3px',
			'padding'        => '4px 14px',
			'text-shadow'    => '0 0 1px #000, 0 0 5px rgba(0,0,0,0.2)',
			'box-shadow'     => '0 0 0 1px rgba(0,0,0,0.25) inset, 0 4px 5px -2px rgba(0,0,0,0.3)',
		];

		foreach ( $def_styles as $key => $value ) {
			if ( ! isset( $styles[ $key ] ) ) {
				$styles[ $key ] = $value;
			}
		}

		$block_style = '';
		$style       = '';
		foreach ( $block_styles as $key => $val ) {
			$block_style .= $key . ':' . $val . ';';
		}
		foreach ( $styles as $key => $val ) {
			$style .= $key . ':' . $val . ';';
		}

		$marker = sprintf(
			'<div style="%1$s"><span style="%2$s">%3$s</span></div>',
			$block_style,
			$style,
			$label
		);

		return (object) [
			'html'  => $marker,
			'hash'  => $hash,
			'text'  => $label,
			'color' => '#' . $color,
		];
	}

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	// Protected / Internal functions
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	/**
	 * Return details about the function that called the debug function.
	 *
	 * @since  2.3.1
	 * @return array
	 */
	protected function calling_function() {
		$info  = [];
		$trace = debug_backtrace();

		foreach ( $trace as $i => $item ) {
			if ( isset( $trace[ $i + 1 ] ) ) {
				$next = $trace[ $i + 1 ];
			} else {
				$next = $item;
			}

			if ( ! isset( $item['file'] ) ) {
				$item['file'] = false;
			}
			if ( ! isset( $next['function'] ) ) {
				$next['function'] = false;
			}

			if ( __FILE__ == $item['file'] ) {
				continue;
			}
			if ( ! isset( $item['line'] ) ) {
				continue;
			}

			$info['line']       = $item['line'];
			$info['file']       = $item['file'];
			$info['file_short'] = $item['file'];
			$info['function']   = $next['function'];

			if ( strlen( $info['file_short'] ) > 100 ) {
				$info['file_short'] = '...' . substr( $info['file_short'], - 97 );
			}
			break;
		}

		return $info;
	}

	/**
	 * Output details about the current HTTP request
	 *
	 * @since  2.0.0
	 *
	 * @param bool $output Optional. If false then the details will be returned
	 *                     instead of echo'ed. Default: true (echo)
	 *
	 * @return string Returns http-request details.
	 * @api
	 */
	protected function http_request( $output = true ) {
		if ( ! $this->is_enabled() ) {
			return '';
		}
		$plain_text = $this->is_format( 'text' );

		$info_str = '';

		if ( ! $plain_text ) {
			$this->add_scripts();
			$block_id = 'wp-debug-' . md5( 'request' . rand() );
			$info_str .= sprintf(
				'<span class="wdev-trace-toggle" onclick="toggleBlock(\'%1$s-trace\')">
					<b>HTTP Request</b>
				</span>
				<div class="%1$s-trace" style="display:none">
				<table class="wdev-trace" width="100%%" cellspacing="0" cellpadding="3" border="1">
				',
				$block_id
			);
		}

		$groups = [
			'Request' => [],
			'Cookie'  => [],
			'HTTP'    => [],
		];

		$groups['Request']['URI']    = $this->_server['REQUEST_URI'];
		$groups['Request']['Method'] = $this->_server['REQUEST_METHOD'];

		if ( isset( $this->_server['HTTP_COOKIE'] ) ) {
			$cookies = explode( ';', $this->_server['HTTP_COOKIE'] );

			foreach ( $cookies as $cookie ) {
				$parts = explode( '=', $cookie );
				$name  = trim( array_shift( $parts ) );

				$groups['Cookie'][ $name ] = implode( '=', $parts );
			}

			ksort( $groups['Cookie'] );
		} else {
			unset( $groups['Cookie'] );
		}

		foreach ( $this->_server as $key => $value ) {
			if ( 'HTTP_COOKIE' == $key ) {
				continue;
			}

			if ( 0 === strpos( $key, 'HTTP_' ) ) {
				$header = strtolower( substr( $key, 5 ) );
				$header = ucwords( trim( str_replace( '_', ' ', $header ) ) );

				$groups['HTTP'][ $header ] = $value;
			}
		}

		ksort( $groups['HTTP'] );

		foreach ( $groups as $label => $infos ) {
			if ( $plain_text ) {
				$info_str .= sprintf(
					"\r\n  %s:",
					$label
				);
			} else {
				$info_str .= sprintf(
					"<tr><td class='trc-group' colspan='2'>%s</td></tr>\r\n",
					$label
				);
			}

			foreach ( $infos as $key => $value ) {
				if ( $plain_text ) {
					$info_str .= sprintf(
						"\r\n  %s: \t %s",
						$key,
						$value
					);
				} else {
					$info_str .= sprintf(
						"<tr><td class='trc-key' onclick='_m(this)'>%s</td><td class='trc-val'>%s</td></tr>\r\n",
						$key,
						$value
					);
				}
			}
		}

		if ( $plain_text ) {
			$info_str .= "\r\n-----\r\n";
		} else {
			$info_str .= '</table>';
			$info_str .= '</div>';
		}

		if ( $output ) {
			echo $info_str;
		}

		return $info_str;
	}

	/**
	 * Output stats like memory usage and time.
	 *
	 * @since  2.0.0
	 *
	 * @param bool $output Optional. If false then the details will be returned
	 *                     instead of echoed. Default: true (echo)
	 *
	 * @return string Returns stats details.
	 * @api
	 */
	protected function report_stats( $output = true ) {
		if ( ! $this->is_enabled() ) {
			return '';
		}
		$plain_text = $this->is_format( 'text' );

		$mem_usage = memory_get_usage();
		$mem_max   = (int) ini_get( 'memory_limit' ) * 1048576; // MB to byte.

		$infos = [];

		$infos['memory'] = $this->format_size( $mem_usage );

		$infos['max_memory'] = sprintf(
			'%d%% of %s',
			round( $mem_usage / $mem_max * 100, 0 ),
			$this->format_size( $mem_max )
		);

		$infos['elapsed_time'] = sprintf(
			'%s sec',
			round( microtime( true ) - $this->start_stamp, 2 )
		);

		$infos['phpversion'] = 'php ' . PHP_VERSION;

		if ( $plain_text ) {
			$info_str = sprintf(
				"\r\n %s",
				implode( ' | ', $infos )
			);
		} else {
			$info_str = sprintf(
				"<span class='wdev-trace-stats'><b>%s</b></span>\r\n",
				implode( '</b> | <b>', $infos )
			);
		}

		if ( $output ) {
			echo $info_str;
		}

		return $info_str;
	}

	/**
	 * Format an byte integer as readable size expression
	 *
	 * @since  2.3.8
	 *
	 * @param int $size      The byte value.
	 * @param int $precision Optional. Precisission of the final expression.
	 *
	 * @return string The size expression, like '2.54 KB'.
	 */
	protected function format_size( $size, $precision = 2 ) {
		$base     = log( $size, 1024 );
		$suffixes = [ '', 'K', 'M', 'G', 'T' ];

		return sprintf(
			'%s %s',
			round( pow( 1024, $base - floor( $base ) ), $precision ),
			$suffixes[ floor( $base ) ]
		);
	}

	/**
	 * Returns the var dump as (HTML) string.
	 *
	 * @internal
	 * @since  1.1.0
	 *
	 * @param mixed $data          The variable/object/value to dump.
	 * @param mixed $item_key
	 * @param int   $default_depth Deeper items will be collapsed
	 * @param int   $level         Do not change this value!
	 *
	 * @return string The var dump.
	 */
	protected function _dump_var( $data, $item_key = null, $default_depth = 3, $level = [ null ], $args = [] ) {
		$result = [];

		if ( ! is_string( $data ) && is_callable( $data ) ) {
			$type = 'Callable';
		} else {
			$type = ucfirst( gettype( $data ) );
		}

		if ( empty( $level ) ) {
			$level = [ null ];
		}
		if ( ! is_array( $args ) ) {
			$args = [];
		}
		if ( empty( $args['containers'] ) || ! is_array( $args['containers'] ) ) {
			$args['containers'] = [];
		}
		if ( empty( $args['collapsed'] ) || ! is_array( $args['collapsed'] ) ) {
			$args['collapsed'] = [];
		}

		$type_data   = null;
		$type_length = null;

		switch ( $type ) {
			case 'String':
				$type_length = strlen( $data );
				$type_data   = '"' . htmlentities( $data ) . '"';
				break;

			case 'Double':
			case 'Float':
				$type        = 'Float';
				$type_length = strlen( $data );
				$type_data   = htmlentities( $data );
				break;

			case 'Integer':
				$type_length = strlen( $data );
				$type_data   = htmlentities( $data );

				$str_val = (string) $data;
				if ( 10 == strlen( $str_val ) && '1' === $str_val[0] ) {
					$type = 'Timestamp';

					$type_data = sprintf(
						'<span title="%1$s">%2$s</span> <span class="val-ts">%1$s</span>',
						gmdate( 'Y-m-d H:i:s', $data ) . ' UTC',
						$type_data
					);
				}
				break;

			case 'Boolean':
				$type_length = strlen( $data );
				$type_data   = $data ? '<i class="bool-true"></i> TRUE' : '<i class="bool-false"></i> FALSE';
				break;

			case 'NULL':
				$type_length = 0;
				$type_data   = 'NULL';
				break;

			case 'Array':
				$type_length = count( $data );
				break;
		}

		$type_label = $type . ( null !== $type_length ? '(' . $type_length . ')' : '' );

		if ( in_array( $type, [ 'Object', 'Array' ] ) ) {
			$populated = false;

			// Prevent circular references.
			if ( is_object( $data ) ) {
				foreach ( $this->dumped as $recursion ) {
					if ( $recursion === $data ) {
						$result[] = $this->_dump_line(
							$item_key,
							get_class( $recursion ),
							'*RECURSION*',
							$level,
							$args
						);

						return;
					}
				}
				$this->dumped[] = $data;
			}

			$dump_data = (array) $data;

			// Sort the dump alphabetically for better overview.
			if ( $this->flags['sort'] ) {
				ksort( $dump_data );
			}

			if ( 'Object' == $type ) {
				$type_label .= ' [' . get_class( $data ) . ']';
			}

			$keys     = array_keys( $dump_data );
			$last_key = end( $keys );
			reset( $dump_data );

			foreach ( $dump_data as $key => $value ) {
				if ( ! $populated ) {
					$populated = true;
					$id        = substr( md5( rand() . ':' . $key . ':' . count( $level ) ), 0, 8 );

					$args['containers'][] = $id;

					if ( count( $args['containers'] ) >= $default_depth ) {
						$args['collapsed'][] = $id;
					}

					$title_args           = $args;
					$title_args['toggle'] = $id;

					$result[] = $this->_dump_line(
						$item_key,
						$type_label,
						'',
						$level,
						$title_args
					);

					unset( $args['protected'] );
					unset( $args['private'] );
				}

				// Tree right before the item-name
				$new_level = $level;

				if ( $last_key == $key ) {
					$new_level[]     = false;
					$args['lastkey'] = true;
				} else {
					$new_level[]     = true;
					$args['lastkey'] = false;
				}

				$encode_key = json_encode( $key );
				$matches    = null;

				if ( 1 === strpos( $encode_key, '\\u0000*\\u0000' ) ) {
					$args['protected'] = true;
					$key               = substr( $key, 3 );
				} elseif ( 1 === preg_match( '/\\\\u0000(\w+)\\\\u0000/i', $encode_key, $matches ) ) {
					$args['private'] = true;
					$key             = substr( $key, 2 + strlen( $matches[1] ) );
				}

				$result[] = $this->_dump_var(
					$value,
					$key,
					$default_depth,
					$new_level,
					$args
				);

				unset( $args['protected'] );
				unset( $args['private'] );
			} // end of array/object loop.

			if ( ! $populated ) {
				$result[] = $this->_dump_line(
					$item_key,
					$type_label,
					'',
					$level,
					$args
				);
			}
		} else {
			$result[] = $this->_dump_line(
				$item_key,
				$type_label,
				$type_data,
				$level,
				$args
			);
		}

		return implode( "\n", $result );
	}

	/**
	 * Returns a single line of the dump_var output.
	 *
	 * @internal
	 * @since  1.1.4
	 * @return string The dump-line.
	 */
	protected function _dump_line( $key, $type, $value, $level, $args = [] ) {
		$type_color = '#999';
		$type_key   = strtolower( $type );

		if ( strlen( $type_key ) > 4 ) {
			$type_key = substr( $type_key, 0, 4 );
		}

		$custom_type_colors = [
			'stri' => 'green',
			'doub' => '#0099c5',
			'floa' => '#0099c5',
			'inte' => 'red',
			'time' => 'red',
			'bool' => '#92008d',
			'null' => '#AAA',
		];

		if ( isset( $custom_type_colors[ $type_key ] ) ) {
			$type_color = $custom_type_colors[ $type_key ];
		}

		if ( '*RECURSION*' === $value ) {
			$type_color = '#940';
		}

		$collapse            = array_intersect( $args['containers'], $args['collapsed'] );
		$args['do_collapse'] = is_array( $collapse ) && count( $collapse ) > 0;

		if ( ! empty( $args['toggle'] ) ) {
			$args['containers'] = array_diff( $args['containers'], [ $args['toggle'] ] );
			$args['collapsed']  = array_diff( $args['collapsed'], [ $args['toggle'] ] );

			$collapse_this            = array_intersect( $args['containers'], $args['collapsed'] );
			$args['do_collapse_next'] = $args['do_collapse'];
			$args['do_collapse']      = is_array( $collapse_this ) && count( $collapse_this ) > 0;
		}

		$row_class = '';
		$row_attr  = '';
		if ( ! empty( $args['containers'] ) ) {
			$row_class = implode( ' ', $args['containers'] );
		}
		if ( ! empty( $args['do_collapse'] ) ) {
			$row_attr = 'style="display:none;"';
		}

		$line   = [];
		$line[] = '<tr class="' . $row_class . '"' . $row_attr . '><td>';

		// Property-key, if set.
		if ( null === $key ) {
			// Full Tree-level.
			$line[] = '<span class="dev-tree">';

			for ( $i = 0; $i < count( $level ); $i += 1 ) {
				if ( null === $level[ $i ] ) {
					continue;
				}
				if ( $level[ $i ] ) {
					$line[] = '&nbsp;│&nbsp;';
				} else {
					$line[] = '&nbsp;&nbsp;&nbsp;';
				}
			}

			$line[] = '</span>';
		} else {
			$line[] = '<span class="dev-tree">';

			// Tree-level without last level.
			for ( $i = 0; $i < count( $level ) - 1; $i += 1 ) {
				if ( null === $level[ $i ] ) {
					continue;
				}
				if ( $level[ $i ] ) {
					$line[] = '&nbsp;│&nbsp;';
				} else {
					$line[] = '&nbsp;&nbsp;&nbsp;';
				}
			}

			if ( empty( $args['lastkey'] ) ) {
				$line[] = '&nbsp;├─';
			} else {
				$line[] = '&nbsp;└─';
			}
			$line[] = '</span>';

			$key_style = '';
			if ( ! empty( $args['protected'] ) ) {
				$key_style .= 'color:#900;';
				$prefix    = '';
			} elseif ( ! empty( $args['private'] ) ) {
				$key_style .= 'color:#C00;font-style:italic;';
				$prefix    = 'PRIVATE ';
			} else {
				$key_style .= 'color:#000;';
				$prefix    = '';
			}

			$mark_ids = $this->flags['mark_fields'];
			if ( $mark_ids && in_array( (string) $key, $mark_ids ) ) {
				$key_style .= 'background:#FDA;';
			}

			$line[] = '<span class="dev-item dev-item-key" style="' . $key_style . '">[ ' . $prefix . $key . ' ]</span>';
			$line[] = '<span class="dev-item"> => </span>';
		}

		// Data-Type.
		if ( ! empty( $args['toggle'] ) ) {
			$collapsed    = ! empty( $args['do_collapse_next'] );
			$toggle_style = 'display: ' . ( $collapsed ? 'inline' : 'none' );

			$line[] = '<a href="javascript:toggleDisplay(\'' . $args['toggle'] . '\',\'' . trim( $row_class . ' ' . $args['toggle'] ) . '\');" class="dev-item dev-toggle-item">';
			$line[] = '<span style="color:#666666">' . $type . '</span>&nbsp;&nbsp;';
			$line[] = '</a>';

			$line[] = '<a href="javascript:toggleDisplay(\'' . $args['toggle'] . '\',\'' . trim( $row_class . ' ' . $args['toggle'] ) . '\',1);" class="dev-item dev-toggle-alt">';
			$line[] = '<span id="plusalt' . $args['toggle'] . '" class="plus-alt dev-item" style="color:#666666;' . $toggle_style . '">+</span>';
			$line[] = '</a>';

			$line[] = '<span id="plus' . $args['toggle'] . '" class="plus dev-item" style="' . $toggle_style . '">&nbsp;&#10549;</span>';
		} elseif ( $type ) {
			$line[] = '<span class="dev-item" style="color:#666666">' . $type . '&nbsp;&nbsp;</span>';
		}

		// Value.
		if ( null !== $value ) {
			$value_style = '';
			if ( isset( $args['highlight'] ) ) {
				$value_style = $args['highlight'];
			}
			$line[] = '<span class="dev-item val" style="color:' . $type_color . ';' . $value_style . '">' . $value . '</span>';
		}

		$line[] = '</td></tr>';
		$line[] = "\r\n";

		$html_out = implode( '', $line );

		if ( 'html' == $this->flag( 'format' ) ) {
			return $html_out;
		} else {
			return html_entity_decode( strip_tags( $html_out ) );
		}
	}

	/**
	 * Outputs the CSS and JS scripts required to display the debug dump/trace.
	 *
	 * @internal
	 * @since 2.0.0
	 */
	protected function add_scripts() {
		if ( $this->is_format( 'text' ) ) {
			return;
		}
		if ( ! empty( $this->flags['scripts_done'] ) ) {
			return;
		}
		$this->flag( 'scripts_done', true );

		if ( ! headers_sent() ) {
			header( 'Content-type: text/html; charset=utf-8' );
		}

		?>
		<style>
			.wp-debug {
				clear: both;
				border: 1px solid #C00;
				background: rgba(255, 200, 200, 1);
				padding: 10px;
				margin: 10px;
				position: relative;
				z-index: 999999;
				box-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
				font-size: 14px;
				font-family: sans-serif;
				font-weight: 400;
				line-height: 1;
			}

			.wp-debug .dev-tree {
				color: #000;
				opacity: .2;
				font-family: monospace;
				font-size: 19px;
				line-height: 16px;
				float: left;
			}

			.wp-debug .dev-item {
				float: left;
				line-height: 18px;
				white-space: pre-wrap;
			}

			.wp-debug .dev-item.val {
				font-family: Monaco, monospace;
				font-weight: 500;
			}

			.wp-debug .dev-item .bool-true,
			.wp-debug .dev-item .bool-false {
				display: inline-block;
				width: 6px;
				height: 6px;
				border-radius: 5px;
				margin: -1px 3px 0;
				vertical-align: middle;
			}

			.wp-debug .dev-item .bool-true {
				background: rgba(0, 148, 32, 0.8);
			}

			.wp-debug .dev-item .bool-false {
				background: rgba(128, 32, 32, 0.4);
			}

			.wp-debug .dev-toggle-item,
			.wp-debug .dev-toggle-alt {
				text-decoration: none;
				background: rgba(255, 255, 255, 0.2);
				display: inline-block;
			}

			.wp-debug .dev-toggle-alt {
				margin: 0 0 0 4px;
				padding: 0 0.3em;
			}

			.wp-debug .wp-debug-dump {
				margin: 0;
				border-collapse: collapse;
				padding: 0;
				border: 0;
			}

			.wp-debug .wdev-trace-toggle {
				display: block;
				margin: 10px 0 0 0;
				text-align: left;
			}

			.wp-debug .wdev-trace-stats {
				display: block;
				text-align: left;
				margin: 10px 0 0 -4px;
				font-size: 10px;
				opacity: 0.4;
				color: #333;
			}

			.wp-debug .wdev-trace-stats:hover {
				opacity: 1;
			}

			.wp-debug .wdev-trace-stats b {
				display: inline-block;
				margin: 0 4px;
			}

			.wp-debug .wp-debug-dump td {
				font-size: 14px;
				line-height: 1.15;
				font-family: sans-serif;
				background: transparent;
				cursor: default;
				padding: 2px 0;
				border: 0;
			}

			.wp-debug .wp-debug-dump tr:hover td {
				background-color: #FFF;
				background-color: rgba(255, 255, 255, 0.3);
			}

			.wp-debug-clear {
				clear: both;
				display: table;
				padding: 0;
				margin: 0;
			}

			.wp-debug-label {
				font-size: 14px;
				font-weight: 600;
				line-height: 20px;
				float: right;
				margin: -10px;
				color: #FFF;
				text-shadow: 0 1px 1px #0008;
				background-color: #b34d4d;
				padding: 0 8px;
				cursor: pointer;
				max-width: 50%;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				transistion: all .3s;
			}

			.wp-debug-label:hover {
				background-color: #E66;
			}

			.wp-debug pre {
				font-size: 14px !important;
				margin: 1px 0 !important;
				background: rgba(255, 200, 200, 0.8);
			}

			.wp-debug .val-ts {
				font-style: italic;
				color: #555;
				opacity: 0.8;
				font-size: 0.95em;
			}

			.wp-debug .val-ts:before {
				content: '\028';
			}

			.wp-debug .val-ts:after {
				content: '\029';
			}

			.wdev-trace td {
				padding: 3px !important;
				font-size: 14px;
				vertical-align: top;
				transition: all 0.3s;
			}

			.wdev-trace {
				margin: 4px 0 0 0;
				background: #FFF2;
				border-collapse: collapse;
			}

			.wdev-trace tr.mark td {
				background: #EC9;
				color: #444;
			}

			.wdev-trace tr.mark td.trc-num {
				background: #EC9;
				color: #222;
			}

			.wdev-trace tr td {
				cursor: default;
				color: #666;
				opacity: 0.7;
			}

			.wdev-trace tr:hover td {
				color: #222;
				background: #fff8;
				opacity: 0.85;
			}

			.wdev-trace-toggle {
				cursor: pointer;
				color: #333;
			}

			.wp-debug .trc-num {
				width: 32px;
				min-width: 32px;
				text-align: right;
			}

			.wp-debug .trc-loc {
				width: 60%;
			}

			.wp-debug .trc-arg {
				width: 40%;
				white-space: pre-wrap;
			}

			.wp-debug .trc-key {
				width: 25%;
				white-space: pre-wrap;
			}

			.wp-debug .trc-val {
				width: 75%;
			}

			.wp-debug .trc-group {
				border-top: 1px solid #444;
				opacity: 1;
				background: rgba(255, 255, 255, .1);
			}

			.wp-debug .trc-param {
				padding: 0 3px;
				display: block;
				margin: 2px 0 2px 8px;
				max-height: 4.3em;
				overflow: hidden;
				transition: all 0.3s;
			}

			.wp-debug .trc-param:hover {
				max-height: calc(100vh - 30px);
				overflow: auto;
			}

			.wp-debug .trc-param + .trc-param {
				margin-top: 4px;
			}

			.wp-debug .trc-param font {
				background: rgba(0, 0, 0, .05);
				display: inline-block;
				padding: 2px;
				transition: all 0.3s;
			}

			.wp-debug .trc-param:hover font {
				background: rgba(0, 0, 0, 0.1);
				color: #000;
			}

			.wp-debug .trc-param:after {
				content: ',';
			}

			.wp-debug .trc-param:last-child:after {
				content: '';
			}

			.wp-debug .trc-line {
				float: right;
				background: rgba(0, 0, 0, 0.07);
				padding: 2px 2px;
				margin: -2px;
				font-size: 12px;
				line-height: 10px;
			}
		</style>
		<script>
			function toggleBlock(clsname) {
				var wrap = document.getElementsByClassName(clsname);

				for (var i = 0; i < wrap.length; i += 1) {
					var state = (wrap[i].style.display == 'none' ? 'block' : 'none');

					wrap[i].style.display = state;
				}
			}

			function _m(cell) {
				cell.parentElement.classList.toggle('mark');
			}

			function toggleDisplay(clsname, full_class, recursive) {
				var elements = false,
					plus = document.getElementById('plus' + clsname),
					plus_alt = document.getElementById('plusalt' + clsname),
					plus_state = (plus.style.display === 'none' ? 'inline' : 'none'),
					el_state = (plus_state === 'none' ? 'table-row' : 'none');

				if (recursive) {
					elements = document.querySelectorAll('[class^="' + full_class + '"]');
				} else {
					elements = document.getElementsByClassName(clsname);
				}

				plus.style.display = plus_state;
				plus_alt.style.display = plus_state;

				for (var i = 0; i < elements.length; i += 1) {
					var sub_plus = elements[i].getElementsByClassName('plus');
					var sub_plus_alt = elements[i].getElementsByClassName('plus-alt');

					if (sub_plus && sub_plus.length) {
						sub_plus[0].style.display = (recursive ? 'none' : 'inline');
					}
					if (sub_plus_alt && sub_plus_alt.length) {
						sub_plus_alt[0].style.display = (recursive ? 'none' : 'inline');
					}

					if (elements[i].className === full_class || recursive) {
						elements[i].style.display = el_state;
					} else {
						elements[i].style.display = 'none';
					}
				}
			}
		</script>
		<?php
	}

	/**
	 * Add some JS debugging code to the output.
	 *
	 * @since 2.5.0
	 */
	public function js_debug() {
		if ( ! EVR_DEBUG_JS ) {
			return;
		}
		if ( defined( 'ABSPATH' ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return;
			}
		}

		?>
		<script>
			// https://gist.github.com/stracker-phil/e5b3bbd5d5eb4ffb2acdcda90d8bd04f
			function globalSearch(startObject, needle, searchField, limit) {
				if (!startObject) {
					console.warn('globalSearch() usage:');
					console.info('globalSearch(scope, needle, field, limit)');
					console.info("\nParams:" +
						"\n- scope .. document, window, or a global variable name, like 'etCore'" +
						"\n- needle .. scalar value, RegExp object or callback function" +
						"\n- field .. either 'key', 'value', 'all'" +
						"\n- limit .. optional. Stop searching after number of matches"
					);
					console.info("\nSamples:" +
						"\nglobalSearch(document, 'sample', 'value', 10)" +
						"\nglobalSearch(window, '^et', 'key')" +
						"\nglobalSearch(window, /sub(word|term)/', 'all', 3)" +
						"\nglobalSearch('etCore', /portability/', 'all')" +
						"\nglobalSearch('etCore', item => 'function' === typeof item, 'all')"
					);
					console.info("\nNotes:" +
						"\nResults are stored in the global `gsResults` array:" +
						"\n`gsResults[1]` contains details about the first match."
					);
					return;
				}

				var startName = '';

				if ('string' === typeof startObject) {
					startName = startObject;
					try {
						startObject = eval(startObject);
					} catch (exception) {
						console.error(
							'The scope object "' + startName + '" was not found:',
							exception.message
						);
						startObject = {};
					}
				} else if (window === startObject) {
					startName = 'window';
				} else if (document === startObject) {
					startName = 'document';
				}

				var stack, fullSearch;

				if ('gsResults' === startName && window.gsResults && Array.isArray(gsResults)) {
					fullSearch = false;
					stack = [];
					for (var resI = 0; resI < gsResults.length; resI++) {
						if (gsResults[resI] && 'object' === typeof gsResults[resI]) {
							var resPath = gsResults[resI].pathOrig;
							var resObj;

							try {
								resObj = eval(resPath);
							} catch (exception) {
								resObj = null;
							}

							stack.push([resObj, resPath, resPath]);
						}
					}
				} else {
					fullSearch = true;
					stack = [[startObject, startName, startName]];
				}

				var searched = [];
				var found = 0;
				var count = 1;
				var isCallback = 'function' === typeof needle;
				var isRegex = 'string' === typeof needle && (-1
					!== needle.indexOf('*')
					|| '^'
					=== needle[0]
					|| '$'
					=== needle[needle.length - 1]);
				var startTime = (new Date()).getTime();

				window.gsResults = [];

				if (isRegex) {
					needle = new RegExp(needle);
				} else if ('object' === typeof needle && needle instanceof RegExp) {
					isRegex = true;
				}

				if (undefined === limit) {
					limit = -1;
				}

				if (undefined === searchField) {
					searchField = 'value';
				}

				if (-1 === ['value', 'key', 'all'].indexOf(searchField)) {
					console.error(
						'The "searchField" parameter must be either of [value|key|all]. Found:',
						searchField
					);
					return;
				}

				function isArray(test) {
					var type = Object.prototype.toString.call(test);
					return '[object Array]' === type || '[object NodeList]' === type;
				}

				function isElement(o) {
					var res;
					try {
						res = typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
							o
							&& typeof o
							=== "object"
							&& o.nodeType
							=== 1
							&& typeof o.nodeName
							=== "string";
					} catch ($ex) {
						res = false;
					}
					return res;
				}

				function isMatch(type, value) {
					if ('undefined' === typeof value || null === value) {
						return value === needle;
					}

					if (isCallback) {
						return needle(value, type);
					} else if (isRegex) {
						return needle.test(value.toString ? value.toString() : '');
					} else {
						return needle === value;
					}
				}

				function result(type, address, shortAddr, value) {
					var charLimit = 150;
					var msg = [];
					var displayRes = value.toString();
					var addressParts = shortAddr.split('.');
					var displayAddress = shortAddr;

					if (displayRes.length > charLimit) {
						displayRes = displayRes.substr(0, charLimit - 3) + '...';
					}
					if (displayAddress.length > charLimit) {
						var addrFront = '';
						var addrTail = '';
						for (var i = 0; i < addressParts.length / 2; i++) {
							if (addrTail.length) {
								addrTail = '.' + addrTail;
							}
							addrTail = addressParts[addressParts.length - i - 1] + addrTail;
							if (3 + addrTail.length + addrFront.length > charLimit) {
								break;
							}
							if (addrFront.length) {
								addrFront += '.';
							}
							addrFront += addressParts[i];
							if (3 + addrTail.length + addrFront.length > charLimit) {
								break;
							}
						}
						displayAddress = addrFront + '...' + addrTail;
					}

					found++;
					window.gsResults[found] = {
						match: type,
						value: value,
						name: addressParts[addressParts.length - 1],
						pathOrig: address,
						pathShort: shortAddr
					};

					msg.push(found + ". Match: \t" + type.toUpperCase());
					msg.push("   Type:    \t" + typeof value);
					msg.push("   Name:    \t" + addressParts[addressParts.length - 1]);
					msg.push("   Value:   \t" + displayRes);
					msg.push("   Address: \t" + displayAddress);
					msg.push("   Details: \tgsResults[" + found + ']');

					console.log(msg.join("\n"));
				}

				function skip(obj, key) {
					var traversing = [
						'firstChild',
						'previousSibling',
						'nextSibling',
						'lastChild',
						'previousElementSibling',
						'nextElementSibling',
						'firstEffect',
						'nextEffect',
						'lastEffect'
					];
					var scopeChange = [
						'ownerDocument'
					];
					var deprecatedDOM = [
						'webkitStorageInfo'
					];

					if (-1 !== traversing.indexOf(key)) {
						return true;
					}
					if (-1 !== scopeChange.indexOf(key)) {
						return true;
					}
					if (-1 !== deprecatedDOM.indexOf(key)) {
						return true;
					}

					var isInvalid = false;
					try {
						obj[key];
					} catch (ex) {
						isInvalid = true;
					}

					return isInvalid;
				}

				while (stack.length) {
					if (limit > 0 && found >= limit) {
						break;
					}

					var fromStack = stack.pop();
					var obj = fromStack[0];
					var address = fromStack[1];
					var display = fromStack[2];

					count++;
					if ('key' !== searchField && isMatch('value', obj)) {
						result('value', address, display, obj);
						if (limit > 0 && found >= limit) {
							break;
						}
					}

					if (obj && typeof obj == 'object' && -1 === searched.indexOf(obj)) {
						var objIsArray = isArray(obj);

						if (isElement(obj) && obj.id) {
							display = 'document.getElementById("' + obj.id + '")';
						}

						for (var i in obj) {
							if (obj.hasOwnProperty && !obj.hasOwnProperty(i)) {
								continue;
							}
							if (skip(obj, i)) {
								continue;
							}

							var subAddr = (objIsArray || !isNaN(i)) ? '[' + i + ']' : '.' + i;
							var addr = address + subAddr;
							var displayAddr = display + subAddr;

							if (fullSearch) {
								stack.push([obj[i], addr, displayAddr]);
							}
							count++;

							if ('value' !== searchField && isMatch('key', i)) {
								result('key', address, displayAddr, obj[i]);
								if (limit > 0 && found >= limit) {
									break;
								}
							}
						}
						searched.push(obj);
					}
				}

				searched = null;

				console.log("\n-----"
					+
					"\nAll Done!"
					+
					"\nSearched "
					+ count.toLocaleString()
					+ ' items'
					+
					"\nFound "
					+ found.toLocaleString()
					+ ' results'
					+
					"\nIn "
					+ (Math.round(((new Date()).getTime() - startTime) / 10) / 100)
					+ ' sec'
					+
					"\n-----\n"
				);
				return found;
			}
		</script>
		<?php
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public function get_trace_details( $item ) {
		if ( ! isset( $item['file'] ) ) {
			$item['file'] = false;
		}
		if ( ! isset( $item['line'] ) ) {
			$item['line'] = false;
		}
		if ( ! isset( $item['class'] ) ) {
			$item['class'] = false;
		}
		if ( ! isset( $item['type'] ) ) {
			$item['type'] = false;
		}
		if ( ! isset( $item['function'] ) ) {
			$item['function'] = false;
		}
		if ( ! isset( $item['args'] ) ) {
			$item['args'] = false;
		}

		return $item;
	}
}

debug_hooks( true, [
	'/save/',
	'/update/',
], true );

// This indicates, that debugging is active.
return true;
