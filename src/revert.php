<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$commit =  commit_or(  $_GET['commit'], null );
$confirm =  ( isset( $_GET['confirm'] ) && $_GET['confirm'] == "yes" ? $_GET['confirm'] : "no" );

if( is_array( $commit ) ) {
    $commit = array_shift( $commit );
}

if( !can( "revert", $commit ) ) {

    echo layout(
        array(
            'header'            => gen_header( "Not allowed" ), 
            'content'           => gen_error( "You cannot revert this commit" )
        )
    );

    exit;
}

if( $confirm != "yes" ) {

    echo layout(
        array(
            'header'            => gen_header( "Confirm deletion" ), 
            'content'           => note( 
                                        "Are you sure you want to revert this commit ($commit)?",
                                        "<div>
                                            <a href=\"show_commit.php?commit=" . $commit . "\">No, I've made a terrible mistake.</a>
                                        </div>
                                        <div>
                                            I want to revert this file, a thousand times <a href=\"revert.php?commit=" . $commit . "&confirm=yes\">YES</a>.
                                        </div>"
                                    )
        )
    );

} else {

    $ret_message = '';

    list( $ret, $ret_message ) = git_revert( $commit, $_SESSION['usr']['git_user'], "Reverting $commit" );

    if( !$ret ) {
        $ret_message = "A problem has occurred while reverting this commit '$commit': $ret_message";

    } else {
        $ret_message = note( 
            "Your commit has been reverted",
            "This commit has been rolled back. Record of such has been made, but all changes prior to this commit are restored."
        );
    }

    // Perform delete
    echo layout(
        array(
            'header'            => gen_header( "File Deletion" ), 
            'content'           => $ret_message
        )
    );
}

?>
