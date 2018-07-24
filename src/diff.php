<?php
require_once('include/header.php');
require_once('include/footer.php');
require_once('include/git_html.php');

$commit_before  = $_GET['commit_before'];
$commit_after   = $_GET['commit_after'];
$file           = $_GET['file'];
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
        'header'    => gen_header( 
            "Diff" . ( file_or( $file, false ) !== false ? " " . basename( $file ) : "" )  
        ), 
        'content'   =>  gen_diff( 
            $commit_before, 
            $commit_after, 
            $file, 
            $plain,
            $subtractions,
            $additions
        ),
    )
);

?>
