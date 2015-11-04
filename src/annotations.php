<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$file = file_or( $_GET['file'], null );

echo layout(
    array(
        'header'            => gen_header( "Documents with Annotations" ), 
        'content'           => gen_annotations( $file )
    )
);

?>
