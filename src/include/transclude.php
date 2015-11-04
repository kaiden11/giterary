<?
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/config/conventions.php");
require_once( dirname( __FILE__ ) . "/util.php");
require_once( dirname( __FILE__ ) . "/meta.php");

$transclude_loop_detection = array();


function pop_transclude( $originating_file ) {

    GLOBAL $transclude_loop_detection;

    unset( $transclude_loop_detection[ $originating_file ] );

    return;

}
function push_transclude( $originating_file ) {
    GLOBAL $transclude_loop_detection;

    $transclude_loop_detection[ $originating_file ] = 1;

    return;
}

function is_transclude_loop( $originating_file ) {
    GLOBAL $transclude_loop_detection;
    return isset( $transclude_loop_detection[ $originating_file ] );
}

function transclude_helper( $transcluded_file, &$opts ) {
    GLOBAL $php_tag_pattern;

    $content    = git_file_get_contents( $transcluded_file );
    $extension  = detect_extension( $transcluded_file, null );

    if( $opts['strip'] == "tags" ) {
        $content = preg_replace( "/$php_tag_pattern/m", '', $content );
    }

    $meta = array();
    if( in_array( $extension, array( "markdown", "text", "print" ) ) ) {

        $content = metaify( 
            $content,
            $transcluded_file,
            $meta
        );

    }

    $content = _display( 
        $transcluded_file, 
        $content,
        $opts['as']
    );

    if( in_array( $extension, array( "markdown", "text", "print" ) ) ) {

        $content = metaify_postprocess( 
            $content,
            $transcluded_file,
            $meta
        );

    }


    return $content;

}

function transclude( $originating_file, $transcluded_file, &$opts ) {

    GLOBAL $transclude_loop_detection;
    static $depth = null;

    if( $depth == null ) {
        $depth = 0;
    }

    $ret = '';

    $originating_file = dirify( $originating_file );
    $transcluded_file = dirify( $transcluded_file );
    $transcluded_file_extension = detect_extension( $transcluded_file, null );

    # echo "$depth: $originating_file => $transcluded_file;";

    $depth++;

    /*  This is overzealous in the context of previews of
        new files.
    if( !git_file_exists( $originating_file ) ) {
        return "'$originating_file' does not exist";
    }
    */

    if( !git_file_exists( $transcluded_file ) ) {
        $depth--;
        return linkify( '[[' . $transcluded_file . '|' . basename( $transcluded_file ). ']]' ) . " does not exist";
    }

    if( $originating_file == $transcluded_file ) {
        $depth--;
        return "Cannot transclude oneself: '$originating_file'";
    }
    push_transclude( $originating_file );

    if( is_transclude_loop( $transcluded_file ) ) {
        $depth--;
        return "Transclude loop detected with transcluded file: '$transcluded_file'";
    }
    push_transclude( $transcluded_file );


    $ret = transclude_helper( $transcluded_file, $opts );

    pop_transclude( $transcluded_file );
    pop_transclude( $originating_file );

    $depth--;



    return $ret;
}


?>
