<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/download.php');


$file       = file_or( $_GET['file'], null );
$download   = $_GET['download'] == "yes" ? true : false;
$versioned  = set_or( $_GET['versioned'], false );

$versioned  = ( $versioned === false ? false : ( strtolower( trim( $versioned ) ) == "yes" ) );


# $mode = mode_or( $_GET['mode'], null );

if( is_null( $file ) || $file == "" ) {
    $file = DEFAULT_FILE;
}

# Single viewing for the time being...
if( is_array( $file ) ) {
    $file = array_shift( $file );
}

$file = dirify( $file );


$is_dirifile = is_dirifile( $file );

# Let us not.
# if( $is_session_available ) {
#     maintain_breadcrumb( $file );
# }


if( !git_file_exists( $file ) ) {

    echo layout(
        array(
            'header'            => gen_header( "LaTeX" ), 
            'content'           => gen_error( "Cannot view LaTeX display of file that doesn't exist" )
        )
    );

    exit;

} 

if( $is_dirifile ) {

    echo layout(
        array(
            'header'            => gen_header( "LaTeX" ), 
            'content'           => gen_error( "Cannot view LaTeX display of a directory" )
        )
    );

    exit;

} 

if( !can( "read", $file ) ) {
    echo layout(
        array(
            'header'            => gen_header( "LaTeX" ), 
            'content'           => gen_error( "You are denied access to one or more of the components requested for viewing." )
        )
    );

    exit;
}

// Close out the session concurrency lock
session_write_close();


$extension = detect_extension( $file, null );
$file_path = GIT_REPO_DIR . "/" . $file;

$content_type = "text/plain";

switch ( $extension ) {
    case "pub":
    case "audio": 
    case "image": 

        die( "Cannot create 'clean' versions of a binary file." );

    default: 
        $content_type = "text/plain";
        break;
}

if( $download === true  ) {

    $to_file_name = preg_replace( '/[\/\\:&><\[\]]/', '_', undirify( $file ) );

    if( $versioned === true ) { 

        $hc = git_head_commit();
        $hc = commit_excerpt( $hc );
        $dt = strftime( "%Y%m%d.%H%M%S", time() );

        $to_file_name = preg_replace( '/(\.[a-z]+)$/', ".$dt.$hc\\1", $to_file_name );

    }

    header( 'Content-Disposition: attachment; filename="' . $to_file_name . '"' );

}

header( "Content-Type: $content_type" );

echo gen_latex( $file );

?>
