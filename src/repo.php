<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/stats.php');

echo layout(
    array(
        'header'            => gen_header( "Repository Stats" ),
        'content'           => ( 
            gen_repo_stats( )
        )
    )
);

?>
