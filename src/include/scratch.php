<?
require_once( dirname( __FILE__ ) . '/util.php' );


function gen_scratch() {
    
    perf_enter( 'gen_scratch' );
    return render( 
        'gen_scratch', 
        array()
    ) . perf_exit( "gen_scratch" );

}

?>
