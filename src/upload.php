<?
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');

$upload_name = 'file_upload';

$default_upload_target_directory = "Uploads";

$target_directory   =   file_or( $_POST['directory'], null );

if( $target_directory == DEFAULT_FILE || $target_directory == "/" ) {
    $target_directory = null;
}

function err( $msg ) {
    die( layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_error( $msg )
        )
    ) );
}

if( !is_logged_in() ) {

    err( "You are not logged in." );

} else {

    if( $target_directory == null ) {

        $target_directory = $default_upload_target_directory;
    }

    $target_directory = dirify( $target_directory, true );

    if( !git_file_exists( $target_directory ) && !is_dir( GIT_REPO_DIR . "/$target_directory" ) ) {

        # err( "Target directory does not exist" );
        if( !mkdir( GIT_REPO_DIR . "/$target_directory", 0777, true ) ) {
            err( "Unable to create directory: '$target_directory'" );
        }
    }

    if($_FILES[$upload_name]['error'] == 2 ) {
        err( "Uploaded file is too large." );
    }

    $target_path = "$target_directory/" . $_FILES[$upload_name]['name'];
    $target_filesystem_path = GIT_REPO_DIR . $target_path;

    if( is_dirifile( $target_path ) ) {
        err( "Cannot upload file that is named like a directory (*." . DIRIFY_SUFFIX . ")" );
    }

    if( !move_uploaded_file($_FILES[$upload_name]['tmp_name'], $target_filesystem_path ) ) {
                
        err( 
            "Unable to copy file into '$target_filesystem_path': "
            . $_FILES[$upload_name]['error'] 
        );

    }

    # Add the new file to the index
    $add_ret = git( "add " . escapeshellarg( $target_path ) );
    if( $add_ret['return_code'] != 0 ) {

        err( $add_ret['out'] );

    } else {

        list( $commit_ret, $commit_ret_message ) = git_commit( 
            $_SESSION['usr']['git_user'],
            "Uploading file '$target_path'"
        );

        if( !$commit_ret ) {
            err( $commit_ret_message );
        } else {
            # Rebuild associations, if necessary
            if( ASSOC_ENABLE ) {
                require_once( dirname( __FILE__ ) . '/include/assoc.php');

                build_assoc( $target_path );
            }

            # Succeed by redirecting to the uploaded page
            header( 'Location: index.php?file=' . urldecode( undirify( $target_path ) ) );
        }
    }
}
