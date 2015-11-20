<?
require_once( dirname( __FILE__ ) . '/auth.lib.php' );

$registered_login_calls = array();
$registered_userlist_calls = array();

# register_login_call( new StaticUserList(), "validate_login" );

GLOBAL $application_name;
GLOBAL $instance_name;

$pw_authenticator = new PasswordFile( 
    "/var/lib/$application_name/auth/$instance_name/passfile.csv"
);

$ad_authenticator = new LDAPAuthenticator( 
    'ldap://ceav001.chugachelectric.com',
    array(
        'domain'        =>  'CHUGACHELECTRIC',
        'username_attr' =>  'samaccountname',
        'base_dn'       =>  'OU=CEA,DC=chugachelectric,DC=com'
    )
);

register_login_call( 
    $ad_authenticator,
    "validate_login" 
);

register_login_call( 
    $pw_authenticator,
    "validate_login" 
);


register_userlist_call( 
    $authenticator,
    "userlist" 
);



?>
