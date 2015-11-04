<?
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');


$file = file_or( $_GET['file'], null );
$assoc_type = $_GET['assoc_type'];

echo layout(
    array(
        'header'            => gen_header( "Page Associations" ),
        'content'           => gen_assoc( $file, $assoc_type )
    )
);

# $file = dirify( $file );
# 
# $file_md5 = dirify( md5( $file ), true );
# 
# $assoc_dir = dirify( ASSOC_DIR, true );
# 
# print_r( git_glob( "$assoc_dir/*/$file_md5/*" ) );

?>
