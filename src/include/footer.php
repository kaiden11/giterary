<?
require_once( dirname( __FILE__ ) . '/util.php');

$end_microtime = microtime(FALSE);

$is_footer_generated = false;

function gen_footer( $show_elapsed = false ) {
    GLOBAL $perf_results;

    return _gen_footer(
        array( 
            'perf_results'  =>  &$perf_results,
            'show_elapsed'  =>  $show_elapsed,
        )
    );
}

function _gen_footer( $opts = array() ) {
    perf_exit( "total" );

    $renderer       = set_or( $opts['renderer'],        'gen_footer' );
    $perf_results   = set_or( $opts['perf_results'],    array()      );
    $show_elapsed   = set_or( $opts['show_elapsed'],    false        );

    $puck = array(
        'show_elapsed'              =>  $show_elapsed,
        'perf_results'              =>  $perf_results,
    );

    return render( $renderer, $puck );

}
 
?>
