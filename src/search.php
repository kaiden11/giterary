<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');


$term = substr( $_GET['term'], 0, 100 );

echo layout(
    array(
        'header'            => gen_header( "Search" ), 
        'content'           => gen_search( $term )
    )
);

?>
