<?
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");
require_once( dirname( __FILE__ ) . "/collection.php");
require_once( dirname( __FILE__ ) . "/transclude.php");
require_once( dirname( __FILE__ ) . "/stats.php");



function funcify_clean( $text, $current_file = null, $is_preview = false ) {

    GLOBAL $functionlink_pattern;

    perf_enter( 'funcify_clean' );


    $text = callback_replace( 
        $functionlink_pattern,
        $text,
        function( $match ) use( $current_file, $is_preview ) {
            $orig = $match[0][0];

            $escape = $match[1][0];

            $func = $match[3][0];
            $params = $match[5][0];
            $display = $match[11][0];

            $replacement = '';

            if( $escape == "\\" || $escape == "!" ) {
                $replacement = substr( $orig, 1 );
            } else {

                switch( $func ) {
                    case "ref":
                        $replacement = '';
                        break;
                    case "todo":
                    case "todos":
                    case "meta":
                    case "metadata":
                    case "metrics":
                    case "metric":
                    case "progress":
                    case "tags":
                    case "tag":
                    case "associations":
                    case "assoc":
                    case "relationships":
                    case "relations":
                    case "list":
                    case "edit":
                    case "jot":
                    case "template":
                    case "churn":
                    case "image":
                    case "toc":
                    case "tableofcontents":
                        // Squelch all non-Markdown bits.
                        $replacement = '';
                        break;
                    case "include":
                    case "transclude":

                        $args       = argify( $params );
                        // Force the 'as' for the transclude to be 'clean'
                        $args['as'] = 'clean';

                        $replacement =  _handle_transclude( $current_file, $func, $args, $display );
                        break;
                    case "stamp":
                        $replacement =  _handle_stamp( $current_file, $func, $params, $display );
                        break;
                    case "blame":
                    case "cherrypick":
                    case "clear_cache":
                    case "diff":
                    case "history":
                    case "index":
                    case "partition":
                    case "move":
                    case "revert":
                    case "search":
                    case "show_commit":
                    case "users":
                    case "view":
                    case "stats":
                    case "new":
                    case "form":
                    case "whatlinkshere":
                    case "timeline":
                    case "raw":
                        $replacement = '[' . $display . '](' . "$func.php" . '?' . paramify( $func, $params, $current_file ) . ')';
                        break;
                    case "lookup":
                        $replacement = _handle_lookup( $current_file, $func, $params, $display );
                        break;

                    case "table":
                    case "csv":
                        $replacement =  _handle_table( $current_file, $func, $params, $display );
                        break;

                    default:
                        $replacement = $orig;
                        break;

                }
            }
            
            return $replacement;
        }
    );
    

    return $text . perf_exit( 'funcify_clean' );


}

function funcify( $text, $current_file = null, $is_preview = false ) {

    GLOBAL $functionlink_pattern;

    perf_enter( 'funcify' );

    $text = callback_replace( 
        $functionlink_pattern,
        $text,
        function( $match ) use( $current_file, $is_preview ) {
            $orig = $match[0][0];

            $escape = $match[1][0];

            $func = $match[3][0];
            $params = $match[5][0];
            $display = $match[11][0];

            $replacement = '';

            if( $escape == "\\" || $escape == "!" ) {
                $replacement = substr( $orig, 1 );
            } else {

                switch( $func ) {
                    case "ref":
                        $replacement = '';
                        break;
                    case "include":
                    case "transclude":
                        $replacement =  _handle_transclude( $current_file, $func, $params, $display );
                        break;
                    case "stamp":
                        $replacement =  _handle_stamp( $current_file, $func, $params, $display );
                        break;
                    case "blame":
                    case "cherrypick":
                    case "clear_cache":
                    case "diff":
                    case "history":
                    case "index":
                    case "partition":
                    case "clean":
                    case "move":
                    case "revert":
                    case "search":
                    case "show_commit":
                    case "users":
                    case "view":
                    case "stats":
                    case "new":
                    case "form":
                    case "whatlinkshere":
                    case "timeline":
                    case "raw":
                        $replacement = '<a class="wikilink" href="' . "$func.php" . '?' . paramify( $func, $params, $current_file ) . '">' . $display . '</a>';
                        break;
                    case "lookup":
                        $replacement = _handle_lookup( $current_file, $func, $params, $display );
                        break;

                    case "todo":
                    case "todos":
                        $replacement = _handle_todos( $func, $params, $display );
                        break;
                    case "meta":
                    case "metadata":
                        $replacement = _handle_meta( $current_file, $func, $params, $display );
                        break;

                    case "metrics":
                    case "metric":
                        $replacement = _handle_metrics( $current_file, $func, $params, $display );
                        break;
                    case "searchgrid":
                        $replacement = _handle_searchgrid( $current_file, $func, $params, $display );
                        break;

                    case "progress":
                        $replacement = _handle_progress( $func, $params, $display );
                        break;
                    case "tags":
                    case "tag":
                        $replacement =  _handle_tags( $func, $params, $display );
                        break;
                    case "table":
                    case "csv":
                        $replacement =  _handle_table( $current_file, $func, $params, $display );
                        break;

                    case "associations":
                    case "assoc":
                    case "relationships":
                    case "relations":
                        $replacement =  _handle_assoc( $current_file, $func, $params, $display );
                        break;
                    case "list":
                        $replacement =  _handle_list( $current_file, $func, $params, $display );
                        break;
                    case "edit":
                        $replacement =  _handle_edit( $current_file, $func, $params, $display );
                        break;
                    case "jot":
                        $replacement =  _handle_jot( $current_file, $func, $params, $display );
                        break;
                    case "template":
                        $replacement =  _handle_template( $current_file, $is_preview, $func, $params, $display );
                        break;
                    case "churn":
                        $replacement =  _handle_churn( $current_file, $func, $params, $display );
                        break;

                    case "image":
                        $replacement =  _handle_image( $current_file, $func, $params, $display );
                        break;
                    case "toc":
                    case "tableofcontents":
                        // Do nothing, we take care of this in a post-processing step
                        // $replacement =  _handle_toc( $display );
                        $replacement = $orig;
                        break;

                    default:
                        $replacement = $orig;
                        break;

                }
            }
            
            return $replacement;
        }
    );

    

    return $text . perf_exit( 'funcify' );

}

function argify( $params ) {

    $ret = array();

    foreach( preg_split( "/(?!\\\)[,&]/", $params ) as $kvp ) {

        if( $kvp == "" ) {
            continue;
        }

        list( $name, $value ) = preg_split( "/\s*=\s*/", $kvp );

        $name = trim( $name );
        $value = trim( $value );

        # "Auto-vivification of arrays
        if( isset( $ret[ $name ] ) ) {
            if( is_array( $ret[ $name ] ) ) {
                $ret[ $name ][] = $value;
            } else {
                $ret[ $name ] = array( $ret[ $name ], $value );
            }
        } else {
            $ret[ $name ] = $value;
        }
    }

    if( count( $ret ) == 0 ) {
        return array();
    } else {
        return $ret;
    }
}

function paramify( $func, $params, $current_file = null ) {

    $ret = array();

    foreach( preg_split( "/(?!\\\)[,&]/", $params ) as $kvp ) {

        if( $kvp == "" ) {
            continue;
        }

        list( $name, $value ) = preg_split( "/\s*=\s*/", $kvp );

        if( $name == 'file' ) {
            $value = file_or( $value, $value, $current_file );
        }

        $ret[] = urlencode( $name ) . '=' . urlencode( $value );
    }

    if( count( $ret ) == 0 ) {
        return '';
    } else {
        return implode( '&', $ret );
    }

}

function _handle_progress( $func, $params, $display ) {

    $ret = '';

    $args = argify( $params );

    $as =   set_or( $args['as'],    'list'  );
    $sort = set_or( $args['sort'],  "true"  );
    $total_files = set_or( $args['total_files'],  null );

    $files_to_measure = array();

    if( $args['file'] ) {
        $file = $args['file'];

        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        $file   =   array_map(
                        function( $a ) use( $current_file ) {
                            return interpolate_relative_path( $a, $current_file );
                        },
                        $file
                    );

        $files_to_measure = collect_files(
            $file,
            $current_file
        );

    } elseif( $args['list'] ) {

        $list = file_or( $args['list'], false );

        if( !git_file_exists( $list ) ) {
            return "List '$list' does not exist";
        }

        $files_to_measure = collect_files(
            preg_split( 
                '/\r?\n/',
                git_file_get_contents( $list )
            ),
            $list
        );
    }

    # $list   =   file_or( $args['list'], null, $current_file );

    if( count( $files_to_measure ) <= 0 ) {
        return "No files to measure progress!";
    }

    $total_files_estimated = false;
    if( $total_files == null ) {
        $total_files = count( $files_to_measure );
    } else {
        if( $total_files < count( $files_to_measure ) ) {
            $total_files = count( $files_to_measure );
        } else {
            $total_files_estimated = true;
        }
    }

    $assumed_per_file = false;
    $assumed_work_seconds = 0;
    if( $args['assumed'] ) {
        if( is_array( $args['assumed'] ) ) {
            if( count( $args['assumed'] ) >= count( $files_to_measure ) ) {
                $assumed_per_file = true;
                $assumed_work_seconds = $args['assumed'];
            } else {
                $assumed_work_seconds = array_shift( $args['assumed'] );
            }
        } else {
            $assumed_work_seconds = $args['assumed'];
        }
    }

    $status_assoc_type = "status";
    $progress_regex = '/^' . $status_assoc_type . '_([0-9]{1,3})$/';

    $progress_stats = array();

    foreach( $files_to_measure as $i => $f ) {
        $df =   dirify( $f );
        $uf =   undirify( $f );
        $commit =   commit_or( $args['commit'], false );
        if( $commit === false ) {
            $commit = git_file_head_commit( $df );
            if( !$commit ) {
                $commit = git_head_commit();
            }
        }
    
        $progress_stats[ $df ] = array(
            'seconds'   =>  0,
            'commits'   =>  0,
            'progress'  =>  0
        );

        $work_stats     =   git_work_stats( $df );

        if( $assumed_per_file ) {
            // $progress_stats[ $df ]['seconds'] += ( $assumed_work_seconds[ $i ] + $author_stats['seconds'] );
            $progress_stats[ $df ]['seconds'] += ( $assumed_work_seconds[ $i ] + 0 );
        }

        foreach( $work_stats as $author => &$author_stats ) {
            $progress_stats[ $df ]['seconds'] += $author_stats['seconds'];
            $progress_stats[ $df ]['commits'] += $author_stats['commits'];
        }

        $progress_stats[ $df ]['hours_elapsed'] = plural( 
            sprintf( 
                "%.1f", 
                $progress_stats[ $df ]['seconds'] / 3600 
            ),
            "hour"
        );

        $assocs = array_map( 
            function( $a ) {
                return $a['type'];
            },
            array_merge(
                file_assoc_sources( $df, "$status_assoc_type.*" ),
                file_assoc_targets( $df, "$status_assoc_type.*" )
            )
        );

        rsort( $assocs );

        $greatest_progress = array_shift( $assocs );
        $progress = 0;

        $match = array();
        if( preg_match( $progress_regex, $greatest_progress, $match ) !== false ) {
            $progress = $match[1]+0;
        }

        $progress_stats[$df]['progress'] = $progress;

        $progress_stats[$df]['seconds_total'] = (
            $progress == 0
                ? $progress_stats[$df]['seconds']
                : (
                    $progress_stats[ $df ]['seconds'] /
                    ( $progress / 100 )
                )
        );

        $progress_stats[$df]['seconds_remaining'] = (
            $progress_stats[ $df ]['seconds_total'] - $progress_stats[ $df ]['seconds'] 
        );


        $progress_stats[ $df ]['hours_remaining'] = plural( 
            sprintf( 
                "%.1f", 
                $progress_stats[ $df ]['seconds_remaining'] / 3600 
            ),
            "hour"
        );

        $progress_stats[ $df ]['hours_total'] = plural( 
            sprintf( 
                "%.1f", 
                $progress_stats[ $df ]['seconds_total'] / 3600 
            ),
            "hour"
        );


    }

    if( $as == "list" ) {
        if( count( $progress_stats ) == 1 ) {

            foreach( $progress_stats as $mf => &$s ) {
                $ret .= linkify( '[[' . undirify( $uf ) . '|' . $display . ']]' ) . ': ';
                $ret .= implode( ', ', $s );
            }
        } else {
            $ret .= '<ul class="metric-list">';
            foreach( $progress_stats as $mf => &$s ) {
                $ret .= '<li>';
                $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' ) . ': ';
                $ret .= implode( ', ', $s );
                $ret .= '</li>';
            }
            $ret .= '</ul>';
        }
    } elseif( $as == "table" ) {
        $ret .= '<table class="metric-table tabulizer ' 
                    . ( in_array( $sort, array( "false", "no", false ) ) ? 'no-sort' : '' ) 
        . '">';
        $ret .= '<thead>';
        $ret .= '<tr>';
        $ret .= '<th>File</th>';

        foreach( array( "Progress", "Commits", "Work Hours Elapsed", "Work Hours Remaining", "Work Hours Total" ) as $m ) {
            $ret .= '<th>';
            $ret .= '<span>' . $m . '</span>';
            $ret .= '</th>';
        }
        $ret .= '</tr>';
        $ret .= '</thead>';
        $ret .= '<tbody>';

        $total_commits              = 0;
        $total_seconds_elapsed      = 0;
        $total_completion_seconds   = 0;
        $total_remaining_seconds    = 0;
        $total_progress             = 0;

        foreach( $progress_stats as $mf => &$s ) {
            $ret .= '<tr>';
            
            $ret .= '<td>';
            $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' );
            $ret .= '</td>';

            foreach( array( "progress", "commits", "hours_elapsed", "hours_remaining", "hours_total" ) as $m ) {
                $ret .= '<td>';

                if( $m == "progress" ) {
                    $ret .= '<span class="status_' . $s[ $m ] . '">';
                }

                $ret .= $s[ $m ];

                if( $m == "progress" ) {
                    $ret .= '%</span>';
                }

                $ret .= '</td>';
            }

            $ret .= '</tr>';

            $total_commits              += $s[ 'commits' ];
            $total_seconds_elapsed      += $s[ 'seconds' ];
            $total_completion_seconds   += $s[ 'seconds_total' ];
            $total_remaining_seconds    += $s[ 'seconds_remaining' ];
            $total_progress             += $s[ 'progress' ];
        }

        $ret .= '</tbody>';
        $ret .= '<tfoot>';

        $ret .= '<tr>';
        $ret .= '<td colspan="2">Totals:</td>'; 

        // Total # Commits
        $ret .= '<td>';
        $ret .= $total_commits;
        $ret .= '</td>';

        // Total work time thus far
        $ret .= '<td>';
        $ret .= plural( 
            sprintf( 
                "%.1f", 
                $total_seconds_elapsed / 3600 
            ),
            "hour"
        );
        $ret .= '</td>';

        // Total remaining seconds
        $ret .= '<td>';
        $ret .= plural( 
            sprintf( 
                "%.1f", 
                $total_remaining_seconds / 3600 
            ),
            "hour"
        );
        $ret .= '</td>';

        // Total seconds to estimated completion
        $ret .= '<td>';
        $ret .= plural( 
            sprintf( 
                "%.1f", 
                $total_completion_seconds / 3600 
            ),
            "hour"
        );

        $ret .= '</td>';


        $ret .= '</tr>';

        // Averages
        $ret .= '<tr>';
        $ret .= '<td colspan="1">';
        $ret .= 'Averages (for ' . plural( count( $files_to_measure ), "referenced file" ) . '):';
        $ret .= '</td>';

        $ret .= '<td>';

        $ret .= '<span class="status_' . round( $total_progress  / count( $files_to_measure ) ) . '">' . round( $total_progress / count( $files_to_measure ) ) . '%</span>';

        $ret .= '</td>';

        $ret .= '<td>';
        $ret .= sprintf( "%.1f", $total_commits / $total_files );
        $ret .= '</td>';

        $ret .= '<td colspan="3">';
        $ret .= '</td>';


        $ret .= '</tr>';

        $ret .= '<tr>';
        $ret .= '<td>';
        $ret .= 'Estimated Total Progress (' . plural( $total_files, ( $total_files_estimated ? ' estimated file' : 'file' ) ) . '):';
        $ret .= '</td>';

        $ret .= '<td colspan="5">';


        if( $total_files_estimated ) {
            $ret .= '<span class="status_' . round( $total_progress  / $total_files ) . '">' . round( $total_progress / $total_files ) . '%</span>';
        }

        $ret .= '</td>';
        $ret .= '</tr>';

        $ret .= '</tfoot>';


        $ret .= '</table>';
    }

    return $ret;
}

function _handle_lookup_helper( $file, $cache = true ) {

    perf_enter( '_handle_lookup.helper' );

    $file = dirify( $file );

    $memoize_key = git_file_head_commit( $file ) . ':' . $file ;

    # echo $memoize_key;


    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) && $cache === true ) {
                $r = decache( 'lookup', $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "_handle_lookup.helper" );
                    return $r;
                }

            } elseif( is_array( $cache ) ) {
                $r = decache( $cache['tag'], $memoize_key );

                if( !is_null( $r ) ) {
                    perf_exit( "_handle_lookup.helper" );
                    return $r;
                }
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_enter( "_handle_lookup.helper.cache_miss" );
    }

    $lookup = array();

    $c = git_file_get_contents( $file );

    $lines = preg_split( '/\r?\n/', $c );

    if( count( $lines ) <= 1 ) {
        // We need at least a header row
        return array();
    }

    $header = array_shift( $lines );

    $properties = str_getcsv( $header );

    // Trip all header properties
    foreach( $properties as $i => $p ) {
        $properties[$i] = trim( $p );
    }

    foreach( $lines as $line ) {

        if( trim( $line) == '' ) {
            continue;
        }

        $l = str_getcsv( $line );

        if( count( $l ) < 2 ) {
            // A line's key without a property is the same as
            // the line being missing
            continue;
        }

        $k = trim( $l[0] );

        $lookup[ $k ] = array();


        foreach( $properties as $i => $p ) {

            if( isset( $l[ $i ] ) ) {
                $lookup[ $k ][ $p ] = trim( $l[ $i ] );
            }
        }
    }

    // Try to cache our results if we're being
    // asked to do so
    if( CACHE_ENABLE ) {
        if( !is_null( $cache ) ) {
            if( is_bool( $cache ) ) {
                encache( 'lookup', $memoize_key, $lookup );

            } elseif( is_array( $cache ) ) {
                encache( $cache['tag'], $memoize_key, $lookup );
            }
        }
    }

    if( CACHE_ENABLE ) {
        perf_exit( "_handle_lookup.helper.cache_miss" );
    }

    perf_exit( '_handle_lookup.helper' );

    return $lookup;

}

function _handle_lookup( $current_file, $func, $params, $display, $cache = true ) {

    perf_enter( "_handle_lookup" );
    $ret = '?';

    $args = argify( $params );

    $file = null;
    $key = null;

    if( !isset( $args['file'] ) ) {
        return 'Parameter "file" is required';
    }

    if( file_or( $args['file'], false ) === false ) {
        return "File '$file' is not valid";
    }

    if( !git_file_exists( $args['file'] ) ) {
        return "File '$file' does not exist";
    }

    $file = $args['file'];

    if( !isset( $args['key'] ) ) {
        $key = $current_file;

    } else {
        if( is_array( $args['key'] ) ) {
            $key = array_shift( $args['key'] );
        } else {
            $key = $args['key'];
        }
    }

    $lookup = _handle_lookup_helper( $file, $cache );


    $properties = null;

    if( isset( $lookup[ $key ] ) ) {
        $properties = $lookup[ $key ];
    } else {
        if( file_or( $key, false ) !== false ) {
            
            if( isset( $lookup[ dirify( $key ) ] ) ) {
                $properties = $lookup[ dirify( $key ) ];
            }
        }
    }

    if( !is_null( $properties  ) ) {

        if( isset( $properties[ $display ] ) ) {
            $ret = $properties[ $display ];
        }
    }


    perf_exit( "_handle_lookup" );

    return $ret;

}

function _handle_meta( $current_file, $func, $params, $display ) {

    $ret = '';

    $args = argify( $params );

    $headers = array();

    $all_meta = git_all_meta();

    if( isset( $args['header'] ) ) {
        $headers =   $args['header'];
    }

    if( $headers == null ) {
        $headers = array_keys( $all_meta );
    }

    if( !is_array( $headers ) ) {
        $headers = array( $headers );
    }

    $as         = set_or( $args['as'],    'list'  );
    $sort       = set_or( $args['sort'],  "true"  );
    $display    = set_or( $args['display'],  "file"  );


    $files_to_measure = array();
    # $file   =   file_or( $args['file'], null, $current_file );

    if( $args['file'] ) {
        $file = $args['file'];

        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        $file   =   array_map(
            function( $a ) use( $current_file ) {
                return interpolate_relative_path( $a, $current_file );
            },
            $file
        );

        $files_to_measure = collect_files(
            $file,
            $current_file
        );

    } elseif( $args['list'] ) {

        $list = file_or( $args['list'], false, $current_file );

        if( !git_file_exists( $list ) ) {
            return "List '$list' does not exist";
        }

        $files_to_measure = collect_files(
            preg_split( 
                '/\r?\n/',
                git_file_get_contents( $list )
            ),
            $list
        );
    }

    $measured_headers = array();

    foreach( $headers as $h ) {

        if( isset( $all_meta[ $h ] ) ) {
           
            foreach( $all_meta[ $h ] as $file => $values ) {

                if( count( $files_to_measure ) > 0 ) {

                    if( in_array( $file, $files_to_measure ) ) {
                        if( !isset( $measured_headers[ $file ] ) ) {
                            $measured_headers[ $file ] = array();
                        }

                        $measured_headers[ $file ][ $h ] = $values;
                    }

                } else {
                    if( !isset( $measured_headers[ $file ] ) ) {
                        $measured_headers[ $file ] = array();
                    }

                    $measured_headers[ $file ][ $h ] = $values;
                }
            }
        }
    }

    // "Measure" the display, should it be the name of one
    // of the known meta keys
    if( $display ) {
        if( isset( $all_meta[ $display ] ) ) {
            foreach( $all_meta[ $display ] as $file => $values ) {
                if( count( $files_to_measure ) > 0 ) {
                    if( in_array( $file, $files_to_measure ) ) {
                        if( !isset( $measured_headers[ $file ] ) ) {
                            $measured_headers[ $file ] = array();
                        }

                        $measured_headers[ $file ][ $display ] = $values;
                    }
                } else {
                    if( !isset( $measured_headers[ $file ] ) ) {
                        $measured_headers[ $file ] = array();
                    }

                    $measured_headers[ $file ][ $display ] = $values;
                }
            }
        }
    }

    if( $as == "list" ) {
        if( count( $measured_headers ) == 1 ) {

            foreach( $measured_headers as $mf => &$mhs ) {
                $ret .= linkify( '[[' . undirify( $mf ) . '|' . $display . ']]' ) . ': ';
                $ret .= implode( 
                    ', ', 
                    array_map(
                        function( $a ) use( $mhs ) {
                            return "$a: " . ( isset( $mhs[ $a ] ) ? implode( ',', $mhs[ $a ] ) : '' );

                        },
                        $headers
                    )
                );
            }
        } else {
            $ret .= '<ul class="metric-list">';
            foreach( $measured_headers as $mf => &$mhs ) {
                $ret .= '<li>';
                $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' ) . ': ';
                $ret .= implode( 
                    ', ', 
                    array_map(
                        function( $a ) use( $mhs ) {
                            return "$a: " . ( isset( $mhs[ $a ] ) ? implode( ',', $mhs[ $a ] ) : '' );

                        },
                        $headers
                    )
                );

                $ret .= '</li>';
            }
            $ret .= '</ul>';
        }
    } elseif( $as == "table" ) {
        $ret .= '<table class="metric-table tabulizer ' 
                    . ( in_array( $sort, array( "false", "no", false ) ) ? 'no-sort' : '' ) 
        . '">';
        $ret .= '<thead>';
        $ret .= '<tr>';
        $ret .= '<th>File</th>';

        if( $display == "line" ) {
            $ret .= '<th>Excerpt</th>';
        }

        foreach( $headers as $h ) {
            $ret .= '<th>';
            $ret .= '<span>' . $h . '</span>';
            $ret .= '</th>';
        }
        $ret .= '</tr>';
        $ret .= '</thead>';
        $ret .= '<tbody>';

        foreach( $measured_headers as $mf => &$mhs ) {
            $ret .= '<tr>';

            $ret .= '<td>';

            if( $display == "line" ) {


                // Sorto f a mess...
                $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' );
                $ret .= '</td><td>';

                $c      =   git_file_get_contents( $mf );
                $s      =   preg_split( '/^([\*\-\_]\s*){3,}$/m', $c );
                $e      =   array_shift( $s );
                $ret    .=  '<a href="index.php?file=' . urlencode( $mf ) . '">' . he( excerpt( $e, 100 ) ) . '</a>';


            } elseif( isset( $mhs[ $display ] ) ) {

                $ret .= linkify( 
                    '[[' 
                    . undirify( $mf ) 
                    . '|' 
                    . implode( ',', $mhs[ $display ] ) 
                    . ']]' 
                );

            } else {

                $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' );
            }

            $ret .= '</td>';

            foreach( $headers as $h ) {
                $ret .= '<td>';
                $ret .= ( isset( $mhs[ $h ] ) ? implode( ',', $mhs[ $h ] ) : '' );
                $ret .= '</td>';
            }

            $ret .= '</tr>';
        }

        $ret .= '</tbody>';
        $ret .= '</table>';
    }

    return $ret;
}


function _handle_searchgrid( $current_file, $func, $params, $display ) {
    $ret = '';

    $args = argify( $params );

    $files_to_search = array();

    if( $args['file'] ) {
        $file = $args['file'];

        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        $file = array_map(
            function( $a ) use( $current_file ) {
                return interpolate_relative_path( $a, $current_file );
            },
            $file
        );

        $files_to_search = collect_files(
            $file,
            $current_file
        );

    } elseif( $args['list'] ) {

        $list = file_or( $args['list'], false );

        if( !git_file_exists( $list ) ) {
            return "List '$list' does not exist";
        }

        $files_to_search = collect_files(
            preg_split( 
                '/\r?\n/',
                git_file_get_contents( $list )
            ),
            $list
        );
    }



    $terms =   set_or( $args['term'], array() );
    if( !is_array( $terms ) ) {
        $terms = array( $terms );
    }

    if( count( $terms ) <= 0 ) {
        return "No terms to search!";
    }

    $search = array();

    foreach( $terms as $t ) {
        $matches = array();

        $t_temp = $t;
        $as_regex = false;
        if( preg_match( '@^/(.+)/$@', $t, $matches ) === 1 ) {
            $t_temp = $matches[ 1 ];
            $as_regex = true;
        }

        $result = git_grep( 
            $t_temp,
            $as_regex
        ); 

        if( is_array( $result ) && count( $result ) <= 0 ) {
            $search[ $t ] = array();
        } else {

            foreach( $result as $res_file => $res ) {

                if( !isset( $search[ $t ] ) ) {
                    $search[ $t ] = array();
                }

                if( !isset( $search[ $t ][ $res_file ]  ) ) {
                    $search[ $t ][ $res_file ] = 0;
                }

                $search[ $t ][ $res_file ] += $res[ 'count' ];

            }
        }

    }

    if( count( $files_to_search ) <= 0 ) {

        $temp_files = array();

        foreach( $search as $t => $sf ) {
            $temp_files[ $sf ] = 1;
        }

        $temp_files = array_keys( $temp_files );

        sort( $temp_files );

        $files_to_search = $temp_files;

        $temp_files = null;
    }

    $ret = '';

    $ret .= '<table class="table table-hover table-condensed table-striped">';

    $ret .= '<thead>';

    $ret .= '<tr>';
    $ret .= '<th>Term / File</th>';

    foreach( $files_to_search as $sf ) {
        $ret .= '<th>';

        $ret .= linkify( '[[' . undirify( $sf ) . '|' . basename( $sf ) . ']]' ) ;

        $ret .= '</th>';
    }

    $ret .= '<th>Total</th>';

    $ret .= '</tr>';
    
    $ret .= '</thead>';

    $ret .= '<tbody>';

    foreach( $search as $search_term => $res ) {
        $ret .= '<tr>';

        $ret .= '<td><code><a href="search.php?term=' . urlencode( $search_term ) . '">' . he( $search_term ) ."</a></code></td>";

        $total_count = 0;

        foreach( $files_to_search as $sf ) {

            $count = 0;

            if( isset( $search[ $search_term ][ $sf ] ) ) {
                $count = $search[ $search_term ][ $sf ] ;
            }

            $ret .= "<td>" . $count . "</td>";

            $total_count += $count;


        }

        $ret .= "<td>" . $total_count . "</td>";


        $ret .= '</tr>';

    }

    $ret .= '</tbody>';

    $ret .= '</table>';









    return $ret;

}

function _handle_metrics( $current_file, $func, $params, $display ) {

    $ret = '';

    $args = argify( $params );

    $metric =   $args['metric'];
    if( !is_array( $metric ) ) {
        $metric = array( $metric );
    }

    $as =   set_or( $args['as'],    'list'  );
    $sort = set_or( $args['sort'],  "true"  );

    $files_to_measure = array();
    # $file   =   file_or( $args['file'], null, $current_file );

    if( $args['file'] ) {
        $file = $args['file'];

        if( !is_array( $file ) ) {
            $file = array( $file );
        }

        $file = array_map(
            function( $a ) use( $current_file ) {
                return interpolate_relative_path( $a, $current_file );
            },
            $file
        );

        $files_to_measure = collect_files(
            $file,
            $current_file
        );

    } elseif( $args['list'] ) {

        $list = file_or( $args['list'], false );

        if( !git_file_exists( $list ) ) {
            return "List '$list' does not exist";
        }

        $files_to_measure = collect_files(
            preg_split( 
                '/\r?\n/',
                git_file_get_contents( $list )
            ),
            $list
        );
    }

    # $list   =   file_or( $args['list'], null, $current_file );

    if( count( $files_to_measure ) <= 0 ) {
        return "No files to measure!";
    }

    $measured_stats = array();


    foreach( $files_to_measure as $f ) {
        $df =   dirify( $f );
        $uf =   undirify( $f );
        $commit =   commit_or( $args['commit'], false );
        if( $commit === false ) {
            $commit = git_file_head_commit( $df );
            if( !$commit ) {
                $commit = git_head_commit();
            }
        }
    
        $opts = array();
        if( in_array( 'dictionary_words', $metric ) ) {
            $opts['calculate_dictionary_words'] = true;
        }

        if( in_array( 'work_time', $metric ) ) {
            $opts['calculate_work_time'] = true;
        }
        
        $stats  =   _document_stats( $df, $commit, $opts );

        $values = array();
        foreach( $metric as $m ) {

            if( $m == "most_used_word" ) {
                $i = 0;
                $top_three = array();
                foreach( $stats['word_counts'] as $word => $count ) {
                    $top_three[] = plural( $count, $word, "" );
                    $i++;
                    if( $i > 2 ) {
                        $values[ $m ] = implode( ', ', $top_three );
                        break;
                    }
                }

                continue;
            }

            if( $m == "work_time" ) {

                $values[ 'work_time_seconds' ] = $stats['work_time_seconds'];
                $values[ 'work_time' ] = "<a
                    href=\"work_stats.php?file=" .
                        urlencode( $uf ) 
                    . "\" title=\"" . 
                        plural( $stats['work_time_seconds'], "second" )
                    . '">' . 
                plural( 
                    sprintf( 
                        "%.1f", 
                        $stats['work_time_seconds'] / 3600 
                    ),
                    "hour"
                ) . "</a>";

                continue;
            }


            if( $stats[ $m ] ) {
                $values[ $m ] =  $stats[ $m ];
            }
        }

        $measured_stats[ $df ] = $values;

    }


    if( $as == "list" ) {
        if( count( $measured_stats ) == 1 ) {

            foreach( $measured_stats as $mf => &$s ) {
                $ret .= linkify( '[[' . undirify( $uf ) . '|' . $display . ']]' ) . ': ';
                $ret .= implode( ', ', $s );
            }
        } else {
            $ret .= '<ul class="metric-list">';
            foreach( $measured_stats as $mf => &$s ) {
                $ret .= '<li>';
                $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' ) . ': ';
                $ret .= implode( ', ', $s );
                $ret .= '</li>';
            }
            $ret .= '</ul>';
        }
    } elseif( $as == "table" ) {
        $ret .= '<table class="metric-table tabulizer ' 
                    . ( in_array( $sort, array( "false", "no", false ) ) ? 'no-sort' : '' ) 
        . '">';
        $ret .= '<thead>';
        $ret .= '<tr>';
        $ret .= '<th>File</th>';

        foreach( $metric as $m ) {
            $ret .= '<th>';
            $ret .= '<span>' . $m . '</span>';
            $ret .= '</th>';
        }
        $ret .= '</tr>';
        $ret .= '</thead>';
        $ret .= '<tbody>';

        $totals = array();

        $summation_metrics = array( 
            'total_words', 
            'work_time', 
            'manuscript_page_count',
            'work_time',
            'work_time_seconds' 
        );

        foreach( $measured_stats as $mf => &$s ) {
            $ret .= '<tr>';
            
            $ret .= '<td>';
            $ret .= linkify( '[[' . undirify( $mf ) . '|' . basename( $mf ) . ']]' );
            $ret .= '</td>';

            foreach( $metric as $m ) {
                $ret .= '<td>';
                $ret .= $s[ $m ];
                $ret .= '</td>';

                if( in_array( $m, $summation_metrics ) ) {

                    if( $m == 'work_time' ) {

                        $totals[ 'work_time_seconds' ] += $s[ 'work_time_seconds' ];

                    } else {

                        $totals[ $m ] += $s[ $m ];

                    }
                }

            }

            $ret .= '</tr>';
        }

        $ret .= '</tbody>';

        $ret .= '<tfoot>';

        $ret .= '<tr>';
        
        $ret .= '<td>';
        $ret .= 'Summary';
        $ret .= '</td>';

        foreach( $metric as $m ) {
            $ret .= '<td>';


            if( in_array( $m, $summation_metrics ) ) {

                if( $m == 'work_time' ) {

                    
                    $ret .= plural( 
                        sprintf( 
                            "%.1f", 
                            $totals[ 'work_time_seconds' ] / 3600 
                        ),
                        "hour"
                    );

                } else {

                    $ret .= to_si( $totals[ $m ] );
                }

            } else {
                $ret .= '&nbsp;';
            }
            $ret .= '</td>';
        }

        $ret .= '</tr>';

        $ret .= '</tfoot>';
        

        $ret .= '</table>';
    }

    return $ret;
}

function _handle_todos( $func, $params, $display ) {
    $args = argify( $params );
    
    $file   = file_or( $args['file'], null, $current_file );
    
    if( is_array( $file ) ) {
        $file = array_shift( $file );
    } 
    
    $replacement = '<a class="wikilink" href="' . "todos.php" . '?' . paramify( $func, "file=$file" ) . '">' . $display . '</a>';

    return $replacement;
}

function _handle_toc( $display ) {

    // $args = argify( $params );

    // $show = set_or( $args['show']

    // We're pre-processing the document to later insert the ToC
    return '<div><h6>' . he( $display ) . "</h6></div><div>" . TOC_REPLACEME . "</div>";
}


function _handle_tags( $func, $params, $display ) {
    $args = argify( $params );
    
    $file   = file_or( $args['file'], null, $current_file );
    $tags   = $args['tag'];
    
    if( is_array( $file ) ) {
        $file = array_shift( $file );
    } 
    
    if( !is_array( $tags ) ){
        $tags = array( $tags );
    }
    
    foreach( $tags as &$t ) {
        $t = "tag=$t";
    }
    
    $params_modified = implode( 
        ",", 
        array_merge(
            array(
                "file=$file",
            ),
            $tags
        )
    );
    
    $replacement = '<a class="wikilink" href="' . "tags.php" . '?' . paramify( $func, $params_modified  ) . '">' . $display . '</a>';

    return $replacement;
}

function _handle_stamp( $current_file, $func, $params, $display ) {
    
    $ret = '';

    $args       = argify( $params );
    $file       = file_or(  $args['file'],  false,  $current_file   );

    // Default to current file, if file is not specified
    if( $file === false ) {
        $file = $current_file;
    }

    if( !git_file_exists( $file ) ) {
        // File isn't committed yet, so we can't really provide any
        // timestamp / commit information for it
        return '!';
    }


    $display = trim( strtolower( $display ) );

    $hc = git_file_head_commit( dirify( $file ) );

    if( commit_or( $hc, false ) === false ) {
        return "$file?$hc?";
    }

    $commit = git_show( $hc );

    switch( $display ) {

        case "commit":
            $ret = $commit['commit'];
            break;


        case "commit_excerpt":
            $ret = commit_excerpt( $commit['commit'] );
            break;

        case "epoch":
            $ret = $commit['author_date_epoch'];
            break;


        case "date":
            $ret = strftime( "%Y/%m/%d", $commit['author_date_epoch'] ) ;
            break;

        case "time":
            $ret = strftime( "%H:%m", $commit['author_date_epoch'] ) ;
            $ret = $commit['author_date'];
            break;

        case "datetime":
            $ret = $commit['author_date'];
            break;

        case "author":
            $ret = $commit['author_name'];
            break;

        default:
            $ret = "$display?";
            break;
    }

    return $ret;
}

function _handle_transclude( $current_file, $func, $params, $display ) {

    // Allow sending args explicitly 
    // $args       = argify( $params );
    $args = array();
    if( is_array( $params ) ) {
        $args = $params;
    } else {
        $args = argify( $params );
    }

    $file       = file_or(  $args['file'],  false,  $current_file   );
    
    $strip      = set_or(   $args['strip'], null                    );
    $as         = set_or(   $args['as'],    null                    );
    
    $display = ( !$display ? undirify( $file ) : $display );
    
    $extension = detect_extension( $file, $as );
    
    $replacement = '';
    
    if( $file === false ) {
        return "Invalid transclude file '$file'";
    }
    
    if( !in_array( $extension, array( "raw", "clean" ) ) ) {
    
        $replacement .= '<h6>' . linkify( '[[' . undirify( $file ) . '|' . he( $display ) . ']]' ) . "</h6>";
    }
    
    $replacement .= transclude( $current_file, $file, $args );

    return $replacement;
}

function _handle_table( $current_file, $func, $params, $display ) {
    $args = argify( $params );
    # $replacement = 'table!';
    
    $file           =   file_or( 
                            set_or( 
                                $args['table'], $args['file'] 
                            ), 
                            null, 
                            $current_file  
                        );
    $filter         =   set_or( $args['filter'],      null );
    $show_search    =   set_or( $args['show_search'], true );
    
    if( $show_search === true || in_array( $show_search, array( "true", "yes" ) )  ) {
        $show_search = true;
    } else {
        $show_search = false;
    }
    
    if( $file == null ) {
        $replacement = "Must submit either 'file' or 'table' parameter";
    } else {
    
        $file = dirify( is_array( $file ) ? array_shift( $file ) : $file );
    
        if( git_file_exists( $file ) ) {
            # $head_commit = git_file_head_commit( $file );                        
    
            $contents = git_file_get_contents( $file );
    
            if( !is_null( $filter ) ) {
    
                $contents = implode( "\n", 
                    array_filter(
                        preg_split( '/\r?\n/', $contents ),
                        function( $a ) use ( $filter ) {
                            static $linenum = 0;
                            # echo "hey: '" . preg_quote( $filter );
                            
                            $is_match =  preg_match( '/' . preg_quote( $filter, '/' ) . '/', $a );
                            $is_header = ( $linenum === 0 );
                            # $is_header = false;
                            $linenum++;
                            return $is_header || $is_match;
                        }
                    )
                );
            }
    
            # $replacement = _display( $file, $contents, "csv" );
            $replacement = csv_display( $file, $contents, $show_search );
        }
    }
    
    $u = undirify( $file );
    $replacement = '<div><p>' . linkify( '[[' . $u . '|' . $display . ']]' ) . ' [<a class="wikilink edit" href="edit.php?file=' . urldecode( $u ) . '">Edit</a>]' . '</p>' . $replacement. '</div>';

    return $replacement;
}

function _handle_assoc( $current_file, $func, $params, $display ) {
    $args       =   argify( $params );
    
    $file       =   file_or( $args['file'],         false,      $current_file   );
    $which      =   set_or( $args['which'],         'targets'                   );
    $sort       =   set_or( $args['sort'],          'default'                   );
    $direction  =   set_or( $args['direction'],     'ascending'                 );
    $ref_type   =   set_or( $args['type'],          false                       );
    $show_type  =   set_or( $args['show_type'],     false                       );
    
    # print_r( $args );
    # print_r( $ref_type );
    
    if( !in_array( $which, array( "targets","sources" ) ) ) {
        $which = "targets";
    }
    
    if( $show_type !== false && $show_type == "yes" ) {
        $show_type = true;
    }
    
    if( !in_array( $sort, array( "sequence","name","default" ) ) ) {
        if( $which != 'targets' ) {
            $sort = "name";
        } else {
            $sort = "sequence";
        }
    } else {
        if( $sort == "sequence" && $which == "sources" ) {
            $sort = "name";
        }
    }
    
    if( !in_array( $direction, array( "ascending","descending","asc","desc","forward","reverse" ) ) ) {
        $direction = "ascending";
    }

    if( $file === false ) {
        $file = file_or( $current_file, false );
    }
    
    if( $file !== false ) {
    
        if( !git_file_exists( $file ) ) {
            $replacement = "File '$file' does not exist";
            return $replacement;
        }
        $assocs = array();
    
        if( !is_array( $ref_type ) ) {
            $ref_type = array( $ref_type );
        }
    
        foreach( $ref_type as $rt ) {
    
            if( $which == "sources" ) {
                $assocs    =    array_merge(
                                    $assocs,
                                    file_assoc_sources( 
                                        $file, 
                                        ( $rt !== false ? $rt : null  )
                                    )
                                );
            } else {
                $assocs    =    array_merge( 
                                    $assocs,
                                    file_assoc_targets( 
                                        $file,   
                                        ( $rt !== false ? $rt : null  ),
                                        ( $sort == "sequence" ) // If we're sorting by
                                                                // sequence, we need
                                                                // to send the flag
                                                                // to retrieve the
                                                                // sequence.
                                    )
                                );
            }
        }
    
        /*
        if( $ref_type !== false ) {
            $assocs = array_filter(
                $assocs,
                function( $a ) use ( $ref_type ) {
                    return ( $a['type'] == $ref_type );
                }
            );
        }
        */

        # print_r( $assocs );
        
    
        $file_list = array();
        foreach( $assocs as $a ) {
            // $file_list[] = $a['path'];
            if( !isset( $file_list[ $a['path'] ] ) ) {
                $file_list[ $a['path'] ] = array();
            }
    
            if( !isset( $file_list[ $a['path'] ]['types'] ) ) {
                $file_list[ $a['path'] ]['types'] = array();
            }
    
    
            if( isset( $a['sequence'] ) && !isset( $file_list[ $a['path'] ]['least_sequence'] ) ) {
                $file_list[ $a['path'] ]['least_sequence'] = $a['sequence'];
            }
    
    
            if( !in_array( $a['type'], $file_list[ $a['path'] ]['types'] ) ) {
                
                $file_list[ $a['path'] ]['types'][] = $a['type'];
            }
    
            if( isset( $file_list[ $a['path'] ]['least_sequence'] ) ) {
                
                if( $a['sequence'] < $file_list[ $a['path'] ]['least_sequence'] ) {
    
                    $file_list[ $a['path'] ]['least_sequence']  = $a['sequence'];
                }
            }
    
        }
    
        switch( $sort ) {
    
            case "sequence":
    
                uksort( 
                    $file_list,
                    function( $a, $b ) use( $file_list ) {
                        if( isset( $file_list[$a]['least_sequence'] ) && isset( $file_list[$b]['least_sequence'] ) ) {
                            return $file_list[$a]['least_sequence'] - $file_list[$b]['least_sequence'];
                        }
    
                        return 0;
                    }
                );
    
    
                break;
            case "name":
    
                # echo "here";
                uksort( 
                    $file_list,
                    function( $a, $b ) use ($file_list) {
                        # $r = strcmp( $a['type'], $b['type'] );
    
                        return strcmp( $file_list[$a]['path'], $file_list[$b]['path'] );
    
                    }
                );
    
            case "default":
            default:
                uksort( 
                    $file_list,
                    function( $a, $b ) use( $file_list ) {
                        $r = strcmp( $file_list[$a]['type'], $file_list[$b]['type'] );
    
                        if( $r == 0 ) {
                            return strcmp( $file_list[$a]['path'], $file_list[$b]['path'] );
                        }
    
                        return $r;
                    }
                );
    
    
                break;
        }
    
        switch( $direction ) {
            case "ascending":
            case "asc":
            case "forward":
                # Do nothing
    
                break;
    
            case "descending":
            case "desc":
            case "reverse":
            default:
                $file_list = array_reverse( $assocs );
    
                break;
    
        }
    
    
        $replacement = gen_assoc_functionlink(
            $file,
            $file_list,
            $show_type,
            $args
        );
    
        $replacement = "\n\n" . 
            (
                $file !== false ? 
                    linkify( '[[' . undirify( $file ) . '| ' . he( $display ) . ']]' )
                    :
                    he( $display )
            ) .
            "\n\n" . 
            trim( $replacement );
    
    }

    return $replacement;
}

function _handle_list( $current_file, $func, $params, $display ) {
    $args = argify( $params );
    $list_file = false;
    
    if( $args['list'] ) {
        $list_file = file_or( $args['list'], false, $current_file );
    
        if( $list_file !== false && git_file_exists( $list_file ) ) {
    
            $list_file = dirify( $list_file );
    
            $head_commit = git_file_head_commit( $list_file );
    
            $view = git_view( $list_file, $head_commit );
    
            $matched_files = array();
    
            foreach( preg_split( '/\r?\n/', $view["$head_commit:$list_file"] ) as $line ) {
                if( $line == "" ) { continue; }
                $matched_files = array_merge(
                    $matched_files,
                    collect_files( 
                        $line,
                        $list_file
                    )
                );
            }
    
            $replacement = gen_list(
                $matched_files,
                $args
            );
    
        }
    } else {
        $file = interpolate_relative_path( $args['file'], $current_file );
    
    
        $tags = array();
        if( !is_array( $args['tag'] ) ) {
            $args['tag'] = array( $args['tag'] );
        }
    
        foreach( $args['tag'] as $t ) {
            $t = preg_replace( '/^~/', '', $t );
            if( tag_or( $t, false ) !== false ) {
                $tags[] = $t; 
            }
        }
    
        if( $file == null ) {
            $replacement = gen_list(
                array_keys(
                    git_tags( $tags )
                ),
                $args
            );
    
        } else {
    
            if( count( $tags ) <= 0 ) {
                $replacement = gen_list(
                    collect_files( 
                        $file,
                        $current_file
                    ),
                    $args
                );
            } else {
                $tag_w_prefix = array();
                foreach( $tags as $t ) {
                    $tag_w_prefix[] = "~$t";
                }
    
                $replacement = gen_list(
                    collect_files( 
                        implode( 
                            ":",
                            array(
                                $file,
                                implode(
                                    ",",
                                    $tag_w_prefix
                                )
                            )
                        ),
                        $current_file
                    ),
                    $args
                );
            }
        }
    }
    
    $replacement = "\n\n" . 
        (
            $list_file !== false ? 
                linkify( '[[' . undirify( $list_file ) . '| ' . he( $display ) . ']]' )
                :
                he( $display )
        ) .
        "\n\n" . 
        trim( $replacement );

    return $replacement;
}

function _handle_edit( $current_file, $func, $params, $display ) {
    $args   = argify( $params );
    
    $_file      = file_or( rtrim( $args['file'],        '/' ), null, $current_file );
    $_template  = file_or( rtrim( $args['template'],    '/' ), null, $current_file );
    $_fmt       = $args['format'];
    
    $to_edit = "";
    
    $edit_class = "edit";
    if( git_file_exists( dirify( $_file ) ) ) {
        $edit_class = "";                        
    }
    
    $to_edit = undirify( $_file );
    
    if( $_template == null ) {
    
        $replacement = '<a class="wikilink ' . $edit_class . '" href="' . "edit.php" . '?file=' . urlencode( $to_edit ) . '">' . he( $display ) . '</a>';
    
    } else {
    
        $replacement = '<a class="wikilink ' . $edit_class . '" href="' . "template.php" . '?file=' . urlencode( $to_edit ) . '&template=' . urlencode( $_template ) . '">' . he( $display ) . '</a>';
    }
}

function _handle_jot( $current_file, $func, $params, $display ) {
    $args   = argify( $params );
    
    $_file      = file_or( rtrim( $args['file'],        '/' ), null, $current_file );
    $_template  = file_or( rtrim( $args['template'],    '/' ), null, $current_file );
    $_fmt       = $args['format'];
    
    $to_jot = "";
    
    if( !isset( $_fmt ) || $_fmt == "" ) {
        $_fmt = array( "%Y", "%m", "%d-%a" );
    } else {
        if( !is_array( $_fmt ) ) {
            $_fmt = array( $_fmt );
        }
    }
    
    foreach ( $_fmt as $i => &$f ) {
        $f = strftime( $f );
    }
    
    if( $_file == null ) {
        $to_jot = implode( '/', $_fmt );
    } else {
        $_file = undirify( $_file, true );
        $to_jot = implode( '/', array_merge( array( $_file ), $_fmt ) );
    }
    
    if( $_template == null ) {
    
        $replacement = '<a class="wikilink jot" href="' . "edit.php" . '?file=' . $to_jot . '">' . he( $display ) . '</a>';
    
    } else {
    
        $replacement = '<a class="wikilink jot" href="' . "template.php" . '?file=' . urlencode( $to_jot ) . '&template=' . urlencode( $_template ) . '">' . he( $display ) . '</a>';
    }

    return $replacement;
}


function _handle_template( $current_file, $is_preview, $func, $params, $display ) {


    $replacement = render( 
        'gen_template', 
        array(
            'current_file'  =>  $current_file,
            'is_preview'    =>  $is_preview,
            'func'          =>  $func,
            'params'        =>  $params,
            'display'       =>  $display
        )
    );

    return $replacement;
}

function word_diff_count( $text ) {

    $adds       = array();
    $subtracts  = array();

    $word_count_adds        = 0;
    $word_count_subtracts   = 0;
    
    preg_match_all( 
        '/\{\+(.+?)\+\}/s',
        $text,
        $adds,
        PREG_SET_ORDER
    );

    preg_match_all( 
        '/\[\-(.+?)\-\]/s',
        $text,
        $subtracts,
        PREG_SET_ORDER
    );

    foreach( $adds as $d ) {
        $s = _calc_stats( $d[1] );

        $word_count_adds += $s['total_words'];

    }

    foreach( $subtracts as $d ) {
        $s = _calc_stats( $d[1] );

        $word_count_subtracts += $s['total_words'];

    }


    return array( $word_count_adds, $word_count_subtracts );
}

/*
function _handle_timeline( $current_file, $func, $params, $display ) {

    $args = argify( $params );
    $file = set_or( $args['file'], false );
    $list_file = set_or( $args['list'], false );

    if( $file !== false && !is_array( $file ) ) {
        $file = array( $file );
    }

    if( $list_file !== false && is_array( $list ) ) {
        $list_file = array_shift( $list_file );
    }

    if( $file !== false ) {
        foreach( $file as &$f ) {
            $f = undirify( file_or( $f, false, $current_file ) );
        }
    }
    
    $list_file  = file_or( $list_file, false, $current_file );

    $matched_files = array();

    if( $list_file !== false && git_file_exists( $list_file ) ) {
        $list_contents = git_file_get_contents( $list_file );
        foreach( preg_split( '/\r?\n/', $list_contents ) as $line ) {
            if( $line == "" ) { continue; }
        
            $matched_files = array_merge(
                $matched_files,
                collect_files( 
                    $line,
                    $list_file
                )
            );
        }
    } else {

        foreach( $file as $f ) {
            if( git_file_exists( $f ) ) {
                $matched_files[] = $f;
            }
        }
    }

    if( count( $matched_files ) <= 0 ) {
        return 'No files matches for file= or list= specification';
    }

    return gen_timeline( $matched_files, $display );

}
*/


function _handle_churn( $current_file, $func, $params, $display ) {

    $args = argify( $params );
    $file = set_or( $args['file'], false );
    $list_file = set_or( $args['list'], false );

    if( $file !== false && !is_array( $file ) ) {
        $file = array( $file );
    }

    if( $list_file !== false && is_array( $list ) ) {
        $list_file = array_shift( $list_file );
    }

    if( $file !== false ) {
        foreach( $file as &$f ) {
            $f = undirify( file_or( $f, false, $current_file ) );
        }
    }
    
    $list_file  = file_or( $list_file, false, $current_file );

    $matched_files = array();

    if( $list_file !== false && git_file_exists( $list_file ) ) {
        $list_contents = git_file_get_contents( $list_file );
        foreach( preg_split( '/\r?\n/', $list_contents ) as $line ) {
            if( $line == "" ) { continue; }
        
            $matched_files = array_merge(
                $matched_files,
                collect_files( 
                    $line,
                    $list_file
                )
            );
        }
    } else {

        foreach( $file as $f ) {
            if( git_file_exists( $f ) ) {
                $matched_files[] = $f;
            }
        }
    }

    if( count( $matched_files ) <= 0 ) {
        return 'No files matches for file= or list= specification';
    }

    $file_churn = array();

    // Maximum commit entries to iterate over
    $max_commits = 200;

    // Walk the file's history backwards, looking at "churn" of the
    // added/subtracted words in the file
    foreach( $matched_files as $f ) {

        $authors = array();

        $df = dirify( $f );

        $hist =  git_history( $max_commits, $df );

        foreach( $hist as $h ) {

            if( !isset( $authors[ $h['author'] ] ) ) {
                $authors[$h['author']] = array(
                    'adds'      =>  0,
                    'subtracts' =>  0,
                    'commits'   =>  0
                );
            }

            $diff = git_diff( 
                $h['parent_commit'],
                $h['commit'],
                $df
            );

            $counts = word_diff_count( $diff );

            $authors[ $h['author'] ]['adds']        += $counts[ 0 ];
            $authors[ $h['author'] ]['subtracts']   += $counts[ 1 ];
            $authors[ $h['author'] ]['commits']     += 1;
        }

        $file_churn[ $df ] = $authors;
    }


    if( count( $file_churn ) <= 0 ) {
        return 'No commits for these files';
    }

    $ret = '';
    $ret .= '<table class="churn-table tabulizer">';
    $ret .= '<thead>';
    $ret .= '<tr>';
    $ret .= '<th>File</th>';

    foreach( array( "Author", "Commits", "Adds", "Subtracts","Adds+Subtracts" ) as $m ) {
        $ret .= '<th>';
        $ret .= '<span>' . $m . '</span>';
        $ret .= '</th>';
    }

    $ret .= '</tr>';
    $ret .= '</thead>';
    $ret .= '<tbody>';

    foreach( $file_churn as $f => $fc ) {

        foreach( $fc as $author => $churn ) {
            $ret .= '<tr>';
            $ret .= '<td>' . linkify( '[[' . undirify( $f ) . ']]' ) . '</td>';
            $ret .= '<td>' . $author                . '</td>';
            $ret .= '<td>' . $churn['commits']      . '</td>';
            $ret .= '<td>' . $churn['adds']         . '</td>';
            $ret .= '<td>' . $churn['subtracts']    . '</td>';
            $ret .= '<td>' . ( $churn['adds'] + $churn['subtracts'] ) . '</td>';
            $ret .= '</tr>';
        }

    }

    $ret .= '</tbody>';
    $ret .= '</table>';

    return $ret;
}

function _handle_image( $current_file, $func, $params, $display ) {
    $args = argify( $params );
    $image_file = undirify( file_or( $args['file'], null, $current_file ) );
    $list_file  = file_or( $args['list'], false, $current_file );

    $width      = set_or( $args['width'],   false );
    $height     = set_or( $args['height'],  false );
    
    if( $list_file !== false ) {
        if( !git_file_exists( ( $list_file = dirify( $list_file ) ) ) ) {
            $replacement = '<div class="image-collection">';
    
            $replacement .= '<div><a class="wikilink edit" href="index.php?file=' . undirify( $list_file ) . '">' . $display . '</a></div>';
    
        } else  {
            $head_commit = git_file_head_commit( $list_file );
    
            $view = git_view( $list_file, $head_commit );
    
            $matched_files = array();
    
            foreach( preg_split( '/\r?\n/', $view["$head_commit:$list_file"] ) as $line ) {
                if( $line == "" ) { continue; }
    
                $matched_files = array_merge(
                    $matched_files,
                    collect_files( 
                        $line,
                        $list_file
                    )
                );
            }
    
            $matched_files = array_filter(
                $matched_files,
                function( $a ) {
                    return detect_extension( $a, null ) == "image";
                }
            );
    
            $replacement = '<div class="image-collection">';
    
            $replacement .= '<div><a href="index.php?file=' . undirify( $list_file ) . '">' . $display . '</a></div>';
    
            foreach( $matched_files as $image_file ) {
    
                $replacement .=  '<a class="wikilink" href="' . "index.php" . '?file=' . he( $image_file ) . '">';
                $replacement .= '<img ' 
                    . ( $width !== false ? "width=\"$width\" " : '' ) 
                    . ( $height !== false ? "height=\"$height\" " : '' ) 
                    . ' title="' . he( $display ) . '"'
                    . ' src="' . "raw.php" . '?file=' . he( $image_file ) 
                    . '">';
                $replacement .= '</a>';
            }
    
    
            $replacement .= '</div>';
        }
    
    
    } else {
    
        $replacement =  '<a class="wikilink" href="' . "raw.php" . '?file=' . he( $image_file ) . '">';
        # $replacement .= '<img title="' . he( $display ) . '" src="' . "raw.php" . '?file=' . he( $image_file ) . '">';
        $replacement .= '<img ' 
            . ( $width !== false ? "width=\"$width\" " : '' ) 
            . ( $height !== false ? "height=\"$height\" " : '' ) 
            . ' title="' . he( $display ) . '"'
            . ' src="' . "raw.php" . '?file=' . he( $image_file ) 
            . '">';

        $replacement .= '</a>';
    
    }

    return $replacement;
}



?>
