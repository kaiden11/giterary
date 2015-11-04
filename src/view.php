<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$commit = commit_or( $_GET['commit'], null );
$files  = file_or( $_GET['file'],  null );
$as     = set_or( $_GET['as'], null );


echo layout(
    array(
        'header'            => gen_header( "View commit" ), 
        'contents'          => gen_view( $files, $commit, $as )
    )
    # array(  
    #     'renderer'  =>  'show_layout' 
    # )
);

?>
