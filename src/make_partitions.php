<? 
require_once('include/header.php');
require_once('include/footer.php');
require_once('include/partitions.php');

$json   = stripslashes( $_POST['json'] );

echo layout(
    array(
        'header'            => gen_header( "Making partitions" ), 
        'content'           => gen_make_partitions( $json ),
    )
);

?>
