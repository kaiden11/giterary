<?
require_once( dirname( __FILE__ ) . '/auth.lib.php' );

$registered_login_calls = array();
$registered_userlist_calls = array();

# register_login_call( new StaticUserList(), "validate_login" );

GLOBAL $application_name;
GLOBAL $instance_name;

## Authentication Modules 
$pw_authenticator = new PasswordFile( 
    "/var/lib/$application_name/auth/$instance_name/passfile.csv"
);

/*
$ad_authenticator = new LDAPAuthenticator( 
    'ldap://ldap.yourdomain.com',
    array(
        'domain'        =>  'WINDOWSDOMAIN',
        'username_attr' =>  'samaccountname',
        'base_dn'       =>  'OU=department,DC=yourdomain,DC=com'
    )
);
*/


## Module Registration for login

/*
register_login_call( 
    $ad_authenticator,
    "validate_login" 
);
*/

register_login_call( 
    $pw_authenticator,
    "validate_login" 
);


## Module Registration for userlist

register_userlist_call( 
    $authenticator,
    "userlist" 
);



?>
