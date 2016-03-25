<?
require_once( dirname( __FILE__ ) . '/config.php' );
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/git.php' );
require_once( dirname( __FILE__ ) . '/toc.php' );
require_once( dirname( __FILE__ ) . '/display.php' );



function _calc_stats( &$contents, $opts = array( ) ) {
    GLOBAL $non_counted_words;
    GLOBAL $conjunctions;
    GLOBAL $past_tense_verbs;
    GLOBAL $present_tense_verbs;
    GLOBAL $filter_words;

    // print_r( $past_tense_verbs );
    // print_r( $present_tense_verbs );

    perf_enter( '_calc_stats' );

    $dictionary = array();
    if( $opts['calculate_dictionary_words'] ) {
        # Grab dictionary words...
        perf_enter( '_dict_read' );
        $dictionary_cache_path = TMP_DIR . "/dictionary.cache";
        $dictionary = unserialize( file_get_contents( $dictionary_cache_path ) );
        perf_exit( '_dict_read' );
    }

    $word_counter = array();
    $total_words = 0;
    $matches = array();
    $dictionary_words = 0;
    $common_words = 0;
    $count_conjunctions = 0;
    $count_filter_words = 0;
    $count_numbers = 0;

    $gerund_regex = "/ing$/";
    $count_past_tense = 0;
    $count_present_tense = 0;
    $count_gerund = 0;


    if( preg_match_all( '/\b([\w\']+)\b/', $contents , $matches ) ) {

        # echo "word matches: " . count( $matches[0] );
        # print_r( $matches );

        foreach( $matches[0] as $match ) {
            $match = trim( strtolower( $match ) );

            if( is_numeric( $match ) ) {
                $count_numbers++;
                continue;
            }

            $total_words++;

            if( in_array( $match, $conjunctions ) ) {
                $count_conjunctions++;
            }

            if( in_array( $match, $filter_words ) ) {
                $count_filter_words++;
            }


            if( in_array( $match, $non_counted_words ) ) {
                $common_words++;
            } else {

                if( !isset( $word_counter[$match] ) ) {
                    $word_counter[$match] = 0;
                }

                if( !in_array( $match, $non_counted_words ) ) {
                    $word_counter[$match]++;
                }
            }

            perf_enter( '_dict_lookup' );

            # $match_offset = ( isset( $dict_offsets[ strtolower( $match[0] ) ] ) ? $dict_offsets[ strtolower( $match[0] ) ] : 0 );
            $match_offset = 0;

            if( 
                // isset( $dictionary_cache[ $match ] ) || 
                isset( $dictionary[ $match ] )
            ) {
                $dictionary_words++;

                if( in_array( $match, $past_tense_verbs ) ) {
                    $count_past_tense++;
                }

                if( in_array( $match, $present_tense_verbs ) ) {
                    $count_present_tense++;
                }



                if( preg_match( $gerund_regex, $match ) == 1 ) {
                    $count_gerund++;
                }

                // if( isset( $dictionary_cache[ $match ] ) ) {
                //     perf_enter( '_dict_internal' );
                //     perf_exit(  '_dict_internal' );
                // }
                // $dictionary_cache[ $match ] = 1;
            }
            perf_exit( '_dict_lookup' );
        }
    }
    
    arsort( $word_counter );

    $stats['character_count'] = strlen( $contents );
    $stats['word_counts'] = array_splice( $word_counter, 0, 10 );
    $stats['distinct_words'] = count( array_keys( $word_counter ) );
    $stats['dictionary_words'] = $dictionary_words;
    $stats['total_words'] = $total_words;
    $stats['common_words'] = $common_words;
    $stats['conjunctions'] = $count_conjunctions;
    $stats['numbers'] = $count_numbers;
    $stats['past_tense'] = $count_past_tense;
    $stats['present_tense'] = $count_present_tense;
    $stats['gerund'] = $count_gerund;
    $stats['filter_words'] = $count_filter_words;

    $stats['page_count'] = round( $total_words / 550 ) . '-' . round( $total_words / 350 );
    $stats['manuscript_page_count'] = round( $total_words / 250 );

    /*
    if( $opts['calculate_work_time'] ) {
        $work_stats = git_work_stats( $files );

        $stats['work_time_seconds'] = 0;

        foreach( $work_stats as $author => &$author_stats ) {
            $stats['work_time_seconds'] += $author_stats['seconds'];
        }
    }
    */

    perf_exit( '_calc_stats' );

    return $stats;
}

function _document_stats( $file, $commit, $opts = array(), $cache = true ) {

    perf_enter( '_document_stats' );

    $toc = array();
    $contents = '';

    $memoize_key = implode( 
        ':', 
        array_merge( 
            array( 
                $file, 
                $commit 
            ), 
            $opts 
        ) 
    );

    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( '_document_stats', $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "_document_stats" );
                    return $r;
                }
    
            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );
    
                if( !is_null( $r ) ) {
                    perf_exit( "_document_stats" );
                    return $r;
                }
            }
        }
    }
    
    if( CACHE_ENABLE ) {
        perf_enter( "_document_stats.cache_miss" );
    }


    if( git_file_exists( $file ) ) {
        $view = git_view( $file, $commit );

        $commit_file_tag = "$commit:$file";
        $contents = $view[$commit_file_tag];

        list( $contents, $toc ) = tocify( 
            _display( 
                $file, 
                $contents 
            )
        );
    }

    $contents = strip_tags( $contents );

    $stats = _calc_stats( $contents, $opts );

    if( $opts['calculate_work_time'] ) {
        $work_stats = git_work_stats( $file );

        $stats['work_time_seconds'] = 0;

        foreach( $work_stats as $author => &$author_stats ) {
            $stats['work_time_seconds'] += $author_stats['seconds'];
        }
    }


    if( CACHE_ENABLE ) {
        perf_exit( "_document_stats.cache_miss" );
    }
    
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                encache( '_document_stats', $memoize_key, $stats );
    
            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $stats );
            }
        }
    }

    perf_exit( '_document_stats' );

    return $stats;
}

function gen_stats( $file, $commit = null ) {
    perf_enter( 'gen_stats' );

    perf_mem_snapshot( "before_stats" );

    $file = dirify( $file );
    $commit = commit_or( $commit, git_head_commit() );

    $stats = _document_stats( 
        $file, 
        $commit ,
        array(
            'calculate_dictionary_words'    => true
        )
    );

    $puck = array(
        'file'      => undirify( $file ),
        'stats'    =>  &$stats
    );

    return render( 'gen_stats', $puck ) .  perf_exit( "gen_stats" ) . perf_mem_snapshot( "after_stats" );;
}

function gen_repo_stats() {

    perf_enter( 'gen_repo_stats' );

    $rev_count = git( "rev-list --count HEAD" );
    $commits  = git( "rev-list --reverse HEAD" );
    $head_files = git_head_files();

    $file_count = $assoc_count = $alias_count = $normal_count = 0;

    $commits = preg_split( 
        '/\r?\n/', 
        $commits['out'],
        -1,
        PREG_SPLIT_NO_EMPTY
    ); 

    $first_commit = $commits[ 0 ];
    $last_commit = $commits[ count( $commits ) - 1 ];

    $first_commit   = git_show( $first_commit );
    $last_commit    = git_show( $last_commit );

    foreach( $head_files as $file ) {
        $file_count++;

        if( has_directory_prefix( ASSOC_DIR, $file ) ) {
            $assoc_count++;
        } elseif( has_directory_prefix( ALIAS_DIR, $file ) ) {
            $alias_count++;
        } else {
            $normal_count++;
        }
    }

    $puck = array(
        'rev_count'         => $rev_count['out'],
        'file_count'        => $file_count,
        'assoc_count'       => $assoc_count,
        'alias_count'       => $alias_count,
        'normal_count'      => $normal_count,
        'first_commit'      => $first_commit,
        'last_commit'       => $last_commit
    );

    return render( 'gen_repo_stats', $puck ) .  perf_exit( "gen_repo_stats" );


}


?>
