<?php

require_once( dirname( __FILE__ ) . '/config/base.php');
require_once( dirname( __FILE__ ) . '/util.php');

// Attempt automatic conflict resolution, returning
// the successful merge, or returning FALSE upon failure
function diff3( $my_file, $old_file, $your_file ) {

    $path_my_file   = tempnam( TMP_DIR, "my_file" );
    $path_old_file  = tempnam( TMP_DIR, "old_file" );
    $path_your_file = tempnam( TMP_DIR, "your_file" );


    // Give up if we simply can't perform a diff3 
    if( !DIFF3_PATH ) { return false; }
    if( !file_exists( DIFF3_PATH ) ) { return false; }
    if( !is_executable( DIFF3_PATH ) ) { return false; }

    $res = false;

    // Crater if we can't write to any of the temp files
    $res = file_put_contents( $path_my_file, $my_file );
    if( $res === false || $res == 0 ) { die( "Unable to write my '$path_my_file'" ); }

    $res = file_put_contents( $path_old_file, $old_file );
    if( $res === false || $res == 0 ) { die( "Unable to write old '$path_old_file'" ); }

    $res = file_put_contents( $path_your_file, $your_file );
    if( $res === false || $res == 0 ) { die( "Unable to write old '$path_your_file'" ); }

    

    $cmd = DIFF3_PATH . " -m " 
        .  escapeshellarg( $path_my_file ) 
        . " "
        .  escapeshellarg( $path_old_file ) 
        . " "
        .  escapeshellarg( $path_your_file ) 
    ;

    list( $result, $out ) = _call( $cmd );

    unlink( $path_my_file );
    unlink( $path_old_file );
    unlink( $path_your_file );

    if( $result != 0 ) {
        return false;
    }
    
    return $out;
}


?>
