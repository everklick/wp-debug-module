# Debug Module

Version 2.5.2

A simple and lightweight debug module that can be used on any PHP project.

## Installation

### Option 1:

* Save the `debug.php` file into the `mu-plugins` folder

### Option 2:

* Save the `debug.php` file in the WordPress root directory
* Include it at the beginning of your `wp-config.php` file

Example:
```php
<?php
# Beginning of your wp-config.php
include_once 'debug.php';

# Rest of your wp-config code ...
```

-----
## Live Demo

1. Clone this repo to your web server root folder.
2. Open the file `https://localhost/docs` to see some more samples and real output.
 
-----

## Usage

### Different debugging methods

**On screen:**

 * `debug( $val1, $val2, ... )`
 * `debug_and_die( $val1, $val2, ... )`
 * `debug_marker( $val )`
 * `debug_show()`
 * `debug_hooks()`

**As return value:**

 * `debug_get_marker( $val )`

**To logfile:**

 * `debug_log( $val1, $val2, ... )`
 * `debug_log_trace()`

**In HTTP response headers:**

 * `debug_header( $messages )`

**Send to Slack:**

 * `log_slack( $message, $channel, $trace_lines, $is_private )`
   `debug_slack()` is an alias for `log_slack()`

-----

### Load the debugging module

```php
require 'debug.php';

// Check if debugging is active via the return value of the include:
$is_active = require 'debug.php';
```

### Flags to customize the behavior

```php
// Disable this module.
define( 'EVR_DEBUG', false );
```

```php
// Enable debugging, when a cookie with name 'debugging' is found.
// This overrules EVR_DEBUG.
define( 'EVR_DEBUG_WITH_COOKIE', 'debugging' );
```

```php
// Enable debugging for the specified IP address.
// This overrules EVR_DEBUG and EVR_DEBUG_WITH_COOKIE.
define( 'EVR_DEBUG_WITH_IP', 'debugging' );
```

```php
// Do not append back-trace to debug(), debug_and_die() and debug_header()
define( 'EVR_DEBUG_TRACE', false );
```

```php
// Do not sort array/object keys alphabetically but show the original order.
define( 'EVR_DEBUG_SORT', false );
```

```php
// Highlight fields with the key "pkey" or "order_id" in array/object dumps.
define( 'EVR_DEBUG_MARK_FIELDS', 'pkey,order_id' );
```

```php
// Set the hook for the log_slack() function
define( 'EVR_SLACK_HOOK', '123456789' ); // REQUIRED for log_slack()
define( 'EVR_SLACK_NAME', 'Debug Bot' );
define( 'EVR_SLACK_ICON', ':wink:' );
define( 'EVR_SLACK_CHANNEL', '#general,@username' );
```

```php
// Customize the output dir of the logfile.
// Default dir is same dir as this file is in.
define( 'EVR_LOG_DIR', '/etc/log' );
```

```php
// Customize the filename of the logfile (only the file, not whole path!)
// Default name is: "debug-info.log"
define( 'EVR_LOG_FILE', 'my-logfile.txt' );
```

```php
// Disable output of JS debugging functions.
define( 'EVR_DEBUG_JS', false );
```

### Special WordPress debugging features

All WordPress redirects include a back-trace in the HTTP response headers.

```php
// Instead of redirecting display a back-trace and the target URL on the
// screen.
define( 'EVR_STOP_REDIRECT', true );
```

```php
// WordPress only: No Debug output in admin-ajax.php responses.
define( 'EVR_AJAX_DEBUG', false );
```

```php
// In WordPress: Enable the debug module without enabling WP_DEBUG.
define( 'EVR_DEBUG', true );
```

```php
// The "No-Conflict" mode allows you to modify the `debug` function name:
define( 'EVR_NOCONFLICT_DEBUG', '_debug' ); // debug() is now _debug()
define( 'EVR_NOCONFLICT_DEBUG', '§' ); // debug() is now §()  .. U+00A7
```

-----

## Screenshots

*Log to HTTP response headers*\
![](docs/debug-header-01.png)

*Log to Slack*\
![](docs/slack-debug-01.png)

*Log to Slack*\
![](docs/slack-debug-02.png)
