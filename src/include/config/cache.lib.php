<?
require_once( dirname( __FILE__ ) . '/../util.php' );

function register_cache_handler( 
    $cache_obj, 
    $encache_method, 
    $decache_method,
    $clear_cache_method,
    $cache_keys_method,
    $clear_key_method
) {
    GLOBAL $registered_cache_handlers;

    $registered_cache_handlers[] = array( 
        $cache_obj, 
        $encache_method, 
        $decache_method,
        $clear_cache_method,
        $cache_keys_method,
        $clear_key_method
    );

    return;
}

class FileCache {

    private $cache_prefix       = null;
    private $cache_dir          = null;
    private $tag_expirations    = null;

    function __construct( $tag_expirations, $cache_prefix, $cache_dir ) {
        $this->tag_expirations  =   $tag_expirations;
        $this->cache_prefix     =   $cache_prefix;
        $this->cache_dir        =   $cache_dir;
    }

    function clear_key( $key ) {
        // Do not let anyone delete files without 
        // going through tag/discriminator handling
        return array();
    }

   
    function clear_cache( $tag, $discriminator = null ) {
        
        $ret = array();
    
        if( is_null( $tag ) || $tag == '' ) {
            die( 'Must submit tag to clear.' );
        } else {
            $tag = trim( $tag );
    
            if( !is_null( $discriminator ) ) {
                $tag .= '.' . md5( $discriminator );
            }

            if( !is_null( $this->cache_prefix ) ) {
                $tag = $this->cache_prefix . '.' . $tag;
            }
    
            $g = glob( $this->cache_dir . "$tag*");
            if( $g !== FALSE ) {
                foreach ( $g as $filename) {
                    if( file_exists( $filename ) && is_file( $filename ) ) {
                        if( unlink( $filename ) ) {
                            $ret[] = $filename;
                        } else {
                            $ret[] = "PROBLEM: $filename";
                        }
                    }
                }
            }
        }
    
        return $ret;
    }
    
    function encache( $tag, $discriminator, $value ) {
        perf_enter( "encache.FileCache" );
        if( is_null( $discriminator ) ) {
            die( "Invalid call to encache, no discriminator.");
        } 
    
        if( is_null( $tag ) ) { // value to prefix the cached file name with
            $tag = '';
        }
    
        if( is_null( $value ) ) { // value to prefix the cached file name with
            die( "Must pass non-null value to encache.");
        }
  
        $filename = null;
        if( !is_null( $this->cache_prefix ) && $this->cache_prefix != '' ) {
            $filename = join(".", array( $this->cache_prefix, $tag, md5( $discriminator ) ) );
        } else {
            $filename = join(".", array( $tag, md5( $discriminator ) ) );
        }
    
        $filepath = $this->cache_dir . "/$filename";
    
        // Hope we never reach above 1MB
        $s = serialize( $value );
    
        if( strlen( $s ) < 1000000 ) {
    
            $res = fopen($filepath, "w");
    
            # Maintain file locking
            if( flock( $res, LOCK_EX | LOCK_NB ) ) {
                $r = fwrite($res, $s, 1000000);
    
                # In the event that we tried to write to the cache
                # and failed, we don't want to leave the cache in
                # an invalid state. Clean up your mess, and die
                # if you can't (as something is terribly, horribly
                # wrong). If you can clean up, subsequent accesses
                # will just look like cache misses.
                if( $r === false || $r === 0 ) {
                    if( file_exists( $filepath ) && unlink( $filepath ) === false ) {
                        die( "Unable to write to cache, and unable to clean up cache mess" );
                    }
                }
    
                flock($res, LOCK_UN); 
            } else {
    
                # Somebody else is already writing to this file. We can't
                # try to write ours as well, even if we really, really want
                # to.
                echo "Cache blocking conflict!";
    
            }
    
            fclose($res);
    
            if( file_exists( $filepath ) ) {
                chmod( $filepath, 0666 );
            }
        }
    
        perf_exit( "encache.FileCache" );
    }
    
    function decache( $tag, $discriminator  ) {
    
        perf_enter( "decache.FileCache" );
        if( is_null( $discriminator ) ) {
            die( "Invalid call to decache, no discriminator");
        } 
    
        if( is_null( $expire ) ) { // value to prefix the cached file name with
            if( isset( $this->tag_expirations[$tag] ) ) {
                $expire = $this->tag_expirations[$tag];
            } else {
                $expire = 10;
            }
        }
    
        if( is_null( $tag ) ) { // value to prefix the cached file name with
            $tag = '';
        }
        perf_enter( "decache.FileCache.$tag" );
    
    
        $discriminator = md5( $discriminator );
        # perf_enter( "decache.$tag.$discriminator" );
        $filename = null;
        if( !is_null( $this->cache_prefix ) && $this->cache_prefix != '' ) {
            $filename = join(".", array( $this->cache_prefix, $tag, $discriminator ) ); 
        } else {
            $filename = join(".", array( $tag, $discriminator ) ); 
        }
    
        $filepath = $this->cache_dir . $filename;
    
        $ret = null;
    
        if( file_exists( $filepath ) ) {
            $timestamp = filemtime( $filepath );
    
            
            if( ($timestamp + $expire) > time() ) {
    
                $fh = fopen( $filepath, 'r' );
    
                if( flock( $fh, LOCK_SH ) ) {
                    //echo "timestamp: $timestamp, expire: $expire";
                    $ret = unserialize( file_get_contents( $filepath ) );
    
                    flock( $fh, LOCK_UN );
                }
    
                fclose( $fh );
            }
        }

        if( $ret == null ) {
            perf_exit( "decache.FileCache.$tag.miss" );
        }
    
        # perf_exit( "decache.$tag.$discriminator" );
        perf_exit( "decache.FileCache.$tag" );
        perf_exit( "decache.FileCache" );
        return $ret;
    }
}

class memcacheCache {

    private $cache_prefix   =   null;
    private $cache_server   =   null;
    private $cache_port     =   null;

    private $memcache_obj   =   null;

    private $tag_expirations    = null;

    function __construct( $tag_expirations, $cache_prefix, $cache_server, $cache_port ) {
        $this->cache_prefix     =   $cache_prefix;
        $this->cache_server     =   $cache_server;
        $this->cache_port       =   $cache_port;

        $this->tag_expirations  =   $tag_expirations;

        $this->memcache_obj     =   new Memcache;

        $this->memcache_obj->addServer( 
            $this->cache_server,
            $this->cache_port,
            true            // Persistent
        );

    }

    function clear_key( $key ) {
        $ret = array();
        if( !is_null( $this->cache_prefix ) ) {
            $key = $this->cache_prefix . '.' . $key;
        }

        if( $this->memcache_obj->delete( $key ) ) {
            $ret[] = "memcache:$key";
        }

        return $ret;
    }

    function clear_cache( $tag, $discriminator = null ) {
        
        $ret = array();

        if( is_null( $tag ) || $tag == '' ) {
            die( 'Must submit tag to clear.' );
        } else {
            $tag = trim( $tag );

            $result = $this->decache( $tag, $discriminator );


            if( !is_null( $result ) ) {


                if( !is_null( $discriminator ) ) {
                    $tag .= '.' . md5( $discriminator );
                }


                # perf_log( "memcache clearing: $tag" );

                $ret = clear_key( $tag );

                # clear_key( $tag );

                # if( $this->memcache_obj->delete( $tag ) ) {
                #     $ret[] = "memcache:$tag";
                # }
            }
        }

        return $ret;
   
    }
    
    function encache( $tag, $discriminator, $value ) {

        perf_enter( "encache.memcacheCache.$tag" );
        if( is_null( $discriminator ) ) {
            die( "Invalid call to encache, no discriminator.");
        } 

        if( is_null( $value ) ) {
            die( "Cannot store null cache value" );
        }

    
        if( is_null( $tag ) ) { // value to prefix the cached file name with
            $tag = '';
        }


        $expire = set_or( $this->tag_expirations[ $tag ], 0 );

        $key = null;
        if( !is_null( $this->cache_prefix ) && $this->cache_prefix != '' ) {
            $key = join(".", array( $this->cache_prefix, $tag, md5( $discriminator ) ) );
        } else {
            $key = join(".", array( $tag, md5( $discriminator ) ) );
        }

        # perf_log( "encache.memcacheCache.$tag.key.$key" );
        # perf_log( "encache.memcacheCache.$tag.value.$value" );

        $success = $this->memcache_obj->set( 
            $key, 
            serialize( $value ),
            0,  //  No flags
            $expire 
        );

        if( !$success ) {
            perf_log( "encache.memcacheCache.failure" );
        }
    
        perf_exit( "encache.memcacheCache.$tag" );
    }

    function cache_keys() {

        $ret = array();
        perf_enter( 'cache_keys.memcacheCache' );
        $allSlabs = $this->memcache_obj->getExtendedStats('slabs');
         $items = $this->memcache_obj->getExtendedStats('items');

        foreach($allSlabs as $server => $slabs) {
            if( is_array( $slabs ) ) {
                foreach($slabs AS $slabId => $slabMeta) {
                   $cdump = $this->memcache_obj->getExtendedStats('cachedump',(int)$slabId );
                    foreach($cdump AS $keys => $arrVal) {
                        if (!is_array($arrVal)) continue;
                        foreach($arrVal AS $k => $v) {

                            if( strpos( $k, $this->cache_prefix ) === 0 ) {

                                $ret[] = str_replace( $this->cache_prefix . '.', '', $k );
                            }
                        }
                   }
                }
            }
        }

        # $this->memcache_obj->close();

        // Perform a no-op query, as something seems to
        // get screwed after querying all keys
        # echo $this->memcache_obj->getVersion();
        # echo $this->memcache_obj->getVersion();
        # echo $this->memcache_obj->getVersion();
        # echo $this->memcache_obj->getVersion();

        perf_exit( 'cache_keys.memcacheCache' );

        return $ret;
    }
    
    function decache( $tag, $discriminator  ) {

        perf_enter( "decache.memcacheCache" );
        if( is_null( $discriminator ) ) {
            die( "Invalid call to decache, no discriminator");
        }
   
        if( is_null( $tag ) ) { // value to prefix the cached file name with
            $tag = '';
        }

        if( is_array( $tag ) ) {
            die( "Array memcache tags not supported!" );
        }

        perf_enter( "decache.memcacheCache.$tag" );
    
    
        $discriminator = md5( $discriminator );
        # perf_enter( "decache.$tag.$discriminator" );
        $key = null;
        if( !is_null( $this->cache_prefix ) && $this->cache_prefix != '' ) {
            $key = join(".", array( $this->cache_prefix, $tag, $discriminator ) ); 
        } else {
            $key = join(".", array( $tag, $discriminator ) ); 
        }

        # perf_log( "decache.memcacheCache.$key" );

        $flags = false;
        $ret = $this->memcache_obj->get( $key, $flags  );

        if( $ret === false ) {

            perf_log( "decache.memcacheCache.$tag.miss" );
            perf_exit( "decache.memcacheCache.$tag" );
            perf_exit( "decache.memcacheCache" );

            // Flags remains untouched if something isn't found
            return null;
        }

        # print_r( "key:$key\n" );
        # print_r( "result:" . ( $ret === false ? "falsisih" : "" ) . "\n" );
        # print_r( "flag: '$flags'" . ( is_bool( $flags ) ? "bool" : "" ) . "\n" );
        # print_r( "flag: '$flags'" . ( $flags === false ? "false" : "" ) . "\n" );
        /*
        if( is_array( $result ) ) {
            # echo "MEMCACHE_COMPRESSED:" . MEMCACHE_COMPRESSED;
            # echo "MEMCACHE_HAVE_SESSION:" . MEMCACHE_HAVE_SESSION;
            # print_r( $flags );
            # print_r( $result );
            # die("Got back an array, expected string: $tag, $discriminator" );

            # Do nothing, already "unserialized"
            $ret = $result;
        } else {
            $ret = unserialize( $result );
        }
        */
        # perf_exit( "decache.$tag.$discriminator" );
        # perf_exit( "decache.memcacheCache.$tag" );

        perf_exit( "decache.memcacheCache.$tag" );
        perf_exit( "decache.memcacheCache" );
        return unserialize( $ret );
    }


}



?>
