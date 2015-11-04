<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');


$file =  file_or(  $_GET['file'], null );
$confirm =  ( isset( $_GET['confirm'] ) && $_GET['confirm'] == "yes" ? $_GET['confirm'] : "no" );

if( is_array( $file ) ) {
    $file = array_shift( $file );
}

if( !can( "delete", $file ) ) {

    echo layout(
        array(
            'header'            => gen_header( "Not allowed" ), 
            'content'           => "You are not allowed to delete this file"
        )
    );

    exit;
}
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
                                            "Are you sure you want to delete this file ($file)?",
                                            "<div>
                                                <a href=\"index.php?file=" . $file . "\">No, I've made a terrible mistake.</a>
                                            </div>
                                            <div>
                                                I want to delete this file, a thousand times <a href=\"delete.php?file=" . $file . "&confirm=yes\">YES</a>.
                                            </div>"
                                        )
            )
        );
    } else {

        $ret_message = '';

        $file = dirify( $file );

        if( !git_file_exists( $file ) ) {
            $ret_message = "File '$file' does not appear to exist...";
        } else {

            list( $ret, $ret_message ) = git_rm( $file, $_SESSION['usr']['git_user'], "Deleting $file" );

            if( !$ret ) {
                $ret_message = "A problem has occurred while deleting the file '$file': $ret_message";

            } else {

                if( ASSOC_ENABLE ) {
                    disassociate( $file );
                }

                $ret_message = note( 
                    "Your file has been deleted",
                    "The record of this file is still available, it will, however, no longer appear as an 'existing' file until something is put in its place."
                );
            }
        }

        // Perform delete
        echo layout(
            array(
                'header'            => gen_header( "File Deletion" ), 
                'content'           => $ret_message
            )
        );
    }
}

?>
