<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$files  = file_or( $_GET['file'], null );

echo layout(
    array(
        'header'            => gen_header( "Work Time stats" ), 
        'content'           => ( $files == null ? "No file submitted" : gen_work_stats( $files ) )
    )
);

?>
