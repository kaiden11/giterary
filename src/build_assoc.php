<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
# require_once( dirname( __FILE__ ) . '/include/cache.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');

$file =  file_or(  $_GET['file'], null );

if( is_array( $file ) ) {
    $file = array_shift( $file );
}

if( !can( "build_assoc", $file ) ) {

    die( layout(
        array(
            'header'            => gen_header( "Not allowed" ), 
            'content'           => gen_error( "You are not allowed to rebuild associations for this file." )
        )
    ) );
}


$ret_message = '';

$file = dirify( $file );

if( !git_file_exists( $file, 'HEAD', false ) ) {
    die( layout(
        array(
            'header'            => gen_header( "Build Associations" ), 
            'content'           => gen_error( "File $file does not appear to exist" )
        )
    ) );

} else {


    echo layout(
        array(
            'header'            => gen_header( "Building Associations" ), 
            'content'           => gen_build_assoc( $file )
        )
    );


}


?>
