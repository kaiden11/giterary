<? 
require_once('include/header.php');
require_once('include/footer.php');
require_once('include/partitions.php');
# require_once('include/git_html.php');

$file   = $_GET['file'];

echo layout(
    array(
        'header'            => gen_header( "Partition" ), 
        'content'           => gen_partition( $file ),
    ),
    array(
        'renderer'    =>  'edit_layout'
    )
);

?>
