<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');


$query = proper_parse_str($_SERVER['QUERY_STRING']);

$file       = file_or(  $query['file'], false );
$list_file  = set_or(   $query['list'], false );

if( $file !== false && !is_array( $file ) ) {
    $file = array( $file );
}

if( $list_file !== false && is_array( $list ) ) {
    $list_file = array_shift( $list_file );
}

if( $file !== false ) {
    foreach( $file as &$f ) {
        $f = undirify( file_or( $f, false ) );
    }
}
    
$list_file  = file_or( $list_file, false );

$matched_files = array();

$title = false;

if( $list_file !== false && git_file_exists( $list_file ) ) {
    $list_contents = git_file_get_contents( $list_file );
    foreach( preg_split( '/\r?\n/', $list_contents ) as $line ) {
        if( trim( $line ) == "" ) { continue; }
    
        $matched_files = array_merge(
            $matched_files,
            collect_files( 
                $line,
                $list_file
            )
        );
    }

    $title = basename( $list_file );
} else {

    foreach( $file as $f ) {
        if( git_file_exists( $f ) ) {
            $matched_files[] = $f;
        }
    }

    $title = implode( 
        ", ",
        array_map(
            function( $a  ) {
                return basename( $a );
            },
            $matched_files
        )
    );
}


echo layout(
    array(
        'header'            => gen_header( "Timeline for $title" ), 
        'content'           => gen_timeline( $matched_files, $title  )
    )
);

?>
