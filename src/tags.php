<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');


$query = proper_parse_str($_SERVER['QUERY_STRING']);

$tags = $query['tag'];
$file = file_or( $query['file'], null );

if( !is_null( $tags ) && !is_array( $tags ) ) {
    $tags = array( $tags );
}

echo layout(
    array(
        'header'            => gen_header( "Tagged Documents" ), 
        'content'           => gen_tags( $tags, $file )
    )
);

?>
