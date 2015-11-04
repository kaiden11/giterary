<?
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/git.php' );

function gen_partition( $file  )  {
    perf_enter( "gen_partition" );

    if( !is_logged_in() || !can( "partition", implode( ":", array( $file ) ) ) ) {
        return gen_error( "You are not allowed to perform this function." );
    }


    return _gen_partition(
        array( 
            'file'              => &$file,
            'commit_before'     => &$commit_before,
            'commit_after'      => &$commit_after,
            'renderer'          => 'gen_partition',
        ) 
    ) . perf_exit( "gen_partition" );
}

function _gen_partition( $opts = array() ) {
    perf_enter( "_gen_partition" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_partition'); 

    $file = dirify( $file );

    $contents = git_file_get_contents( $file );

    function line_of_codify( $v, $offset ) {

        return "<span class=\"line-of-code\" offset=\"$offset\">$v</span>";
    }

    $blank = "\r?\n\s*\r?\n";

    # split only on absolutely empty lines
    $divisions = preg_split( 
        "/($blank)/",
        $contents, 
        -1, 
        ( PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE )
    );

    $ret = '';

    $counter = 1;

    # $ret .= '
    # <div class="partition-boundary enabled">
    #     <span>
    #         <input type="text"   class="partition-name"   name="partition_name" value="New partition #0"/>
    #         <input type="hidden" class="partition-offset" name="partition_offset" value="0"/>
    #     </span>
    # </div>';


    foreach( $divisions  as $d ) {

        # print_r( $d );
        if( preg_match( "/\A$blank\Z/", $d[0] ) == 0 ) {
            $ret .= line_of_codify( $d[0], $d[1] );
        } else {
            // strlen( $d[0] )
            $ret .= '
                <div class="partition-boundary">
                    <span>
                        <input type="hidden" class="partition-offset" name="partition_offset" value="' . ( 0 + $d[1] ) . '"/>
                    </span>
                </div>';
            $counter++;
        }

        // <input type="text"   class="partition-name"   name="partition_name" value="New partition #' . $counter . '"/>
    }

    # echo substr( $contents, 0, 270);

    # $ret = '<span class="line-of-code partition enabled"><input type="text" name="partition_name" value="New partition #1"/><input type="hidden" name="partition_offset" value="0"></span>' . $ret;

    $puck = array(
        'file'              =>  &$file,
        'partitioning'      =>  &$ret,
        'contents'          =>  &$contents
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_partition" );
}



function gen_make_partitions( $json = null ) {

    perf_enter( 'gen_make_partitions' );

    if( !can( "partition", $json ) ) {
        return render('not_logged_in', array() );
    }

    $obj = null;
    if( !is_null( $json ) ) {
        $obj = json_decode( $json, true ); // As associative array...
    }

    if( is_null( $obj ) ) {
        echo "Unable to parse JSON";
        exit;
    } 

    // Validation steps...

    $filename = $obj['filename'];

    if( !file_or( $filename, false ) ) {
        return gen_error( "File to partition appears invalid: '$filename'" );
    }

    $filename = dirify( $filename );

    if( !git_file_exists( $filename ) ) {
        return gen_error( "File to partition does not appear to exist: '$filename'" );
    }

    # Make sure nobody sends us trash filenames...
    $new_collection_name = $obj['new_collection_name'];

    if( !file_or( $new_collection_name, false ) ) {
        return gen_error( "Name for new collection file appears to be invalid: '$new_collection_name'" );
    }

    $new_collection_name = dirify( $new_collection_name );

    $partition_names = $obj['partition_names'];

    if( !is_array( $partition_names ) || count( $partition_names ) <= 0 ) {
        return gen_error( "Invalid partition names, or no partition names submitted." );
    }

    foreach( $partition_names as $i => &$partition_name ) {
        if( !file_or( $partition_name, false ) ) {
            return gen_error( "Partition name appears to be invalid: '$partition_name'" );
        }
        $partition_name = dirify( $partition_name );
    }

    # Check to see if any of the files being created already
    # exist
    $files_already_exist = array();

    if( git_file_exists( $new_collection_name ) ) {
        $files_already_exist[] = $new_collection_name;
    }

    foreach( $partition_names as $i => &$partition_name ) {
        if( git_file_exists( $partition_name ) ) {
            $files_already_exist[] = $partition_name;
        }
    }

    if( count( $files_already_exist ) > 0 ) {
        return gen_error( 
            "Sorry, but the following file(s) already exist: " . 
            implode("</br>", $files_already_exist )
        );
    }

    # Check that offsets are valid

    $boundary_offsets = $obj['boundary_offsets'];

    if( !is_array( $boundary_offsets ) || count( $boundary_offsets ) <= 0 ) {
        return gen_error( "Invalid boundary offsets, or no boundary offsets submitted." );
    }

    $uniq_offset = array();
    foreach( $boundary_offsets as $i => &$offset ) {
        if( !is_numeric( $offset ) ) {
            return gen_error( "Non-numeric offset submitted: '$offset'" );
        }


        if( array_key_exists( $offset, $uniq_offset )  ) {
            return gen_error( "Duplicate offsets submitted: $offset" );
        } 

        $uniq_offset[ $offset ] = 1;

        $offset = $offset + 0;

        if( $offset <= 0 ) {
            return gen_error( "Offsets cannot be less than or equal to zero: $offset" );
        }
    }

    if( count( $boundary_offsets ) != ( count( $partition_names ) - 1 ) ) {
        return gen_error( "Number of boundary offsets and partition names appear invalid." );
    }


    // End validation
    return _gen_make_partitions( 
        array( 
            'filename'              => &$filename,
            'new_collection_name'   => &$new_collection_name,
            'partition_names'       => &$partition_names,
            'boundary_offsets'      => &$boundary_offsets,
            'renderer'              => 'gen_make_partitions',
        ) 
    ) . perf_exit( "gen_make_partitions" );
}

function _gen_make_partitions( $opts = array() ) {

    perf_enter( "_gen_make_partitions" );

    $filename               = ( isset( $opts['filename'] ) ? $opts['filename'] : null );
    $new_collection_name    = ( isset( $opts['new_collection_name'] ) ? $opts['new_collection_name'] : null );
    $partition_names        = ( isset( $opts['partition_names'] ) ? $opts['partition_names'] : null );
    $boundary_offsets       = ( isset( $opts['boundary_offsets'] ) ? $opts['boundary_offsets'] : null );
    $renderer               = ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_make_partitions'); 

    $head_commit = git_file_head_commit( $filename );

    $view = git_view( $filename, $head_commit );

    if( !array_key_exists( "$head_commit:$filename", $view ) ) {
        return gen_error( "Unable to retrieve head commit for '$filename'" );
    }

    $contents = $view["$head_commit:$filename"];

    $seen_partitions = false;
    $partitions = array();
    $i = 0;
    $t = 0;

    sort( $boundary_offsets );

    # echo substr( $contents, 0, 389 );

    foreach( $boundary_offsets as $offset ) {

        $partitions[ $partition_names[$i] ] = substr( $contents, $t, ( $offset - $t ) );

        $seen_partitions = true;

        $t = $offset;

        $i++;

    }

    // Grab the last partition
    if( $seen_partitions ) {
        $partitions[ $partition_names[$i] ] = substr( $contents, $t );

        # echo "LAST:" . $partitions[ $partition_names[$i] ];
    }

    // Now we're done partitioning. Now to write to the various
    // partition files, and create the files/directories along
    // the way.

    // First, grab the head commit so we can revert in
    // the event something goes sour

    # print_r( $partitions );

    # return 'short circuit';

    $backout_head_commit = commit_or( git_head_commit(), false );

    if( $backout_head_commit === false ) {
        return "Failed to get backout head commit!";
    }

    $ret = false;
    $ret_message = '';

    $needs_backout = false;

    $new_paths = array();
    $new_collection_path = GIT_REPO_DIR . "/$new_collection_name";
    $new_paths[] = $new_collection_path;

    foreach( $partitions as $partition_name => $content ) {
        $new_partition_path = GIT_REPO_DIR . "/$partition_name";

        $new_paths[] = $new_partition_path;
    }


    foreach( $new_paths as $n ) {
        # If this is valid path, but doesn't exist yet...

        if( file_or( $n, null ) != null && !git_file_exists( $n ) ) {

            $dir_to_create = dirname( $n );

            if( !is_dir( $dir_to_create ) ) {

                # Create our directory 
                if( !mkdir( $dir_to_create, 0777, true ) ) {
                    return "Unable to create directory!";
                }
            }

            touch( $n );
        }
    }

    // Put content into files as appropriate...    
    file_put_contents( 
        $new_collection_path,
        implode( "\n", $partition_names )
    );

    foreach( $partitions as $partition_name => $content ) {
        $new_partition_path = GIT_REPO_DIR . "/$partition_name";

        file_put_contents( 
            $new_partition_path,
            $content
        );
    }

    // Add all of the new files to git.
    foreach( $new_paths as $n ) {

        // return array( "return_code" => $result, "out" => $out );;
        $add_ret = git( "add " . escapeshellarg( $n ) );

        if( $add_ret['return_code'] !== 0 ) {

            git( "reset --hard " . escapeshellarg( $backout_head_commit ), $output, null, false );
            git( "clean -fd" );

            return "Failed to add '$n' to git index! Reset back to $backout_head_commit.";
        }
    }

    // Commit the changes
    $commit_ret = git_commit( 
        $_SESSION['usr']['git_user'], 
        "Partitioning '$filename' into " . count( $partitions ) . " parts."
    );
    
    if( $commit_ret['return_code'] != 0 ) {
        git( "reset --hard " . escapeshellarg( $backout_head_commit ), $output, null, false );
        git( "clean -fd" );
        return $commit_ret['out'];
    }

    $puck = array(
        'filename'              => &$filename,
        'new_collection_name'   => &$new_collection_name,
        'partition_names'       => &$partition_names,
        'boundary_offsets'      => &$boundary_offsets,
        'renderer'              => $renderer,
    );

    # echo $renderer;

    return render( $renderer, $puck ) .  perf_exit( "_gen_make_partitions" );

}


?>
