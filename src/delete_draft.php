<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
# require_once( dirname( __FILE__ ) . '/include/edit.php');


$draft =  $_GET['draft'];
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
                                            "Are you sure you want to delete this draft ($draft)?",
                                            "<div>
                                                <a href=\"drafts.php\">No, I've made a terrible mistake.</a>
                                            </div>
                                            <div>
                                                I want to delete this file, a thousand times <a href=\"delete_draft.php?draft=" . $draft . "&confirm=yes\">YES</a>.
                                            </div>"
                                        )
            )
        );
    } else {

        $ret_message = '';

        if( !file_exists( DRAFT_DIR . "/" . $draft ) ) {
            $ret_message = "Draft '$draft' does not appear to exist...";
        } else {

            $ret = unlink( DRAFT_DIR . "/" . $draft );

            if( !$ret ) {
                $ret_message = "A problem has occurred while deleting the file '$draft'";

            } else {
                $ret_message = note( 
                    "Your draft has been deleted",
                    "Hope it wasn't too important."
                );
            }
        }

        // Perform delete
        echo layout(
            array(
                'header'            => gen_header( "Draft Deletion" ), 
                'content'           => $ret_message
            )
        );
    }
}

?>
