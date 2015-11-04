<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

$commit = commit_or( $_GET['commit'], null );
$file   = file_or( $_GET['file'], null );

echo layout(
    array(
        'header'            => gen_header( "Show Commit Contents" ), 
        'show'              => gen_show( $commit )
    )
    # array(  
    #     'renderer'  =>  'show_layout' 
    # )
);

?>
