<?
require_once( dirname( __FILE__ ) . '/include/config.php' );
require_once( dirname( __FILE__ ) . '/include/util.php' );

# Grab dictionary words...
perf_enter( 'total' );
perf_enter( '_dict_read' );


$dictionary_cache_path = TMP_DIR . "/dictionary.cache";

$dict_cache = array();
if( !file_exists( $dictionary_cache_path ) ) {

    perf_mem_snapshot( "before file_get_contents" );
    $dictionary = file_get_contents( DICTIONARY_PATH );
    perf_mem_snapshot( "after file_get_contents" );

    $length = strlen( $dictionary );
    for( $i = 0; $i < $length; $i++ ) {
        if( $dictionary[$i] != "\n" ) {
            $tmp .= $dictionary[$i];
        } else {
            $dict_cache[trim(strtolower($tmp))] = true;
            $tmp = '';
        }

        // if( $i > 1000000 ) {
        //     break;
        // }
    }

    perf_mem_snapshot( "after build dict_cache" );
    unset( $dictionary );
    perf_mem_snapshot( "after unset dict" );

    file_put_contents( $dictionary_cache_path, serialize( $dict_cache ) );
    unset( $t );

    perf_mem_snapshot( "after unset dict_cache" );
}

perf_mem_snapshot( "before dict cache load" );
$dict_cache = unserialize( file_get_contents( $dictionary_cache_path ) );

perf_mem_snapshot( "after dict cache load" );

echo "Keys:" . count( array_keys( $dict_cache ) );

perf_exit( '_dict_read' );
perf_exit( 'total' );

// unlink( $dictionary_cache_path );

?>
<pre><?= perf_print() ?></pre>
