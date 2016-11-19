<?
require_once( dirname( __FILE__ ) . '/config.php' );
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/display.php' );
require_once( dirname( __FILE__ ) . '/funcify.php' );



function collection_operand( $op, &$cur, &$tag_counts ) {
    $ret = false;

    switch( $op['type'] ) {
        case "match":

            if( preg_match( "/" . $op['match'] . "/", $cur->plaintext ) ) {

                $ret = true;
            }
    
            break;
        case "offset":
            if( $op['tag'] == "h" ) {

                if( preg_match( '/^h[1-6]$/', $cur->tag ) && $tag_counts['h'] == $op['num']  ) {
    
                    $ret = true;
                }

            } else {

                if( $cur->tag == $op['tag'] && $tag_counts[$op['tag']] == $op['num']  ) {
    
                    $ret = true;
                }
            }
            break;
        default:
            print_r( $op );
            die("Unknown range operator: " .  $op['type'] );
    }

    return $ret;
}

function node_next( $cur ) {

    if( $cur->first_child() != null ) {
        $cur = $cur->first_child();
    } else {
        if( $cur->next_sibling() != null ) {
            # echo "sibling\n";
            $cur = $cur->next_sibling();
        } else {
            if( $cur->parent() != null ) {
                # echo "parent\n";
                $cur = $cur->parent()->next_sibling();
            } else {
                $cur = null;
            }
        }
    }

    return $cur;
}

# function doc_select_helper( $dom, $operators, $mode = "INVALID" ) {
# 
#     $ret = '';
# 
#     $cur = $dom->find( '*', 0 );
#     
#     $tag_counts = array();
#     $state = array();
# 
#     $captured_elements = array();
# 
#     while( $cur != null ) {
#     
#         $success = false;
#     
#         # Increment out tag counts...
#         if( preg_match( '/^h[1-6]$/', $cur->tag ) ) {
#     
#             $tag_counts[ 'h' ]++;
#     
#         } else {
#     
#             $tag_counts[ $cur->tag ]++;
#     
#         }
#     
#         $already_skipped = false;
#    
#         if( $mode == "RANGE" ) {
#             $a = collection_operand( $operators[0], $cur, &$tag_counts );
#             $b = collection_operand( $operators[1], $cur, &$tag_counts );
# 
#             if( tristate( $a, $b, &$state ) ) {
# 
#                 if( !( $a || $b ) ) {
# 
#                     $captured_elements[] = $cur;
# 
#                 } else {
#                     if( $a && $operators[0]['inclusive'] ) {
#                         $captured_elements[] = $cur;
#                     }
# 
#                     if( $b && $operators[1]['inclusive'] ) {
# 
#                         $captured_elements[] = $cur;
#                     }
#                 }
# 
#                 $cur = $cur->next_sibling();
# 
#                 $already_skipped = true;
#             }
# 
#         } else {
#             if( $mode == "SINGLE" ) {
#                 if( collection_operand( $operators, $cur, &$tag_counts ) ) {
# 
#                     $captured_elements[] = $cur;
# 
#                     $cur = $cur->next_sibling();
# 
#                     $already_skipped = true;
#                 }
#             } else {
#                 die( "Unknown mode: $mode" );
#             }
#         }
#     
#         if( $cur != null && !$already_skipped ) {
#             $cur = node_next( $cur );
#         }
#     
#         if( $cur == null ) {
#             break;
#         }
#     }
# 
#     return $captured_elements;
# 
# }

# If a child entity (or the tag itself) comes back as 
# matching successfully, what action do we perform?
#  - replace: Do not use only the children matched from
#    the return of the child call, instead return the
#    parent entity wholesale.
#  - bubble (the default): Return the parent, with its children
#    associated as 'children'.

$tag_actions = array(
    'p'             =>  'replace',
    'blockquote'    =>  'bubble',
    'root'          =>  'ignore'
);

function traverse_select_helper( &$cur, &$operators, $mode, &$state, &$tag_counts, $depth = 0 ) {
    GLOBAL $tag_actions;

    $is_found = false;
    $ret_html = array();
    $i_match = false;
    $ignore_self = false;

    if( $cur == null ) {
        $is_found = false;
        $ret_html = array();
    } else {

        # Maintain tag counts
        # echo "Tag: " . $cur->tag;
        if( $cur->tag != '' && $cur->tag != 'root' ) {
            if( preg_match( '/^h[1-6]$/', $cur->tag ) ) {
                $tag_counts[ 'h' ]++;
            } else {
                $tag_counts[ $cur->tag ]++;
            }
        }

        # echo str_repeat( " ", ($depth*4) ) . "(" . $cur->tag . ": " . excerpt( $cur->innertext ) . "\n";

        # Test against current node:
        $i_match = false;

        # Don't match on root
        if( $cur->tag != 'root' ) { 
            
            if( $mode == "RANGE" ) {


                $a = collection_operand( $operators[0], $cur, $tag_counts );
                $b = collection_operand( $operators[1], $cur, $tag_counts );

                if( tristate( $a, $b, $state ) ) {

                    $i_match = true;
                    if( $a && !$operators[0]['inclusive'] ) {
                        $ignore_self = true;
                    }

                    if( $b && !$operators[1]['inclusive'] ) {
                        $ignore_self = true;
                    }
                }

            } else {
                if( $mode == "SINGLE" ) {

                    if( collection_operand( $operators, $cur, $tag_counts ) ) {

                        $i_match = true;
                    } else {
                        # echo 'failed single match';
                    }
                } else {
                    die( "Unknown mode: $mode" );
                }
            }
        }

        if( $i_match && isset( $tag_actions[ $cur->tag ] ) && $tag_actions[$cur->tag] == 'replace'  ) {
            # We're "replacing" our references to any children, only 
            # returning us as the parent to capture all children
            if( !$ignore_self ) {
                $ret_html[] = $cur->outertext;

            }
        } else {
            if( $cur->first_child() == null ) {
                # Childless elements
                if( $i_match && !$ignore_self ) {
                    $ret_html[] = $cur->outertext;
                }
            } else {

                $matched_children = array();
                foreach( $cur->children() as $child ) {
                    list( $child_found, $child_returns ) = traverse_select_helper( 
                        $child, 
                        $operators, 
                        $mode, 
                        $state, 
                        $tag_counts,
                        $depth + 1
                    );

                    $matched_children = array_merge( $matched_children, $child_returns );

                    if( $child_found ) {
                        $is_found = true; # Set if any child came back with a successful match.
                    }

                }

                if( $is_found || $i_match ) {
                    if( $ignore_self || isset( $tag_actions[ $cur->tag ] ) && $tag_actions[$cur->tag] == 'ignore'  ) {
                        $ret_html = $matched_children;
                    } else {
                        $ret_html[] = '<' . $cur->tag . '>'
                            . join( '', $matched_children )
                            . '</' . $cur->tag . '>'
                        ;
                    }
                }
            }
        }
    }

    # echo  str_repeat( " ", $depth*4) .  "returning " . ( $i_match || $is_found ? 't' : 'f' ) . ", " . count ($ret_tree ) . ")\n";

    return array( 
        ( $i_match || $is_found ),  # Either I match, or one of my child traversals matched.
        $ret_html 
    );
}

function doc_select( $file, $contents, $components ) {
    GLOBAL $allowed_tags;
    require_once( dirname( __FILE__ ) . '/simple_html_dom.php' );
   
    static $tag_pattern;

    $ret = '';

    if( !isset( $tag_pattern ) ) {
        $tmp = array();
        foreach( $allowed_tags as $t ) {
            $tmp[] = trim( $t, '<>' );
        }

        $tag_pattern = join('|', $tmp );
    }
    
    $dom = str_get_html( $contents );

    $cur = $dom->find('*', 0 );
    $cur = $cur->parent();

    $range_state = array();
    $tag_counts = array();
    $was_found = false;

    $captured_elements = array();

    foreach( preg_split( '/\s*,\s*/', $components ) as $selection ) {


        $matches = array();

        # Single operator...
        if( preg_match( "/^($tag_pattern)(([0-9]+)|(\{([^\}]+)\}))$/", $selection, $matches ) ) {

            $tag = strtolower( $matches[1] );

            $operator_matches = array();
            if( preg_match( '/^([0-9]+)$/', $matches[2], $operator_matches ) ) {

                $num = $operator_matches[1];
                $operator = array(
                    'type'  =>  'offset',
                    'tag'   =>  $tag,
                    'num'   =>  $num
                );


                list( $was_found, $ret_elements ) = traverse_select_helper( 
                    $cur,
                    $operator,
                    "SINGLE",
                    $range_state,
                    $tag_counts
                );

                if( $was_found ) {
                    $captured_elements = array_merge( $captured_elements, $ret_elements );
                }


            } else {
                if( preg_match( '/^{([^}]+)}$/', $matches[2], $operator_matches ) ) {

                    $search = $operator_matches[1];
                    $operator = array(
                        'type'  =>  'match',
                        'tag'   =>  $tag,
                        'match' =>  $search
                    );

                    list( $was_found, $ret_elements ) = traverse_select_helper( 
                        $cur,
                        $operator,
                        'SINGLE',
                        $range_state,
                        $tag_counts
                    );

                    if( $was_found ) {
                        $captured_elements = array_merge( $captured_elements, $ret_elements );
                    }
                }
            }

        } else {


            $range = preg_split( '/\s*-\s*/', $selection, 2 );

            if( $range[0] && $range[1] ) {

                $left_inclusive = true;
                $right_inclusive = true;

                $inclusion_matches = array();
                if( preg_match( '/^([\(\[])([^\)\]]+)$/', $range[0], $inclusion_matches ) ) {
                    $left_inclusive = ( $inclusion_matches[1] == "(" ? false : true );
                    $range[0] = $inclusion_matches[2];
                }

                if( preg_match( '/^([^\(\[]+)([\)\]])$/', $range[1], $inclusion_matches ) ) {
                    $right_inclusive = ( $inclusion_matches[2] == ")" ? false : true );
                    $range[1] = $inclusion_matches[1];
                }

                $range_operators = array();

                foreach( $range as $r ) {

                    if( preg_match( "/^($tag_pattern)([0-9]+)$/", $r, $matches ) ) {
                        $range_operators[] = array(
                            'type'  =>  'offset',
                            'tag'   =>  $matches[1],
                            'num'   =>  $matches[2]
                        );
                    } else {

                        if( preg_match( "/^($tag_pattern){(.+)}$/", $r, $matches ) ) {

                            $range_operators[] = array(
                                'type'  =>  'match',
                                'tag'   =>  $matches[1],
                                'match' =>  $matches[2]
                            );
                        } else {
                            die( "Invalid range pattern: $r" );
                        }
                    } 
                }

                $range_operators[0]['inclusive'] = $left_inclusive;
                $range_operators[1]['inclusive'] = $right_inclusive;

                # print_r( $range_operators );

                list( $was_found, $ret_elements ) = traverse_select_helper( 
                    $cur, 
                    $range_operators, 
                    "RANGE",
                    $range_state,
                    $tag_counts
                );

                if( $was_found ) {
                    $captured_elements = array_merge( $captured_elements, $ret_elements );
                }
            }
        }
    }

    # print_r( $captured_elements );

    # $first = array_shift( $captured_elements );

    # echo "$file, $components, was found?" . ( $was_found ?  'true' : 'false' );
    # echo $first['node']->tag;

    # if( count( $captured_elements  ) <= 0 ) {
    #     $ret = 'No matches.';
    # } else {

    #     foreach( $captured_elements as $e ) {
    #         if( $e['node']->tag != 'root' ) {
    #             # $ret .= $e['node']->tag;
    #             $ret .= $e['node']->outertext;
    #         }
    #     }
    # }

    # $unique_top_elements = array();

    # foreach( $captured_elements as $e ) {
    # 
    #     while( $e['node']->parent() != null && $e['node']->parent()->tag != 'root' ) {
    #         $e = $e['note']->parent();
    #     }

    #     $ot = $e->outertext;

    #     if( !isset( $unique_top_elements[ $ot ] ) ) {
    #         $ret .= $ot;
    #         $unique_top_elements[$ot] = 1;
    #     }
    # }

    $ret = join('', $captured_elements );

    return $ret;
}

$collection_loop_detection = array();
function collect_files( $lines = array(), $current_file = null ) {
    GLOBAL $collection_loop_detection;

    $file_collection = array();

    if( !is_array( $lines ) ) {
        $lines = array( $lines );
    }

    foreach( $lines as $line ) {

        if( $line == "" ) {
            continue;
        }

        list( $_file, $components ) = explode( ":", $line, 2 );

        if( $current_file != null ) {
            $_file = interpolate_relative_path( $_file, $current_file );
        }

        if( strpos( $_file, '@' ) === 0 ) {

            // We provide the ability to "import" other collections
            $_file = substr( $_file, 1 );

            if( git_file_exists( $_file ) ) {
                
                $file_collection = array_merge( 
                    $file_collection,
                    collect_files( 
                        preg_split( 
                            '/\r?\n/', 
                            git_file_get_contents( $_file )
                        ),
                        $current_file
                    )
                );
            }

        } else {

            // Case insensitive
            $glob_matches = git_glob( dirify( trim( $_file ) ), false );

            if( count( $glob_matches ) <= 0 ) { 
                $ret .= "";
            } else {

                foreach( $glob_matches as $matched_file ) {


                    # $matched_file = dirify( trim( $matched_file ) );

                    if( !file_or( $matched_file, false ) || $file == $matched_file || isset( $collection_loop_detection[$matched_file] ) ) {
                        next;
                    } else {
                        $collection_loop_detection[$matched_file] = 1;


                        if( $components == null || $components == "" ) {
                            # We're relying on just the normal display mechanism for the file.

                            if( !git_file_exists( $matched_file ) ) {
                                # $ret .= '<p>(Does not exist: ' . linkify( '[[' . undirify( $matched_file ) . ']]' ) . ')</p>';
                            } else {

                                $file_collection[] = $matched_file;
                            }

                        } else {
                            # $ret .= 'Not implemented';

                            $tags_to_search = array();


                            foreach( preg_split( '/,\s*/', $components ) as $tag ) {
                                
                                $tag = preg_replace( '/^~/', '', $tag );

                                if( tag_or( $tag, false ) !== false ) {
                                    $tags_to_search[] = $tag;
                                }
                            }

                            if( count( $tags_to_search ) > 0 ) {

                                $match_results = git_tags( 
                                    $tags_to_search, 
                                    $matched_file
                                );

                                foreach ($match_results as $file => $result ) {
                                    $file_collection[] = $file;
                                }
                            }
                        }
                    }

                    unset( $collection_loop_detection[$matched_file] );
                }
            }
        }
    }

    return $file_collection;

}


function collection_display( $file, &$contents, $as = "collection", $is_preview = false ) {
    GLOBAL $collection_loop_detection;
    perf_enter( "collection_display" );

    $ret = '';

    $collection_loop_detection[$file] = 1;

    $file_collection = array();

    foreach( preg_split( "/(\r)?\n/", $contents ) as $line ) {

        $file_collection = array_merge( 
            $file_collection,
            collect_files( $line, $file )
        );

    }


    $file_collection = array_unique( $file_collection );

    $file_collection = array_filter( 
        $file_collection,
        function( $a ) {
            return can( "read", $a );
        }
    );

    # print_r( $file_collection );

    if( count( $file_collection ) <= 0 ) {
        $ret .= "No matches in collection.";
    } else {

        if( $as == "list" ) {
            $ret .= gen_list( $file_collection );
        } else {

            foreach( $file_collection as $matched_file ) {

                if( $as == "collection" ) {
                    // Insert a "hidden" header, indicating the source file for the
                    // following contents
                    $ret .= "<h6 class=\"collection-file-boundary\">" . linkify( "[[" . undirify( $matched_file ) . "]]" ) . "</h6>";
                }

                $head_commit = commit_or( git_file_head_commit( $matched_file ), "HEAD" );

                $view = git_view( $matched_file , $head_commit );

                $extension = detect_extension( $matched_file, null );

                foreach( $view as $commit_file => &$contents ) {
                    list( $c, $f ) = explode( ":", $commit_file );

                    # echo "$c $f";

                    if( $f == $matched_file ) {
                        if( $as == "collection" ) {
                            # $ret .= _display( $matched_file,  $contents, null, true, $is_preview );

                            $ret .= metaify_prepost( 
                                $matched_file, 
                                $extension, 
                                $contents, 
                                true,       // Caching
                                $is_preview 
                            );

                        } elseif( $as == "raw" ) {
                            if( $extension == "collection" ) {

                                # $ret .= _display( $matched_file,  $contents, $as, true, $is_preview );

                                $ret .= metaify_prepost( 
                                    $matched_file, 
                                    $extension, 
                                    $contents, 
                                    true,       // Caching
                                    $is_preview 
                                );
                                 
                            } else {
                                $ret .= $contents;
                            }
                        } elseif( $as == "clean" ) {

                            if( $extension == "collection" ) {

                                $ret .= metaify_prepost( 
                                    $matched_file, 
                                    $extension, 
                                    $contents, 
                                    true,       // Caching
                                    $is_preview 
                                );
                                 
                            } else {
                                $ret .= _collect_display_clean( $matched_file, $contents );
                            }


                        }
                        break;
                    }
                }
            }
        }
    }

    unset( $collection_loop_detection[$file] );


    return $ret . perf_exit( "collection_display" );



}


function _collect_display_clean( $file, &$contents ) {

    return _display_clean( $file, $contents );
}


?>
