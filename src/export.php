<?
require_once( dirname( __FILE__ ) . '/include/header.php'   );
require_once( dirname( __FILE__ ) . '/include/footer.php'   );
require_once( dirname( __FILE__ ) . '/include/util.php'     );
require_once( dirname( __FILE__ ) . '/include/tar.php'      );

if( !is_logged_in() ) {

    die( "You must be logged in to perform an export" );

} else {

    $tar_file = tar_export_repo();

    if( $tar_file && file_exists( $tar_file ) ) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . 'giterary.export.tar' );
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize( $tar_file ) );
        ob_clean();
        flush();
        readfile($tar_file);
        exit;
    }
}
?>
