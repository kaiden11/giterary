<?php 
require_once( dirname( __FILE__ ) . '/config.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/alias.php');
require_once( dirname( __FILE__ ) . '/cache.php');

function is_git_initialized() {
    git_lint();

    static $is_initalized;

    if( isset( $is_initialized ) ) {
        return $is_initialized;
    }

    $git_dir = GIT_REPO_DIR;

    if( file_exists( "$git_dir/.git" ) && 
        file_exists( "$git_dir/.git/config" ) && 
        file_exists( "$git_dir/.git/description" )
        # More? Less?
    ) {
        $is_initialized = true;
        return $is_initialized;
    }

    $is_initialized = false;
    return $is_initialized;

}

function git_lint() {
    perf_enter( 'git_lint' );
    $git_path = GIT_PATH;
    $git_dir = GIT_REPO_DIR . "/.git";

    static $valid;

    if( isset( $valid ) ) {
        perf_exit( 'git_lint' );
        return $valid;
    }

    if( !file_exists( $git_dir ) ) {
        die( "'$git_dir' does not appear to exist!" );
    }

    if( !file_exists( $git_path ) ) {
        die( "Path for GIT_PATH does not exist: '$git_path'" );
    }

    if( !is_executable( $git_path ) ) {
        die( "Path for GIT_PATH is not executable: '$git_path'" );
    }

    $valid = true;

    perf_exit( 'git_lint' );

    return $valid;
}


function git( $command, &$output = "", $env = null, $debug = null, $suppress_error_return = false ) {

    perf_enter( "git" );
    # Establish paths
    $git_path = GIT_PATH;
    $git_dir = GIT_REPO_DIR . "/.git";
    $git_work_tree = GIT_REPO_DIR;

    $verb = trim( array_shift( preg_split( "/\s+/", $command ) ) );

    perf_enter( "git.$verb" );

    # git_lint( $git_path, $git_dir );
    git_lint();

    if( !is_git_initialized() ) {
        die( "GIT_REPO_DIR does not point to an initialized Git repo: '$git_dir'" );
    }

    $git_cmd = GIT_PATH . " --git-dir=$git_dir --work-tree=$git_work_tree $command";

    # echo get_caller_method( 2 ) . ": suppress error return? " . ( $suppress_error_return ? 'true' : 'false' ) . "\n";

    if( $debug === true ) {
        echo "\n$git_cmd\n";
    }

    list( $result, $out ) = _call( $git_cmd, $env );

    $output .= $out;
    
    if ($result !== 0 && !$suppress_error_return ) {
    	echo(
            "<h1>Error</h1>\n<pre>\n"
    	    . "" . he( $git_cmd ) . "\n"
    	    . he( $out ) . "\n"
    	    . "Return code: " . $result . "\n"
    	    . "From: " . get_caller_method(1 ) . "\n"
    	    . "</pre>"
        );
    }

    perf_exit( "git.$verb" );
    perf_exit( "git" );

    return array( "return_code" => $result, "out" => $out );;
}

function git_head_commit( $cache = true ) {
    static $output = null;

    perf_enter( 'git_head_commit' );

    $memoize_key = 'HEAD';

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_head_commit', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_head_commit" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_head_commit" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_head_commit.cache_miss" );
    }

    $output = "";

    git( "rev-parse HEAD", $output );

    $output = trim( $output );

    // git( "rev-list -n1 HEAD -- " . escapeshellarg( $file ), $output );
    if( CACHE_ENABLE ) {
        perf_exit( "git_head_commit.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_head_commit', $memoize_key, $output );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $output );
            }
        }
    }


    perf_exit( 'git_head_commit' );

    return $output;
}

function git_file_head_commit( $file, $cache = true ) {
    perf_enter( 'git_file_head_commit' );
    $output = '';

    if( is_array( $file ) ) {
        $file = array_shift( $file );
    }

    # This file does not exist, therefore, we cannot
    # return any sort of head commit.

    $memoize_key = path_to_filename( $file );

    # echo $memoize_key;

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'file_head_commit', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_file_head_commit" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_file_head_commit" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "query.cache_miss.git_file_head_commit" );
    }

    // TODO: Condiser caching this.
    git("log -n1 --format='%H' -- " . escapeshellarg( $file ), $output );

    $output = trim( $output );

    // git( "rev-list -n1 HEAD -- " . escapeshellarg( $file ), $output );
    if( CACHE_ENABLE ) {
        perf_exit( "query.cache_miss.git_file_head_commit" );
    }

    // Try to cache our results if we're being
    // asked to do so
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'file_head_commit', $memoize_key, $output );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $output );
            }
        }
    }

    return $output . perf_exit( 'git_file_head_commit' );
}

function git_users_list( ) {

    $ret = array();
    $output = '';

    git( "log --format='%aN'", $output );

    foreach( explode( "\n", $output ) as $line ) {
        if( $line != "" ) {
        
            if( !isset( $ret[ $line ] ) ) {
                $ret[ $line ] = 0;
            }

            $ret[ $line ]++;
        }
    }

    return $ret;
}

function git_blame( $file ) {

    $ret = array();
    $output = '';

    git( "blame --follow -lt -- " . escapeshellarg( $file ), $output );

    foreach( explode( "\n", $output ) as $line ) {
        if( $line != "" ) {

            $commit = $author = $timestamp = $timezone = $line_number = $line_in_question = '';
            list( $commit, $rest ) = explode( " ", $line, 2 );

            $matches = array();
            if( preg_match( '/\(([^\)]*)\)/', $rest, $matches ) ) {

                $name_and_date_components = preg_split( '/\s+/', $matches[1] );

                list( $timestamp, $timezone, $line_number ) = array_splice( $name_and_date_components, -3, 3 );

                $author = join( " ", $name_and_date_components );
            }

            list( $prefix, $line_in_question ) = preg_split( "/\) /", $line, 2 );
        
            $ret[] = array(
                'commit'            => $commit,
                'author'            => $author,
                'timestamp'         => $timestamp,
                'timezone'          => $timezone,
                'line_number'       => $line_number,
                'line_in_question'  => $line_in_question
            );
        }
    }

    return $ret;
}

function git_file_get_contents( $file, $as_of_commit = null ) {

    perf_enter( 'git_file_get_contents' );
    $ret = false;

    if( !isset( $file ) || $file == "" ) { die( "Cannot pass unset file into git_file_get_contents" ); }

    $file = dirify( $file );

    $being_lazy = false;
    if( is_null( $as_of_commit ) ) {
        $being_lazy = true;
        $as_of_commit = git_head_commit();
    }

    if( git_file_exists( $file, $as_of_commit ) ) {
        $hc = ( $being_lazy ? git_file_head_commit( $file ) : $as_of_commit );

        $view = git_view( $file, $hc );

        $ret = $view[ "$hc:$file" ];
    }

    perf_exit( 'git_file_get_contents' );

    return $ret;
}

function git_view( $file, $commit = null, $retrieve_contents = true, $cache = true ) {

    perf_enter('git_view');

    # perf_enter( "git_view.$file" );
    # perf_exit( "git_view.$file" );


    if( is_null( $file ) || $file == "" ) {
        if( isset( $commit ) && $commit != "" ) {
            $files = git_commit_file_list( $commit );

            if( count( $files ) > 0 ) {
                $file = $files;
            }

        } else {
            die( "Cannot view unknown file." );
        }
    }

    if( !is_array( $file ) ) {
        $file = array( $file );
    }



    # $file = dirify( $file );


    $commit_file_array = array();
    foreach( $file as $f ) {


        if( !is_null( $f ) ) {
            $d = dirify( $f );

            # $hist = git_history( 1, $f );
            if( is_null( $commit ) || $commit == "" ) {
                # Set latest commit for file as 
                # commit

                $commit = commit_or( 
                    git_file_head_commit( $f ),
                    git_head_commit()
                );

            }

            if( git_file_exists( $d, $commit )  ) {
                $commit_file_array[] = implode( ":", array( $commit, $f ) );
            }
        }
    }

    sort( $commit_file_array );
    $memoize_key = serialize( $commit_file_array );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                $r = decache( 'git_view', $memoize_key );

                if( !is_null( $r ) ) {

                    perf_exit( "git_view" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_view" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_view.cache_miss" );
    }


    $ret = array();

    foreach( $commit_file_array as $t ) {

        list( $c, $f ) = explode( ":", $t, 2 );

        if( $retrieve_contents === true ) {
            $ret[$t] = git_view_show_helper( $c, $f );
        } else {
            $ret[$t] = '';
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_view.cache_miss" );
    }

    // Try to cache our results if we're being
    // asked to do so
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {

            if( is_bool( $cache ) ) {
                encache( 'git_view', $memoize_key, $ret );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit('git_view');

    return $ret;
}

function git_view_show_helper( $commit, $file, $cache = true ) {

    perf_enter( 'git_view_show_helper' );

    $output = '';

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_view_show_helper', "$commit:$file" );

                if( !is_null( $r ) ) {

                    perf_exit( 'git_view_show_helper' );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], "$commit:$file" );

                if( !is_null( $r ) ) {
                    perf_exit( 'git_view_show_helper' );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "query.cache_miss.git_view_show_helper" );
    }


    git("show " . escapeshellarg( "$commit:$file" ) , $output );

    if( CACHE_ENABLE ) {
        perf_exit( "query.cache_miss.git_view_show_helper" );
    }

    // Try to cache our results if we're being
    // asked to do so
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {

            if( is_bool( $cache ) ) {
                encache( 'git_view_show_helper', "$commit:$file", $output );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], "$commit:$file", $output );
            }
        }
    }

    perf_exit( 'git_view_show_helper' );

    return $output;
}

function git_show( $commit = null, $cache = true ) {
    $output = '';
    $ret = array();

    perf_enter( "git_show" );

    $separator = '->>-';
    $format = join(
        $separator, 
        array(
            '%H', # Hash 
            '%P', # parent hash
            '%T', # time
            '%an', # author name
            '%ae', # author email
            '%aD', # author date
            '%at', # author date (unix)
            '%s', # subject
            '%n%B%N', # body, notes
        )
    );

    if( is_null( $commit ) ) {
        $commit = git_head_commit();
    }

    if( commit_or( $commit, false ) === false ) {
        print_r( debug_backtrace() );
        // echo perf_print();
        die( "Unable to deal with parameters to git_show: '$commit'\n" );
    }

    $memoize_key = $commit;

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_show', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_show" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_show" );
                    return $r;
                }
            }
        }
    }
    
    if( CACHE_ENABLE ) {
        perf_enter( "git_show.cache_miss" );
    }
    
    $ret = git("show " . escapeshellarg( $commit ) . " --stat --pretty=format:" . escapeshellarg( $format ) , $output );

    if( $ret[ 'return_code' ] != 0 ) {
        # Unable to show commit
        return false;
    }

    $lines = explode( "\n", $output);

    $first = array_shift( $lines );

    list( $commit, $parent_commit, $tree_hash, $author_name, $author_email, $author_date, $author_date_epoch, $subject ) = explode( $separator, $first );

    $ret['commit']          = $commit;
    $ret['parent_commit']   = $parent_commit;
    $ret['tree_hash']       = $tree_hash;
    $ret['author_name']     = $author_name;
    $ret['author_email']    = $author_email;
    $ret['author_date']     = $author_date;
    $ret['author_date_epoch'] = $author_date_epoch;
    $ret['subject']         = $subject;

    $ret['body'] = '';

    foreach( $lines as $line ) {
        $ret['body'] .= "$line\n";
    }

    $ret['file_list'] = git_commit_file_list( $commit ) ;

    if( CACHE_ENABLE ) {
        perf_exit( "git_show.cache_miss" );
    }
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'git_show', $memoize_key, $ret );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( "git_show" );

    return $ret;
}

function git_line_diff( $commit_before = null, $commit_after = null, $file = null ) {

    if( $commit_before == null && $commit_after == null && is_array( $file ) && count( $file ) == 2 ) {

        return git_diff( 
            null, 
            null, 
            null, 
            " -U9999 --word-diff=porcelain --inter-hunk-context=9999 -- " 
                . escapeshellarg( $file[0] ) 
                . ' ' 
                . escapeshellarg( $file[1] ), 
            true 
        );

    } else {
        return git_diff( 
            $commit_before, 
            $commit_after, 
            $file, 
            " -U9999 --word-diff=porcelain --inter-hunk-context=9999" 
        );
    }
}


function git_diff( $commit_before = null, $commit_after = null, $file = null, $git_diff_opts = null, $suppress = false ) {
    $output = '';

    # $env = array(
    #     'GIT_EXTERNAL_DIFF' => SRC_DIR . '/difftool/tool.sh'
    # );

    if( is_null( $git_diff_opts ) ) {
        $git_diff_opts = "--word-diff=plain   -U9999 --inter-hunk-context=9999 --diff-algorithm=patience";
    }


    if( is_null( $commit_before ) && is_null( $commit_after )  && is_null( $file ) ) {
        git("diff $git_diff_opts", $output, $env, false, $suppress );
    }

    if( is_null( $commit_before ) && is_null( $commit_after ) && !is_null( $file ) && $file != "" ) {
        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        foreach( $file as $f ) {
            git("diff $git_diff_opts -- " . escapeshellarg( $f ), $output, $env );
        }
    }

    if( !is_null( $commit_before ) && $commit_before != "" && !is_null( $commit_after ) && $commit_after != "" && is_null( $file ) ) {
        git("diff $git_diff_opts " . escapeshellarg( $commit_before ) . " " . escapeshellarg(  $commit_after ), $output, $env );
    }


    if( !is_null( $commit_before ) && $commit_before != "" && 
        !is_null( $commit_after ) && $commit_after != "" && 
        !is_null( $file ) && $file != "" ) 
    {
        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        foreach( $file as $f ) {
            // echo "$commit_before $commit_after $f";
            git("diff $git_diff_opts " . escapeshellarg( $commit_before ) . " " . escapeshellarg( $commit_after ) . " -- " . escapeshellarg( $f ), $output, $env );
        }
    }


    return $output;

}

function git_file_diff( $file_a, $file_b, $git_diff_opts = null, $suppress = true ) {
    $output = '';

    # $env = array(
    #     'GIT_EXTERNAL_DIFF' => SRC_DIR . '/difftool/tool.sh'
    # );

    if( is_null( $git_diff_opts ) ) {
        $git_diff_opts = "--no-index --word-diff=plain -U9999 --inter-hunk-context=9999";
    }

    if( is_null( $file_a ) || is_null( $file_b ) ) {
        return 'Invalid arguments';
    }

    // Establish full path on files to account for "no-index" being
    // set
    $fa = dirify( $file_a );
    $fb = dirify( $file_b );
    if( !has_directory_prefix( GIT_REPO_DIR, $fa ) ) {
        $fa = GIT_REPO_DIR . '/' . $fa;
    }

    if( !has_directory_prefix( GIT_REPO_DIR, $fb ) ) {
        $fb = GIT_REPO_DIR . '/' . $fb;
    }

    $cmd = "diff $git_diff_opts " 
        . " -- " 
        . escapeshellarg( $fa ) 
        . ' '
        . escapeshellarg( $fb ) 
    ;

    git(
        $cmd,
        $output,
        $env,
        false,
        $suppress
    );

    return $output;

}

function git_notes_list( $ref = null ) {

    perf_enter( 'git_notes_list' );

    $output = '';
    $ret = null;

    $git_cmd = 'notes';
    $git_verb = 'list';

    $git_ret = array();
    if( $ref != null ) {
        $git_ret = git(
            "$git_cmd --ref " . escapeshellarg( $ref ) . " $git_verb",
            $output,
            null,
            null,
            true
        );
    } else {
        $git_ret = git(
            "$git_cmd $git_verb",
            $output,
            null,
            null,
            true
        );
    }

    if( $git_ret && isset( $git_ret['out'] ) && $git_ret['out'] != '' ) {

        $ret = array();
        
        foreach( preg_split( "/(\r)?\n/", $git_ret['out'] ) as $line ) {
            if( $line == "" ) {
                continue;
            }

            list( $note_id, $object_id ) = explode( " ", $line );

            if( $object_id ) {
            
                $ret[] = array( 
                    'notes'     =>  git_notes( $object_id, $ref ),
                    'commit'    =>  git_show( $object_id )
                );
            }
        }
    }

    if( $ret ) {
        usort( 
            $ret,
            function( $a, $b ) {
                return $a['commit']['author_date_epoch'] - $b['commit']['author_date_epoch'];

            }
        );
    }

    return $ret;
}

function git_notes( $commit = null, $ref = null, $cache = true ) {

    perf_enter( 'git_notes' );

    $output = '';
    $ret = null;

    $git_cmd = 'notes';
    $git_verb = 'show';

    if( commit_or( $commit, false ) === false ) {
        $commit = git_head_commit();
    }

    $memoize_key = implode( ".", array_map( function( $a ) { return ( $a != null ? $a : 'commits' ); }, array( $commit, $ref ) ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_note', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_notes" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_notes" );
                    return $r;
                }
            }
        }
    }
    
    if( CACHE_ENABLE ) {
        perf_enter( "git_notes.cache_miss" );
    }


    $git_ret = array();
    if( $ref != null ) {
        $git_ret = git(
            "$git_cmd --ref " . escapeshellarg( $ref ) . " $git_verb " . escapeshellarg( $commit ), 
            $output,
            null,
            null,
            true
        );
    } else {
        $git_ret = git(
            "$git_cmd $git_verb " . escapeshellarg( $commit ), 
            $output,
            null,
            null,
            true
        );
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_notes.cache_miss" );
    }


    perf_exit( 'git_notes' );

    if( $git_ret['return_code'] != 0 ) {
        $ret = false;
    } else {
        $ret = $git_ret['out'];
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_note', $memoize_key, $ret );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    return $ret;

}

function _either_case( $matches ) {
    return '[' . strtolower( $matches[0] ) . strtoupper( $matches[0] ) . ']';
}


function git_glob( $pattern, $case_sensitive = true, $cache = true  ) {
    $ret = array();
    $output = "";

    perf_enter( 'git_glob' );

    $memoize_key = implode( ':', array( git_head_commit(), $pattern, $case_sensitive ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_glob', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_glob" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_glob" );
                    return $r;
                }
            }
        }
    }
    
    if( CACHE_ENABLE ) {
        perf_enter( "git_glob.cache_miss" );
    }

    if( !$case_sensitive ) {

        # $pattern = preg_replace( '[a-zA-Z]', "either_case", $pattern );
        $pattern = preg_replace_callback( '/[a-zA-Z]/', "_either_case", $pattern );
    }


    // list( $ret_code, $ret_message ) = git( "ls-files -- " . escapeshellarg( $pattern ) , $output, null, false );
    list( $ret_code, $ret_message ) = git( "ls-files -- " . escapeshellarg( $pattern ), $output, null, false );

    if( $ret_code == 0 ) {

        foreach( preg_split( "/(\r)?\n/", $output ) as $line ) {
            if( $line != "" && file_or( $line, false ) !== FALSE ) {
                $ret[] = $line;
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_glob.cache_miss" );
    }
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_glob', $memoize_key, $ret );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_glob' );

    return $ret;
}


function git_search( $pattern ) {

    $ret = array();
    $output = "";

    // This might get out of hand, but there's no way to grab a case insensitive
    // filename search purely from git (and not relying on a pipe to grep, etc.

    // list( $ret_code, $ret_message ) = git( "ls-files -- " . escapeshellarg( $pattern ) , $output, null, false );
    list( $ret_code, $ret_message ) = git( "ls-files", $output, null, false );

    if( $ret_code == 0 ) {

        foreach( preg_split( "/(\r)?\n/", $output ) as $line ) {
            if( $line != "" && file_or( $line, false ) !== FALSE && strpos( strtolower( dirify( $line ) ), strtolower( dirify( $pattern ) ) ) !== FALSE ) {
                $ret[] = $line;
            }
        }
    }

    return $ret;
}

function git_mv_renames( $file, $new_file ) {

    $ret = array();
    $output = '';

    if( !( $file = file_or( $file, false ) ) || !git_file_exists( $file ) ) {
        die( "$file does not appear to be a valid file!" );
    }

    if( ( $new_file = file_or( $new_file, false ) ) !== false && git_file_exists( $new_file ) ) {
        die( "$new_file appears to already exist, cannot replace." );
    }

    list( $ret_code, $ret_message )  = git( "mv --dry-run -- " . escapeshellarg( $file ) . " " . escapeshellarg( $new_file ), $output, null, false );

    $i = 0;
    foreach( preg_split( '/\r?\n/', $output ) as $line ) {
        $i++;
        if( $i <= 1 ) {
            // SKip the first line
            continue;
        }

        $match = null;

        if( preg_match( '/^Renaming (.+) to (.+)$/', $line, $match ) == 1 ) {

            // Renaming..       ...to
            $ret[ $match[1] ]   =       $match[2];

        }
    }

    return $ret;

}

function git_mv( $file, $new_file, $author, $commit_notes = "Moving file." ) {

    $ret = false;
    $output = '';

    if( !file_or( $file, false ) || !git_file_exists( $file ) ) {
        die( "$file does not appear to be a valid file!" );
    } 

    if( file_or( $new_file, false ) && git_file_exists( $new_file ) ) {
        die( "$new_file appears to already exist, cannot replace." );
    }

    list( $ret_code, $ret_message )  = git( "mv -- " . escapeshellarg( $file ) . " " . escapeshellarg( $new_file ), $output, null, true );

    # print_r( $ret_code );
    # print_r( $ret_message );

    if( $ret_code != 0 ) {
        $ret = false;
        git( "reset --hard", $output, null, false );
    } else {
        git_commit( $author, $commit_notes );
        $ret = true;
    }

    if( CACHE_ENABLE ) {
        // Clear all git_file_exists information
        # clear_cache( 'git_file_exists', null );
        if( !is_dirifile( $file ) ) {
            // Clear cache for original location
            clear_all_caches( $file );
        }
        # clear_cache( 'git_file_head_commit', null );
    }


    return array( $ret, $output );

}

function git_rm( $file, $author, $commit_notes = "Deleted file(s)." ) {

    $ret = false;
    $output = '';

    if( !file_or( $file, false ) || !git_file_exists( $file ) ) {
        die( "$file does not appear to be a valid file!" );
    } else {

        $result = git( "rm -r -- " . escapeshellarg( $file ), $output, null );
        $ret_code = $result['return_code'];


        if( $ret_code != 0 ) {
            $ret = false;
        } else {

            git_commit( $author, $commit_notes );
            $ret = true;
        }
    }

    if( CACHE_ENABLE ) {
        // Clear all git_file_exists information
        clear_all_caches( $file );
    }

    return array( $ret, $output );

}

function git_revert( $commit, $author, $commit_notes = "Deleted file(s)." ) {

    $ret = false;
    $output = '';

    if( !commit_or( $commit, false ) ) {
        die( "$commit not appear to be a valid commit!" );
    } else {

        $result = git( "revert --no-edit --no-commit " . escapeshellarg( $commit ), $output, null );
        $ret_code = $result['return_code'];


        if( $ret_code != 0 ) {
            $ret = false;
        } else {

            git_commit( $author, $commit_notes );
            $ret = true;
        }
    }

    return array( $ret, $output );

}

function git_head_files( $cache = true ) {

    perf_enter( 'git_head_files' );

    $output = '';
    $ret = array();

    $hc = git_head_commit();

    $memoize_key = implode( ':', array( $hc ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_head_files', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_head_files" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_head_files" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_head_files.cache_miss" );
    }

    git( "ls-tree -r --name-only '$hc'", $output );


    foreach( preg_split( "/\r?\n/", $output ) as $line ) {
        if( file_or( $line, false ) ) {
            $ret[] = $line;
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_head_commit.cache_miss" );
    }
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_head_files', $memoize_key, $ret );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_head_files' );

    return $ret;
}


function git_ls_tree( $file, $commit = null, $recursive = false, $directories_only = false, $cache = true ) {

 
    perf_enter( 'git_ls_tree' );
    $ret = array();

    $commit = commit_or( $commit, git_head_commit() );

    # print_r( $file );

    $file = rtrim( file_or( $file, null ), '/' );

    $output = '';

    $args = implode( 
        " ", 
        array_filter(
            array(
                ( $recursive        === true ? "-r" : false ),
                ( $directories_only === true ? "-d" : false ),
            ),
            function( $a ) {
                return $a !== false;
            }
        )
    );

    $memoize_key = $commit . ":" . set_or( $file, 'NULL' ) . $args;

    # echo $memoize_key;


    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_ls_tree', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_ls_tree" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_ls_tree" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_ls_tree.cache_miss" );
    }

    if( $file == null || $file == "" || $file == DEFAULT_FILE . "." . DIRIFY_SUFFIX ) {
        git( "ls-tree " . $args . " "  . escapeshellarg( $commit ), $output );
    } else {
        git( "ls-tree " . $args . " "  . escapeshellarg( $commit ) . ' ' . escapeshellarg( "$file/" ), $output, null );
    }
    

    foreach( explode("\n", $output ) as $line ) {
        if( $line != "" ) {

            $row = explode( " ", $line, 3 );

            if( count( $row ) >= 3 ) {

                # print_r( $row );

                list( $_object, $_file ) = explode( "\t", $row[2] );

                $ret[] = array( 
                    "mode"      =>  $row[0],
                    "type"      =>  $row[1],
                    "object"    =>  $_object,
                    "file"      =>  $_file
                );
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_ls_tree.cache_miss" );
    }
    
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_ls_tree', $memoize_key, $ret );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_ls_tree' );


    return $ret;
}

function git_commit_file_list( $commit = null, $cache = true ) {

    perf_enter( "git_commit_file_list" );

    if( !isset( $commit ) ){
        $commit = git_head_commit();
    }

    $output = "";

    $memoize_key = $commit;

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_commit_file_list', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_commit_file_list" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_commit_file_list" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_commit_file_list.cache_miss" );
    }    

    git("show --name-only --pretty=format:'' $commit ", $output  );
    
    $lines = explode( "\n", $output );

    array_shift( $lines );
    array_pop( $lines );

    if( CACHE_ENABLE ) {
        perf_exit( "git_commit_file_list.cache_miss" );
    }
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'git_commit_file_list', $memoize_key, $lines );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $lines );
            }
        }
    }



    perf_exit( "git_commit_file_list" );

    return $lines;

}

function git_file_rev_list( $file, $cache = true ) {

    perf_enter( 'git_file_rev_list' );
    $file = file_or( $file, false );

    if( $file === false ) {
        return false;
    }

    $file = dirify( $file );

    if( is_dirifile( $file ) ) {
        return false;
    }

    $hc = commit_or( git_file_head_commit( $file ), false );

    if( $hc === false ) {
        return false;
    }

    $memoize_key = implode( ".", array( $file, $hc ) ); 

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_file_rev_list', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( 'git_file_rev_list' );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( 'git_file_rev_list' );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_file_rev_list.cache_miss.$memoize_key" );
    }

    $rev_list = git_rev_list( array( 'path' => $file ) );

    if( CACHE_ENABLE ) {
        perf_exit( "git_file_rev_list.cache_miss.$memoize_key" );
    }

    perf_exit( 'git_file_rev_list' );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'git_file_rev_list', $memoize_key, $rev_list );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $rev_list );
            }
        }
    }

    return $rev_list;
}

function git_file_prior_commit( $file, $commit ) {
    
    $rev_list = git_file_rev_list( $file );

    if( $rev_list === false || !is_array( $rev_list ) )  {
        return false;
    }

    $index = array_search( $commit, $rev_list );

    if( $index === false ) {
        return false;
    }

    if( ($index+1) >= count( $rev_list ) ) {
        return false;
    } else {
        return $rev_list[ $index+1 ];
    }
}

function git_rev_list( $opts = array() ) {

    $ret = array();

    $output = '';

    $reverse    = ( isset( $opts['reverse'] ) && is_bool( $opts['reverse'] ) ? $opts['reverse'] : false );
    $notes      = ( isset( $opts['notes'] ) && is_bool( $opts['notes'] ) ? $opts['notes'] : false );
    $path       = ( isset( $opts['path'] ) && isset( $opts['path'] ) ? $opts['path'] : false );

    $cmd = "rev-list --branches" 
        . ( 
            $reverse === true 
            ?
                ' --reverse' 
            : 
                '' 
        )
        . ( 
            $notes === true 
            ?
                ' --notes' 
            : 
                ' --no-notes' 
        )
        . ( 
            $path !== false 
            ?
                ' -- ' . escapeshellarg( $path ) 
            : 
                ''
        )
    ;

    # echo $cmd;

    git(
        $cmd, 
        $output  
    );

    foreach( preg_split( "/\r?\n/", $output ) as $line ) {
        if( $line == '' ) {
            continue;
        }

        $ret[] = $line;
    }

    return $ret;
}

function git_commit_append_note( $commit, $note, $ref = null ) {

    $git_cmd = "notes";
    $git_verb = "append";

    if( commit_or( $commit, false ) === false ) {
        die( "Invalid commit passed to git_commit_append_notes: '$commit'" );
    }

    if( $note == null || $note == "" ) {
        die( "Passed in an invalid note: '$note'" );
    } else {

        $commit_notes_file = tempnam( TMP_DIR , "tmp" );

        file_put_contents( $commit_notes_file, $note );

        $output = '';
        $ret = null;

        if( $ref == null ) {
            $ret = git( 
                "$git_cmd $git_verb " 
                . escapeshellarg( $commit ) 
                . " --file " . escapeshellarg( $commit_notes_file ), 
                $output 
            );

        } else {
            $ret = git( 
                "$git_cmd --ref " 
                . escapeshellarg( $ref ) 
                . " $git_verb " 
                . escapeshellarg( $commit ) 
                . " --file " . escapeshellarg( $commit_notes_file ), 
                $output 
            );
        }

        if( CACHE_ENABLE ) {
            $memoize_key = implode( ".", array_map( function( $a ) { return ( $a != null ? $a : 'commits' ); }, array( $commit, $ref ) ) );

            $cleared = clear_cache( 'git_note', $memoize_key );
        }

        return $ret;
    }
}

function git_commit( $author, $commit_notes ) {

    # $orig_file = tempnam( TMP_DIR , "tmp" );
    $ret = false;
    $ret_message = '';

    # Attempt file locking when performing a commit
    $fp = null;
    $fp = fopen( GIT_REPO_DIR . "/.git/config", 'r' );
    if( $fp !== false ) {
        flock( $fp, LOCK_EX );
    }

    $commit_notes_file = tempnam( TMP_DIR , "commit_notes" );

    file_put_contents( $commit_notes_file, $commit_notes );

    $output = "";


    $commit_ret = git("commit --no-verify --file=" . escapeshellarg( $commit_notes_file ) . " --author=" . escapeshellarg( author_or( $author, "Unknown <unknown@unknown.com>" ) ) );

    if( $commit_ret['return_code'] != 0 ) {
        $ret_message = $commit_ret['out'];
    } else {
        $ret = true;
    }

    if( CACHE_ENABLE ) {
        clear_cache( 'git_head_commit', 'HEAD' );
    }

    $u = unlink( $commit_notes_file );

    # Attempt file locking when performing a commit
    if( $fp !== false ) {
        flock( $fp, LOCK_UN );
    }

    if( !$u ) {
        die( "Commit succeeded, but unable to delete '$commit_notes_file'" );
    }


    return array( $ret, $ret_message );

}

function git_file_exists( $file, $commit = 'HEAD', $cache = true ) {

    perf_enter( 'git_file_exists' );

    $ret = false;

    $latest_commit = ( is_null( $commit ) || $commit === 'HEAD' );

    $file = dirify( $file );


    // Make use of the cache, while trying to
    // not duplicate cache file entries
    if( $commit == "HEAD" ) {

       
        # This seems a little strange, but we need *a*
        # commit, and having nothing messes with the cache's
        # ability to establish a key. If we can't find a head
        # commit for a given file, choose the SHA sum for
        # the repository's head commit (but not 'HEAD' itself)
        $commit = commit_or( 
            git_file_head_commit( $file ), 
            git_head_commit()
        );
    }

    $memoize_key = "$commit:$file";

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_file_exists', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_file_exists" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_file_exists" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_file_exists.cache_miss.$memoize_key" );
    }

    # We want to cheat at this point. If a commit isn't specified ($latest_commit === true ),
    # and having the working directory at the HEAD commit, we can make some assumptions:
    #  - Anything present in the working directory can be said to exist
    #  - Anything not present in the working directory can be said to not exist
    #
    # So, if we are only interested in the latest commit (we usually are), we can do a 
    # single file_exists, skipping the git repo. If we were interested in a specific
    # commit, we can't cheat in this way.

    if( $latest_commit ) {

        perf_enter( "git_file_exists.cheating.$file" );
        $ret = file_exists( GIT_REPO_DIR . "/" . $file );
        perf_exit( "git_file_exists.cheating.$file" );

    } else {

        $output = '';
        $call_return = git( "ls-tree -r " . escapeshellarg( $commit ) . " -- " . escapeshellarg( $file ), $output, null, null, true );

        if( $call_return['return_code'] == 0 && strlen( $output ) >= 1 ) {
            
            $ret = true;

        } else {

            $ret = false;
        }
    }


    if( CACHE_ENABLE ) {
        perf_exit( "git_file_exists.cache_miss.$memoize_key" );
    }

    perf_exit( 'git_file_exists' );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'git_file_exists', $memoize_key, $ret );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }


    return $ret;

}

function git_is_working_directory_clean() {

    $output = null;

    $diff_ret = git( "status --porcelain", $output );

    $output = trim( $output );

    return $output == "";

}

function git_update_and_commit( $file, $existing_commit, $author, $contents, $commit_notes ) {
    GLOBAL $git_author_pattern;

    $ret = false;
    $ret_message = '';

    $needs_backout = false;
    $is_new = false;

    if( $commit_notes == null || $commit_notes == ""  ) {
        $ret_message['error_message'] = "You must have some commit notes for the change you're making.";
        return array( $ret, $ret_message );
    } 

    $potential_author = author_or( $author, false );

    if( $potential_author === false ) {
        die( "The author '$potential_author' does not appear to be a valid git author." );
    }
    $author = $potential_author;

    # if( $_SESSION['usr']['name'] == "jrhoades" ) {
    #     $author = "Blah <blah@something";
    # }

    # If this is valid path, but doesn't exist yet...
    if( file_or( $file, null ) != null && !git_file_exists( $file ) ) {

        $dir_to_create = dirname( GIT_REPO_DIR . "/$file" );
        if( !is_dir( $dir_to_create ) ) {
            # Create our directory 
            if( !mkdir( $dir_to_create, 0777, true ) ) {
                $ret_message = "Unable to create directory!";
                return array( $ret, $ret_message );
            }
        }

        touch( GIT_REPO_DIR . "/$file" );
        $is_new = true;
    }

    if( $is_new ) {
        $needs_backout = true;
    
        if( file_put_contents( GIT_REPO_DIR . "/$file", $contents ) === false ) {
            die( "Unable to write to " . GIT_REPO_DIR . "/$file" );            
        }
    
        # Add the new version of the file to the index.
        $add_ret = git( "add " . escapeshellarg( $file ) );

        if( $add_ret['return_code'] != 0 ) {
            $ret_message = $add_ret['out'];
        } else {

            list( $commit_ret, $commit_ret_message ) = git_commit( $author, $commit_notes );

            if( !$commit_ret ) {
                $ret_message = $commit_ret_message;
            } else {
                $ret = true;
            }
        }
    } else {

        # If this file exists, we need to check to make sure we're committing
        # against the commit we expect
        // $history = git_history( 1, $file );
        // $history_record = array_shift( $history );

        $file_head_commit = commit_or( git_file_head_commit( $file ), 'HEAD' );

        // if( commit_or( $history_record['commit'], 'HEAD' ) != $existing_commit ) {

        if( $file_head_commit != $existing_commit ) {
            $ret_message = "You are committing against a different version than when you began.";
            return array( $ret, $ret_message );
        }

        $view_ret = git_view( $file, $existing_commit );
        
        # print_r( $view_ret );
        # print $existing_commit;
        # print $file;
        
        if( !isset( $view_ret["$existing_commit:$file"] ) ) {
            $ret_message = "Commit does not exist for this file!";
        } else {
            if( md5( $view_ret["$existing_commit:$file"] ) == md5( $contents ) ) {
                $ret_message = "No changes to the file!";
            } else {
                # make a copy of the original, out of the way.
                # copy( GIT_REPO_DIR . "/$file", $orig_file );
        
                $needs_backout = true;
        
                if( file_put_contents( GIT_REPO_DIR . "/$file", $contents ) === false ) {
                    die( "Unable to write to " . GIT_REPO_DIR . "/$file" );            
                }

                # file_put_contents( $commit_notes_file, $commit_notes );
        
                # Add the new version of the file to the index.
                $add_ret = git( "add " . escapeshellarg( $file )  );
        
                if( $add_ret['return_code'] != 0 ) {
                    $ret_message = $add_ret['out'];
                } else {
        
                    list( $commit_ret, $commit_ret_message ) = git_commit( $author, $commit_notes );

                    if( !$commit_ret ) {
                        $ret_message = $commit_ret_message;
                    } else {
                        $ret = true;
                    }
                }
            }
        }
    }

    if( !$ret && $needs_backout ) {
        # Return the file back to its original state
        # copy( $orig_file, GIT_REPO_DIR . "/$file" );
        $reset_ret = git( "reset HEAD -- " . escapeshellarg( $file ) );

        if( $reset_ret['return_code'] != 0 ) {
            $ret_message = $reset_ret['out'];
        }
    }

    if( $ret && CACHE_ENABLE ) {

        $cleared = array();
        
        $cleared = array_merge( $cleared,   clear_all_caches( $file ) );
        // Should be taken care of my git_commit
        // $cleared = array_merge( $cleared,   clear_cache( 'git_head_commit', 'HEAD' ) );

        # print_r( $cleared );

        # clear_cache( 'file_head_commit', path_to_filename( $file ) );
        # clear_cache( 'git_file_exists', "HEAD:$file" );
    }

    # unlink( $orig_file );
    # unlink( $commit_notes_file );

    return array( $ret, $ret_message );

}

function git_work_stats( $files ) {

    $ret = array();

    if( is_array( $files ) ) {
        // Not supporting multiple files yet
        $files = $files[0];
    }

    if( INCLUDE_WORK_TIME ) {
        $hist = git_history( 1000, $files );
        $work = array();

        foreach( $hist as $key => $commit ) {
            if( !isset( $work[ $commit['author'] ] ) ) {
                $work[ $commit['author'] ] = array( 
                    'seconds'               =>  0,
                    'commits'               =>  0,
                    'commits_with_stats'    =>  0
                );
            }

            $work[ $commit['author'] ][ 'commits' ] += 1;

            if( ( $n = git_notes( $commit['commit'], WORKING_TIME_NOTES_REF ) ) !== false ) {

                $work[ $commit['author'] ][ 'commits_with_stats' ] += 1;
                $work[ $commit['author'] ][ 'seconds' ] += from_short_time_diff( $n );
            }
        }

        $ret = $work;
    }

    return $ret;

}

function git_history( $num = 100, $file = null, $author = null, $since = null, $skip = 0, $cache = true  ) {

    perf_enter( 'git_history' );

    if( !is_numeric( $num ) ) {
        $num = 100;
    }


    $output = "";

    $separator = '->>-';
    $format = join(
        $separator, 
        array(
            '%H',
            '%T',
            '%an',
            '%ae',
            '%aD',
            '%s',
            '%P',
            '%at',
        )
    );

    $hc = git_head_commit();

    $memoize_key = implode( " ", array( $hc, $num, json_encode( $file ), $author, $since, $skip, $format ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_history', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_history" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "git_history" );
                    return $r;
                }
            }
        }
    }
    
    if( CACHE_ENABLE ) {
        perf_enter( "git_history.cache_miss" );
    }
   
    if( !is_null( $author ) ) {
        git("log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) . " --name-only --pretty=format:" . escapeshellarg( $format ) . " --author=" . escapeshellarg( $author ) . " -- ", $output );
    } else {

        if( !is_null( $since ) ) {
            git("log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) . " --name-only --pretty=format:" . escapeshellarg( $format )  . ' ' . escapeshellarg( $since . "~..HEAD" )  , $output );

        } else {
    
            if( is_null( $file ) || $file == "" ) {
                git("log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) . " --name-only --pretty=format:" . escapeshellarg( $format ), $output );
            } else {

                if( is_array( $file ) ) {
                    git(
                        "log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) 
                        . " --name-only --pretty=format:" . escapeshellarg( $format ) 
                        . " -- " . join(
                            ' ', 
                            array_map( 
                                function( $a ) { 
                                    return escapeshellarg( $a );  
                                },
                                $file
                            )
                        ), $output
                    );

                } else {
                    # echo "here: $skip";
                    # print_r("log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) . " --follow --name-only --pretty=format:" . escapeshellarg( $format ) . " -- " . escapeshellarg( $file ), $output );
                    git("log --no-notes -n " . escapeshellarg( $num ) . " --skip " . escapeshellarg( $skip ) . " --follow --name-only --pretty=format:" . escapeshellarg( $format ) . " -- " . escapeshellarg( $file ), $output );
                }
            }
        }
    }
    
    $history = array();
    $historyItem = array();
    $found_header = false;

    $lines = preg_split( '/\r?\n/' , $output );

    while( !is_null( ( $line = array_shift( $lines ) ) ) ) {
        $log_entry = explode( $separator, $line );

        # Header line?
        if( count( $log_entry ) > 1 ) {
            $history_item = null;
            $history_item = array(
                "author"            =>  $log_entry[2], 
                "email"             =>  $log_entry[3],
                "linked-author"     =>  (
                                            $log_entry[3] == "" ?  $log_entry[2] : "<a href=\"mailto:$log_entry[3]\">$log_entry[2]</a>"
                                        ),
                "date"              =>  from_git_time( $log_entry[4] ), 
                "date_orig"         =>  $log_entry[4],
                "message"           =>  $log_entry[5],
                "commit"            =>  $log_entry[0],
                "parent_commit"     =>  $log_entry[6],
                "epoch"             =>  $log_entry[7]
            );

            # While there are additional lines, check if the next line
            # is a "header." If not, check if it looks like a file. If so
            # add that file to the history item, and keep checking
            # until you run out of lines, or you peek the next "header."
            while( count( $lines ) > 0 ) {
            
                if( ( substr_count( current( $lines ), $separator ) > 1 ) ) {
                    # Stop processing file list, we found another header.
                    break;
                } else {

                    # Process this line as a commit content file
                    $curr = array_shift( $lines );

                    if( $curr != "" && file_or( $curr, null ) != null ) {
                        # Looks to be content files...
                        if( !isset( $history_item["pages"] ) ) {
                            $history_item["pages"] = array( trim( $curr ) );
                        } else {
                            $history_item["pages"][] = trim( $curr );
                        }
                    }
                }
            }

            # Add this history item
            $history[] = $history_item;

        } else {
            # Do nothing
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_history.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_history', $memoize_key, $history );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $history );
            }
        }
    }

    perf_exit( 'git_history' );

    return $history;
}


function from_git_time( $date ) {

    # Tue, 17 Jul 2012 01:59:12 -0400
    # return date_parse_from_format( "D, j M Y H:i:s O" , $date );
    $ret = strptime( $date, "%a, %d %b %Y %H:%M:%S" );

    $match = array();
    if( preg_match( '/^\s*([+-])([0-9]{2})([0-9]{2})$/', $ret['unparsed'], $match  ) > 0 ) {
       
        $ret['tm_tz_offset'] = $ret['unparsed'];

        $pos_neg = 1;
        if( $match[1] == "-" ) {
            $pos_neg = -1;
        }

        $tz_second_offset = $pos_neg*( $match[2]*3600 + $match[3]*60 );
        $ret['tm_second_offset'] = $tz_second_offset;
    }

    return $ret;

}

function dt_display( &$commit, $fmt = null  ) {
    $dt = ( is_array( $commit['date'] ) ? $commit['date'] : from_git_time( $commit['date'] ) );

    if( isset( $commit['epoch'] ) ) {
        $dt_epoch = $commit['epoch'];

    } else {
        $dt_epoch = mktime( 
            $dt['tm_hour'],
            $dt['tm_min'], 
            $dt['tm_sec'], 
            $dt['tm_mon']+1, 
            $dt['tm_mday'], 
            $dt['tm_year']+1900 
        );
    }

    $diff = short_time_diff( $dt_epoch, time() );

    if( $fmt == null ) {

        $now = localtime( time(), true );

        if( $dt['tm_year'] != $now['tm_year'] ) {
            $fmt = '%Y-%m-%d';
        } else {
            if( $dt['tm_mon'] != $now['tm_mon'] ) {
                $fmt = '%Y-%m-%d';
            } else {
                if( $dt['tm_mday'] != $now['tm_mday'] ) {
                    $fmt .= '%m-%d %H:%M';
                } else {
                    $fmt .= '%H:%M:%S';
                }
            }
        }
    }


    return strftime(
        '<span title="%Y-%m-%d %H:%M:%S ' . $dt['tm_tz_offset'] . '">' . $fmt . " $diff</span>",
        $dt_epoch
    );
}

function git_whatlinkshere( $file ) {

    $file = file_or( $file, false );

    if( $file === false ) {
        return array();
    } else {

        # \[\[\(Cursor/Haven\|Cursor.dir/Haven\)\(|\([^]]\+\)\)\?\]\]

        $pattern = "\[\[\(" . preg_quote( undirify( $file ), '/' ) . "\|" . preg_quote( dirify( $file ), '/' ) . "\)\(|\([^]]\+\)\)\?\]\]";

        return git_grep( $pattern, true );
    }

}

function git_annotations( $file = null, $cache = false ) {

    perf_enter( 'git_annotations' );

    # $pattern = "\({\[^}\]\+}\)";
    $pattern = '\({[^\}]\+}\)\(([^)]\+)\|\[[^]]\+\]\)';

    if( USE_LIBPCRE_FOR_GIT_GREP ) {
        $pattern = '(?<!\!)(\{[^\}]+\})(\([^)]+\)|\[[^]]+\])';
    }

    $hc = git_head_commit();

    $memoize_key = implode( ":", array( $pattern, $hc, $file ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_annotations', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_annotations" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_annotations" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_annotations.cache_miss" );
    }

    $ret = git_grep( $pattern, true, $file );

    if( ANNOTATORJS_ENABLE ) {

        $anno_files = git_glob( '*.dir/' . ANNOTATORJS_FILE );

        foreach( $anno_files as $anno_file ) {

            $content = git_file_get_contents( $anno_file );
            $annotations = json_decode( $content, true );
            $ret[ $anno_file ] = array(
                'count' =>  count( $annotations ),
                'match' =>  array_map(
                                function( $a ) {
                                    return $a['quote'];
                                },
                                $annotations
                            ),
                'type'  =>  'external'
            );
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_annotations.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_annotations', $memoize_key, $ret );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_annotations' );

    return $ret;
}

function git_todo_count( $commit = null, $cache = true ) {

    perf_enter( 'git_todo_count' );
    $commit = commit_or( $commit, git_head_commit() );

    $memoize_key = $commit;

    // echo "memoize ke: $memoize_key $commit";

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_todo_count', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_todo_count" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_todo_count" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_todo_count.cache_miss" );
    }

    $todos = git_todos();

    $ret = 0;

    foreach( $todos as $file => &$result ) {
        $ret += $result['count'];
    }

    // git( "rev-list -n1 HEAD -- " . escapeshellarg( $file ), $output );
    if( CACHE_ENABLE ) {
        perf_exit( "git_todo_count.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_todo_count', $memoize_key, $ret );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_todo_count' );

    return $ret;
}


function git_todos( $file = null, $cache = true ) {

    perf_enter( 'git_todos' );
    $pattern = "\\b\(TODO\|TBD\|CB\)\(:\)\?.*";
    # $pattern = "\(TODO\|TBD\)\(:\).*";

    if( USE_LIBPCRE_FOR_GIT_GREP ) {
        $pattern = "\\b(?<![!=\/+])(TODO|TBD|CB)(:)?.*";
        # $pattern = "(?<!\!)(TODO|TBD)(:).*";
    }

    # " . preg_quote( undirify( $file ) ) . "\|" . preg_quote( dirify( $file ) ) . "\)\(|\([^]]\+\)\)\?\]\]";

    $hc = git_head_commit();

    $memoize_key = implode( ":", array( $pattern, $hc, $file ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_todos', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_todos" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_todos" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_todos.cache_miss" );
    }

    $ret = git_grep( 
        $pattern, 
        true, 
        $file, 
        false   // Case insensitive: false
    );

    if( CACHE_ENABLE ) {
        perf_exit( "git_todos.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_todos', $memoize_key, $ret );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $ret );
            }
        }
    }

    perf_exit( 'git_todos' );
    return $ret;
}

function git_grep_output( $output, $as_regex = false, $term = ''  ) {

    $matched_files = array();


    return $matched_files;


}

function git_all_meta( $cache = true ) {
    GLOBAL $git_meta_header_pattern;

    perf_enter( 'git_all_meta' );

    $output = "";

    $memoize_key = git_head_commit();

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_all_meta', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_all_meta" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_all_meta" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_all_meta.cache_miss" );
    }


    git(
        "grep " .  " -I --perl-regexp -e " .  escapeshellarg( 
            $git_meta_header_pattern
        ),
        $output, 
        null, 
        null, 
        true 
    );

    $matched_meta = array();

    if( strlen( $output) > 0 ) {
        foreach( preg_split( "/(\r)?\n/", $output  ) as $line ) {

            if( $line == "" ) {
                continue;
            }

            list( $_file, $match ) = explode( ":", $line, 2 );

            $header = array();
            metaify( $match, $_file, $header );

            foreach( $header as $key => $values ) {

                if( !isset( $matched_meta[$key] ) ) {
                    $matched_meta[$key] = array();
                } 

                if( !isset( $matched_meta[$key][$_file] ) ) {
                    $matched_meta[$key][$_file] = array();
                } 

                if( is_array( $values ) ) {
                    $matched_meta[$key][$_file] = array_merge( 
                        $matched_meta[$key][$_file],
                        $values
                    );
                } else {
                    $matched_meta[$key][$_file][] = $value;
                }

            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_all_meta.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_all_meta', $memoize_key, $matched_meta );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $matched_meta );
            }
        }
    }

    perf_exit( 'git_all_meta' );

    return $matched_meta;
}



function git_all_tags( $cache = true ) {
    GLOBAL $tag_pattern;

    perf_enter( 'git_all_tags' );

    $output = "";

    $memoize_key = git_head_commit();

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_all_tags', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_all_tags" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "git_all_tags" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_all_tags.cache_miss" );
    }


    git(
        "grep " .  " -Ii " .  escapeshellarg( $tag_pattern ),
        $output, 
        null, 
        null, 
        true 
    );

    $matched_tags = array();

    if( strlen( $output) > 0 ) {
        foreach( preg_split( "/(\r)?\n/", $output  ) as $line ) {

            if( $line == "" ) {
                continue;
            }

            list( $_file, $match ) = explode( ":", $line, 2 );

            if( !isset( $matched_tags[$match] ) ) {
                $matched_tags[$match] = array();
            } 

            $matched_tags[$match][] = $_file;
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_all_tags.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_all_tags', $memoize_key, $matched_tags );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $matched_tags );
            }
        }
    }

    perf_exit( 'git_all_tags' );

    return $matched_tags;
}


function _git_meta( $all_meta, $meta = array(), $pathspec = null, $cache = true ) {

    GLOBAL $php_meta_header_pattern;
    # $php_meta_header_pattern = '@^(!)?(%([^%:]+?):\s*(.+?)\s*)$@';

    perf_enter( '_git_meta' );

    if( !$all_meta ) {
        return array();
    }

    if( count( $meta ) <= 0 ) {
        # must filter by at least 1 tag
        return array();
    }

    $matched_files = array();
    foreach(  $meta as $m ) {

        // Check for whether we're querying on a meta header value
        // as well as the presence of the key
        $mv = false;
        $match = array();
        if( preg_match( $php_meta_header_pattern, "%$m", $match ) == 1 ) {
            $m  = $match[ 3 ];
            $mv = $match[ 4 ];
        }

        if( isset( $all_meta[ $m ] ) ) {

            foreach( $all_meta[ $m ] as $_file => &$values ) {

                # TODO: Match on pathspec, ignore if we don't belong
                # to the proper path prefix
                # has_prefix( ... )

                if( $mv !== false ) {
                    if( !in_array( $mv, $values ) ) {
                        continue;
                    }
                }

                if( !isset( $matched_files[ $_file ] ) ) {
                    $matched_files[ $_file ] =  array( 
                        'count'         => 0, 
                        'match'         => array(),
                        'type'          => 'contents match',
                        'all_tags'      =>  array()
                    );
                }

                
                $matched_files[$_file]['count']++;

                if( $mv !== false ) {
                    $matched_files[$_file]['match'][ "$m:$mv" ] = $values;
                } else {
                    $matched_files[$_file]['match'][ $m ] = $values;
                }
            }
        }
    }

    # Must match *all* tags queried
    $matched_files = array_filter(
        $matched_files,
        function( $a ) use( $meta ) {
            return ( count( $a['match'] ) == count( $meta ) );
        }
    );

    foreach( $all_meta as $m => &$files ) {
        foreach( $files as $file => $values ) {
            if( isset( $matched_files[ $file ] ) ) {
                $matched_files[ $file ][ 'all_meta' ][] = $m;
            }
        }
    }


    perf_exit( '_git_meta' );

    return $matched_files;
}


function _git_tags( $all_tags, $tags = array(), $pathspec = null, $cache = true ) {

    perf_enter( '_git_tags' );

    if( !$all_tags ) {
        return array();
    }

    if( count( $tags ) <= 0 ) {
        # must filter by at least 1 tag
        return array();
    }

    $matched_files = array();
    foreach(  $tags as $t ) {

        if( isset( $all_tags[ "~$t" ] ) ) {

            foreach( $all_tags[ "~$t" ] as $_file ) {

                # TODO: Match on pathspec, ignore if we don't belong
                # to the proper path prefix
                # has_prefix( ... )


                if( !isset( $matched_files[ $_file ] ) ) {
                    $matched_files[ $_file ] =  array( 
                                                    'count'         => 0, 
                                                    'match'         => array(),
                                                    'type'          => 'contents match',
                                                    'all_tags'      =>  array()
                                                );
                }

                $matched_files[$_file]['count']++;
                $matched_files[$_file]['match'][] = "~$t";

            }
        }
    }

    # Must match *all* tags queried
    $matched_files = array_filter(
        $matched_files,
        function( $a ) use( $tags ) {
            return ( count( $a['match'] ) == count( $tags ) );
        }
    );

    foreach( $all_tags as $t => &$files ) {
        foreach( $files as $file ) {
            
            if( isset( $matched_files[ $file ] ) ) {
                $matched_files[ $file ][ 'all_tags' ][] = $t;
            }
        }
    }

    # print_r( $all_tags );

    perf_exit( '_git_tags' );

    return $matched_files;
}

function git_tags( $tags = array(), $pathspec = null, $cache = true ) {


     perf_enter( 'git_tags' );
    // perf_enter( 'git_tags.' . implode( ',', $tags ) . '.' . $pathspec  );
    $output = '';

    $matched_files = array();

    $hc = git_head_commit();

    $memoize_key = serialize( array( $hc, $tags, $pathspec ) );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'git_tags', $memoize_key );

                if( !is_null( $r ) ) {

                    perf_exit( 'git_tags.' . implode( ',', $tags ) . '.' . $pathspec  );
                    // perf_exit( "git_tags" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( 'git_tags.' . implode( ',', $tags ) . '.' . $pathspec  );
                    // perf_exit( "git_tags" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "git_tags.cache_miss" );
    }

    # git grep -Ii --all-match  -e "^~hashtag\s*$" --or -e "^~something\s*"
    $tag_patterns = array();
    foreach( $tags as $t ) {
        if( tag_or( $t, false ) !== false ) {
            $tag_patterns[] = "-e " . escapeshellarg( "^~$t\s*$" );
        }
    }

    if( ( $pathspec = file_or( $pathspec, null ) ) != null ) {
        $pathspec = rtrim( $pathspec, "/" );

        if( $pathspec == DEFAULT_FILE ) {
            // Search the whole repository
            $pathspec = null;
        } else {

            // Search on both the undirified and dirififed path specs
            if( strpos( $pathspec, "/" ) !== FALSE ) {
                $pathspec = dirname( $pathspec ) . "/" . undirify( basename( $pathspec ), true );
            } else {
                $pathspec = undirify( $pathspec, true );
            }
        }
    }

    if( count( $tag_patterns ) > 0 ) {
        git("grep " .  
            " -Ii --all-match " . 
            implode( ' --or ', $tag_patterns ) .
            ( $pathspec == null ? "" : " -- " . escapeshellarg( dirify( $pathspec ) ) . ' ' . escapeshellarg( dirify( $pathspec, true ) ) )
            ,  
            $output, null, null, true );
    }

    if( strlen( $output) > 0 ) {
        foreach( preg_split( "/(\r)?\n/", $output  ) as $line ) {

            if( $line == "" ) {
                continue;
            }

            list( $_file, $match ) = explode( ":", $line, 2 );

            if( !isset( $matched_files[$_file] ) ) {

                $matched_files[$_file] = array( 
                    'count' =>  0,
                    'match' =>  array(),
                    'type'  =>  'contents match'
                );
            }

            $matched_files[$_file]['count']++;
            $matched_files[$_file]['match'][] = $match;
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "git_tags.cache_miss" );
    }

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( 'git_tags', $memoize_key, $matched_files );
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $matched_files );
            }
        }
    }

    perf_exit( 'git_tags' );

    // perf_exit( 'git_tags.' . implode( ',', $tags ) . '.' . $pathspec  );

    return $matched_files;
}


function git_grep( $term, $as_regex = false, $file = null, $case_insensitive = true ) {


    $output = '';

    $matched_files = array();

    $use_libpcre = false;
    if( USE_LIBPCRE_FOR_GIT_GREP ) {
        $use_libpcre = true;
    }

    if( $case_insensitive !== true ) {
        $case_insensitive = false;
    }

    # perf_log( 'git.grep.log' );

    if( $term != null && $term != "" ) {

        $file = file_or( $file, false );

        if( $file === false ) {
            git( 
                "grep " 
                    .  ( !$as_regex ? "-F" : "" ) 
                    . " " 
                    . ( $as_regex && $use_libpcre ? "--perl-regexp" : "" ) 
                    . ( $case_insensitive ? ' -i ' : '' )
                    . " -I -e " 
                . escapeshellarg( $term ),  
                $output, 
                null, 
                null, 
                true 
            );
        } else {
            git(
                "grep " 
                    . ( !$as_regex ? "-F" : "" ) 
                    . " " 
                    . ( $as_regex && $use_libpcre ? "--perl-regexp" : "" ) 
                    . ( $case_insensitive ? ' -i ' : '' )
                    . " -I -e " 
                    . escapeshellarg( $term ) 
                    . ' -- ' 
                    . escapeshellarg( dirify( undirify( $file, true ) ) ) 
                    . ' ' 
                . escapeshellarg( dirify( $file, true ) ), 
                $output, 
                null, 
                null, 
                true 
            );
        }
    }

    if( strlen( $output) > 0 ) {
        foreach( preg_split( "/(\r)?\n/", $output  ) as $line ) {

            if( $line == "" ) {
                continue;
            }

            list( $_file, $match ) = explode( ":", $line, 2 );

            if( !isset( $matched_files[$_file] ) ) {

                $matched_files[$_file] = array( 
                    'count' =>  0,
                    'match' =>  array(),
                    'type'  =>  'contents match'
                );
            }

            $matched_files[$_file]['count']++;
            $matched_files[$_file]['match'][] = match_excerpt( 
                $match, 
                ( 
                    $as_regex ? 
                        # Massage the git-grep pattern to a PHP-grep pattern
                        # \(TODO\|TBD\)\(:\)\? -> (TODO|TBD)(:)?
                        # This is likely to be brittle, but alas, why git-grep
                        # differs terrible from other greps is frustrating to
                        # begin with.
                        # preg_replace( '@\\\\([\+\?]?)@', '$1', $term )
                        preg_replace( '@\\\([\(\)\+\?\|])@', '$1', $term )
                        :
                        $term
                ),
                50, 
                $as_regex 
            );
        }
    }

    return $matched_files;
}

# function dt_from_git_time( $date ) {
# 
#     return DateTime::createFromFormat( "D, j M Y H:i:s O" , $date );
# }

function cmp_git_date_r( $a_dt, $b_dt ) {
    return cmp_git_date( $b_dt, $a_dt );
}

function cmp_git_date( $a_dt, $b_dt ) {
    $a_dt = ( is_array( $a_dt['date'] ) ? $a_dt['date'] : from_git_time( $a_dt['date'] ) );
    $b_dt = ( is_array( $b_dt['date'] ) ? $b_dt['date'] : from_git_time( $b_dt['date'] ) );


    $a_epoch = mktime ( $a_dt['tm_hour'], $a_dt['tm_min'], $a_dt['tm_sec'], $a_dt['tm_mon']+1, $a_dt['tm_mday'], $a_dt['tm_year']+1900 ) + $a_dt['tm_second_offset'];
    $b_epoch = mktime ( $b_dt['tm_hour'], $b_dt['tm_min'], $b_dt['tm_sec'], $b_dt['tm_mon']+1, $b_dt['tm_mday'], $b_dt['tm_year']+1900 ) + $b_dt['tm_second_offset'];

   
    return ( $a_epoch == $b_epoch ? 0 : ( $a_epoch < $b_epoch ? -1 : 1 ) );
}

function commit_excerpt( $commit ) {
    return substr( $commit, 0, 6 );
}

function file_or( $file, $or = null, $current_file = null, $redirect = true ) {
    $ret = valid_file_or( $file, $or, $current_file );

    if( $ret === $or ) {
        return $ret;
    }

    if( ALIAS_ENABLE && $redirect === true ) {
        # Attempt an alias translation, if necessary
        $ret = file_alias( dirify( $ret ) );
    }

    return $ret;

}


?>
