<?
require_once( dirname( __FILE__ ) . '/git.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/cache.php');


$file_aliases = null;

function file_alias( $file ) {

    if( is_array( $file ) ) {
        $ret = array();

        $fa =   get_aliases();
        foreach( $file as $f ) {
            if( file_is_aliased( $f ) ) {
                $ret[] = file_alias( $fa[ $f ] );
            } else {
                $ret[] = $f;
            }
        }

        return $ret;

    } else {
        if( file_is_aliased( $file ) ) {
            $fa =   get_aliases();
            return file_alias(
                $fa[ $file ] 
            );
        }

        return $file;
    }
}

function file_is_aliased( $file ) {
    $fa = get_aliases();
    return isset( $fa[ $file ] );
}

function get_aliases( $cache = true ) {
    global $file_aliases;
    if( isset( $file_aliases ) && $cache === true ) {
        return $file_aliases;
    }

    $file_aliases = array();

    $alias_dir = dirify( ALIAS_DIR, true );

    # echo "alias dir: $alias_dir";
    $alias_paths = git_glob( "$alias_dir/*/*" );

    # print_r( $alias_paths );

    foreach( $alias_paths as $ap ) {
        list( $_dummy, $target, $alias ) = explode( "/", $ap );

        $t = alias_file_denormalize( undirify( $target, true ) );
        $a = alias_file_denormalize( $alias );

        $file_aliases[ $a ] = $t;
    }

    return $file_aliases;
}

function _alias_backout( $backout_commit ) {
    git( "reset --hard $backout_commit" );
    git( "clean -fd" );
    die( "BACKOUT" );
    return;
}

function alias_move( $from, $to, $perform_commit = true ) {

    $ret = array();

    if( !ALIAS_ENABLE ) {
        return $ret;
    }

    if( !is_logged_in() ) {
        die( "Not logged in!" );
    }

    $backout_commit = git_head_commit();

    $from   = dirify( $from );
    $to     = dirify( $to   );

    $ret = $action = perform_alias( $from, $to );

    // print_r( $action );
    $git_ret = null;
    $err = false;

    // Process the actions to inform the Git index
    // of the operations.
    foreach( $action as $a ) {

        list( $prefix, $_file ) = explode( ":", $a, 2 );

        if( $prefix == "add" ) {
           
            $git_ret = git( "add " . escapeshellarg( $_file ) );

        } elseif( $prefix == "rm" ) {

            $git_ret = git( "rm " . escapeshellarg( $_file ) );
                        
        } else {
            _alias_backout( $backout_commit );
            die( "Unhandled prefix: $prefix" );
        }

        if( $git_ret['return_code'] != 0 ) {
            $err = $git_ret['out'];
            break;
        }
    }

    if( $err !== false ) {
        _alias_backout( $backout_commit );
        die( $err );
    }

    if( !git_is_working_directory_clean() ) {

        if( $perform_commit ) {

            $commit_notes = "Aliasing '$from' as '$to'";

            list( $commit_ret, $commit_ret_message ) = git_commit(
                $_SESSION['usr']['git_user'],
                $commit_notes 
            );

            if( !$commit_ret ) {
                _alias_backout( $backout_commit );
                die( $commit_ret_message );
            }
        }
    }

    # _alias_backout( $backout_commit );

    return $ret;
}

function perform_alias( $from, $to ) {

    if( !ALIAS_ENABLE ) {
        return;
    }

    // Array of activities
    $ret = array();

    // Ensure alias file structures exist, or can be created.
    $base_alias_dir = dirify( ALIAS_DIR, true );
    $alias_dir = preg_replace( '@/+@', '/', ( GIT_REPO_DIR . '/' . $base_alias_dir  ) );

    if( !file_exists( $alias_dir ) ) {

        if( !mkdir( $alias_dir, 0777, true ) ) {
            die( "Unable to create '$alias_dir'" );                
        } else {
            $ret[] = "add:$base_alias_dir";
        }
    }

    // Massage, validate from/to paths.
    $from   = dirify( $from );
    $to     = dirify( $to   );

    $from_normalized    = alias_file_normalize( $from   );
    $to_normalized      = alias_file_normalize( $to     );

    $from_normalized_dir    = dirify( $from_normalized, true );
    $to_normalized_dir      = dirify( $to_normalized,   true );

    // Validate and make sure we aren't trying to double-alias a file.
    if( file_exists( "$alias_dir/$to_normalized_dir" ) ) {
        die( "Target $to has alias node ('$alias_dir/$to_normalized_dir') that already exists!" );
    }

    // Create the directory we'll be aliasing to
    if( !mkdir( "$alias_dir/$to_normalized_dir", 0777, true ) ) {
        die( "Unable to create '$alias_dir/$to_normalized_dir'" );                
    } else {
        $ret[] = "add:$base_alias_dir/$to_normalized_dir";
    }

    /*
        Now we have two paths:

         - Where no aliases exist for this file, and we're creating a new alias
         - Where 1 or more aliases exist for this file, and we need to migrate 
            the old aliases, in addition to creating the new ones.
    */

    if( !file_exists( "$alias_dir/$from_normalized_dir" ) ) {

        // We don't have to do anything, this is a new alias,
        // without the need to migrate the old aliases.

    } else {
        // At least one prior alias exists for this, such that the $from
        // path has an alias directory.

        // Strategy here is to move the prior directory to the new one
        // in order to carry over the previous aliases

        // $ret[] = "rm:$base_alias_dir/$from_normalized_dir";

        $g =  "$alias_dir/$from_normalized_dir/*";
        $from_paths = glob( $g );


        foreach( $from_paths as $p ) {

            $b = basename( $p );
    
            if( copy( $p, "$alias_dir/$to_normalized_dir/$b" ) !== true ) {
                die( "Unable to copy from '$p' to '$alias_dir/$to_normalized_dir/$b" );
            }

            $ret[] = "rm:$base_alias_dir/$from_normalized_dir/$b";
            $ret[] = "add:$base_alias_dir/$to_normalized_dir/$b";
        }
    }

    // Now, we need to create at least one (1) new alias for the new
    // name referencing the old name.

    if( 
        file_put_contents( 
            "$alias_dir/$to_normalized_dir/$from_normalized", 
            $from
        ) === false 
    ) {
        die( "Unable to create '$alias_dir/$to_normalized_dir/$from_normalized' for $from -> $to" );

    } else {
        $ret[] = "add:$base_alias_dir/$to_normalized_dir/$from_normalized";
    }

    return $ret;

}


function alias_file_normalize( $file ) {
    return str_replace( 
        "=",
        "_-_",
        base64_encode( 
            dirify( $file ) 
        )
    );
}

function alias_file_denormalize( $file ) {
    $ret = base64_decode(
        str_replace(
            "_-_",
            "=",
            $file
        ),
        true
    );

    if( $ret === false ) {
        return $file;
    }

    return $ret;
}




?>
