<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/snippet.php');

if( is_logged_in() ) {

    $file         =   file_or( $_POST['file'], false );
    $commit       =   commit_or( $_POST['commit'], false );
    $snippet      =   set_or( $_POST['snippet'], false );
    $context      =   set_or( $_POST['context'], false );
    $type         =   set_or( $_POST['type'], false );


    if( $file === false ) {
        die( "fail: invalid file ($file)" );
    }

    if( $commit === false ) {
        die( "fail: invalid commit ($commit)" );
    }

    if( $snippet === false || $snippet == "" ) {
        die( "fail: no snippet sent" );
    }

    $ret = snippet_update( 
        $_SESSION['usr']['name'], 
        $file, 
        $commit, 
        $snippet, 
        $context, 
        $type,
        null
    );

    /*
    maintain_status( 
        array(
            'user'          =>  $_SESSION['usr']['name'],
            'page_title'    =>  "Reading '" . undirify( $draft_filename ) . "'",
            'path'          =>  dirify( $draft_filename )
        )
    );
    */

    if( $ret === false ) {
        die( "fail: unable to save snippet" );
    }

    echo "success";
}


?>
