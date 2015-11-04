<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/cache.php');


$tag = set_or( $_GET['clear_tag'], false );
$key = set_or( $_GET['clear_key'], false );

if( !is_logged_in() ) {
    die( "Not logged in" );
}

echo layout(
    array(
        'header'            => gen_header( "Cache contents" ), 
        'content'           => gen_cache(
            array(
                'tag'   =>  $tag,
                'key'   =>  $key
            )
        )
    )
);
?>
