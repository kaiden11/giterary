<?php
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/cache.php');

$file =  file_or(  $_GET['file'], null );

# Command line handling
$is_cli = false;
if( is_null( $file  ) ) {
    $file = file_or( $argv[1], false );

    if( $file !== false ) {
        $is_cli = true;
    }
}

if( is_array( $file ) ) {
    $file = array_shift( $file );
}

if( !$is_cli && !can( "clear_cache", $file ) ) {
    echo layout(
        array(
            'header'            => gen_header( "Not allowed" ), 
            'content'           => "You are not allowed to clear the cache for this file"
        )
    );

    exit;
}



$ret_message = '';

$file = dirify( $file );

if( !git_file_exists( $file, 'HEAD', false ) ) {
    $ret_message = "File '$file' does not appear to exist...";
} else {


    $cleared = array();
    if( is_dirifile( $file ) ) {


        $hc = git_head_commit();
        $c = commit_or( git_file_head_commit( $file ), $hc );
        $file = rtrim( $file, '/' );

        $possible_dir_caches = array( 
            $file               . "",
            $c  . ":" . $file   . "",
            $c  . ":" . $file   . "-r",
            $c  . ":" . $file   . "-r -d",
            $hc . ":" . $file   . "",
            $hc . ":" . $file   . "-r",
            $hc . ":" . $file   . "-r -d"
        );

        foreach( $possible_dir_caches as $dc ) {
            $cleared = array_merge(
                clear_cache( 
                    'git_ls_tree', 
                    $dc
                ),
                $cleared
            );
        }


    } else {


        $cleared = clear_all_caches( $file );
    }


    $cleared = array_merge(
        $cleared,
        clear_cache( 'git_head_commit', 'HEAD' )
    );

    /* Refactored into include/cache.php
    $head_commit = git_file_head_commit( $file );
    $content = git_view_show_helper( $head_commit, $file );

    $extension = detect_extension( $file, null );

    $cleared = array();
    $cleared = array_merge( $cleared, clear_cache( 'file_head_commit',   path_to_filename( $file ) ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_helper',    "$head_commit:$file"      ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_helper',    "HEAD:$file"              ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_file_exists',    "$head_commit:$file"      ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_file_exists',    "HEAD:$file"              ) );
    $cleared = array_merge( $cleared, clear_cache( 'display',            "$extension::$content"    ) );
    */

    if( !$is_cli ) {
        $ret_message = note( 
             "Clearing cache...",
             "Cleared " . count( $cleared ) ." cache file(s) for this file. Go to " .
             '<a href="index.php?file=' . urlencode( $file ) . '">' . undirify( $file ) . '</a>' . 
             '
             <pre><code>' . var_export( $cleared, true ) . '</code></pre>'
        );
    }
}


if( $is_cli ) {
    print_r( $cleared );
} else {
    echo layout(
        array(
            'header'            => gen_header( "Clearing cache" ), 
            'content'           => $ret_message
        )
    );
}

?>
