<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/bookmark.php');



if( is_logged_in() ) {

    $file         =   file_or( $_POST['file'], false );
    $commit       =   commit_or( $_POST['commit'], false );
    $bookmark     =   set_or( $_POST['bookmark'], false );


    if( $file === false ) {
        die( "fail: invalid file ($file)" );
    }

    if( $commit === false ) {
        die( "fail: invalid commit ($commit)" );
    }

    if( $bookmark === false ) {
        die( "fail: no bookmark sent" );
    }

    $b = json_decode( $bookmark, true );

    if( is_null( $b ) ) {
        die( "fail: invalid bookmark sent" );
    }


    $ret = bookmark_update( $_SESSION['usr']['name'], $file, $commit, $b );

    maintain_status( 
        array(
            'user'          =>  $_SESSION['usr']['name'],
            'page_title'    =>  "Reading '" . undirify( $file ) . "'",
            'path'          =>  dirify( $file )
        )
    );

    if( $ret === false ) {
        die( "fail: unable to save bookmark" );
    }

    echo "success";
}


?>
