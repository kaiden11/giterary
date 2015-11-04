<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$file = file_or( $_GET['file'], false );
$template = file_or( $_GET['template'], false );

if( !is_logged_in() ) {
    
    echo layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_not_logged_in()
        ),
        array(
        )
    );

} else {
    echo layout(
        array(
            'header'            => gen_header( "Create new document" ), 
            'content'           => gen_new( $file, $template )
        ),
        array(
        )
    );
}



?>
