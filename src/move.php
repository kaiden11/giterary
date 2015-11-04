<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');


$file =  file_or(  $_GET['file'], null );
$new_dir = file_or(  rtrim( $_GET['new_dir'], '/' ), null );

# echo "what? $new_dir : '" . $_GET['new_dir'];

$new_file = file_or(  $_GET['new_file'], null );

# Move counterpart also
$move_counterpart   = ( $_GET['move_counterpart'] == "yes" ? true : false );
$leave_alias        = ( $_GET['leave_alias'] == "yes" ? true : false );

if( is_array( $file ) ) {
    $file = array_shift( $file );
}

if( is_array( $new_file ) ) {
    $new_file = array_shift( $new_file );
}

if( is_array( $new_dir ) ) {
    $new_dir = array_shift( $new_dir );
}

# Single editing for the time being...

if( !is_logged_in() ) {

    echo layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_not_logged_in()
        )
    );
} else {

    echo layout(
        array(
            'header'            => gen_header( "Move file" ), 
            'content'           => gen_move( $file, $new_dir, $new_file, $move_counterpart, $leave_alias )
        )
    );

    
}

?>
