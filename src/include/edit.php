<?
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/display.php');
require_once( dirname( __FILE__ ) . '/drafts.php');

function gen_help( $file ) {
    perf_enter( "gen_help" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_help'); 


    $puck = array(
        'file'              =>  &$file,
    );
    return render( $renderer, $puck ) .  perf_exit( "gen_help" );

}


function gen_edit( $parameters = array()  )  {
    perf_enter( "gen_edit" );

    if( !can( "edit", implode( ":", array( $parameters['file'] ) ) ) ) {
        return render( 'not_logged_in', array() );
    }

    # Multiplex rendering of edit screens based on detectect/overridden
    # file extension.
    $extension = detect_extension( $parameters['file'], $parameters['as'] );

    switch( $extension ) {
        case "markdown":
        case "talk":

            return _gen_edit( 
                array( 
                    'parameters'        => &$parameters,
                    'extension'         =>  $extension,
                    'renderer'          => 'gen_markdown_edit',
                ) 
            ) . perf_exit( "gen_edit" );
        case "storyboard":
            return _gen_edit( 
                array( 
                    'parameters'        => &$parameters,
                    'extension'         =>  $extension,
                    'do_preview'        =>  true,
                    'renderer'          => 'gen_storyboard_edit',
                ) 
            ) . perf_exit( "gen_edit" );
        default:
            return _gen_edit( 
                array( 
                    'parameters'        => &$parameters,
                    'extension'         =>  $extension,
                    'renderer'          => 'gen_edit',
                ) 
            ) . perf_exit( "gen_edit" );
    }
}

/*
function _gen_edit_donotuse( $opts = array() ) {
    perf_enter( "_gen_edit" );

    $parameters =       ( isset( $opts['parameters'] ) ? $opts['parameters'] : array() );
    $extension  =       ( isset( $opts['extension'] ) ? $opts['extension'] : "markdown" );
    $renderer   =       ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_edit'); 

    # We only ever want to edit one file at once...
    if( is_array( $parameters['file'] ) ) {
        $parameters['file'] = array_pop( $parameters['file'] );
    }

    $dirified_file      = dirify( $parameters['file'] );
    $position           = numeric_or( $parameters['position'], 0 );
    $preview_position   = numeric_or( $parameters['preview_position'], 0 );

    $already_exists = false;
    $existing_contents = '';
    $existing_commit = '';
    if( file_or( $dirified_file, null) && git_file_exists( $dirified_file ) ) {
        $already_exists = true;

        $head_commit = git_file_head_commit( $dirified_file );
        $existing_commit = $head_commit;

        $view_contents = git_view( $dirified_file, $head_commit );

        if( isset( $view_contents["$head_commit:$dirified_file"] ) ) {
            $existing_contents = $view_contents["$head_commit:$dirified_file"];
        }
    }

    if( 
        $already_exists &&
        isset( $parameters['existing_commit'] ) && 
        $parameters['existing_commit'] != $existing_commit 
    )  {
        return _gen_cherrypick(
            array( 
                "file"                  =>  $dirified_file,
                "conflicting_contents"  =>  $parameters['edit_contents'] 
            )
        );


        # die( "You are committing to a different version than what you started with! ($existing_commit != " . $parameters['existing_commit'] . ")" );
    }

    # $show = gen_show( $existing_commit );
    $rendered_contents = '';
    if( $parameters['edit_contents'] ) {
        $rendered_contents = _display( $parameters['file'], $parameters['edit_contents'], null, false, true ) ;
    }


    if( $already_exists && !$parameters['edit_contents']  ) {
        $parameters['edit_contents'] = $existing_contents;
    }


    $puck = array(
        'parameters'                    =>  &$parameters,
        'extension'                     =>  &$extension,
        'already_exists'                =>  &$already_exists,
        'existing_contents'             =>  &$existing_contents,
        'existing_commit'               =>  &$existing_commit,
        # 'rendered_existing_contents'    =>  _display( $parameters['file'], $existing_contents ),
        'rendered_edit_contents'        =>  &$rendered_contents,
    );



    return render( $renderer, $puck ) .  perf_exit( "_gen_edit" );

}
*/

function _gen_edit( $opts = array() ) {
    perf_enter( "_gen_markdown_edit" );

    $parameters =       ( isset( $opts['parameters'] ) ? $opts['parameters'] : array() );
    $extension  =       ( isset( $opts['extension'] ) ? $opts['extension'] : "markdown" );
    $do_preview =       ( isset( $opts['do_preview'] ) ? $opts['do_preview'] : false );
    $renderer   =       ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_markdown_edit'); 

    # We only ever want to edit one file at once...
    if( is_array( $parameters['file'] ) ) {
        $parameters['file'] = array_pop( $parameters['file'] );
    }

    $dirified_file      = dirify( $parameters['file'] );
    $preview_position   = numeric_or( $parameters['preview_position'], 0 );
   
    $already_exists = false;
    $existing_contents = '';
    $existing_commit = '';
    if( file_or( $dirified_file, null) && git_file_exists( $dirified_file ) ) {
        $already_exists = true;

        $head_commit = git_file_head_commit( $dirified_file );
        $existing_commit = $head_commit;

        $view_contents = git_view( $dirified_file, $head_commit );

        if( isset( $view_contents["$head_commit:$dirified_file"] ) ) {
            $existing_contents = $view_contents["$head_commit:$dirified_file"];


        }
    }

    if( 
        $already_exists &&
        isset( $parameters['existing_commit'] ) && 
        $parameters['existing_commit'] != $existing_commit 
    ) {

        return _gen_cherrypick(
            array( 
                "file"                  =>  $dirified_file,
                "conflicting_contents"  =>  $parameters['edit_contents'] 
            )
        );
        // die( "You are committing to a different version than what you started with! ($existing_commit != " . $parameters['existing_commit'] . ")" );
    }

    # $show = gen_show( $existing_commit );
    $rendered_contents = '';

    if( isset( $parameters['edit_contents'] ) && $parameters['edit_contents'] != "" ) {
        $rendered_contents = _display( $parameters['file'], $parameters['edit_contents'], null, false, true ) ;

    } else {

        if( $do_preview ) {
            $rendered_contents = _display( $parameters['file'], $existing_contents, null, false, true ) ;
        }
    }

    # Load current content if the file already exists, but be careful
    # not to "double-append" Talk.talk appending timestamps if the user 
    # submits content in edit_contents
    if( $already_exists ) { 
        if( !$parameters['edit_contents'] ) {
            $parameters['edit_contents'] = $existing_contents;

            # Append a simple timestamp/user when this flag is set.
            if( $parameters['talk_append'] === true ) {
                $parameters['edit_contents'] .= talk_append_text();
            }
        }

    } else {
        if( $parameters['talk_append'] === true ) {
            $parameters['edit_contents'] .= talk_append_text();
        }
    }


    $puck = array(
        'parameters'                    =>  &$parameters,
        'extension'                     =>  &$extension,
        'already_exists'                =>  &$already_exists,
        'existing_contents'             =>  &$existing_contents,
        'existing_commit'               =>  &$existing_commit,
        # 'rendered_existing_contents'    =>  _display( $parameters['file'], $existing_contents ),
        'rendered_edit_contents'        =>  &$rendered_contents,
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_markdown_edit" );

}

function gen_commit( $parameters = array()  )  {
    perf_enter( "gen_commit" );

    if( !can( "commit", implode( ":", array( $parameters['file'] ) ) ) ) {
        return render( 'not_logged_in', array() );
    }


    return _gen_commit( 
        array( 
            'parameters'        => &$parameters,
            'renderer'          => 'gen_commit',
        ) 
    ) . perf_exit( "gen_commit" );
}

function _gen_commit( $opts = array() ) {
    perf_enter( "_gen_commit" );

    $parameters =       ( isset( $opts['parameters'] ) ? $opts['parameters'] : array() );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_commit'); 

    # We only ever want to edit one file at once...
    if( is_array( $parameters['file'] ) ) {
        $parameters['file'] = array_pop( $parameters['file'] );
    }

    $dirified_file = dirify( $parameters['file'] );

    $commit_message_addendums   = array();
    $commit_note_addendums      = array();

    $orig_word_count = 0;
    if(  git_file_exists( $dirified_file ) ) {
        $view_contents = git_view( $dirified_file, commit_or( $parameters['existing_commit'], 'HEAD' )  );

        foreach( $view_contents as $cf_tag => &$contents ) {
            list( $c, $f ) = explode(":", $cf_tag );

            if( $f == $dirified_file ) {
                $existing_contents = $contents;
                $existing_commit = $c;
                break;
            }
        }

        $orig_word_count = str_word_count( $existing_contents );
    }

    $new_word_count = str_word_count( $parameters['edit_contents'] );

    $word_count_text = ( $new_word_count - $orig_word_count > 0 ? "+" : "-" ) . abs( $new_word_count - $orig_word_count ) . " word" . ( ( $new_word_count - $orig_word_count ) == 1 ? "" : "s" );

    if( $parameters['commit_notes'] == null || $parameters['commit_notes'] == ""  ) {

        $commit_note_addendums[ WORD_COUNT_NOTES_REF ] = $word_count_text;

        $parameters['commit_notes'] = $word_count_text;
    } else {

        $commit_note_addendums[ WORD_COUNT_NOTES_REF ] = $word_count_text;

        if( INCLUDE_WORD_COUNT ) {

            $commit_message_addendums[] = $word_count_text;
        }
    }

    // Handling for grabbing the work_time from the latest draft
    $draft_name = draftify( 
        $_SESSION['usr']['name'], 
        $parameters['file'],
        commit_or( $parameters['existing_commit'], git_head_commit() )
    );

    $potential_prior_draft_path = DRAFT_DIR . '/' . $draft_name;

    # echo $potential_prior_draft_path;
    $draft_work_time = null;
        
    if(  file_exists( $potential_prior_draft_path ) ) {
            
        $prior_draft = unserialize( file_get_contents( $potential_prior_draft_path ) );

        # Add draft time to the prior total time recorded with
        # the draft
        $draft_work_time = ( isset( $prior_draft['work_time'] ) ? $prior_draft['work_time'] : 0 );

        $commit_note_addendums[ WORKING_TIME_NOTES_REF ] = short_time_diff( $prior_draft['work_time'], 0 );

        if( INCLUDE_WORK_TIME_IN_LOG ) {
            $commit_message_addendums[] = short_time_diff( $prior_draft['work_time'], 0 );
        }
    }
    // End work_time handling

    if( count( $commit_message_addendums ) > 0 ) {
        $parameters['commit_notes'] .= " (" . implode( ",", $commit_message_addendums ) . ")";
    }

    list( $ret, $ret_message ) = git_update_and_commit( 
        $dirified_file, 
        $parameters['existing_commit'],
        $_SESSION['usr']['git_user'],
        $parameters['edit_contents'], 
        $parameters['commit_notes'] 
    );

    if( !$ret ) {
        $parameters["submit"] =  "Preview";
        $parameters["error_message"] =  $ret_message;

        return gen_edit( $parameters );

    } else {

        $possible_drafts = _draft_glob(
            draftify( 
                $_SESSION['usr']['name'],
                $parameters['file'],
                '*'
            )
        );

        if( $possible_drafts !== false && count( $possible_drafts ) > 0 ) {
            foreach( $possible_drafts as $path ) {
                if( file_exists( $path  ) ) {
                    unlink( $path );
                }
            }
        }

        # Add any notes to the commit
        if( count( $commit_note_addendums ) > 0 ) {
            $latest_commit = git_file_head_commit( $dirified_file, false );

            if( commit_or( $latest_commit, false ) === false ) {
                die( "Unable to get latest commit for file just committed: '$latest_commit'" );
            } else {
                foreach( $commit_note_addendums as $note_ref => &$note ) {
                    if( $note_ref == "" || $note == "" ) {
                        continue;
                    }

                    git_commit_append_note( $latest_commit, $note, $note_ref );

                }
            }
        }

        # Rebuild associations, if necessary
        if( ASSOC_ENABLE ) {
            require_once( dirname( __FILE__ ) . '/assoc.php');

            build_assoc( $dirified_file );
        }


        if( $parameters['submit'] == "Commit" ) {
            header( "Location: index.php?file=" . $parameters['file'] . "" );
        } else {
            $parameters["submit"] =  "Preview";
            $parameters["error_message"] =  "Successfully saved '" . excerpt( $parameters['commit_notes'], 100 ) . "'";
            $parameters["commit_notes"] =  "";
            # die(  git_file_head_commit( $parameters['file'] ) );
            # echo $parameters["existing_commmit"];

            $parameters["existing_commit"] =  git_file_head_commit( $dirified_file );

            return gen_edit( $parameters );

        }
    }
}

function talk_append_text() {
    return "\n\n" 
        . '----'
        . "\n\n"
        . '### '
        . strftime( "(%Y/%m/%d %H:%M:%S) " )
        . $_SESSION['usr']['name']
        . "\n\n\n\n"
    ;
}

?>
