<?
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/display.php' );

function gen_import( $file = null  )  {
    perf_enter( "gen_import" );

    if( !can( "import", implode( ":", array( $file ) ) ) ) {
        return render( 'not_logged_in', array() );
    }

    return _gen_import( 
        array( 
            'file'              => &$file,
            'renderer'          => 'gen_import',
        ) 
    ) . perf_exit( "gen_import" );
}

function _gen_import( $opts = array() ) {

    perf_enter( "_gen_import" );

    $file =             ( isset( $opts['file'] ) ? $opts['file'] : null );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_import'); 

    if( !is_null( $file ) && $file != "" ) {
        $file = dirify( $file );
    }

    // $history = git_history( $num, $file, $author, $since, $skip );

    $puck = array(
        'file'              =>  &$file,
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_import" );
}



?>
