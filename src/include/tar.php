<?php 
require_once( dirname( __FILE__ ) . '/config.php');
require_once( dirname( __FILE__ ) . '/util.php');

function tar( $command, &$output = "", $env = null, $debug = null, $suppress_error_return = false ) {

    perf_enter( "tar" );
    # Establish paths
    $tar_path = TAR_PATH;

    $tar_cmd = $tar_path . " $command";

    if( $debug === true ) {
        echo "\n$tar_cmd\n";
    }

    list( $result, $out ) = _call( $tar_cmd, $env );

    $output .= $out;
    
    if ($result !== 0 && !$suppress_error_return ) {
    	echo(
            "<h1>Error</h1>\n<pre>\n"
    	    . "" . he( $tar_cmd ) . "\n"
    	    . he( $out ) . "\n"
    	    . "Return code: " . $result . "\n"
    	    . "From: " . get_caller_method(1 ) . "\n"
    	    . "</pre>"
        );
    }

    perf_exit( "tar" );

    return array( "return_code" => $result, "out" => $out );;
}

function tar_export_repo( ) {

    $ret = false;
    perf_enter( 'tar_export_repo' );

    $output = "";

    $git_repo_dir_path = GIT_REPO_DIR;

    if( file_exists( $git_repo_dir_path ) ) {

        $export_file    = tempnam(  TMP_DIR , "giterary.export" );
        $cd_dir         = dirname(  $git_repo_dir_path          );
        $basename       = basename( $git_repo_dir_path          );

        tar( 
            "cf " 
            . escapeshellarg( $export_file ) 
            . " -C " . escapeshellarg( $cd_dir )
            . " " . escapeshellarg( $basename ),
            $output,
            null,   // env
            null,   // debug
            true    // suppress error return
        );

        $ret = $export_file;
    }

    perf_exit( 'tar_export_repo' );

    return $ret;
}

?>
