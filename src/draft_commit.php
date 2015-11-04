<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/drafts.php');


$drafts_to_commit   = $_POST[ 'draft_to_commit' ];
$confirm            = set_or( $_POST[ 'confirm' ],      false ) == "yes";
$commit_notes       = set_or( $_POST[ 'commit_notes' ], false );

if( !is_array( $drafts_to_commit ) ) {
    $drafts_to_commit = array( $drafts_to_commit );
}

echo layout(
    array(
        'header'            =>  gen_header( "Draft Commit" ), 
        'content'           =>  gen_draft_commit( 
                                    $drafts_to_commit, 
                                    $commit_notes, 
                                    $confirm 
                                )
    )
);

?>
