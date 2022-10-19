<?php

// Example usage of the debug module.
$debug_file = dirname( __DIR__ ) . '/debug.php';
require_once $debug_file;

debug_header( 'Use this to debug redirects or similar!' );
?>
<html lang="en">
<head>
	<title>Debug module</title>
	<link rel="stylesheet" href="styles.css"/>
</head>
<body>
<div class="content">
	<h1>Debug module examples and documentation</h1>
	<a href="https://github.com/everklick/wp-debug-module" target="_blank" class="cta">DOWNLOAD</a>

	<hr>
	<h4>Contents</h4>
	<ul class="toc">
		<li class="group-end">
			<a href="#isage">Usage</a>
		</li>
		<li>
			<a href="#debug">
				<small>HTML</small>
				debug()
			</a>
		</li>
		<li>
			<a href="#debug_and_die">
				<small>HTML</small>
				debug_and_die()
			</a>
		</li>
		<li>
			<a href="#debug_marker">
				<small>HTML</small>
				debug_marker()
			</a>
		</li>
		<li>
			<a href="#debug_constants">
				<small>HTML</small>
				debug_constants()
				<i class="always">*</i>
			</a>
		</li>
		<li>
			<a href="#debug_log">
				<small>Logfile</small>
				debug_log()
				<i class="always">*</i>
			</a>
		</li>
		<li>
			<a href="#debug_log_trace">
				<small>Logfile</small>
				debug_log_trace()
				<i class="always">*</i>
			</a>
		</li>
		<li>
			<a href="#debug_header">
				<small>HTTP Header</small>
				debug_header()</a>
		</li>
		<li>
			<a href="#debug_slack">
				<small>Slack Integration</small>
				debug_slack()
				<i class="always">*</i>
			</a>
		</li>
		<li class="group-end">
			<a href="#debug_format">
				<small>Config</small>
				debug_format()
				<i class="always">*</i>
			</a>
		</li>
		<li class="group-end">
			<a href="#settings">Advanced configuration</a>
		</li>
		<li class="group-end">
			<a href="#compatibility">Compatibility</a>
		</li>
		<li class="info">
			<i class="always">*</i> ...
			This function is also available when <kbd>EVR_DEBUG</kbd> is disabled.
		</li>
	</ul>

	<hr id="usage">
	<h4>Usage</h4>
	<code>&lt;?php<br>include 'debug.php';</code>
	<p>
		Simply include the debug.php file somewhere in the main index.php or other place (as early
		as possible).<br>
		This is the only file that is required.
	</p>

	<hr id="debug">
	<h4>HTML Output</h4>

	<code><b>debug</b>( 'Debug Example', $_SERVER );</code>
	<p>
		Dump all params to the screen. Also includes a full stack trace and HTTP request
		parameters.
	</p>
	<?php debug( 'Debug Example', $_SERVER ); ?>

	<h4>Some more examples</h4>
	<code>
		$some_obj = new DemoClass();<br>
		<b>debug</b>( $some_obj );
	</code>
	<ul>
		<li>Even private and protected members of objects are dumped.</li>
		<li>The "id" property of any object is always highlighted.</li>
		<li>Recursion is detected (e.g. "<tt>$some_obj->parent = $some_obj</tt>")</li>
		<li>Timestamps are displayed in a readable way.</li>
		<li>Object properties and Arrays are always sorted by the array-key/name.</li>
	</ul>
	<?php

	class DemoClass {
		protected $alias = 'Protected properties have a red label';
		private $internal = 'Private properties are labeled [PRIVATE]';
		public $id = 1234;
		public $alias_public = 'This is a public property. The label is black';
		protected $hash = [ 1, 2, 3 ];
		public $parent = null;
		public $time = null;

		public function init() {
		}
	}

	$some_obj         = new DemoClass;
	$some_obj->parent = $some_obj;
	$some_obj->time   = time();
	debug( $some_obj );
	?>


	<hr id="debug_and_die">
	<code><b>debug_and_die</b>( 'Debug Example', $_SERVER );</code>
	<p>Additionally ends the request after the debug output.</p>

	<hr id="debug_marker">
	<code><b>debug_marker</b>();</code>
	<p>
		Displays a visually conspicuous random hash value. Use it to quickly spot new ajax response
		data or test if caching is active.
	</p>
	<?php debug_marker(); ?>

	<hr id="debug_constants">
	<code><b>debug_constants</b>();<br>debug_constants( 'INDEX' ); // Filter by constant name</code>
	<p>
		Outputs a list of all PHP constants. Optionally, the list is filtered by constant name. The example below shows all constants that contain "INDEX" in their name:
	</p>
	<?php debug_constants( 'INDEX' ); ?>

	<hr>
	<code><b>debug_marker</b>( 'Marker Example' );</code>
	<p>
		Creates a unique color based on the input parameter. Use it to visually detect changes in
		complex object.
	</p>
	<?php debug_marker( 'Marker Example' ); ?>

	<code><b>debug_marker</b>( $_SERVER );</code>
	<?php debug_marker( $_SERVER ); ?>

	<hr>
	<code>$infos = <b>debug_get_marker</b>( 'Marker Example' );</code>
	<p>Does not output the marker but returns the HTML and color codes as object.</p>
	<?php debug( debug_get_marker( 'Marker Example' ) ); ?>


	<hr id="debug_log">
	<h4>Logfile</h4>

	<code><b>debug_log</b>( 'Log this!', $_SERVER );</code>
	<p>Write the debug output to the log file "<b>debug-info.log</b>".</p>

	<hr>
	<code>define( 'EVR_LOG_DIR', '/logs' );<br>
		define( 'EVR_LOG_FILE', 'debug.log' );<br>
		<b>debug_log</b>( 'Log this!', $_SERVER );</code>
	<p>Write the debug output to the defined log file "<b>/logs/debug.log</b>".</p>

	<hr id="debug_log_trace">
	<code><b>debug_log_trace</b>();</code>
	<p>Write a full stack trace to the log file.</p>

	<hr id="debug_header">
	<h4>HTTP Header</h4>

	<code><b>debug_header</b>( 'Use this to debug redirects or similar!' );</code>
	<p>
		Write debug details to the HTTP response headers. Check the response headers of this page
		for
		an example
	</p>

	<p>
		<small>Example:</small><br>
		<img src="debug-header-01.png" class="block-img"/>
	</p>

	<hr id="debug_slack">
	<h4>Slack integration</h4>

	<p>
		Important: To use <b>debug_slack()</b> you need to set up, or review your Slack "Incoming
		WebHooks"
	</p>

	<pre>define( 'EVR_SLACK_HOOK', 'T00000000/B11111111/q22222222222222222222222' );
<b>debug_slack</b>(
  array(
    'text' => 'Onboarding Prozess gestartet.',
    'fields' => array(
      'User' => 2,
      'Time' => time(),
    )
  )
);</pre>
	<br>
	Send a debug message to slack! Note that the second param is optional. By default the message is
	sent to the channel, that is defined by the slack webhook.

	<p>
		<small>Result:</small><br>
		<img src="slack-debug-01.png" class="block-img"/>
	</p>

	<pre>define( 'EVR_SLACK_HOOK', 'T00000000/B11111111/q22222222222222222222222' );
<b>debug_slack</b>(
  array(
    'pretext' => ':warning: Fehler im Shop &lt;!here&gt;.',
    'text' => 'Uncaught exception "Test"',
    'author' => 'Customer Name',
    'color' => '#F00',
    'title' => 'PHP Error',
    'fields' => array(
      'Source' => array(
        'File' => '/home/httpd/vhosts/example/httpdocs/test.php',
        'Line' => 123,
      ),
      'URL' => '&lt;http://example.com/test.php|Open Website&gt;',
      'Logged in User' => 'Philipp',
    ),
    'field_size' => array( 'Source' => 'big' ),
    'image_url' => 'http://www.tutorialchip.com/wp-content/uploads/2011/04/Powerful-Errors-PHP-Ajax-Error-Template-520x314.jpg',
    'ts' => 12345678,
  ),
  '@user1,@user2,#general-logs'
);</pre>
	<br>
	Example with more options and details.

	<p>
		<small>Result:</small><br>
		<img src="slack-debug-02.png" class="block-img"/>
	</p>

	<hr id="debug_format">
	<h4>Config</h4>

	<pre><b>debug_format</b>( 'text' );
debug_dump( ... );</pre>
	<br>
	<p>The function call debug_format() will not generate any ouput. It only defines the output
		format of the next debug call. It effects the following functions:</p>

	<ul>
		<li><code>debug()</code>
		<li><code>debug_and_die()</code>
	</ul>

	<p>Following options are available:</p>
	<pre>debug_format( <b>'text'</b> );
debug_format( <b>'html'</b> );</pre>

	<hr>

	<a href="https://github.com/everklick/wp-debug-module" class="cta">DOWNLOAD</a>

	<h2 id="settings">Debug settings</h2>
	<hr>
	<code>define( '<b>EVR_DEBUG</b>', false );</code>
	<p>
		Disable the debugging module, but define empty debug functions (so your debug() calls will
		not cause an fatal error).
	</p>


	<hr>
	<code>define( '<b>EVR_DEBUG</b>', false );<br>
		define( '<b>EVR_DEBUG_WITH_COOKIE</b>', 'cookie-name' );</code>
	<p>Override the EVR_DEBUG value and enable debugging when the specified cookie is present.</p>

	<hr>
	<code>define( '<b>EVR_DEBUG</b>', false );<br>
		define( '<b>EVR_DEBUG_WITH_IP</b>', '::1, 127.0.0.1, <?php echo $_SERVER['REMOTE_ADDR'] ?>'
		);</code>
	<p>
		Override the EVR_DEBUG and EVR_DEBUG_WITH_COOKIE value and enable debugging for specific
		IPv4
		or IPv6 addresses.
	</p>

	<hr>
	<code>define( '<b>EVR_DEBUG_TRACE</b>', false );</code>
	<p>Disable the stack trace and HTTP request details in the debug() output.</p>

	<hr>
	<h4>Logfile</h4>
	<code>define( '<b>EVR_LOG_DIR</b>', __DIR__ );</code>
	<p>
		Define a custom output dir for the debug_log() and debug_log_trace() calls. Default is the
		directory where the debug.php file is located.
	</p>

	<hr>
	<code>define( '<b>EVR_LOG_FILE</b>', 'debug-info.log' );</code>
	<p>
		Define a custom output file for the debug_log() and debug_log_trace() calls. Default is
		"debug-info.log".
	</p>

	<hr>
	<h4>Slack integration</h4>
	<code>
		define( '<b>EVR_SLACK_HOOK</b>', 'T00000000/B11111111/q22222222222222222222222' );
	</code>
	<p>
		<b>Required to use the debug_slack() integration</b>. The Hook can be found in your Slack
		"Incoming WebHooks" configuriation, it's the last part of the Webhook URL.
	</p>

	<hr>
	<code>define( '<b>EVR_SLACK_NAME</b>', 'Debug Webhook' );</code>
	<p>
		Optionally override the default webhook name. Useful if you want to use different webhook
		names for each client/project while using the same EVR_SLACK_HOOK for all of them.
	</p>

	<hr>
	<code>define( '<b>EVR_SLACK_ICON</b>', ':wink:' );</code>
	<p>
		Optinally override the default webhook icon; it can be set to any emoji icon.
	</p>

	<hr>
	<code>define( '<b>EVR_SLACK_CHANNEL</b>', '#general,@username' );</code>
	<p>
		Optionally define project-wide default channels. All Messages will be sent to those channels
		<em>in addition</em> to the channels defined in second param of <b>debug_slack()</b>
	</p>

	<hr>

	<a href="https://github.com/everklick/wp-debug-module" class="cta">DOWNLOAD</a>

	<hr>
	<h4 id="compatibility">Compatibility</h4>
	<p>Minimum required PHP Version: 5.4</p>
	<ul>
		<dl>
			<dt><b>WordPress</b></dt>
			<dd>Perfect support; originally built for WordPress
			</dd>
		</dl>
		<dl>
			<dt><b>Drupal</b></dt>
			<dd>I load it in the template.php file of the theme</dd>
		</dl>
		<dl>
			<dt><b>OXID eSales</b></dt>
			<dd>Oxid defines its own debug() function. I solve it by renaming the oxid function to
				oxdebug() in file "core/oxfunctions.php"
			</dd>
		</dl>
		<dl>
			<dt><b>ClanCats</b></dt>
			<dd>Tested and working</dd>
		</dl>
	</ul>

</div>
</body>
