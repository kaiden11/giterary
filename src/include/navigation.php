<?
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");
require_once( dirname( __FILE__ ) . "/git.php");
require_once( dirname( __FILE__ ) . "/display.php");
require_once( dirname( __FILE__ ) . '/html.php' );


function gen_nav() {

    perf_enter( "gen_nav" );

    return _gen_nav( 
        array( 
            'renderer'          => 'gen_nav',
        ) 
    ) . perf_exit( "gen_nav" );
}

function _gen_nav( $opts = array() ) {

    perf_enter( "_gen_nav" );

    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_nav'); 

    $history = git_history( 1 );
    
    if( count( $history ) > 0 ) { 
        // $dt = $history[0]['date'];
    
        // $dt_epoch = mktime(
        //     $dt['tm_hour'],
        //     $dt['tm_min'],
        //     $dt['tm_sec'],
        //     $dt['tm_mon']+1,
        //     $dt['tm_mday'],
        //     $dt['tm_year']+1900
        // );
    
        $diff = short_time_diff( $history[0]['epoch'], time() );
    
        $since_latest_time = $history[0]['epoch'];
        $since_latest = $diff;
    }

    $todo_count = git_todo_count();

    $latest_user_commit = null;
    if( is_logged_in() ) {
        if( ( $a = author_or( $_SESSION['usr']['git_user'], false ) ) !== false ) {
            $_commit = git_history( 1, null, $a  );

            if( count( $_commit ) > 0 ) {
                $latest_user_commit = array_shift( $_commit );
            }
        }
    }

    perf_enter( '_gen_nav.head_files' );

    $head_files = array_values(
        array_map(
            function( $a ) {
                return undirify( $a );
            },
            array_filter(
                git_head_files(),
                function( $a ) {
                    if( ASSOC_ENABLE ) {
                        $has = has_directory_prefix( ASSOC_DIR, $a );
                        if( $has ) {
                            return false;
                        }
                    }
            
                    if( ALIAS_ENABLE ) {
                        $has = has_directory_prefix( ALIAS_DIR, $a );
                        if( $has ) {
                            return false;
                        }
                    }

                    if( !can( "read", $a ) ) {
                        return false;
                    }
                
                    return true;
                }
            )
        )
    );

    perf_exit( '_gen_nav.head_files' );

    perf_enter( '_gen_nav.head_tags' );

    $head_tags = array();

    $head_tags = git_all_tags();

    foreach( $head_tags as $t => &$f ) {
        $head_tags[ $t ] = array_values(
            array_map(
                function( $a ) {
                    return undirify( $a );
                },
                array_filter( 
                    $f, 
                    function( $a ) {
                        return can( "read", $a );
                    }
                )
            )
        );
    }


    perf_exit( '_gen_nav.head_tags' );


    $puck = array(
        'since_latest'          =>  $since_latest,
        'since_latest_time'     =>  $since_latest_time,
        'todo_count'            =>  $todo_count,
        'latest_user_commit'    =>  $latest_user_commit,
        'head_commit'           =>  git_head_commit(),
        'head_files'            =>  &$head_files,
        'head_tags'             =>  &$head_tags,
    );
    return render( $renderer, $puck ) .  perf_exit( "_gen_nav" );
}

?>
