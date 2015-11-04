<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');

$action =   trim( stripslashes( $_GET['action'] ) );

$ret = array();

if( is_logged_in() ) {

    switch( $action ) {
        case "all":
            $all_tags = git_all_tags();
    
            $ret = array_map(
                function( $a) {
                    return preg_replace( "@^~@", "", $a );
                },
                array_keys( $all_tags )
            );
    
        default:
            break;
    
    }
}


echo json_encode( $ret );

?>
