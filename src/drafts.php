<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/drafts.php');

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
            'header'            => gen_header( "Drafts" ), 
            'content'           => gen_drafts()
        ),
        array(
        )
    );
}



?>
