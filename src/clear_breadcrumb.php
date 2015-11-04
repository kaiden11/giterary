<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');

$ret_message = '';

$redirect = set_or( $_GET['redirect'], null );


if( !is_logged_in() ) {

    $ret_message = 'You are not logged in.';
} else {

    $_SESSION['breadcrumb'] = null;
    $ret_message = 'Breadcrumb cleared';
}

if( isset( $redirect ) ) {
    header( 'Location:' . $redirect );
} else {
    echo layout(
        array(
            'header'            => gen_header( "Clearing breadcrumb" ), 
            'content'           => $ret_message
        )
    );
}

?>
