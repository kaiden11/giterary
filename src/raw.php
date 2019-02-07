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
            'header'            => gen_header( "Raw" ), 
            'content'           => gen_error( "Cannot view raw display of file that doesn't exist" )
        )
    );

    exit;

} 

if( $is_dirifile ) {

    echo layout(
        array(
            'header'            => gen_header( "Raw" ), 
            'content'           => gen_error( "Cannot view raw display of a directory" )
        )
    );

    exit;

} 

if( !can( "read", $file ) ) {
    echo layout(
        array(
            'header'            => gen_header( "Raw" ), 
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
    case "wrap": 
    case "wtxt": 
    case "wraptext": 
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
        $content_type = "application/epub+zip";
        break;
    case "pan":

        require_once( dirname( __FILE__ ) . '/include/pandoc.php');

        $pan = _pan_parse( 
            $file, 
            git_file_get_contents( $file )
        );

        if( strtolower( trim( $pan['mode'] ) ) == 'archive' ) {
            $content_type = "application/zip";
        } else {

            switch( strtolower( trim( $pan['format'] ) ) ) {

                case "icml":
                    $content_type = "application/icml";
                    break;

                case "markdown":
                    $content_type = "text/markdown";
                    break;

                case "latex":
                default:
                    $content_type = "application/x-tex";
                    break;
            }
        }
        break;

    case "audio": 
    case "image": 
        $finfo = new finfo(FILEINFO_MIME);
        # $content_type = $finfo->buffer( $view["$head_commit:$file"] );
        $content_type = array_shift( explode( ";", $finfo->file( $file_path ) ) );

        break;

    case "tl":
    case "textile":
    default: 
        $content_type = "text/plain";
        break;
}

if( 
    $download === true 
    || 
    in_array( 
        $content_type, 
        array( 
            "application/x-tex", 
            "application/icml", 
            "application/zip", 
            "text/markdown", 
            "application/epub+zip" 
        ) 
    ) 
) {

    $to_file_name = preg_replace( '/[\/\\:&><\[\]]/', '_', undirify( $file ) );


    switch( $content_type ) {

        case "application/epub+zip":
            // epub file extension
            $to_file_name = preg_replace( '/\.pub$/', '\.epub', $to_file_name );
            break;

        case "application/zip":
            // epub file extension
            $to_file_name = preg_replace( '/\.pan$/', '\.zip', $to_file_name );
            break;

        case "text/markdown":
            // Markdown / pan file extension
            $to_file_name = preg_replace( '/\.pan$/', '\.md', $to_file_name );
            break;

        case "application/icml":
            // ICML / pan file extension
            $to_file_name = preg_replace( '/\.pan$/', '\.icml', $to_file_name );
            break;

        // LaTeX / pan file extension
        case "application/x-tex":
            $to_file_name = preg_replace( '/\.pan$/', '\.tex', $to_file_name );
            break;
        default:
            break;
    }

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


// Attempt to handling caching for certain
// extensions
if( in_array( $extension, array( "image" ) ) ) {


    header( 'Cache-Control: must-revalidate, max-age=3600');

    $file_modified_time = filemtime( GIT_REPO_DIR . '/' . $file );

    if(
        isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] )
    ) {

        if( strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) >= $file_modified_time ) {

            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $file_modified_time ) .' GMT', true, 304);

            // Do not output anything.
            exit;
        }

    } else {
        # header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', ( time()+(3600) ) ) . ' GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $file_modified_time ) . ' GMT' );
    }
}

if( in_array( $extension, array( "image", "audio" ) ) ) {
    handle_partial_download( $file_path );
} else {
    $contents = git_file_get_contents( $file );
    echo _display( 
        $file, 
        $contents, 
        "raw"
    );
}
?>
