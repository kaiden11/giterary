<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');


$file = file_or( substr( $_GET['file'], 0, 100 ), false );

echo layout(
    array(
        'header'            => gen_header( "What Links Here" ), 
        'content'           => gen_whatlinkshere( $file )
    )
);

?>
