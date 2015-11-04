<?
require_once( dirname( __FILE__ ) . "/include/header.php");
require_once( dirname( __FILE__ ) . "/include/footer.php");
require_once( dirname( __FILE__ ) . "/include/auth.php");
require_once( dirname( __FILE__ ) . "/include/util.php");

$response   = set_or( trim( $_POST['response'] ), false );
$commit     = commit_or( trim( $_POST['commit'] ), false );

$error = '';

if( !is_logged_in() ) {
    $error .= "Not logged in";
} else {
    if ( !$response || !$commit ) {
        $error .= "Invalid request.";
    } else {

        $show = git_show( $commit );

        if( $show === false ) {
            $error .= "Invalid commit: '$commit'";
        } else {

            # Limt response to 4000 characters
            $response = substr( $response, 0, 4000 );
        
            $ret .= COMMIT_RESPONSE_PREFIX . $_SESSION['usr']['name'];
            $ret .= "\n\n";
            $ret .= $response;
            $ret .= "\n\n";

            $res = git_commit_append_note( $commit, $ret, COMMIT_RESPONSE_REF );

            if( $res[ 'return_code' ] != 0 ) {
                die( "Unable to append note to commit: '$commit'" );
            }

            header("Location: show_commit.php?commit=" . $commit );
            exit;
        }
    }
}

?>
<?
echo layout(
    array(
        'header'            => gen_header("Response Failure"),
        'content'           => note($error, "I hope you think about what you just did."),
    )
);

?>
