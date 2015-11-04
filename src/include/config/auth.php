<?
require_once( dirname( __FILE__ ) . '/auth.lib.php' );

$registered_login_calls = array();
$registered_userlist_calls = array();

# register_login_call( new StaticUserList(), "validate_login" );

GLOBAL $instance_name;

$authenticator = new PasswordFile( 
    "/var/lib/giterary/auth/$instance_name/passfile.csv"
);


register_login_call( 
    $authenticator,
    "validate_login" 
);

register_userlist_call( 
    $authenticator,
    "userlist" 
);



?>
