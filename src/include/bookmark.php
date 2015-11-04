<?php
require_once( dirname( __FILE__ ) . '/config/base.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/drafts.php');


function bookmarkify( $username, $file, $commit ) {

    $ret = draftify( $username, $file, $commit );

    return "bookmark.$ret";
}

function bookmark_update( $username, $file, $commit, $bookmark ) {
   
    $tmp_dir = TMP_DIR;

    if( !file_exists( $tmp_dir ) || !is_dir( $tmp_dir ) ) {
        echo "Invalid TMP_DIR: '$tmp_dir'";
    }

   
    // Stealing from the drafts
    $bookmark_file = bookmarkify( $username, $file, $commit );

    $ret = file_put_contents( 
        "$tmp_dir/$bookmark_file",
        serialize( $bookmark )
    );

    if( $ret === false ) {
        return false;
    } 

    return true;
}

function bookmark_get( $username, $file, $commit ) {
    

    $tmp_dir = TMP_DIR;

    if( !file_exists( $tmp_dir ) || !is_dir( $tmp_dir ) ) {
        echo "Invalid TMP_DIR: '$tmp_dir'";
        return null;
    }
   
    // Stealing from the drafts
    $bookmark_file = bookmarkify( $username, $file, $commit );

    $bookmark_path = "$tmp_dir/$bookmark_file";

    if( !file_exists( $bookmark_path ) ) {
        // echo "Unable to find $bookmark_path";
        return false;
    }

    $c = file_get_contents( $bookmark_path );

    if( $c === false ) {
        echo "Unable to retrieve $bookmark_file";
        return null;
    }

    $b = unserialize( $c );

    return $b;

}

?>
