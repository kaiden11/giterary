<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/download.php');


$file       = file_or( $_GET['file'], null );
$download   = $_GET['download'] == "yes" ? true : false;
$versioned  = set_or( $_GET['versioned'], false );
$prefix = set_or( $_GET['prefix'], false );
$basename = set_or( $_GET['basename'], false );

$versioned  = ( $versioned === false ? false : ( strtolower( trim( $versioned ) ) == "yes" ) );
$basename  = ( $basename === false ? false : ( strtolower( trim( $basename ) ) == "yes" ) );


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
            'header'            => gen_header( "Clean" ), 
            'content'           => gen_error( "Cannot view clean display of file that doesn't exist" )
        )
    );

    exit;

} 

if( $is_dirifile ) {

    echo layout(
        array(
            'header'            => gen_header( "Clean" ), 
            'content'           => gen_error( "Cannot view clean display of a directory" )
        )
    );

    exit;

} 

if( !can( "read", $file ) ) {
    echo layout(
        array(
            'header'            => gen_header( "Clean" ), 
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
    case "css": 
        $content_type = "text/css";
        break;
    case "js": 
        $content_type = "application/javascript";
        break;
    case "txt": 
    case "text": 
        $content_type = "text/plain";
        break;
    case "md": 
    case "markdown": 
        $content_type = "text/plain";
        break;
    case "collect": 
    case "collection": 
        # potentially collect contents of collection?
        $content_type = "text/plain";
        break;
    case "csv": 
        $content_type = "text/csv";
        break;
    case "pub":
    case "audio": 
    case "image": 

        die( "Cannot create 'clean' versions of a binary file." );

    case "tl":
    case "textile":
    default: 
        $content_type = "text/plain";
        break;
}

if( $download === true || $content_type == "application/epub+zip" ) {

    $temp_file = $file;

    if( $basename !== false ) {
        $temp_file = basename( $temp_file );
    }

    if( $prefix !== false ) {
        $temp_file = $prefix . "." . $temp_file;
    }


    $to_file_name = preg_replace( '/[\/\\:&><\[\]]/', '_', undirify( $temp_file ) );

    if( $versioned === true ) { 

        $hc = git_head_commit();
        $hc = commit_excerpt( $hc );
        $dt = strftime( "%Y%m%d.%H%M%S", time() );

        // If has a file extension, add versioning just 
        // before the file extension
        if( preg_match( '/(\.[a-z]+)$/', $to_file_name ) === 1 ) {
            $to_file_name = preg_replace( '/(\.[a-z]+)$/', ".$dt.$hc\\1", $to_file_name );
        } else {
            $to_file_name .= ".$dt.$hc";
        }

    }

    header( 'Content-Disposition: attachment; filename="' . $to_file_name . '"' );

}

if( $content_type == "text/plain" ) {
    $content_type .= "; charset=UTF-8";
}

header( "Content-Type: $content_type" );

echo gen_clean( $file );

?>
