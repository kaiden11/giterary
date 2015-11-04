<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');


$query = proper_parse_str($_SERVER['QUERY_STRING']);

$meta = $query['meta'];
$file = file_or( $query['file'], null );

if( !is_null( $meta ) && !is_array( $meta ) ) {
    $meta = array( $meta );
}

echo layout(
    array(
        'header'            => gen_header( "Metadata" ), 
        'content'           => gen_meta( $meta, $file )
    )
);

?>
