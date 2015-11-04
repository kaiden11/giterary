<? 
require_once('include/header.php');
require_once('include/footer.php');
require_once('include/git_html.php');

$commit_before  = $_GET['commit_before'];
$commit_after   = $_GET['commit_after'];
$file           = $_GET['file'];
$draft          = $_GET['draft'];

echo layout(
    array(
        'header'            =>  gen_header( "Cherrypick" ), 
        'content'           =>  gen_cherrypick( 
                                    $commit_before, 
                                    $commit_after, 
                                    $file,
                                    $draft
                                )
    ),
    array(  
        'renderer'          =>  'edit_layout' 
    )
);

?>
