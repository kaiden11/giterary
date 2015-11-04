<?
require_once( dirname( __FILE__ ) . '/git.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/collection.php');
require_once( dirname( __FILE__ ) . '/funcify.php');
require_once( dirname( __FILE__ ) . '/cache.php');
require_once( dirname( __FILE__ ) . '/alias.php');


function gen_assoc( $file = null, $assoc_type = null ) {

    perf_enter( "gen_assoc" );

    if( !can( "assoc", implode( ":", array( $file, $assoc_type  ) ) ) ) {
        return render('not_logged_in', array() );
    }

    if( $file == null ) {

        if( $assoc_type == null ) {

            $all_assoc_types = all_assoc_types();

            return render( 
                'gen_all_assoc_type', 
                array(
                    'all_assoc_types'   =>  &$all_assoc_types
                )
            ) .  perf_exit( "gen_assoc" );

        } else {

            $associations = file_assoc( $assoc_type );

            $associations = array_filter(
                $associations,
                function( $a ) {
                    return can( "read", $a['source'] ) && can( "read", $a['target'] );
                }
            );

            $all_assoc_types = array_keys( all_assoc_types() );



            return render( 
                'gen_assoc_type', 
                array(
                    'assoc_type'        =>  $assoc_type,
                    'associations'      =>  &$associations,
                    'all_assoc_types'   =>  &$all_assoc_types
                )
            ) .  perf_exit( "gen_assoc" );
        }


    } else {

        $targets = file_assoc_targets( $file, $assoc_type );
        $sources = file_assoc_sources( $file, $assoc_type );

        # print_r( $targets );

        return render( 
            'gen_assoc', 
            array(
                'file'          =>   $file,
                'targets'       =>   &$targets,
                'sources'       =>   &$sources
            )
        ) .  perf_exit( "gen_assoc" );

    }
  

}

function gen_orphans( ) {

    perf_enter( "gen_orphans" );

    if( !can( "assoc", implode( ":", array( $file, $assoc_type  ) ) ) ) {
        return render('not_logged_in', array() );
    }

  
    $orphans = assoc_orphans();

    $orphans = array_filter(
        $orphans,
        function( $a ) {
            return can( "read", $a['path'] );
        }
    );

    return render( 
        'gen_orphans', 
        array(
            'orphans'       =>   &$orphans,
        )
    ) .  perf_exit( "gen_orphans" );
}

function gen_wanted( ) {

    perf_enter( "gen_wanted" );

    if( !can( "assoc", implode( ":", array( $file, $assoc_type  ) ) ) ) {
        return render('not_logged_in', array() );
    }

  
    $wanted = assoc_wanted();


    $wanted = array_filter(
        $wanted,
        function( $a ) {
            return (
                can( "read", $a['source'] ) &&
                can( "read", $a['target'] )
            );
        }
    );


    return render( 
        'gen_wanted', 
        array(
            'wanted'       =>   &$wanted,
        )
    ) .  perf_exit( "gen_wanted" );
}

function all_assoc_types( ) {

    perf_enter( 'all_assoc_types' );

    $ret = array();

    $assoc_dir = dirify( ASSOC_DIR, true );
    
    $assoc_paths = git_glob( "$assoc_dir/*/*" );

    foreach( $assoc_paths as $ap ) {
        list( $_dummy, $source_md5, $assoc_type ) = explode( "/", $ap );


        $k = undirify( $assoc_type, true );
        if( !isset( $ret[ $k ] ) ) {

            $ret[ $k ] = 0;
        }

        $ret[ $k ]++;
    }

    perf_exit( 'all_assoc_types' );

    return $ret;
}



function assoc_wanted( ) {

    perf_enter( 'assoc_wanted' );

    $ret = array();

    $assoc_dir = dirify( ASSOC_DIR, true );
    
    $source_paths = git_glob( "$assoc_dir/*" );
    $association_paths = git_glob( "$assoc_dir/*/*/*" );

    $all_sources = array();

    foreach( $source_paths as $sp ) {
        list( $_dummy, $source_md5 ) = explode( "/", $sp );

        $all_sources[ undirify( $source_md5, true ) ] = 1;
    }

    foreach( $association_paths as $ap ) {
        list( $_dummy, $source_md5, $assoc_type, $target_md5 ) = explode( "/", $ap );

        $source_md5 = undirify( $source_md5, true );

        # $potential_path = implode( "/", array( $_dummy, $target_md5 ) );
        if( ALIAS_ENABLE ) {
            $target_md5 = assoc_file_normalize( 
                file_or(
                    assoc_file_denormalize( 
                        $target_md5
                    ),
                    false
                )
            );
        }

        if( !isset( $all_sources[ $target_md5 ] ) ) {

            $ret[] = array( 
                "type"      =>  $assoc_type,
                "source"    =>  assoc_file_denormalize( $source_md5 ),
                "target"    =>  assoc_file_denormalize( $target_md5 )
            );
        }
    }

    perf_exit( 'assoc_wanted' );

    return $ret;
}




function assoc_orphans( ) {

    perf_enter( 'assoc_orphans' );

    $ret = array();

    $assoc_dir = dirify( ASSOC_DIR, true );

    $source_paths = git_glob( "$assoc_dir/*" );
    $association_paths = git_glob( "$assoc_dir/*.dir/*.dir/*" );

    $all_targets = array();
    $all_sources = array();

    foreach( $source_paths as $sp ) {
        list( $_dummy, $source_md5 ) = explode( "/", $sp );

        $all_sources[ undirify( $source_md5, true ) ] = 1;
    }

    foreach( $association_paths as $ap ) {
        list( $_dummy, $source_md5, $assoc_type, $target_md5 ) = explode( "/", $ap );

        // If we're honoring aliases, we'll need to translate
        // the target reference to its far-end normalized
        // value before checking
        if( ALIAS_ENABLE ) {
            $target_md5 = assoc_file_normalize( 
                file_or(
                    assoc_file_denormalize( 
                        $target_md5
                    ),
                    false
                )
            );
        }

        if( !isset( $all_targets[ $target_md5 ] ) ) {

            $all_targets[ $target_md5 ] = 1;
        }
    }

    #    print_r( $all_sources );
    #    print_r( $all_targets );

    foreach( $all_sources as $source_md5_key => $dummy ) {
        if( !isset( $all_targets[ $source_md5_key ] ) ) {
            # This source is targeted by nobody. Orphan!

            $ret[] = array( 
                # "path"      =>  $v["$c:$source_path"]
                "path"      =>  assoc_file_denormalize( $source_md5_key )
            );
        }
    }

    perf_exit( 'assoc_orphans' );

    return $ret;
}




function file_assoc_sources( $file, $assoc_type = null ) {

    perf_enter( 'file_assoc_sources' );

    $ret = array();

    $file_md5 = assoc_file_normalize( $file );

    $assoc_dir = dirify( ASSOC_DIR, true );
  
    $association_paths = array();
    if( $assoc_type == null ) {
        $association_paths = git_glob( "$assoc_dir/*/*/$file_md5" );
    } else {

        $assoc_type = assoc_type_normalize( $assoc_type );
        $association_paths = git_glob( "$assoc_dir/*/$assoc_type.dir/$file_md5" );
    }

    foreach( $association_paths as $ap ) {
        list( $_dummy, $source_md5, $assoc_type, $_dummy_file_md5 ) = explode( "/", $ap );

        $source_path = implode( "/", array( $_dummy, undirify( $source_md5, true ) ) );

        if( !git_file_exists( $source_path ) ) {
            continue;
        } else {

            $assoc_type = undirify( $assoc_type, true );

            $ret[] = array( 
                "type"      =>  $assoc_type,
                # "path"      =>  $v["$c:$source_path"]
                "path"      =>  assoc_file_denormalize( undirify( $source_md5, true ) )
            );
        }
    }

    perf_exit( 'file_assoc_sources' );

    return $ret;
}

function file_assoc( $assoc_type ) {

    perf_enter( 'file_assoc' );
    $ret = array();

    $assoc_dir = dirify( ASSOC_DIR, true );
  
    $assoc_type = assoc_type_normalize( $assoc_type );

    $association_paths = git_glob( "$assoc_dir/*/$assoc_type.dir/*" );

    # print_r( $association_paths );

    foreach( $association_paths as $ap ) {
        list( $_dummy, $source_md5, $_dummy_assoc_type, $target_md5 ) = explode( "/", $ap );

        if( !git_file_exists( $ap ) ) {
            continue;
        } else {

            $ret[] = array( 
                "type"      =>  $assoc_type,
                "source"    =>  assoc_file_denormalize( undirify( $source_md5, true ) ),
                "target"    =>  assoc_file_denormalize( $target_md5 )

            );
        }
    }

    perf_exit( 'file_assoc' );

    return $ret;
}



function file_assoc_targets( $file, $assoc_type = null, $get_sequence = true ) {

    perf_enter( 'file_assoc_targets' );
    $ret = array();

    $file_md5 = dirify( assoc_file_normalize( $file ), true );
    
    $assoc_dir = dirify( ASSOC_DIR, true );
  
    $association_paths = array();
    if( $assoc_type == null ) {

        $association_paths = git_glob( "$assoc_dir/$file_md5/*.dir/*" );

    } else {
        $assoc_type = assoc_type_normalize( $assoc_type );

        $association_paths = git_glob( "$assoc_dir/$file_md5/$assoc_type.dir/*" );

    }

    foreach( $association_paths as $ap ) {
        list( $_dummy, $_dummy_source_md5, $assoc_type, $target_md5 ) = explode( "/", $ap );


        if( !git_file_exists( $ap ) ) {
            continue;
        } else {

            $sequence = null;

            if( $get_sequence ) {
                $c = unserialize( git_file_get_contents( $ap ) );
                $sequence = $c['sequence'];
            }

            $assoc_type = undirify( $assoc_type, true );

            $ret[] = array( 
                "type"      =>  $assoc_type,
                # "path"      =>  $v["$c:$ap"]
                "path"      =>  assoc_file_denormalize( $target_md5 ),
                "sequence"  =>  $sequence

            );
        }
    }

    perf_exit( 'file_assoc_targets' );

    return $ret;
}

function build_assoc( $file, $perform_commit = true  ) {

    $backout_commit = git_head_commit();

    $file_commit = git_file_head_commit( $file, false );

    $associations = collect_associations( $file, $file_commit );

    $actions = maintain_associations( $file, $associations );

    $git_ret = null;
    $err = false;

    if( git_is_working_directory_clean() ) {
        # print_r( $actions );
        # die( "Working directory clean" );
    } else {

        foreach( $actions as $a ) {
            
            list( $prefix, $_file ) = explode( ":", $a, 2 );

            if( $prefix == "add" ) {
               
                $git_ret = git( "add " . escapeshellarg( $_file ) );

            } elseif( $prefix == "rm" ) {

                $git_ret = git( "rm " . escapeshellarg( $_file ) );
                            
            } else {
                git( "reset --hard $backout_commit" );
                git( "clean -fd" );
                die( "Unhandled prefix: $prefix" );
            }

            if( $git_ret['return_code'] != 0 ) {
                $err = $git_ret['out'];
                break;
            }
        }

        if( $err !== false ) {

            git( "reset --hard $backout_commit" );
            git( "clean -fd" );
            return gen_error( $err );

        } else {

            if( $perform_commit ) {
                $commit_notes = "Maintaining associations for '$file'";

                list( $commit_ret, $commit_ret_message ) = git_commit(
                    $_SESSION['usr']['git_user'],
                    $commit_notes 
                );

                if( !$commit_ret ) {
                    git( "reset --hard $backout_commit" );
                    git( "clean -fd" );
                    return gen_error( $err );
                }
            }
        }
    }

    return array(
        &$associations,
        &$actions
    );
}


function gen_build_assoc( $file ) {

    perf_enter( "gen_build_assoc" );

    if( !can( "assoc", implode( ":", array( $file, $assoc_type  ) ) ) ) {
        return render('not_logged_in', array() );
    }

    
    list( $associations, $actions ) = build_assoc( $file );

    return render( 
        'gen_build_assoc', 
        array(
            'file'          =>   $file,
            'associations'  =>   &$associations
        )
    ) .  perf_exit( "gen_build_assoc" );
}


function collect_associations( $file, $commit = 'HEAD' ) {

    $ret = array();

    $file = file_or( $file, null );
    $commit = commit_or( $commit, null );

    if( is_dirifile( $file ) ) {
        # Not supported at the moment
    } else {

        $ext = detect_extension( $file, null );

        $assoc_target_counter = 0;

        if( in_array( $ext, array( 'collection', 'list' ) ) ) {

            # echo "Not yet implemented.";

            $content = git_file_get_contents( $file, $commit );
            $files = array();

            $collect_type_counter = 0;
            foreach( preg_split( '/(\r)?\n/', $content ) as $line ) {
                $collected_files = collect_files( $line, $file );

                $files =    array_merge(
                    $files,
                    array_map(
                        function( $a ) use ( $assoc_target_counter, $collect_type_counter ) {

                            $r = _sequence_tuple( $a, $assoc_target_counter, $collect_type_counter );
                            return $r;
                        },
                        $collected_files
                    )
                );

                $assoc_target_counter++;
                $collect_type_counter++;
            }

            $ret['collect'] = $files;

        } else {

            if( !in_array( $ext, array( 'image', 'audio' ) ) ) {

                $content = git_file_get_contents( $file, $commit );

                $ret['link'] = collect_link_associations( $file, $content, $assoc_target_counter );

                foreach( collect_function_link_associations( $file, $content, $assoc_target_counter ) as $assoc_type => $files ) {
                    if( $assoc_type == 'link' ) {
                        $ret['link'] = array_merge( $ret['link'], $files );
                    } else {
                        # echo "building assoc type: $assoc_type";
                        $ret[$assoc_type] = $files;
                    }
                }
            }
        }
    }

    return $ret;
        
}

function _sequence_tuple( $path, $sequence, $type_sequence ) {

    return array(
        'path'          =>  $path,
        'sequence'      =>  $sequence,
        'type_sequence' =>  $type_sequence
    );
}

function collect_function_link_associations( $file, $content, &$assoc_target_counter ) {

    GLOBAL $functionlink_pattern;

    perf_enter( "collect_function_link_associations" );
    $matches = array();
    $ret = array();

    $type_counters = array(
        "template"  =>  0   # May be part of parameters, but not the $func,
                            # initialize here.
    );

    preg_match_all(
        $functionlink_pattern,
        $content, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    foreach( $matches as $match ) {
        
        $escape = $match[1][0];

        $func = $match[3][0];
        $params = $match[5][0];
        $display = $match[11][0];


        if( $escape == "\\" ) {
           # Do nothing
        } else {


            if( !isset( $ret[ $func ] ) ) {
                $ret[ $func ] = array();                
            }

            if( !isset( $type_counters[ $func ] ) ) {
                $type_counters[ $func ] = 0;
            }

            switch( $func ) {
                case "image":
                case "csv":
                case "table":
                case "include":
                case "transclude":
                case "assoc":
                case "associations":
                case "relation":
                case "relations":
                    $args = argify( $params ); 

                    if( ( $candidate = file_or( $args['file'], false, $file ) ) !== false ) {
                        # $ret[$func][]  =   dirify( $candidate );
                        $ret[$func][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters[$func] );
                    }

                    break;
                case "jot":
                    $args = argify( $params ); 

                    if( ( $candidate = file_or( $args['file'], false, $file ) ) !== false ) {
                        # $ret[$func][]  =   dirify( $candidate );
                        $ret[$func][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters[$func] );
                    }

                    if( ( $candidate = file_or( $args['template'], false, $file ) ) !== false ) {
                        # $ret["template"][]  =   dirify( $candidate );
                        $ret["template"][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters["template"] );
                        $type_counters[ "template" ]++;
                    }

                    break;

                case "new":
                case "template":
                    $args = argify( $params ); 

                    # if( ( $candidate = file_or( $args['file'], false, $file ) ) !== false ) {
                    #     $ret[$func][]  =   dirify( $candidate );
                    # }

                    if( ( $candidate = file_or( $args['template'], false, $file ) ) !== false ) {

                        $ret["template"][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters[$func] );
                    }

                    break;


                case "ref":
                    $args = argify( $params ); 

                    # $display = strtolower( preg_replace( '/[^a-zA-Z0-9_]/', '_', $display ) );
                    $display = assoc_type_normalize( $display );

                    if( ( $candidate = file_or( $args['file'], false, $file ) ) !== false ) {
                        # $ret[$display][]  =   dirify( $candidate );
                        $ret[$display][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters[$display] );
                    }

                    break;


                case "list":
                    $args = argify( $params ); 

                    if( ( $candidate = file_or( $args['list'], false, $file ) ) !== false ) {
                        # $ret[$func][]  =   dirify( $candidate );
                        $ret[$func][]  = _sequence_tuple( dirify( $candidate ), $assoc_target_counter, $type_counters[$func] );
                    }
                    
                    if( !isset( $ret[ 'collect' ] ) ) {
                        $ret[ 'collect' ] = array();                
                    }

                    if( set_or( $args['file'], false ) !== false ) {
                        $files = collect_files( $args['file'], $file );

                        foreach( $files as $f ) {

                            # $ret[ 'collect' ][] = $f;
                            $ret["collect"][]  = _sequence_tuple( dirify( $f ), $assoc_target_counter, $type_counters["collect"] );
                        }
                    }

                    break;

                default:
                    # Do nothing
            }

            $assoc_target_counter++;
            $type_counters[ $func ]++;
        }
    }

    perf_exit( "collect_function_link_associations" );

    return $ret;
}


function collect_link_associations( $file, $content, &$assoc_target_counter ) {

    GLOBAL $wikiname_pattern;
    GLOBAL $wikilink_pattern;


    perf_enter( "collection_link_associations" );
    $matches = array();

    $ret = array();

    # $wikiname_pattern = '-_a-zA-Z0-9\.';
    # $wikilink_pattern = "@\[\[([$wikiname_pattern]+(\/[$wikiname_pattern]+)*)(\|([\w\s\.\,-]+))?\]\]@";
    //'<a href="index.php?file=$1">$3</a>';

    preg_match_all(
        $wikilink_pattern,
        $content, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $offset = 0;

    # print_r( $matches );
    foreach( $matches as $match ) {

        if( ( $candidate  =   file_or( $match[2][0], false, $file ) ) !== false ) {

            $ret[] = _sequence_tuple( $candidate, $assoc_target_counter, $assoc_target_counter );

            $assoc_target_counter++;
        }
    }

    perf_exit( "collection_link_associations" );

    return $ret;
}

function disassociate( $file, $perform_commit = true ) {

    $file = dirify( $file );

    $file_md5 = assoc_file_normalize( $file );

    $assoc_dir = dirify( ASSOC_DIR, true );

    if( ASSOC_ENABLE ) {

        $source     = dirify( "$assoc_dir/$file_md5" );
        $source_dir = dirify( $source, true );

        $target_files = git_glob( "$source_dir/*" );

        $to_remove = array_filter( 
            array_merge(
                array( 
                    $source
                ), 
                $target_files
            ),
            function( $v ) { 
                # Do not cache this lookup
                return git_file_exists( $v, 'HEAD', false ); 
            }
        );

        $backout_commit = git_head_commit();

        foreach( $to_remove as $r ) {
            $git_ret = git( "rm -r " . escapeshellarg( $r ) );
                            
            if( $git_ret['return_code'] != 0 ) {

                git( "reset --hard $backout_commit" );
                git( "clean -fd" );
                die( "Failed to remove '$r'" );
            }

            clear_all_caches( $r );
        }

        if( !git_is_working_directory_clean() ) {

            if( $perform_commit ) {

                $commit_notes = "Disassociating '$file'";

                list( $commit_ret, $commit_ret_message ) = git_commit(
                    $_SESSION['usr']['git_user'],
                    $commit_notes 
                );

                if( !$commit_ret ) {
                    git( "reset --hard $backout_commit" );
                    git( "clean -fd" );
                    return gen_error( $commit_ret_message );
                }
            }
        }
    }
}

function maintain_associations( $file, $associations = array() ) {

    // Array of activities
    $ret = array();

    $file = dirify( $file );

    $file_md5 = assoc_file_normalize( $file );

    $base_assoc_dir = dirify( ASSOC_DIR, true );

    $assoc_dir = preg_replace( '@/+@', '/', ( GIT_REPO_DIR . '/' . $base_assoc_dir  ) );

    if( ASSOC_ENABLE ) {


        if( !file_exists( $assoc_dir ) ) {


            if( !mkdir( $assoc_dir, 0777, true ) ) {
                die( "Unable to create '$assoc_dir'" );                
            } else {
                $ret[] = "add:$base_assoc_dir";
            }
        }

        if( !file_exists( "$assoc_dir/$file_md5" ) ) {

            if( file_put_contents( "$assoc_dir/$file_md5", $file ) === false ) {

                die( "Unable to create '$assoc_dir/$file_md5'" );

            } else {
                $ret[] = "add:$base_assoc_dir/$file_md5";
            }
        }


        $file_targets = array();

        $file_assoc_counter = 0;

        foreach( $associations as $assoc_type => &$files ) {

            if( !file_exists( "$assoc_dir/$file_md5.dir" ) ) {

                if( !mkdir( "$assoc_dir/$file_md5.dir", 0777, true ) ) {

                    die( "Unable to create '$assoc_dir/$file_md5.dir'" );                
                } else {
                    $ret[] = "add:$base_assoc_dir/$file_md5.dir";
                }
            }

            if( !file_exists( "$assoc_dir/$file_md5.dir/$assoc_type.dir" ) ) {

                if( !mkdir( "$assoc_dir/$file_md5.dir/$assoc_type.dir", 0777, true ) ) {

                    die( "Unable to create '$assoc_dir/$file_md5.dir/$assoc_type.dir'" );                
                } else {
                    $ret[] = "add:$base_assoc_dir/$file_md5.dir/$assoc_type.dir";
                }
            }

            foreach( $files as $assoc_file_tuple ) {
                $assoc_file = dirify( $assoc_file_tuple['path'] );
                $assoc_file_md5 = assoc_file_normalize( $assoc_file );

                # if( !file_exists( "$assoc_dir/$file_md5.dir/$assoc_type.dir/$assoc_file_md5" ) ) {
                # }

                if( 
                    file_put_contents( 
                        "$assoc_dir/$file_md5.dir/$assoc_type.dir/$assoc_file_md5", 
                        serialize( 
                            array(
                                'path'          =>  $assoc_file_tuple['path'],
                                'sequence'      =>  $assoc_file_tuple['sequence'],
                                'type_sequence' =>  $assoc_file_tuple['type_sequence']
                            )
                        )
                    ) === false 
                ) {

                    die( "Unable to create '$assoc_dir/$file_md5.dir/$assoc_type.dir/$assoc_file_md5'" );

                } else {
                    $ret[] = "add:$base_assoc_dir/$file_md5.dir/$assoc_type.dir/$assoc_file_md5";
                }
                    

                $file_targets["$assoc_type:$assoc_file_md5"] = 1;

            }
        }

    
        $target_paths = glob( "$assoc_dir/$file_md5.dir/*.dir/*" );

        if( $target_paths === false || !is_array( $target_paths ) ) {
            return $ret;
        }

        # Remove any targets for this file that no longer exist

        foreach( $target_paths as $target_path ) {
            $target = preg_replace( '@^' . preg_quote( GIT_REPO_DIR, '@' ) . '@', '', $target_path );

            $target_relative_path = ltrim( $target, '/' );

            list( $assoc_dir, $source_dir, $type_dir, $target_file ) = explode( '/', $target_relative_path );

            $type_dir = undirify( $type_dir, true );

            if( isset( $file_targets["$type_dir:$target_file"] ) ) {
                # echo "Got it!";
                # Do nothing
            } else {
                unlink( $target_path );

                $ret[] = "rm:$target_relative_path";
            }
        }
    }

    return $ret;

}

function assoc_file_normalize( $file ) {
    return str_replace( 
        "=",
        "_-_",
        base64_encode( 
            dirify( $file ) 
        )
    );
}

function assoc_file_denormalize( $file ) {
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


function assoc_type_normalize( $type ) {
    return strtolower( preg_replace( '/[^a-zA-Z0-9_*]/', '_', $type ) );
}

function gen_assoc_functionlink( $file, $assocs, $show_type, $args ) {

    return render( 
        'gen_assoc_functionlink', 
        array(
            'file'              =>  &$file,
            'file_collection'   =>  &$assocs,
            'show_type'         =>  $show_type,
            'args'              =>  &$args
        )
    );
}

?>
