<? 
require_once( dirname( __FILE__ ) . '/header.php');
require_once( dirname( __FILE__ ) . '/footer.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/git.php');


$draft_update_frequency = 10; // seconds

function draftify( $username, $filename, $commit ) {
    $filename = undirify( $filename );
    return join(
        ".", 
        array( 
            $username, 
            preg_replace( '/[\/\\:&><\[\]]/', '_', $filename ),
            md5( $filename ),
            $commit
        )
    );
}

function draft_update( 
    $draft_filename = null, 
    $draft_commit = null,
    $draft_contents = null, 
    $draft_notes = null, 
    $draft_work_time  = null
) {
    GLOBAL $draft_update_frequency;

    if( $draft_filename != null) {

        if( git_file_exists( dirify( $draft_filename ) ) ) {
            if( $draft_commit == null || $draft_commit == '' ) {
                $draft_commit = commit_or( git_file_head_commit( $draft_filename ), git_head_commit() );
            }
        } else {
            // New file, grab the head commit.
            $draft_commit = git_head_commit();
        }

        $draft_name = draftify( $_SESSION['usr']['name'], $draft_filename, $draft_commit );

        $potential_prior_draft_path = DRAFT_DIR . '/' . $draft_name;
        $prior_draft = null;
        
        if( file_exists( $potential_prior_draft_path ) ) {
            
            $prior_draft = unserialize( file_get_contents( $potential_prior_draft_path ) );

            $total_possible_work_time_delta = ( time() - $prior_draft['epoch'] );

            if( $draft_work_time != null && is_numeric( $draft_work_time ) ) {

                $draft_work_time = ( $draft_work_time > $total_possible_work_time_delta ? $total_possible_work_time_delta : $draft_work_time );

                # Add draft time to the prior total time recorded with
                # the draft
                $draft_work_time = ( isset( $prior_draft['work_time'] ) ? $prior_draft['work_time'] : 0 ) + $draft_work_time;
            } else {

                $draft_work_time = ( 
                    isset( $prior_draft['work_time'] ) 
                        ? ( 
                            ( 
                                // This is meant to ensure that a preview work_time update
                                // doesn't just count the seconds between when you hit Preview.
                                // However, this also means you only get 10 seconds per Preview
                                // if you have Javascript disasbled. Hmm.
                                ( time() - $prior_draft['epoch'] > $draft_update_frequency )
                                    ?   $draft_update_freuqency
                                    :   ( time() - $prior_draft['epoch'] )
                            )
                            + $prior_draft['work_time'] )
                        : 0 
                );
            }

        } else {

            $prior_draft = array(
                'user'      =>  $_SESSION['usr']['name'],
                'filename'  =>  $draft_filename,
                'contents'  =>  $draft_contents,
                'notes'     =>  $draft_notes,
                'commit'    =>  $draft_commit,
                'work_time' =>  0,
                'epoch'     =>  time()
            );
        }

        file_put_contents( 
            $potential_prior_draft_path,
            serialize( 
                array(
                    'user'      =>  ( $_SESSION['usr']['name']  != null ? $_SESSION['usr']['name']  :   $prior_draft['user']        ),
                    'filename'  =>  ( $draft_filename           != null ? $draft_filename           :   $prior_draft['filename']    ),
                    'contents'  =>  ( $draft_contents           != null ? $draft_contents           :   $prior_draft['contents']    ),
                    'notes'     =>  ( $draft_notes              != null ? $draft_notes              :   $prior_draft['notes']       ),
                    'work_time' =>  ( $draft_work_time          != null ? $draft_work_time          :   $prior_draft['work_time']   ),
                    // Update the timestamp every time
                    'epoch'     =>  time(),
                    // The submitted, or latest, commit for this file
                    'commit'    =>  $draft_commit
                )
            )
        );

        return $potential_prior_draft_path;
    }
}

function get_draft_list_count( $username ) {
    # $draft_files = glob( DRAFT_DIR . "/" . $_SESSION['usr']['name'] . ".*" );
    $draft_files = glob( DRAFT_DIR . "/" . $username . ".*" );

    return count( $draft_files );
}

function get_draft_list( $username ) {

    # $draft_files = glob( DRAFT_DIR . "/" . $_SESSION['usr']['name'] . ".*" );
    # $draft_files = glob( DRAFT_DIR . "/" . $username . ".*" );
    $draft_files = _draft_glob( "$username.*" );
    
    $drafts = array();
    
    if( $draft_files !== false && count( $draft_files ) > 0 ) {
        foreach( $draft_files as $file ) {
            if( file_exists( $file ) ) {
                $draft = unserialize( file_get_contents( $file ) );
    
                $drafts[] = array( 
                    'filepath'  =>  $file,
                    'filename'  =>  $draft['filename'], 
                    'epoch'     =>  $draft['epoch'], 
                    'length'    =>  strlen( $draft['contents'] ),
                    'commit'    =>  $draft['commit'],
                    'work_time' =>  $draft['work_time']
                );
    
                $draft = null;
            }
        }
    }

    return $drafts;
}

function is_draft_committable( $draft_file, $draft_commit ) {
    $is_committable = true;
    if( git_file_exists( $draft_file ) ) {
        $hc = git_file_head_commit( dirify( $draft_file ) );
    
        if( $hc != $draft_commit ) {
            $is_committable = false;
        }
    }

    return $is_committable;

}

function gen_drafts() {

    perf_enter( "gen_drafts" );

    if( !is_logged_in() ) {
        return 'You are not logged in.';
    } else {
        $draft_files = get_draft_list( $_SESSION['usr']['name'] );

        # We need to check whether each draft is committable by
        # making sure that if the file exists in the repo, the commit the draft
        # was written against is the same as that file's head commit (make sure
        # the file's commit hasn't moved underneath it)
        foreach( $draft_files as &$draft ) {

            # print_r( $draft );

            $draft['is_committable'] = is_draft_committable( $draft['filename'], $draft['commit'] );

        }

        $puck = array(
            'drafts'    =>  &$draft_files
        );

        return render( 'gen_drafts', $puck ) .  perf_exit( "gen_drafts" );
    }
}

function gen_draft_commit( $drafts_to_commit = array(), $commit_notes = null, $confirm = false ) {

    perf_enter( "gen_draft_commit" );

    if( !is_logged_in() ) {
        return gen_not_logged_in();
    } else {

        $drafts_to_process = array();
        $file_targets_for_drafts = array();


        foreach( $drafts_to_commit as &$draft ) {
            list( $username, $file_ish, $file_md5, $commit ) = explode( '.', $draft );

            if( $username != $_SESSION['usr']['name'] ) {
                continue;
            }

            $draft_exists = _draft_glob( $draft );

            if( $draft_exists === false || count( $draft_exists ) != 1 ) {
                continue;
            }

            
            $draft_details = _draft_get( $draft );

            if( !is_draft_committable( $draft_details['filename'], $draft_details['commit'] ) ) {
                continue;
            }

            $drafts_to_process[ $draft ] = $draft_details;

            if( isset( $file_targets_for_drafts[ dirify( $draft_details['filename'] ) ] ) ) {
                return  "Two drafts target the same file: '" 
                        . $draft_details['filename'] 
                        . "', please choose only one draft per file." 
                        . perf_exit( 'gen_draft_commit' )
                ;
            }

            $file_targets_for_drafts[ dirify( $draft_details['filename'] ) ] = 1;


        }

        if( !$commit_notes ) {

            $commit_notes = '';
            
            foreach( $drafts_to_process as &$details ) {
                $commit_notes .= $details['notes'] . "\n";
            }
        }


        if( count( $drafts_to_process ) <= 0 ) {
            return "No drafts available/valid to commit." . perf_exit( 'gen_draft_commit' );
        }

        if( $confirm === true ) {

            perf_exit( 'gen_draft_commit' );

            $backout_commit = git_head_commit();
            $ret = commit_drafts( 
                $drafts_to_process, 
                $_SESSION['usr']['git_user'], 
                $commit_notes 
            );

            if( $ret === true ) {

                if( ASSOC_ENABLE ) {
                    # Rebuild any association changes required as a result
                    # of the changes.
                    require_once( dirname( __FILE__ ) . '/assoc.php');
                    require_once( dirname( __FILE__ ) . '/cache.php');

                    foreach( $drafts_to_process as $draft_name => &$d ) {

                        clear_all_caches( dirify( $d['filename'] ) );

                        build_assoc( dirify( $d['filename'] ), false );
                    }

                    $commit_notes = "Maintaining associations for multi-draft commit.";

                    list( $ret, $ret_message ) = git_commit(
                        $_SESSION['usr']['git_user'],
                        $commit_notes 
                    );
                }

                return "Drafts committed.";
            } else {
                git( "reset --hard $backout_commit" );
                git( "clean -fd" );
            }

            return $ret;
        }
        
        $puck = array(
            'confirm'           =>  $confirm,
            'commit_notes'      =>  $commit_notes,
            'drafts_to_process' =>  &$drafts_to_process,
        );

        return render( 'gen_draft_commit', $puck ) .  perf_exit( "gen_draft_commit" );
    }
}

function commit_drafts( $drafts_to_process, $author, $commit_notes ) {

    $ret = null;

    if( !is_array( $drafts_to_process ) ) {
        return "Invalid argument to commit_drafts";
    }

    if( count( $drafts_to_process ) <= 0 ) {
        return "No drafts passed to commit in commit_drafts.";
    }

    $potential_author = author_or( $author, $false );

    if( $potential_author === false ) {
        return "Invalid author: '$author'";
    }

    # if( git_is_working_directory_clean() ) { }

    foreach( $drafts_to_process as $draft_name => &$draft ) {

        $draft_file_path = GIT_REPO_DIR . '/' . dirify( $draft['filename'] );

        $dir_to_create = dirname( $draft_file_path );
        if( !is_dir( $dir_to_create ) ) {
            # Create our directory 
            if( !mkdir( $dir_to_create, 0777, true ) ) {
                $ret = "Unable to create directory!";
                break;
            }
        }

        if( !file_exists( $draft_file_path ) ) {

            if( !touch( $draft_file_path ) ) {
                $ret = "Unable to touch '$draft_file_path'";
                break;
            }
        }

        if( file_put_contents( $draft_file_path, $draft['contents'] ) === false ) {
            return "Unable to write to '$draft_file_path'";
        }

        $add_ret = git( "add " . escapeshellarg( dirify( $draft['filename'] ) ) );

        if( $add_ret['return_code'] != 0 ) {
            return $add_ret['out'];
        }
    }

    if( is_null( $ret ) ) {
        list( $commit_ret, $commit_ret_message ) = git_commit( $potential_author, $commit_notes );

        if( !$commit_ret ) {
            $ret = $commit_ret_message;
        } else {
            $ret = true;
        }
    }

    if( $ret === true ) {
        // All is well, and all commits are safe.
        // We can remove the drafts we just committed.

        foreach( $drafts_to_process as $draft_name => &$draft ) {

            $draft_files = _draft_glob( $draft_name );

            if( is_array( $draft_files ) && count( $draft_files ) == 1 ) {
                $df = array_shift( $draft_files );

                if( unlink( $df ) === false ) {
                    // Not sure what we should really do here. Reverting won't help much
                    // We've arrived as some problem in how drafts are being stored, not
                    // with how we're trying to commit them here. Manually removal might
                    // be in order.
                }
            }
        }
    }

    return $ret;

}


function gen_draft_warning( $draft_name, $params ) {

    return render( 
        'gen_draft_warning', 
        array(
            'draft_name'    =>  $draft_name,
            'parameters'    =>  $params
        )
    );
}

function _draft_glob( $glob ) {

    $draft_files = glob( DRAFT_DIR . "/" . $glob );
    return $draft_files;
}

function _draft_get_path( $draft_path ) {

    $ret = unserialize( file_get_contents( $draft_path ) );

    return $ret;
}


function _draft_get( $draft_basename ) {

    $ret = null;
    if( count( $draft_files = _draft_glob( $draft_basename ) ) == 1 ) {

        $df = array_shift( $draft_files );
        
        $ret = unserialize( file_get_contents( $df ) );
    }

    return $ret;
}


function find_latest_draft( $drafts ) {

    $latest_draft   =   null;

    foreach( $drafts as $d ) {
        
        $de_contents = file_get_contents( $d );
    
        $de_obj = unserialize( $de_contents );
    
        if( is_null( $latest_draft ) ) {
            $latest_draft = $de_obj;
        } else {
            if( $de_obj['epoch'] < $latest_draft['epoch'] ) {
                $latest_draft = $de_obj;
            }
        }
    }

    return $latest_draft;
}


function draft_exists( $user, $filename, $commit = '*'  ) {
    $ret = false;

    if( $user != null && $user != "" && $filename != null && $filename != "" ) {

        $draft_name_glob = draftify( $user, $filename, $commit );

        $draft_files = _draft_glob( $draft_name_glob );
        
        if( count( $draft_files ) > 0 ) {
            $ret = $draft_files;
        }
    }

    return $ret;
}


?>
