<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/import.php');

$file = file_or( $_GET['file'], null );

echo layout(
    array(
        'header'            => gen_header( "Importing " . basename( $file ) ), 
        'content'           => gen_import( $file )
    ),
    array(
        'renderer'          => 'edit_layout'
    )
);

?>
