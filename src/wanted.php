<?
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');


echo layout(
    array(
        'header'            => gen_header( "Wanted Pages" ),
        'content'           => gen_wanted()
    )
);

?>
