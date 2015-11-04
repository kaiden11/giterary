<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');
require_once( dirname( __FILE__ ) . '/include/bookmark.php');


perf_mem_snapshot( "index, post requires" );

$file = file_or( $_GET['file'], null );
$as =   $_GET['as'];

# $mode = mode_or( $_GET['mode'], null );


if( is_null( $file ) || $file == "" ) {
    $file = DEFAULT_FILE;

    if( !can( "read", $file ) ) {
        
        if( is_logged_in() ) {
            // If we cannot access the home file by default, try to access
            // the user's user page
            $file = $_SESSION['usr']['name'];
        }
    }
}


# Single viewing for the time being...
if( is_array( $file ) ) {
    $file = array_shift( $file );
}

$is_dirifile = is_dirifile( $file );

if( $is_session_available ) {
    maintain_breadcrumb( $file );
    // Close out our session, as we don't want to block anything else while we're rendering.
    release_session();
}


if( !git_file_exists( dirify( $file ) ) && !$is_dirifile  ) {

    header("Location: edit.php?file=$file");

} else {

    switch( $as ) {
        case "print":
        case "printable":
            $layout = "printable";
            break;
        case "read":
        case "readable":
            $layout = "readable";
            break;
        default:
            $layout = "default_layout";
            break;
    }

    echo layout(
        array(
            'header'            =>  gen_header( 
                                        ( 
                                            $is_dirifile 
                                                ? "Directory: " . basename( $file ) 
                                                : basename( $file ) 
                                        ),
                                        dirify( $file )
                                    ),
            'content'           =>  ( 
                                        $is_dirifile 
                                            ? gen_dir_view( $file ) 
                                            : gen_view( $file, null, $as ) 
                                    )
        ),
        array(
            'renderer'          =>  $layout
        )
    );
}

?>
