<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/stats.php');


perf_mem_snapshot( "stats, post requires" );

$file = file_or( $_GET['file'], null );
$commit = commit_or( $_GET['commit'], null );
# $mode = mode_or( $_GET['mode'], null );


if( is_null( $file ) || $file == "" ) {
    $file = DEFAULT_FILE;
}

# Single viewing for the time being...
if( is_array( $file ) ) {
    $file = array_shift( $file );
}

$is_dirifile = is_dirifile( $file );

# if( $is_session_available ) {
#     maintain_breadcrumb( $file );
# }


# if( !git_file_exists( dirify( $file ) ) && !$is_dirifile  ) {
# 
#     header("Location: edit.php?file=$file");
# 
# } else {

echo layout(
    array(
        'header'            => gen_header( "Stats: " . basename( $file )  ),
        'content'           => ( 
            $is_dirifile ? note( "Error", "Can't generate stats on a directory (yet). Sorry!" )
            :
            gen_stats( 
                $file,
                $commit
            ) . perf_mem_snapshot( 'after gen_stats call' )
        )
    )
);

?>
