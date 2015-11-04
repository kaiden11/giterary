<? 
require_once('include/header.php');
require_once('include/footer.php');
require_once('include/git_html.php');

$file_a         = $_GET['file_a'];
$file_b         = $_GET['file_b'];
$plain          = ( $_GET['plain'] == "yes" ? true  : false );
$subtractions   = ( $_GET['subtractions'] == "no" ? false  : true );
$additions      = ( $_GET['additions'] == "no" ? false  : true );

if( $is_session_available ) {
    maintain_breadcrumb( $file );
    // Close out our session, as we don't want to block anything else while we're rendering.
    release_session();
}

echo layout(
    array(
        'header'            => gen_header( "Diff" ), 
        'content'           => gen_file_diff( 
            $file_a, 
            $file_b, 
            $plain,
            $subtractions,
            $additions
        ),
    )
#     array(  
#         'renderer'  =>  'default_layout' 
#     )
);

?>
