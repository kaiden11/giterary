<?
require_once( dirname( __FILE__ ) . '/permissions.lib.php' );

$registered_auth_calls = array();

# All access to loggied in users
# $must_be_logged_in = new MustBeLoggedIn();
# register_auth_call( $work_areas, "can" );


###############################################################################

## Work Areas / Beta Reader configuration
$beta_reader_grants = array(
    'beta','beta.dir'
);


$work_areas = new WorkArea(
    array(
        'username'  =>  true,                       // All privileges
        /*
        'beta_reader' =>  array_merge(              // Beta reader, limited access to work area
            $beta_reader_grants, 
            array( 'keldridge', 'keldridge.dir' ) 
        )
        */
    )
);

register_auth_call( $work_areas, "can" );


###############################################################################

?>
