<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

echo layout(
    array(
        'header'            => gen_header( "Commit Notes" ), 
        'content'           => gen_notes_list()
    )
);

?>
