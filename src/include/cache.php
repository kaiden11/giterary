<?
require( dirname( __FILE__ ) . '/config.php' );

$mem_cache = null;
function clear_cache( $tag, $discriminator = null ) {
    GLOBAL $mem_cache;

    GLOBAL $registered_cache_handlers;

    $ret = array();

    if( is_null( $tag ) || $tag == '' ) {
        die( 'Must submit tag to clear.' );
    } else {

        if( !$registered_cache_handlers || count( $registered_cache_handlers ) <= 0 ) {
            return $ret;
        } 

        foreach( $registered_cache_handlers as &$handler ) {
            if( !is_object( $handler[0] ) ) {
                print_r( $handler );
                die( "Not an object!" );
            }

            if( ! method_exists( $handler[0], $handler[3] ) ) {
                die( "Method " . $handler[3] . " does not exist on object!" );
            }

            #  echo "$tag:$discriminator";
            $ret = array_merge( 
                $ret,
                $handler[0]->{$handler[3]}( $tag, $discriminator )
            );
        }

        if( !is_null( $discriminator ) && $discimrinator != '' ) {
            $tag = "$tag.$discriminator";
        }
   
        // Clean up in-memory cache
        if( isset( $mem_cache[ $tag ] ) ) {
            unset( $mem_cache[ $tag ] );
            $ret[] = "MEMORY:$tag";
        } else {
            $keys_to_kill = array();
            if( $mem_cache && is_array( $mem_cache ) && count( $mem_cache ) > 0 ) {
                foreach( $mem_cache as $key => &$dummy ) {
                    
                    if( strpos( $key, $tag ) === 0 ) {
                        $keys_to_kill[] = $key;
                    }
                }

                foreach( $keys_to_kill as $k ) {
                    unset( $mem_cache[ $k ] );
                    $ret[] = "MEMORY:$k";
                }
            }
        }
    }

    return $ret;
}

function tag_expire_check( $tag ) {
    GLOBAL $tag_expirations;
    return isset( $tag_expirations[ $tag ] );
}

function encache( $tag, $discriminator, $value ) {
    GLOBAL $registered_cache_handlers;
    perf_enter( "encache" );
    if( is_null( $discriminator ) ) {
        die( "Invalid call to encache, no discriminator.");
    } 

    if( is_null( $tag ) ) { // value to prefix the cached file name with
        $tag = '';
    }

    if( is_null( $value ) ) { // value to prefix the cached file name with
        die( "Must pass non-null value to encache.");
    }

    if( !tag_expire_check( $tag ) ) {
        die( "encache: Unspecified expiration for tag: $tag" );
    }


    if( !$registered_cache_handlers || count( $registered_cache_handlers ) <= 0 ) {
        perf_exit(  'encache' );
        return;
    } 

    foreach( $registered_cache_handlers as &$handler ) {
        if( !is_object( $handler[0] ) ) {
            print_r( $handler );
            die( "Not an object!" );
        }

        if( ! method_exists( $handler[0], $handler[1] ) ) {
            die( "Method " . $handler[1] . " does not exist on object!" );
        }

        $handler[0]->{$handler[1]}( $tag, $discriminator, $value );
    }

    perf_exit( "encache" );

    return;
}

function decache( $tag, $discriminator  ) {
    GLOBAL $registered_cache_handlers;
    GLOBAL $mem_cache;

    if( !isset( $mem_cache ) ) {
        $mem_cache = array();
    }

    perf_enter( "decache" );
    perf_enter( "decache.$tag" );

    if( !tag_expire_check( $tag ) ) {
        die( "decache:Unspecified expiration for tag: $tag" );
    }

    if( is_null( $discriminator ) ) {
        
        die( "Invalid call to decache, no discriminator");
    } 

    # perf_enter( "decache.$tag.$discriminator" );
    $mem_cache_key = join(".", array( $tag, md5( $discriminator ) ) );

    # If we find the cached value already in the memory
    # cache, return that instead.
    if( isset( $mem_cache[ $mem_cache_key ] ) ) {
        perf_log( "decache.memory_hit.$tag" );

        perf_exit( "decache.$tag" );
        perf_exit( "decache" );

        return $mem_cache[ $mem_cache_key ];
    }

    if( !$registered_cache_handlers || count( $registered_cache_handlers ) <= 0 ) {
        perf_exit(  "decache.$tag" );
        perf_exit(  'decache' );
        return;
    } 

    $ret = null;

    foreach( $registered_cache_handlers as &$handler ) {
        if( !is_object( $handler[0] ) ) {
            print_r( $handler );
            die( "Not an object!" );
        }

        if( ! method_exists( $handler[0], $handler[2] ) ) {
            die( "Method " . $handler[2] . " does not exist on object!" );
        }

        $ret = $handler[0]->{$handler[2]}( $tag, $discriminator );

        # echo "wrapper: $ret\n";

        if( $ret != null ) {

            perf_log( 'decache.' . get_class( $handler[0] ) . '.hit' );

            $mem_cache[ $mem_cache_key ] = $ret;
            break;
        }
    }

    # perf_exit( "decache.$tag.$discriminator" );
    perf_exit( "decache.$tag" );
    perf_exit( "decache" );

    return $ret;
}

function clear_all_caches( $file ) {
    $head_commit = git_head_commit();
    $file_head_commit = git_file_head_commit( $file );
    $content = git_view_show_helper( $head_commit, $file );

    $extension = detect_extension( $file, null );

    $cleared = array();
    $cleared = array_merge( $cleared, clear_cache( 'file_head_commit',  path_to_filename( $file )  ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_helper',   "$head_commit:$file"       ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_helper',   "$file_head_commit:$file"  ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_helper',   "HEAD:$file"               ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_file_exists',   "$head_commit:$file"       ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_file_exists',   "$file_head_commit:$file"  ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_file_exists',   "HEAD:$file"               ) );

    $cleared = array_merge( $cleared, clear_cache( 'git_view_show_helper', "$file_head_commit:$file"    ) );
    $cleared = array_merge( $cleared, clear_cache( 'git_view_show_helper', "$head_commit:$file"         ) );

    $cleared = array_merge( $cleared, clear_cache( 'lookup',            "$file_head_commit:$file"  ) );
    $cleared = array_merge( $cleared, clear_cache( 'lookup',            "$head_commit:$file"       ) );

    $cleared = array_merge( $cleared, clear_cache( 'display',            "$extension::$content"     ) );

    $cleared = array_merge( $cleared, clear_cache( '_document_stats',    "$file:$head_commit"       ) );
    $cleared = array_merge( $cleared, clear_cache( '_document_stats',    "$file:$file_head_commit"  ) );
    $cleared = array_merge( $cleared, clear_cache( '_document_stats',    "$file:$head_commit:1"     ) );
    $cleared = array_merge( $cleared, clear_cache( '_document_stats',    "$file:$file_head_commit:1") );


    return $cleared;
}

function cache_keys( ) {
    # GLOBAL $mem_cache;
    GLOBAL $registered_cache_handlers;

    $ret = array();

    if( !$registered_cache_handlers || count( $registered_cache_handlers ) <= 0 ) {
        return $ret;
    } 

    foreach( $registered_cache_handlers as &$handler ) {
        if( !is_object( $handler[0] ) ) {
            print_r( $handler );
            die( "Not an object!" );
        }

        if( ! method_exists( $handler[0], $handler[4] ) ) {
            die( "Method " . $handler[4] . " does not exist on object!" );
        }

        #  echo "$tag:$discriminator";
        $ret = array_merge( 
            $ret,
            $handler[0]->{$handler[4]}()
        );
    }

    return $ret;
}

function clear_key( $key ) {
    # GLOBAL $mem_cache;
    GLOBAL $registered_cache_handlers;

    $ret = array();

    if( !$registered_cache_handlers || count( $registered_cache_handlers ) <= 0 ) {
        return $ret;
    } 

    foreach( $registered_cache_handlers as &$handler ) {
        if( !is_object( $handler[0] ) ) {
            print_r( $handler );
            die( "Not an object!" );
        }

        if( ! method_exists( $handler[0], $handler[5] ) ) {
            die( "Method '" . $handler[5] . "' does not exist on object!" );
        }

        #  echo "$tag:$discriminator";
        $ret = array_merge( 
            $ret,
            $handler[0]->{$handler[5]}( $key )
        );
    }

    return $ret;
}


function gen_cache( $opts = array() ) {
    perf_enter( 'gen_stats' );

    $clear_tag  = set_or( $opts['tag'], false );
    $clear_key  = set_or( $opts['key'], false );

    $cleared = array();
    $keys = array();

    if( $clear_key !== false && $clear_key != ""  ) {
        $cleared = clear_key( $clear_key );
    } else {

        $keys = cache_keys();

        # $keys = array( 'git_head_commit.e15e216fc1c639f787b1231ecdfa1bf8' );

        $to_clear = array();
        foreach( $keys as $k ) {
            
            if( $clear_tag !== false && $clear_tag != ""  ) {
                if( strpos( $k, $clear_tag ) === 0 ) {
                    $to_clear[] = $k;
                    continue;
                }
            }

            /*
            if( $clear_key !== false && $clear_key != ""  ) {
                if( $k == $clear_key ) {
                    $to_clear[] = $k;
                }
            }
            */
        }

        if( count( $to_clear ) > 0 ) {
            foreach( $to_clear as $k ) {

                $cleared = array_merge(
                    clear_key( $k  ),
                    $cleared
                );
            }
        }

        $keys = array_filter( 
            $keys, 
            function( $a ) use ( $to_clear ) {
                return !in_array( $a, $to_clear );
            }
        );
    }


    $puck = array(
        'keys'      => &$keys,
        'cleared'   => &$cleared
    );

    return render( 'gen_cache', $puck ) .  perf_exit( "gen_cache" );
}


?>
