<?

# Global flag to indicate whether the application
# is in "maintenance mode."
define( 'MAINTENANCE_MODE', 0);

define( 'APPLICATION_NAME', 'GITERARY' );   // Can use as bareword constant
$application_name = APPLICATION_NAME;       // Can use when referenced with GLOBAL


define( 'INSTANCE_NAME', 'GITERARY' );      // Can use as bareword constant
$instance_name = INSTANCE_NAME;             // Can use when referenced with GLOBAL

# Everything specific to this particular instance of Giterary
# (Everything that you need to configure this instance apart
# from every other instance that may be running with the same
# codebase)
require_once( dirname( __FILE__ ) . "/config/base.php" );

# Modifiable "conventions," like ".dir" directory suffixes, or
# allowable characters in Wikilinks
require_once( dirname( __FILE__ ) . "/config/conventions.php" );

# Dictionary configuration
require_once( dirname( __FILE__ ) . "/config/dict.php" );

# Configuration as regards HTML rendering
require_once( dirname( __FILE__ ) . "/config/html.php" );

# Configuration surrounding the theming/layout mechanism
require_once( dirname( __FILE__ ) . "/config/themes.php" );

# Timimg pieces...
require_once( dirname( __FILE__ ) . "/config/time.php" );

# Permissions pieces...
require_once( dirname( __FILE__ ) . "/config/permissions.php" );

# Performance
require_once( dirname( __FILE__ ) . "/config/perf.php" );

# Page associations
require_once( dirname( __FILE__ ) . "/config/assoc.php" );

# TAR Export
require_once( dirname( __FILE__ ) . "/config/tar.php" );

# Caching configuration
require_once( dirname( __FILE__ ) . "/config/cache.php" );

# ShareJS integration
require_once( dirname( __FILE__ ) . "/config/sharejs.php" );

# AnnotatorJS integration
require_once( dirname( __FILE__ ) . "/config/annotatorjs.php" );

# Page aliases
require_once( dirname( __FILE__ ) . "/config/alias.php" );

# Logging
require_once( dirname( __FILE__ ) . "/config/logging.php" );




?>
