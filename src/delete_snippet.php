<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/snippet.php');


$snippet =  $_GET['snippet'];
$confirm =  ( isset( $_GET['confirm'] ) && $_GET['confirm'] == "yes" ? $_GET['confirm'] : "no" );

# Single editing for the time being...

if( !is_logged_in() ) {

    echo layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_not_logged_in()
        )
    );
} else {

    if( $confirm != "yes" ) {
        echo layout(
            array(
                'header'            => gen_header( "Confirm deletion" ), 
                'content'           => note( 
                                            "Are you sure you want to delete this snippet ($snippet)?",
                                            "<div>
                                                <a href=\"snippets.php\">No, I've made a terrible mistake.</a>
                                            </div>
                                            <div>
                                                I want to delete this snippet, a thousand times <a href=\"delete_snippet.php?snippet=" . urlencode( $snippet ) . "&confirm=yes\">YES</a>.
                                            </div>"
                                        )
            )
        );
    } else {

        $ret = snippet_delete( $_SESSION['usr']['name'], $snippet );
        $ret_message = '';

        if( $ret !== true ) {
            $ret_message = "A problem has occurred while deleting the snppet '$ret'";

        } else {
            $ret_message = note( 
                "Your snippet has been deleted",
                'Hope it wasn\'t too important. Back to <a href="snippets.php">snippets</a>.'
            );
        }

        // Perform delete
        echo layout(
            array(
                'header'            => gen_header( "Snippet Deletion" ), 
                'content'           => $ret_message
            )
        );
    }
}

?>
