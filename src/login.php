<?
require_once( dirname( __FILE__ ) . "/include/header.php");
require_once( dirname( __FILE__ ) . "/include/footer.php");
require_once( dirname( __FILE__ ) . "/include/auth.php");
require_once( dirname( __FILE__ ) . "/include/util.php");

context_log( 'Login Attempt', $_POST['uname'] );

if (!isset($_POST['uname']) || !isset($_POST['pass'])) {
    $error .= "User/password not submitted.";
} else {
    if (($_POST['uname'] == "") || ($_POST['pass'] == "")) {
    
    	$error .= "User/password field left blank.";
    
    } else {

        $usr = validate_login($_POST['uname'], $_POST['pass']);
        
        if (!is_array($usr)) {
            $error .= "Password validation failed.";
        } else {
       
            establish_session( $usr );
            //session_register('usr'); 
            //$_SESSION['usr'] = $usr; 
            //clear_cache( 'count_online' );

            $redirect_to = file_or( $_POST['redirect_to'], false );

            if( $redirect_to !== false ) {
                header( "Location: index.php?file=" . urlencode( $redirect_to ) );
            } else {

                if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != "") {
                    if( preg_match( '/login.php$/', $_SERVER['HTTP_REFERER'] ) == 1 ) {
                        header("Location: index.php");
                    } else {
                        header("Location: " . $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    header("Location: index.php");
                }

                exit;
            }
        }
    }
}

context_log( 'Login Failed', $error );

?>
<?
echo layout(
    array(
        'header'            => gen_header("Login Failure"),
        'content'           => note($error, "I hope you think about what you just did."),
    )
);

?>
