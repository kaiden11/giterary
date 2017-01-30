<?
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/git.php' );
require_once( dirname( __FILE__ ) . '/display.php' );
require_once( dirname( __FILE__ ) . '/meta.php' );
require_once( dirname( __FILE__ ) . '/toc.php' );
require_once( dirname( __FILE__ ) . '/annotations.php' );
require_once( dirname( __FILE__ ) . '/assoc.php' );
require_once( dirname( __FILE__ ) . '/alias.php' );
require_once( dirname( __FILE__ ) . '/drafts.php' );
require_once( dirname( __FILE__ ) . '/html.php' );
require_once( dirname( __FILE__ ) . '/pandoc.php' );

function gen_history( $file = null, $author = null, $num = null, $since = null, $skip = null ) {

    perf_enter( "gen_history" );

    if( !can( "history", implode( ":", array( $file, $author, $num ) ) ) ) {
        return render('not_logged_in', array() );
    }

    return _gen_history( 
        array( 
            'file'              => &$file,
            'author'            => &$author,
            'num'               => &$num,
            'since'             => &$since,
            'skip'              => &$skip,
            'renderer'          => 'gen_history',
        ) 
    ) . perf_exit( "gen_history" );
}

function gen_pickaxe( $search ) {

    perf_enter( "gen_pickaxe" );

    if( !can( "history", implode( ":", array( $search ) ) ) ) {
        return render('not_logged_in', array() );
    }

    return _gen_pickaxe( 
        array( 
            'search'            =>  $search,
            'renderer'          =>  'gen_history',
        ) 
    ) . perf_exit( "gen_pickaxe" );
}


function gen_work_stats( $files ) {

    perf_enter( "gen_work_stats" );

    if( !can( "history", implode( ":", array( $files ) ) ) ) {
        return render('not_logged_in', array() );
    }

    $work_stats = git_work_stats( $files );

    $puck = array(
        'file'          =>  $files,
        'work_stats'    =>  &$work_stats
    );

    return render( 
        'gen_work_stats', 
        $puck
    ) . perf_exit( "gen_work_stats" );
}

function gen_list( $file_collection = array(), $args = array() ) {

    perf_enter( "gen_list" );

    return render( 
        'gen_list', 
        array(
            'file_collection'   =>  &$file_collection,
            'args'              =>  &$args
        )
    ) .  perf_exit( "gen_list" );

}


function gen_search( $term ) {
    perf_enter( 'gen_search' );

    $search = array();

    if( $term != null && $term != "" ) {

        $matches = array();
        if( preg_match( '@^/(.+)/$@', $term, $matches ) === 1 ) {

            $search = git_grep( 
                $matches[ 1 ],
                true    // as_regex = true
            ); 
        } else {
            $search = git_grep( 
                $term, 
                false    // as_regex = true
            ); 
        }


        $filename_matches = git_search( $term );

        foreach( $filename_matches as $g ) {

            if( array_key_exists( $g, $search ) ) {
                $search[$g]['type'] = "both";
            } else {
                $search[$g]['type'] = 'file name match';
            }
        }

        # Filter anything you can't read, or shouldn't
        # be searching.
        $tmp = array();
        foreach( $search as $filename => $dummy ) {

            if( ASSOC_ENABLE ) {
                if( has_directory_prefix( ASSOC_DIR, $filename ) ) {
                    continue;
                }
            }

            if( can( "read", $filename ) ) {
                $tmp[$filename] = $search[$filename];
            }
        }
        $search = $tmp;

        # print_r( $search );
        # print_r( $glob );
    }

    $puck = array(
        'search'    =>  &$search,
        # 'glob'      =>  &$glob
    );

    return render( 'gen_search', $puck ) .  perf_exit( "gen_search" );



}

function gen_blame( $file ) {
    perf_enter( 'gen_blame' );

    if( !can( "blame", $file ) ) {
        return render( 'not_logged_in', array() );
    }


    $blame = array();

    $file = dirify( $file );
    if( !git_file_exists( $file ) ) {
        return $blame;
    }

    $blame = git_blame( $file );

    $puck = array(
        'file'      => $file,
        'blame'    =>  &$blame,
    );

    return render( 'gen_blame', $puck ) .  perf_exit( "gen_blame" );



}

function gen_annotations( $file = null ) {
    perf_enter( 'gen_annotations' );

    if( !can( "annotations", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    $search = git_annotations( $file ); 

    /*
    //Will have to revisit how to do this at some point
    foreach( $search as $key => &$item )  {
        foreach( $item['match'] as $i => $match ) {
            $item['match'][$i] = todoify( $match );
        }
    }
    */

    if( CACHE_ENABLE ) {    // Otherwise, way too performance intensive

        foreach( $search as $file => &$result ) {
            
            $c = git_file_head_commit( $file );

            $result['latest_commit'] = $c;

            $show = git_show( $c );

            $result['author_date_epoch'] = $show['author_date_epoch'];
        }
    }



    $puck = array(
        'search'    =>  &$search,
        'glob'      =>  array()
    );

    return render( 'gen_annotations', $puck ) .  perf_exit( "gen_annotations" );

}


function gen_notes_list() {

    perf_enter( 'gen_notes_list' );

    $notes_list = git_notes_list( 
        COMMIT_RESPONSE_REF 
    );

    if( $notes_list == null && !is_array( $notes_list ) ) {
        $notes_list = array();
    } else {
        $notes_list = array_filter(
            $notes_list,
            function( $a ) {
                foreach( $a['commit']['file_list'] as $f ) {
                    if( !can( "read", $f ) ) {
                        return false;
                    }
                }

                return true;

            }
        );
    }

    $puck = array(
        'notes'     =>  &$notes_list,
    );

    return render( 'gen_notes_list', $puck ) .  perf_exit( "gen_notes_list" );



}

function gen_todo_hierarchy( ) {
    perf_enter( 'gen_todo_hierarchy' );

    if( !can( "todos", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    $search = git_todos( null );

    // Remove associative directories from the results.
    if( ASSOC_ENABLE ) {
       
        $assoc_entries = array();
        foreach( $search as $f => &$dummy ) {
            if( has_directory_prefix( ASSOC_DIR, $f ) ) {
                $assoc_entries[] = $f;
            }
        }
    
        foreach( $assoc_entries as $to_delete ) {
            unset( $search[ $to_delete ] );
        }
    }
    
    $dirs = array();
    
    foreach( $search as $f => &$match ) {

        if( can( "read", $f ) ) { // Try to make this marginally security aware
            $d = dirname( $f );
            if( !isset( $dirs[ $d ] ) ) {
                $dirs[ $d ] = 0;
            }
    
            $dirs[ $d ] = $dirs[ $d ] + $match['count'];
        }
    }
    
    $rollup = array();
    
    foreach( $dirs as $d => &$count ) {
        while( $d != "." && $d != '' && $d != '/' ) {
            if( !isset( $rollup[ $d ] ) ) {
                $rollup[ $d ] = 0;
            }
    
            $rollup[ $d ] = $rollup[ $d ] + $count;
    
            $d = dirname( $d );
        }
    }
    
    uksort(
        $rollup,
        function( $a, $b ) {
    
            return strcmp( $a, $b );
        }
    );

    $puck = array(
        'rollup'        =>  $rollup,
    );

    return render( 'gen_todo_hierarchy', $puck ) .  perf_exit( "gen_todo_hierarchy" );

    
}


function gen_todos( $file = null ) {
    perf_enter( 'gen_todos' );

    if( !can( "todos", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    $search = git_todos( $file ); 

    /*
    //Will have to revisit how to do this at some point
    foreach( $search as $key => &$item )  {
        foreach( $item['match'] as $i => $match ) {
            $item['match'][$i] = todoify( $match );
        }
    }
    */

    # print_r( $search );

    foreach( $search as $f => &$dummy ) {
        if( !can( "read", $f ) ) {
            unset( $search[ $f ] );
        }
    }


    if( CACHE_ENABLE ) {    // Otherwise, way too performance intensive

        foreach( $search as $f => &$result ) {
            
            $c = git_file_head_commit( $f );

            $result['latest_commit'] = $c;

            perf_log( "gen_todos.$f.$c" );

            $show = git_show( $c );

            $result['author_date_epoch'] = $show['author_date_epoch'];
        }
    }

    // Remove associative directories from the results.
    if( ASSOC_ENABLE ) {
       
        $assoc_entries = array();
        foreach( $search as $f => &$dummy ) {
            if( has_directory_prefix( ASSOC_DIR, $f ) ) {
                $assoc_entries[] = $f;
            }
        }

        foreach( $assoc_entries as $to_delete ) {
            unset( $search[ $to_delete ] );
        }
    }

    // Return all directories in the repo...
    $all_directories = git_ls_tree( null, null, true, true );

    $all_directories = array_filter(
        $all_directories,
        function( $a ) {
            if( ASSOC_ENABLE && has_directory_prefix( ASSOC_DIR, $a['file'] ) ) {
                return false;
            }

            if( ALIAS_ENABLE && has_directory_prefix( ALIAS_DIR, $a['file'] ) ) {
                return false;
            }
            
            return true;
        }
    );

    $all_directories = array_map(
        function( $a ) {
            return undirify( $a['file'], false );
        },
        $all_directories
    );

    $all_directories = array_values(
        $all_directories
    );


    # 0print_r( $search );

    $puck = array(
        'search'        =>  &$search,
        'glob'          =>  array(),
        'directories'   =>  &$all_directories,
        'file'          =>  $file
    );

    return render( 'gen_todos', $puck ) .  perf_exit( "gen_todos" );

}

function gen_timeline( $file, $title = 'Timeline' ) {
    perf_enter( 'gen_timeline' );

    if( !can( "timeline", $file ) ) {
        return render( 'not_logged_in', array() );
    }

    if( !is_array( $file ) ) {
        $file = array( $file );
    }

    $logs = array();

    $num = 1000;

    foreach( $file as $f ) {

        if( file_or( $f, false ) !== false && git_file_exists( $f ) ) {

            $logs[ $f ] = git_history( 1000, $f );

        }

    }

    $puck = array(
        'history'   =>  &$logs,
        'files'     =>  $file,
        'title'     =>  $title,
    );

    return render( 'gen_timeline', $puck ) .  perf_exit( "gen_timeline" );

}


function gen_meta( $meta = array(), $pathspec = null ) {
    perf_enter( 'gen_meta' );

    if( !can( "meta", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    if( count( $meta ) <= 0 ) {
        return gen_all_meta() . perf_exit( 'gen_meta' );;
    }

    # Make use of the existing tag results
    # in the cache.
    $all_meta = git_all_meta();

    # $search = git_tags( $tags, $pathspec ); 
    $search = _git_meta( $all_meta, $meta, $pathspec ); 

    foreach( $search as $f => &$dummy ) {
        if( !can( "read", $f ) ) {
            unset( $search[ $f ] );
        }
    }

    # print_r( $all_tags );

    $puck = array(
        'search'    =>  &$search,
        'glob'      =>  array(),
        'pathspec'  =>  $pathspec,
        'meta'      =>  $meta
    );

    return render( 'gen_meta', $puck ) .  perf_exit( "gen_meta" );

}


function gen_tags( $tags = array(), $pathspec = null ) {
    perf_enter( 'gen_tags' );

    if( !can( "tags", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    if( count( $tags ) <= 0 ) {
        return gen_all_tags() . perf_exit( 'gen_tags' );;
    }

    # Make use of the existing tag results
    # in the cache.
    $all_tags = git_all_tags();

    # $search = git_tags( $tags, $pathspec ); 
    $search = _git_tags( $all_tags, $tags, $pathspec ); 

    foreach( $search as $f => &$dummy ) {
        if( !can( "read", $f ) ) {
            unset( $search[ $f ] );
        }
    }

    # print_r( $all_tags );

    $puck = array(
        'search'    =>  &$search,
        'glob'      =>  array(),
        'pathspec'  =>  $pathspec,
        'tags'      =>  $tags
    );

    return render( 'gen_tags', $puck ) .  perf_exit( "gen_tags" );

}

function gen_all_tags( ) {
    perf_enter( 'gen_all_tags' );

    if( !can( "all_tags", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    $tags = git_all_tags();

    $puck = array(
        'tags'  =>  &$tags
    );

    return render( 'gen_all_tags', $puck ) .  perf_exit( "gen_all_tags" );

}

function gen_all_meta( ) {
    perf_enter( 'gen_all_meta' );

    if( !can( "all_meta", "" ) ) {
        return render( 'not_logged_in', array() );
    }

    $meta = git_all_meta();

    $puck = array(
        'meta'  =>  &$meta
    );

    return render( 'gen_all_meta', $puck ) .  perf_exit( "gen_all_meta" );

}




function gen_whatlinkshere( $file ) {
    perf_enter( 'gen_whatlinkshere' );

    if( !can( "whatlinkshere", $file ) ) {
        return render( 'not_logged_in', array() );
    }

    $search = array();
    $file = file_or( $file, false );

    if( $file !== false ) {

        $search = git_whatlinkshere( $file ); 
    }

    $puck = array(
        'search'    =>  &$search,
        'glob'      =>  array()
    );

    return render( 'gen_search', $puck ) .  perf_exit( "gen_whatlinkshere" );

}

function _gen_history( $opts = array() ) {

    perf_enter( "_gen_history" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $author =           ( isset( $opts['author'] ) ? $opts['author'] : null );
    $num =              ( isset( $opts['num'] ) ? $opts['num'] : 100 );
    $since =            ( isset( $opts['since'] ) ? commit_or( $opts['since'], null ) : null );
    $skip =             ( isset( $opts['skip'] ) ? numeric_or( $opts['skip'], 0 ) : 0 );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_history'); 

    if( !is_null( $file ) && $file != "" ) {
        $file = dirify( $file );
    }

    $history = git_history( $num, $file, $author, $since, $skip );

    $puck = array(
        'file'              =>  &$file,
        'num'               =>  &$num,
        'skip'              =>  &$skip,
        'history'           =>  &$history,
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_history" );
}

function _gen_pickaxe( $opts = array() ) {

    perf_enter( "_gen_pickaxe" );

    $search     =   ( isset( $opts['search'] ) ? $opts['search'] : null );
    $renderer   =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_history'); 

    $history = git_pickaxe( $search );

    $puck = array(
        'file'              =>  null,
        'num'               =>  50,
        'skip'              =>  0,
        'history'           =>  &$history,
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_history" );
}


function gen_diff( $commit_before = null, $commit_after = null, $file = null, $plain = false, $subtractions = true, $additions = true )  {
    perf_enter( "gen_diff" );

    if( !can( "diff", implode( ":", array( $commit_before, $commit_after, $file ) ) ) ) {
        return render( 'not_allowed', array() );
    }


    return _gen_diff( 
        array( 
            'file'              => &$file,
            'commit_before'     => &$commit_before,
            'commit_after'      => &$commit_after,
            'plain'             => $plain,
            'subtractions'      => $subtractions,
            'additions'         => $additions,
            'renderer'          => 'gen_diff',
        ) 
    ) . perf_exit( "gen_diff" );
}

function gen_file_diff( $file_a, $file_b, $plain = false, $subtractions = true, $additions = true )  {
    perf_enter( "gen_file_diff" );

    if( !can( "file_diff", implode( ":", array( $file_a ) ) ) ) {
        return render( 'not_logged_in', array() );
    }

    if( !can( "file_diff", implode( ":", array( $file_b ) ) ) ) {
        return render( 'not_logged_in', array() );
    }

    return _gen_file_diff( 
        array( 
            'file_a'            => &$file_a,
            'file_b'            => &$file_b,
            'plain'             => $plain,
            'subtractions'      => $subtractions,
            'additions'         => $additions,
            'renderer'          => 'gen_file_diff',
        ) 
    ) . perf_exit( "gen_file_diff" );
}


function gen_cherrypick( $commit_before = null, $commit_after = null, $file = null, $draft = null )  {
    perf_enter( "gen_cherrypick" );

    if( !can( "cherrypick", implode( ":", array( $commit_before, $commit_after, $file ) ) ) ) {
        return render( 'not_logged_in', array() );
    }


    return _gen_cherrypick(
        array( 
            'file'              => &$file,
            'commit_before'     => &$commit_before,
            'commit_after'      => &$commit_after,
            'draft'             => &$draft,
            'renderer'          => 'gen_cherrypick',
        ) 
    ) . perf_exit( "gen_cherrypick" );
}



function lineskip( $text, $n = 1 ) {
   
    $i = 0;
    while( $n > 0 && ( $i = strpos( $text, "\n", $i ) ) !== false ) {
        $i++;
        $n--;
    }

    return substr( $text, $i );
}

function _gen_cherrypick( $opts = array() ) {
    perf_enter( "_gen_cherrypick" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $commit_before =    ( isset( $opts['commit_before'] ) ? $opts['commit_before'] : null ); 
    $commit_after  =    ( isset( $opts['commit_after']  ) ? $opts['commit_after']  : null ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_cherrypick'); 

    $conflicting_contents   = set_or( $opts['conflicting_contents'],    false );
    $draft                  = set_or( $opts['draft'],                   false );

    # We need to know the list of files at this point if it's not sent...
    if( is_null( $file ) ) {
        $file = git_commit_file_list( $commit_after );
    } else {
        $file = array( dirify( $file ) );
    }

    $diff = array();

    if( $conflicting_contents !== false ) {

        if( ( $conflict_file = tempnam( TMP_DIR, "conflict" ) ) == false ) {
            die( "Unable to create temporary file to resolve conflict!" );
        } else {
            file_put_contents( $conflict_file, $conflicting_contents );

            $diff = git_line_diff( 
                null, 
                null, 
                array( 
                    $conflict_file, 
                    ( GIT_REPO_DIR . $opts['file'] ) 
                ) 
            );
        }

    } elseif( $draft !== false ) {


        // $diff = git_line_diff( $commit_before, $commit_after, $file );
        if( file_exists( DRAFT_DIR . "/" . $draft ) ) {


            $draft = unserialize( file_get_contents( DRAFT_DIR . "/" .  $draft ) );

            if( $draft['user'] != $_SESSION['usr']['name'] ) {
                die( "Draft user does not match session user" );
            } else {

                if( ( $draft_contents_path = tempnam( TMP_DIR, "draft_contents" ) ) == false ) {
                    die( "Unable to create temporary file to show draft diff!" );
                } else {


                    file_put_contents( $draft_contents_path, $draft['contents'] );

                    $comparison_path = ( GIT_REPO_DIR . '/' . dirify( $draft['filename'] ) );
                    // If the file doesn't exist, compare against an empty file.
                    if( !git_file_exists( $draft['filename'] ) ) {

                        $comparison_path = tempnam( TMP_DIR, "comparison_path_dummy" );
                        file_put_contents( $comparison_path, '' );
                    }

                    $diff = git_line_diff( 
                        null, 
                        null, 
                        array( 
                            $comparison_path,
                            $draft_contents_path
                        ) 
                    );
                }
            }
        }

    } else {

        $diff = git_line_diff( $commit_before, $commit_after, $file );

    }
    # $diff_before = '';
    # $diff_after = '';

    if( is_array( $file ) &&  count( $file ) > 1 ) {
        // Do nothing, we can't really render this file
        // well
    } elseif( is_array( $file ) && count( $file ) == 1 ) {
        // Try to render based on file name
        $file = array_shift( $file );

        # $diff = word_diff(
        #     lineskip( 
        #         $diff,
        #         5
        #     )
        # );

        function line_of_codify( $v ) {

            return "<span class=\"line-of-code\">" . $v . "\n</span>";
        }

        $porcelain = '';
        $diff = preg_split( "/\r?\n/", lineskip( $diff, 5 ) );
        $loc = '';

        foreach( $diff  as $line ) {
            $match = array();

            if(         preg_match( '/^ (.*)$/', $line, $match ) == 1 ) {

                $loc .= he( $match[1] );

            } elseif(   preg_match( '/^\+(.*)$/', $line, $match ) == 1 ) {

                $loc .= '<span class="diff add">' . he( $match[1] ) . '</span>';

            } elseif(   preg_match( '/^-(.*)$/', $line, $match ) == 1 ) {

                $loc .= '<span class="diff remove">' . he( $match[1] ) . '</span>';

            } elseif(   preg_match( '/^~$/', $line, $match ) == 1 ) {

                // $porcelain .= line_of_codify( $loc . '<span class="newline"></span>' );
                $porcelain .= line_of_codify( $loc . '' );
                $loc = '';

            } else {
                continue;
                // $porcelain .= 'what? ' . $line;
            }
        }

        $porcelain .= $loc;


        /*
        $diff = implode( 
            "", 
            array_map( 
                "line_of_codify",  
                preg_split( 
                    "/\n/", 
                    lineskip( 
                        $diff, 
                        5 
                    )
                )
            )
        );
        */

        # $diff_before = word_diff_before(
        #     $diff
        # );

        # $diff_after = word_diff_after(
        #     $diff
        # );

        # unset( $diff );
    }

    $puck = array(
        'file'              =>  &$file,
        'commit_before'     =>  &$commit_before,
        'commit_after'      =>  &$commit_after,
        'diff'              =>  &$porcelain,
        'is_conflict'       =>  ( $conflicting_contents !== false ),
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_cherrypick" );
}

function _gen_file_diff( $opts = array() ) {
    perf_enter( "_gen_file_diff" );

    $file_a         =   ( isset( $opts['file_a'] ) ? $opts['file_a'] : null );
    $file_b         =   ( isset( $opts['file_b'] ) ? $opts['file_b'] : null );
    $plain          =   ( isset( $opts['plain']  ) ? $opts['plain']  : false ); 
    $subtractions   =   ( isset( $opts['subtractions']  ) ? $opts['subtractions']  : true ); 
    $additions      =   ( isset( $opts['additions']  ) ? $opts['additions']  : true ); 
    $renderer       =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_file_diff'); 

    $diff = git_file_diff( $file_a, $file_b );

    if( !$plain ) {
        $diff = _display(
            $file_a,
            word_diff(
                lineskip( 
                    $diff,
                    5
                )
            )
        );
    } else {
        $d = '';

        foreach( preg_split( '/\r?\n/', lineskip( $diff, 5 ) ) as $line ) {
            $d .= '<div class="line-of-code">' . he( $line ) . '</div>';
        }

        $diff = word_diff( $d ); 

        $d = null;
    }

    $puck = array(
        'file_a'            =>  &$file_a,
        'file_b'            =>  &$file_b,
        'extension'         =>  detect_extension( $file, null ),
        'plain'             =>  &$plain,
        'subtractions'      =>  &$subtractions,
        'additions'         =>  &$additions,
        'diff'              =>  &$diff,
        'diff_count'        =>  &$diff_count
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_file_diff" );



}

function _gen_diff( $opts = array() ) {
    perf_enter( "_gen_diff" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $commit_before =    ( isset( $opts['commit_before'] ) ? $opts['commit_before'] : null ); 
    $commit_after  =    ( isset( $opts['commit_after']  ) ? $opts['commit_after']  : null ); 
    $plain  =           ( isset( $opts['plain']  ) ? $opts['plain']  : false ); 
    $subtractions  =    ( isset( $opts['subtractions']  ) ? $opts['subtractions']  : true ); 
    $additions  =       ( isset( $opts['additions']  ) ? $opts['additions']  : true ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_diff'); 

    # We need to know the list of files at this point if it's not sent...
    if( is_null( $file ) ) {    
        # TODO: This is questionable, considering before/after may not
        # be subsequent commits.
        # Need something like git_diff_file_list( $commit_before, $commit_after )
        $file = git_commit_file_list( $commit_after );
    } else {
        $file = array( dirify( $file ) );
    }

    $diff = git_diff( $commit_before, $commit_after, $file );
    # $diff_before = '';
    # $diff_after = '';

    if( is_array( $file ) &&  count( $file ) > 1 ) {


        // Present user with choice of files to diff
        return render( 
            'gen_diff_select', 
            array(
                'files'             =>  &$file,
                'commit_before'     =>  &$commit_before,
                'commit_after'      =>  &$commit_after,
            )
        ) .  perf_exit( "_gen_diff" );


    } elseif( is_array( $file ) && count( $file ) == 1 ) {
        // Try to render based on file name
        $file = array_shift( $file );

        if( !$plain ) {
            $diff = _display(
                $file,
                word_diff(
                    lineskip( 
                        $diff,
                        5
                    )
                )
            );
        } else {
            $d = '';

            foreach( preg_split( '/\r?\n/', lineskip( $diff, 5 ) ) as $line ) {
                $d .= '<div class="line-of-code">' . he( $line ) . '</div>';
            }

            $diff = word_diff( $d ); 

            $d = null;
        }

        # $diff_before = word_diff_before(
        #     $diff
        # );

        # $diff_after = word_diff_after(
        #     $diff
        # );

        # unset( $diff );
    }

    $puck = array(
        'file'              =>  &$file,
        'extension'         =>  detect_extension( $file, null ),
        'commit_before'     =>  &$commit_before,
        'commit_after'      =>  &$commit_after,
        'plain'             =>  &$plain,
        'subtractions'      =>  &$subtractions,
        'additions'         =>  &$additions,
        'diff'              =>  &$diff,
        'diff_count'        =>  &$diff_count
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_diff" );



}

function gen_show( $commit = null ) {
    perf_enter( "gen_show" );

    if( !can( "show", $commit ) ) {
        return render( 'not_allowed', array() );
    }

    return _gen_show( 
        array( 
            'commit'            => &$commit,
            'renderer'          => 'gen_show',
        ) 
    ) . perf_exit( "gen_show" );
}

function _gen_show( $opts = array() ) {
    perf_enter( "_gen_show" );

    $commit =           ( isset( $opts['commit'] ) ? $opts['commit'] : null ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_show'); 

    $show = git_show( $commit );

    if( $show === false ) {
        die( "Invalid commit: '$commit'" );
    }

    $notes = array();
    $notes['commits'] = git_notes( $commit );

    if( INCLUDE_WORD_COUNT ) {
        $notes[ WORD_COUNT_NOTES_REF ] = git_notes( $commit, WORD_COUNT_NOTES_REF );
    }

    if( INCLUDE_WORK_TIME ) {
        $notes[ WORKING_TIME_NOTES_REF ] = git_notes( $commit, WORKING_TIME_NOTES_REF );
    }

    if( COMMIT_RESPONSE_REF ) {
        $notes[ COMMIT_RESPONSE_REF ] = git_notes( $commit, COMMIT_RESPONSE_REF );
    }

    $puck = array(
        'commit'            =>  &$commit,
        'show'              =>  &$show,
        'notes'             =>  &$notes
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_show" );



}

function gen_notes( $commit = null, $file = null )  {
    perf_enter( "gen_notes" );

    return _gen_notes( 
        array( 
            'file'              => &$file,
            'commit'            => &$commit,
            'renderer'          => 'gen_notes',
        ) 
    ) . perf_exit( "gen_notes" );
}

function _gen_notes( $opts = array() ) {
    perf_enter( "_gen_notes" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $commit =           ( isset( $opts['commit'] ) ? $opts['commit'] : null ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_notes'); 

    $file = dirify( $file );
    $notes = git_notes( $commit, $file );

    $puck = array(
        'file'              =>  &$file,
        'commit'            =>  &$commit,
        'notes'              =>  &$notes,
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_notes" );

}

function gen_dir_view( $file, $commit = null ) {

    perf_enter( "gen_dir_view" );

    if( !can( "read", $file ) ) {
        return render( 'not_logged_in', array() );
    }

    return _gen_dir_view( 
        array( 
            'file'              => &$file,
            'commit'            => &$commit,
            'renderer'          => 'gen_dir_view',
        ) 
    ) . perf_exit( "gen_dir_view" );



}

function _gen_dir_view( $opts = array() ) {
    perf_enter( "_gen_dir_view" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $commit =           ( isset( $opts['commit'] ) ? $opts['commit'] : null ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_dir_view'); 

    $file = dirify( $file );

    $ls_tree = git_ls_tree( $file, $commit );

    // This can only be safely performed if caching is turned on
    if( CACHE_ENABLE ) {
        foreach( $ls_tree as $i => &$obj ) {
            $head_commit = git_file_head_commit( $obj['file'] );

            if( commit_or( $head_commit, false ) === false ) {
                # die( "Something strange with head commit for '" . $obj['file'] . "'" );
                break;
            }

            $obj['head_commit'] = git_show( $head_commit );
        }
    }

    $puck = array(
        'file'              =>  &$file,
        'commit'            =>  &$commit,
        'ls_tree'           =>  &$ls_tree,
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_dir_view" );

}

function gen_view( $file, $commit = null, $as = null )  {
    perf_enter( "gen_view" );

    if( !can( "read", $file ) ) {
        return gen_error( "You are denied access to one or more of the components requested for viewing." );
    }

    return _gen_view( 
        array( 
            'file'              => &$file,
            'as'                => &$as,
            'commit'            => &$commit,
            'renderer'          => 'gen_view',
        ) 
    ) . perf_exit( "gen_view" );
}

function gen_users_list() {

    perf_enter( 'gen_users_list' );

    $users = git_users_list();
    $statuses = get_statuses();

    $puck = array(
        'users'     =>  &$users,
        'statuses'  =>  &$statuses
    );
    
    return render( 'gen_users_list', $puck ) .  perf_exit( "gen_users_list" );

}

function gen_users_online() {

    perf_enter( 'gen_users_online' );

    $statuses = get_statuses();

    $puck = array(
        'statuses'  =>  &$statuses
    );
    
    return render( 'gen_users_online', $puck ) .  perf_exit( "gen_users_online" );

}


function gen_move( $file, $new_dir, $new_file, $move_counterpart, $leave_alias )  {
    perf_enter( "gen_move" );

    if( !can( "move", implode( ":", array( $file, $new_dir, $new_file ) ) ) ) {
        return render('not_logged_in', array() );
    }

    return _gen_move( 
        array( 
            'file'              => &$file,
            'new_dir'           => &$new_dir,
            'new_file'          => &$new_file,
            'move_counterpart'  => &$move_counterpart,
            'leave_alias'       => &$leave_alias,
            'renderer'          => 'gen_move',
        ) 
    ) . perf_exit( "gen_move" );
}

function _gen_move( $opts = array() ) {
    perf_enter( "_gen_move" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $new_dir =          ( isset( $opts['new_dir'] ) ? $opts['new_dir'] : null ); 
    $new_file =         ( isset( $opts['new_file'] ) ? $opts['new_file'] : null ); 
    $move_counterpart = ( isset( $opts['move_counterpart'] ) ? $opts['move_counterpart'] : false ); 
    $leave_alias      = ( isset( $opts['leave_alias'] ) ? $opts['leave_alias'] : false ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_move'); 

    $file =         dirify( $file );

    $counterpart_exists = false;
    $counterpart = null;

    $backout_commit = git_head_commit();

    if( is_dirifile( $file ) ) {

        # Path/to/somethingdir -> Path/to/something
        $counterpart = dirify( undirify( $file, true ) );
    } else {
        $counterpart = dirify( $file, true );
    }

    if( git_file_exists( $counterpart ) ) {
        $counterpart_exists = true;
    }

   #  echo "new dir: $new_dir";
    $new_dir =      ( $new_dir == null ? file_or( dirname( $file ), '' ) : dirify( $new_dir, true ) );
    # echo "new dir: $new_dir";

    # echo "new file: '$new_file'";
    $new_file =     file_or( ( $new_file == null ? basename( $file ) : basename( $new_file ) ), '' );

    # echo "New dir: $new_dir";

    // Return all directories in the repo...
    $all_directories = git_ls_tree( null, null, true, true );

    if( ASSOC_ENABLE ) {
        $all_directories = array_filter(
            $all_directories,
            function( $a ) {
                return !has_directory_prefix( ASSOC_DIR, $a['file'] );
            }
        );
    }


    $message = '';
    $finished = false;

    if( !isset( $_GET['submit'] ) ) {
        $message = "Please enter a destination directory or a new file name.";
    } else {

        if( !git_file_exists( $file, $backout_commit, false ) ) {
            $message =  "File '$file' does not appear to exist...";
        } else {

            $target = implode( 
                '/', 
                ( 
                    $new_dir == null || $new_dir == '' 
                    ? array( $new_file )
                    : array( $new_dir, $new_file )
                )
            );

            if( git_file_exists( $target ) ) {
                $message = "Destination '$new_file' appears to already exist.";
            } else {


                if( is_dirifile( $file ) xor is_dirifile( $target ) ) {
                    $message = "Destination '$new_file' has a type mismatch (must not assign file to directory, or directory to a file)";
                } else {

                    if( !git_file_exists( $new_dir ) ) {
                        mkdir( join("/", array( GIT_REPO_DIR, $new_dir) ), 0777, true );
                    }

                    $disassociation_candidates = array();
                    $association_candidates = array();
                    $alias_candidates = array();
                    $alias_activity = array();

                    if( ASSOC_ENABLE ) {
                        if( is_dirifile( $file ) ) {

                            $disassociation_candidates[ $file ] = 1;

                            foreach( git_glob( "$file/*" ) as $candidate ) {
                                $disassociation_candidates[ $candidate ] = 1;
                            }

                        } else {

                            $disassociation_candidates[ $file ] = 1;
                        }
                    }

                    if( ALIAS_ENABLE && $leave_alias ) {
                        $alias_candidates = array_merge(
                            $alias_candidates,
                            git_mv_renames( $file, $target )
                        );
                    }
                    
                    list( $ret, $ret_message ) = git_mv( 
                        $file, 
                        $target,
                        $_SESSION['usr']['git_user'], 
                        "Moving '$file' to '$target'" 
                    );

                    if( ASSOC_ENABLE ) {
                        if( is_dirifile( $target ) ) {

                            $association_candidates[ $target ] = 1;

                            foreach( git_glob( "$target/*" ) as $candidate ) {
                                $association_candidates[ $candidate ] = 1;
                            }

                        } else {

                            $association_candidates[ $target ] = 1;

                        }
                    }

                    if( $counterpart_exists && $move_counterpart ) {


                        $new_counterpart = (
                            is_dirifile( $counterpart ) ?
                                # Moving the counterpart directory to be named the same
                                # as the new destination file
                                $new_file . '.' . DIRIFY_SUFFIX
                                :
                                # Moving the counterpart file to be named the same as
                                # the destination dir
                                dirify( undirify( $new_file, true ) )
                        );

                        if( ASSOC_ENABLE ) {
                            if( is_dirifile( $counterpart ) ) {

                                $disassociation_candidates[ $counterpart ] = 1;

                                foreach( git_glob( "$counterpart/*" ) as $candidate ) {
                                    $disassociation_candidates[ $candidate ] = 1;
                                }

                            } else {

                                $disassociation_candidates[ $counterpart ] = 1;

                            }
                        }

                        $counterpart_target = implode( 
                            '/', 
                            ( 
                                $new_dir == null || $new_dir == '' 
                                ? array( $new_counterpart )
                                : array( $new_dir, $new_counterpart )
                            )
                        );

                        if( ALIAS_ENABLE && $leave_alias ) {
                            $alias_candidates = array_merge(
                                $alias_candidates,
                                git_mv_renames( $counterpart, $counterpart_target )
                            );
                        }

                        list( $counterpart_ret, $counterpart_ret_message ) = git_mv( 
                            $counterpart, 
                            $counterpart_target,
                            $_SESSION['usr']['git_user'], 
                            "Moving '$counterpart' to '$counterpart_target'" 
                        );

                        if( ASSOC_ENABLE ) {

                            if( is_dirifile( $counterpart_target ) ) {

                                $association_candidates[ $counterpart_target ] = 1;

                                foreach( git_glob( "$counterpart_target/*" ) as $candidate ) {
                                    $association_candidates[ $candidate ] = 1;
                                }

                            } else {

                                $association_candidates[ $counterpart_target ] = 1;

                            }
                        }
                    }


                    if( ASSOC_ENABLE ) {

                        # print_r( $disassociation_candidates );
                        # print_r( $association_candidates    );

                        foreach( $disassociation_candidates as $candidate => $dummy ) {

                            # echo "dis: $candidate";

                            disassociate( $candidate, false );

                        }

                        foreach( $association_candidates as $candidate => $dummy ) {

                            # echo "ass: $candidate";

                            build_assoc( $candidate, false );

                        }

                        $commit_notes = "Maintaining associations for move '$file' => '$target'";

                        list( $ret, $ret_message ) = git_commit(
                            $_SESSION['usr']['git_user'],
                            $commit_notes 
                        );
                    }

                    if( ALIAS_ENABLE && $leave_alias ) {

                        foreach( $alias_candidates as $source => $destination ) {

                            # echo "from $source to $destination\n\n";

                             $alias_activity = array_merge(
                                $alias_activity,
                                alias_move( 
                                    $source, 
                                    $destination, 
                                    false           // Do not perform commit, yet.
                                )
                            );

                            # print_r( get_aliases( false ) );
                        }

                        $commit_notes = "Maintaining aliases for move '$file' => '$target'";

                        list( $ret, $ret_message ) = git_commit(
                            $_SESSION['usr']['git_user'],
                            $commit_notes 
                        );
                    }

                    # print_r( $alias_activity );
                    # git( "reset --hard $backout_commit" );
                    # git( "clean -fd" );
                    # die( $commit_notes );

                    if( !$ret ) {
                        $message = "A problem occured while trying to move: $ret_message";
                    } else {
                        $message = "Move was successful. View the file in its new location <a href=\"index.php?file=" . undirify( join('/', array( $new_dir, $new_file ) ) ) . "\">" . undirify( join('/', array( $new_dir, $new_file ) ) ) . "</a>";

                        $finished = true;
                    }
                }
            }
        }
    }

    $puck = array(
        'file'              =>  &$file,
        'new_dir'           =>  &$new_dir,
        'new_file'          =>  &$new_file,
        'counterpart'       =>  &$counterpart,
        'counterpart_exists'=>  &$counterpart_exists,
        'all_directories'   => &$all_directories,
        'message'           =>  &$message,
        'finished'          =>  $finished
    );
    
    return render( $renderer, $puck ) .  perf_exit( "_gen_move" );

}



function _gen_view( $opts = array() ) {
    perf_enter( "_gen_view" );
    perf_mem_snapshot( "_gen_view start" );

    GLOBAL $notable_relationships; # From config/assoc.php


    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $as     =           ( isset( $opts['as'] ) ? $opts['as'] : null );
    $commit =           ( isset( $opts['commit'] ) ? $opts['commit'] : null ); 
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_view'); 


    $file = dirify( $file );

    $extension = detect_extension( $file, $as );

    $retrieve_contents = true;

    if( in_array( $extension, array( "image", "audio" ) ) ) {
        $retrieve_contents = false;
    }

    $view = git_view( $file, $commit, $retrieve_contents );

    // print_r( $view );

    $toc = array();
    $meta = array();

    $annotations    = array();
    $file_refs      = array();
    $drafts         = array();

    $show = array();

    foreach( $view as $commit_file_tag => &$contents )  {
        list( $_commit, $_file ) = explode(":", $commit_file_tag );

        if( is_logged_in() ) {
            $drafts[ $commit_file_tag ] = draft_exists( $_SESSION['usr']['name'], $_file );
        }

        if( ASSOC_ENABLE ) {


            $file_refs[ $commit_file_tag ] = array_merge(
                array_map(
                    function( $a ) {
                        $a['direction'] = 'target';
                        return $a;
                    },
                    file_assoc_targets( 
                        $_file ,
                        null,       // Don't care about which association type
                        false       // Don't care at this point about grabbing the file sequence.
                    )
                ),
                array_map(
                    function( $a ) {
                        $a['direction'] = 'source';
                        return $a;
                    },
                    file_assoc_sources( $_file )
                )
            );

            $dedup = array();
            foreach( $file_refs[ $commit_file_tag ] as &$assoc ) {

                if( !isset( $dedup[ $assoc['path'] ] ) ) {
                    $dedup[ $assoc['path'] ] = array(
                        'directions'    =>  array(),
                        'types'         =>  array()
                    );
                }

                if( !in_array( $assoc['direction'], $dedup[ $assoc['path'] ]['directions'] ) ) {
                    $dedup[ $assoc['path'] ]['directions'][] = $assoc['direction'];
                }

                if( !in_array( $assoc['type'], $dedup[ $assoc['path'] ]['types'] ) ) {
                    $dedup[ $assoc['path'] ]['types'][] = $assoc['type'];
                }
            }
            $file_refs[ $commit_file_tag ] = $dedup;
        }

        // Pre-rendering processing
        if( in_array( $extension, array( "markdown", "text", "print","read" ) ) ) {

            $meta[ $commit_file_tag ] = array();
            $contents = metaify( 
                $contents,
                $_file,
                $meta[ $commit_file_tag ]
            );

            $contents = metaify_empty_strip( $contents );
        }

        $contents = _display( 
            $_file, 
            $contents, 
            $as 
        );

        // Post-rendering processing
        if( in_array( $extension, array( "markdown", "text", "print","read" ) ) ) {



            list( $view[$commit_file_tag], $annotations[$commit_file_tag] ) = annotateify( 
                $view[$commit_file_tag] 
            );

            $view[ $commit_file_tag ] = metaify_import_strip( // Strip any [[%Tag]] variables that haven't been replaced
                metaify_postprocess( 
                    $view[$commit_file_tag],
                    $_file,
                    $meta[ $commit_file_tag ]
                )
            );

            // Generate all ToC elements after annotations and meta / transclude replacements
            list( $view[$commit_file_tag], $toc[$commit_file_tag] ) = tocify( 
                $view[$commit_file_tag] 
            );



            // Process this on the client side
            // $view[ $commit_file_tag ] = highlightify( $view[ $commit_file_tag ] );

        }

        # Incude commit metadata
        $show[$_commit] = git_show( $_commit );

    }


    # Grab a listing of the file as viewed as a directory,
    # and figure out how many files are underneath it.
    $ls_tree_count = array();
    if( is_array( $file ) ) {

        foreach( $file as $f ) {

            $as_dir = dirify( $f, true );

            $ls_tree_count[$as_dir]  = count( git_ls_tree( $as_dir, $commit ) );
        }

    } else {

        $as_dir = dirify( $file, true );

        $ls_tree_count[$as_dir] = count( git_ls_tree( $as_dir, $commit ) );

    }

    $puck = array(
        'file'                  =>  &$file,
        'extension'             =>  &$extension,
        'commit'                =>  &$commit,
        'show'                  =>  &$show,
        'view'                  =>  &$view,
        'toc'                   =>  &$toc,
        'meta'                  =>  &$meta,
        'annotations'           =>  &$annotations,
        'file_refs'             =>  &$file_refs,
        'drafts'                =>  &$drafts,
        'ls_tree_count'         =>  &$ls_tree_count,
        'notable_relationships' =>  &$notable_relationships,
    );

    if( $as == "print" ) {
        $renderer = "gen_print";
    }

    if( $as == "read" ) {
        $renderer = "gen_read";
    }


    perf_mem_snapshot( "_gen_view end" );

    return render( $renderer, $puck ) .  perf_exit( "_gen_view" );

}

function _list_all_directories() {
    // Return all directories in the repo...
    $all_directories = git_ls_tree( null, null, true, true );
    
    $all_directories = array_filter(
        $all_directories,
        function( $a ) {
            if( ASSOC_ENABLE && has_directory_prefix( ASSOC_DIR, $a['file'] ) ) {
                return false;
            }
    
            if( ALIAS_ENABLE && has_directory_prefix( ALIAS_DIR, $a['file'] ) ) {
                return false;
            }
            
            return true;
        }
    );
    
    $all_directories = array_map(
        function( $a ) {
            return undirify( $a['file'], true ) . "/";
        },
        $all_directories
    );
    
    $all_directories = array_values(
        $all_directories
    );

    return $all_directories;

}

function gen_latex( $file ) {

    if( !git_file_exists( $file ) ) {
        die( "File '$file' does not exist!" );
    }

    if( !can( "read", $file ) ) {
        return render( 'not_allowed', array() );
    }

    $extension = detect_extension( $file, null );

    if( !in_array( $extension, array( "markdown", "pan" ) ) ) {
        die( "Unable to produce LaTeX content using pandoc outside of Markdown files" );
    }

    $contents = gen_clean( $file );

    $contents = _pandoc_markdown_to_latex( 
        $contents, 
        "latex", 
        array(
            'documentclass' =>  'book',
        )
    );

    return $contents;

}

function gen_clean( $file ) {

    global $metaify_enabled_extensions;

    $meta = array();

    $contents = git_file_get_contents( $file );

    $extension = detect_extension( $file, null );

    // Pre-rendering processing
    if( in_array( $extension, $metaify_enabled_extensions  ) ) {

        $contents = metaify( 
            $contents,
            $file,
            $meta
        );

        $contents = metaify_empty_strip( $contents );

    }

    $contents = _display( 
        $file, 
        $contents, 
        "clean"
    );


    if( in_array( $extension, $metaify_enabled_extensions  ) ) {

        $contents = metaify_postprocess( 
            $contents,
            $file,
            $meta
        );

        // Strip any [[%Tag]] variables that haven't been replaced
        $contents = metaify_import_strip( $contents);

    }

    return $contents;

}

function gen_new( $file = null, $template = null ) {

    perf_enter( "gen_new" );

    if( !is_logged_in() ) {
        return 'You are not logged in.';
    } else {

        // Return all directories in the repo...
        $all_directories = _list_all_directories();

        $puck = array(
            'file'          =>  $file,
            'template'      =>  $template,
            'directories'   =>  &$all_directories
        );

        return render( 'gen_new', $puck ) .  perf_exit( "gen_new" );
    }
}

function gen_form( $file = null, $template = null ) {
    GLOBAL $php_meta_header_pattern;
    GLOBAL $php_meta_empty_pattern;


    perf_enter( "gen_form" );

    if( !is_logged_in() ) {
        return 'You are not logged in.';
    } else {

        $file       = dirify( $file );
        $template   = dirify( $template );

        if( !git_file_exists( $template ) ) {
            return gen_error( "File '$template' does not exist" );
        }

        $contents = git_file_get_contents( $template );


        $all_tags = git_all_tags();
        $all_meta = git_all_meta();
        $all_directories = _list_all_directories();


        $selected_tags = array();
        $selected_meta = array();

        foreach( $all_tags as $tag => &$tagged_files ) {
            if( in_array( $template, $tagged_files ) ) {

                if( $tag != '~template' ) {
                    $selected_tags[] = $tag;
                }
            }
        }



        // We have to detect both filled and empty meta headers
        foreach( preg_split( "/\r?\n/", $contents ) as $line ) {

            callback_replace( 
                $php_meta_header_pattern,
                $line, 
                function( $match ) use( &$selected_meta ) {

                    $escape     = $match[1][0];
                    $wo_escape  = $match[2][0];

                    if( $escape == "!" ) {
                        return;
                    }

                    $key        = $match[3][0];
                    $val        = $match[4][0];

                    if( !isset( $selected_meta[ $key ] ) ) {
                        $selected_meta[ $key ] = array();
                    }

                    $selected_meta[ $key ][] = $val;

                    return '';

                }
            );

            callback_replace( 
                $php_meta_empty_pattern,
                $line, 
                function( $match ) use( &$selected_meta ) {

                    $key        = $match[1][0];

                    if( !isset( $selected_meta[ $key ] ) ) {
                        $selected_meta[ $key ] = array();
                    }

                    $selected_meta[ $key ][] = null;

                    return '';

                }
            );
        }

        $puck = array(
            'file'              =>  $file,
            'contents'          =>  &$contents,
            'template'          =>  $template,
            'all_tags'          =>  &$all_tags,
            'all_meta'          =>  &$all_meta,
            'directories'       =>  &$all_directories,
            'selected_tags'     =>  &$selected_tags,
            'selected_meta'     =>  &$selected_meta,
        );

        return render( 'gen_form', $puck ) .  perf_exit( "gen_form" );
    }
}

?>
