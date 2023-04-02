<?

global $application_name;
global $instance_name;

define('BASE_URL',"http://default.giterary.com/");

//System paths for the codebase
define('SRC_DIR',"/var/lib/$application_name/instance/$instance_name/src/");

# Path to Git binary
define('GIT_PATH',"/usr/bin/git");

# Path to diff3 binary
define('DIFF3_PATH',"/usr/bin/diff3");

define('GIT_REPO_DIR',"/var/lib/$application_name/repo/$instance_name/");
define('TMP_DIR',"/var/lib/$application_name/temp/$instance_name/");
define('DRAFT_DIR',"/var/lib/$application_name/draft/$instance_name/");

define('DEFAULT_AUTHOR', 'Anonymous User <anonymous@giterary.com>' );
define('DEFAULT_FILE', 'Home' );

//Site name
define('SITE_NAME', "default");
define('SHORT_NAME', "default");

//Critical error message
define('CRITICAL_ERROR_MSG', "Something went awry:");

//Stylesheet
define('STYLESHEET', "simpler.css");
define('CSS_DIR', "css/");

//A quick and dirty way to get spaces between things.
define('SPACER', '<div class="spacer"></div>');

//Cookie values

define('COOKIE_DOMAIN', 'default.giterary.com');
define('COOKIE_PATH', '/');
define('COOKIE_EXPR_TIME', 86400);
define('SESS_NAME', "$instance_name-GITERARY-SESSION");
define('SESS_PATH', "/var/lib/$application_name/session/$instance_name/");

$cookie_vars = array("usr");

# Location of cache directory if using filesystem-based caching. If using
# memcached, see
define( 'CACHE_DIR',      "/var/lib/$application_name/cache/$instance_name/" );
define( 'CACHE_ENABLE',   1 );

# File database to maintain and display statuses for users
define( 'STATUS_ENABLE',    1           );
define( 'STATUS_DB',        'status.db' );

?>
