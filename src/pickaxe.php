<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$search = $_GET['search'];

# $num    = numeric_or( $_GET['num'], 50 );
# $since  = commit_or( $_GET['since'], null );
# $skip   = numeric_or( $_GET['skip'], 0 );

echo layout(
    array(
        'header'            => gen_header( "Pickaxe History" ), 
        'content'           => gen_pickaxe( $search  )
    )
);

?>
