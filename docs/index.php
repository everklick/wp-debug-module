<?php

// Example usage of the debug module.
$debug_file = __DIR__ . '../debug.php';
if ( file_exists( $debug_file ) ) { require_once $debug_file; }

debug_header( 'Use this to debug redirects or similar!' );
?>
<html>
<head>
<title>Debug module</title>
<style>
html,
body {
	font-family: 'Helvetica Neue','Open Sans','Arial';
}

pre,
code {
	background: rgba(0,0,0,0.06);
	padding: 3px 6px;
	border-radius: 3px;
	display: inline-block;
	margin: 0 0 6px;
	max-width: 100%;
	overflow: auto;
	box-shadow: 2px 0 0 rgba(0,0,0,0.1) inset;
	padding-left: 10px;
}
li code {
	margin: 0;
	display: inline;
}
li {
	margin: 5px 0;
}
.content {
	max-width: 960px;
	margin: 30px auto 80px;
}
hr {
	margin: 40px auto;
	border: 0;
	width: 80%;
	border-bottom: 1px solid #E0E0E0;
}
h1,h2,h3 {
	text-align: center;
}
.cta {
	display: block;
	font-size: 18px;
	line-height: 36px;
	background: #0052CC;
	color: #FFF !important;
	text-decoration: none;
	font-weight: bold;
	border-radius: 4px;
	width: 180px;
	margin: 20px auto;
	text-align: center;
	box-shadow: 0 1px 1px rgba(255,255,255,0.3) inset,0 4px 20px -2px rgba(0,0,0,0.5);
	text-shadow: 0 -1px 0 rgba(0,0,0,0.5);
}
.cta:hover,
.cta:focus {
	background: #0065FF;
	color: #FFF;
	text-decoration: none;
}
a,
a:visited {
	text-decoration: none;
	color: #007AB8;
}
a:focus,
a:hover,
a:active {
	text-decoration: underline;
}
.block-img {
	border-radius: .5rem;
	border: 1px solid #DDD;
	box-shadow: 0 1px 5px rgba(0,0,0,0.12);
	max-width: 100%;
	height: auto;
}
.toc {
	list-style: none;
}
.toc .group-end {
	margin-bottom: 10px;
}
.toc li {
	clear: both;
}
.toc li a {
	vertical-align: bottom;
}
.toc li.info {
	font-size: 0.9em;
}
.toc li small {
	display: inline-block;
	font-size: 0.8em;
	padding: 1px 5px;
	margin: 1px 4px 1px 0;
	background: rgba(0,0,0,0.08);
	border-radius: 3px;
}
.toc li .always {
	display: inline-block;
	margin: 0 10px;
	color: #A00;
	font-weight: bold;
	font-style: normal;
}
.toc li .always:after {
	content: ' ALWAYS';
	color: #555;
	font-size: 10px;
	font-weight: normal;
	vertical-align: super;
}
</style>
</head>
<body>
<div class="content">
<h1>Debug module examples and documentation</h1>
<center><small>- v<?php echo Evr_Debug::VERSION; ?> -</small></center>
<a href="https://bitbucket.org/everklick/debug-module/src" class="cta">DOWNLOAD</a>

<hr>
<h4>Contents</h4>
<ul class="toc">
	<li class="group-end"><a href="#isage">Usage</a></li>
	<li><a href="#debug"><small>HTML</small>debug()</a></li>
	<li><a href="#debug_and_die"><small>HTML</small>debug_and_die()</a></li>
	<li><a href="#debug_marker"><small>HTML</small>debug_marker()</a></li>
	<li><a href="#debug_log"><small>Logfile</small>debug_log()<i class="always">*</i></a></li>
	<li><a href="#debug_log_trace"><small>Logfile</small>debug_log_trace()<i class="always">*</i></a></li>
	<li><a href="#debug_header"><small>HTTP Header</small>debug_header()</a></li>
	<li><a href="#debug_slack"><small>Slack Integration</small>debug_slack()<i class="always">*</i></a></li>
	<li class="group-end"><a href="#debug_format"><small>Config</small>debug_format()<i class="always">*</i></a></li>
	<li class="group-end"><a href="#settings">Advanced configuration</a></li>
	<li class="group-end"><a href="#compatibility">Compatibility</a></li>
	<li class="info"><i class="always">*</i> ... This function is also available when <code>EVR_DEBUG</code> is disabled.</li>
</ul>

<hr id="usage">
<h4>Usage</h4>
<code>include( 'debug.php' );</code><br>
Simply include the debug.php file somewhere in the main index.php or other place (as early as possible).
This is the only file that is required.

<hr id="debug">
<h4>HTML Output</h4>

<code><b>debug</b>( 'Debug Example', $_SERVER );</code><br>
Dump all params to the screen. Also includes a full stack trace and HTTP request parameters.
<?php debug( 'Debug Example', $_SERVER ); ?>

<h4>Some more examples</h4>
<code>$some_obj = new DemoClass();<br>
<b>debug</b>( $some_obj );</code><br>
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
	protected $hash = [ 1,2,3 ];
	public $parent = null;
	public $time = null;
	public function init() {}
}
$some_obj = new DemoClass();
$some_obj->parent = $some_obj;
$some_obj->time = time();
debug( $some_obj );
?>


<hr id="debug_and_die">
<code><b>debug_and_die</b>( 'Debug Example', $_SERVER );</code><br>
Additionally ends the request after the debug output.

<hr id="debug_marker">
<code><b>debug_marker</b>();</code><br>
Displays a visually conspicuous random hash value. Use it to quickly spot new ajax response data or test if caching is active.
<?php debug_marker(); ?>

<hr>
<code><b>debug_marker</b>( 'Marker Example' );</code><br>
Creates a unique color based on the input parameter. Use it to visually detect changes in complex object.
<?php debug_marker( 'Marker Example' ); ?>

<code><b>debug_marker</b>( $_SERVER );</code><br>
<?php debug_marker( $_SERVER ); ?>

<hr>
<code>$infos = <b>debug_get_marker</b>( 'Marker Example' );</code><br>
Does not output the marker but returns the HTML and color codes as object.
<?php debug( debug_get_marker( 'Marker Example' ) ); ?>


<hr id="debug_log">
<h4>Logfile</h4>

<code><b>debug_log</b>( 'Log this!', $_SERVER );</code><br>
Write the debug output to the log file "<b>debug-info.log</b>".

<hr>
<code>define( 'EVR_LOG_DIR', '/logs' );<br>
define( 'EVR_LOG_FILE', 'debug.log' );<br>
<b>debug_log</b>( 'Log this!', $_SERVER );</code><br>
Write the debug output to the defined log file "<b>/logs/debug.log</b>".

<hr id="debug_log_trace">
<code><b>debug_log_trace</b>();</code><br>
Write a full stack trace to the log file.

<hr id="debug_header">
<h4>HTTP Header</h4>

<code><b>debug_header</b>( 'Use this to debug redirects or similar!' );</code><br>
Write debug details to the HTTP response headers. Check the response headers of this page for an example

<p>
	<small>Example:</small><br>
	<img src="debug-header-01.png" class="block-img" />
</p>

<hr id="debug_slack">
<h4>Slack integration</h4>

<p>
	Important: To use <b>debug_slack()</b> you need to set up or review your <a href="https://mirabit.slack.com/apps/manage/custom-integrations" target="_blank">Slack "Incoming WebHooks"</a>
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
);</pre><br>
Send a debug message to slack! Note that the second param is optional. By default the message is sent to the channel, that is defined by the slack webhook.

<p>
	<small>Result:</small><br>
	<img src="slack-debug-01.png" class="block-img" />
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
);</pre><br>
Example with more options and details.

<p>
	<small>Result:</small><br>
	<img src="slack-debug-02.png" class="block-img" />
</p>

<hr id="debug_format">
<h4>Config</h4>

<pre><b>debug_format</b>( 'text' );
debug_dump( ... );</pre><br>
<p>The function call debug_format() will not generate any ouput. It only defines the output format of the next debug call. It effects the following functions:</p>

<ul>
<li><code>debug()</code>
<li><code>debug_and_die()</code>
</ul>

<p>Following options are available:</p>
<pre>debug_format( <b>'text'</b> );
debug_format( <b>'html'</b> );</pre>

<hr>

<a href="https://bitbucket.org/everklick/debug-module/src" class="cta">DOWNLOAD</a>

<h2 id="settings">Debug settings</h2>
<hr>
<code>define( '<b>EVR_DEBUG</b>', false );</code><br>
Disable the debugging module, but define empty debug functions (so your debug() calls will not cause an fatal error).

<hr>
<code>define( '<b>EVR_DEBUG</b>', false );<br>
define( '<b>EVR_DEBUG_WITH_COOKIE</b>', 'cookie-name' );</code><br>
Override the EVR_DEBUG value and enable debugging when the specified cookie is present.

<hr>
<code>define( '<b>EVR_DEBUG</b>', false );<br>
define( '<b>EVR_DEBUG_WITH_IP</b>', '::1, 127.0.0.1, <?php echo $_SERVER['REMOTE_ADDR']?>' );</code><br>
Override the EVR_DEBUG and EVR_DEBUG_WITH_COOKIE value and enable debugging for specific IPv4 or IPv6  addresses.

<hr>
<code>define( '<b>EVR_DEBUG_TRACE</b>', false );</code><br>
Disable the stack trace and HTTP request details in the debug() output.

<hr>
<h4>Logfile</h4>
<code>define( '<b>EVR_LOG_DIR</b>', __DIR__ );</code><br>
Define a custom output dir for the debug_log() and debug_log_trace() calls. Default is the directory where the debug.php file is located.

<hr>
<code>define( '<b>EVR_LOG_FILE</b>', 'debug-info.log' );</code><br>
Define a custom output file for the debug_log() and debug_log_trace() calls. Default is "debug-info.log".

<hr>
<h4>Slack integration</h4>
<code>define( '<b>EVR_SLACK_HOOK</b>', 'T00000000/B11111111/q22222222222222222222222' );</code><br>
<b>Required to use the debug_slack() integration</b>. The Hook can be found in your <a href="https://mirabit.slack.com/apps/manage/custom-integrations" target="_blank">Slack "Incoming WebHooks" configuriation</a>, it's the last part of the Webhook URL.

<hr>
<code>define( '<b>EVR_SLACK_NAME</b>', 'Debug Webhook' );</code><br>
Optionally override the default webhook name. Useful if you want to use different webhook names for each client/project while using the same EVR_SLACK_HOOK for all of them.

<hr>
<code>define( '<b>EVR_SLACK_ICON</b>', ':wink:' );</code><br>
Optinally override the default webhook icon; it can be set to any emoji icon.

<hr>
<code>define( '<b>EVR_SLACK_CHANNEL</b>', '#general,@username' );</code>
Optionally define project-wide default channels. All Messages will be sent to those channels <em>in addition</em> to the channels defined in second param of <b>debug_slack()</b>
<hr>

<a href="https://bitbucket.org/everklick/debug-module/src" class="cta">DOWNLOAD</a>


<hr>
<h4 id="compatibility">Compatibility</h4>
<p>Minimum required PHP Version: 5.4</p>
<ul>
	<dl>
		<dt><b>WordPress</b></dt>
		<dd>Perfect support; originally built for WordPress (simply save the debug.php file to the /wp-contents/mu-plugins folder, no other change needed)</dd>
	</dl>
	<dl>
		<dt><b>Drupal</b></dt>
		<dd>I load it in the template.php file of the theme</dd>
	</dl>
	<dl>
		<dt><b>OXID eSales</b></dt>
		<dd>Oxid defines its own debug() function. I solve it by renaming the oxid function to oxdebug() in file "core/oxfunctions.php"</dd>
	</dl>
	<dl>
		<dt><b>ClanCats</b></dt>
		<dd>Tested and working</dd>
	</dl>
	<dl>
		<dt><b>mira CMS</b></dt>
		<dd>Tested and working</dd>
	</dl>
</ul>

</div>
</body>
