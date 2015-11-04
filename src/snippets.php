<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/snippet.php');


echo layout(
    array(
        'header'            => gen_header( "Snippets" ), 
        'content'           => gen_snippets()
    )
);

?>
