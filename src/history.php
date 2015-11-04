<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$files  = file_or( $_GET['file'], null );
$author = $_GET['author'];
$num    = numeric_or( $_GET['num'], 50 );
$since  = commit_or( $_GET['since'], null );
$skip   = numeric_or( $_GET['skip'], 0 );

echo layout(
    array(
        'header'            => gen_header( "History" ), 
        'content'           => gen_history( $files, $author, $num, $since, $skip )
    )
#     array(  
#         'renderer'  =>  'default_layout' 
#     )
);

?>
