<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/scratch.php');

echo layout(
    array(
        'header'            => gen_header( "Scratch" ), 
        'content'           => gen_scratch()
    )
);

?>
