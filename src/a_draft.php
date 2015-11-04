<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/drafts.php');


function validate( $text ) {
    return $text;
}

if( is_logged_in() ) {

    $draft_contents     =   validate(   $_POST['draft_contents']            );
    $draft_notes        =   validate(   $_POST['draft_notes']               );

    $draft_filename     =   file_or(        $_POST['draft_filename'],   null    );
    $draft_commit       =   commit_or(      $_POST['draft_commit'],     null    );

    $draft_work_time    =   set_or(          $_POST['draft_work_time'],  0       );

    draft_update( 
        $draft_filename,
        $draft_commit,
        $draft_contents,
        $draft_notes,
        $draft_work_time
    );

    maintain_status( 
        array(
            'user'          =>  $_SESSION['usr']['name'],
            'page_title'    =>  "Editing '" . undirify( $draft_filename ) . "'",
            'path'          =>  dirify( $draft_filename )
        )
    );


    echo "success";
}


?>
