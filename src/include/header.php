<?
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");
require_once( dirname( __FILE__ ) . "/git.php");
require_once( dirname( __FILE__ ) . "/display.php");
require_once( dirname( __FILE__ ) . "/navigation.php");
require_once( dirname( __FILE__ ) . "/drafts.php");
require_once( dirname( __FILE__ ) . "/snippet.php");


perf_enter( "total" );
perf_enter( "session_setup" );

ini_set("session.gc_maxlifetime", COOKIE_EXPR_TIME ); 
ini_set("session.save_path", SESS_PATH ); 
// Get some cookie modifications going
session_name( SESS_NAME );
# $ck = session_get_cookie_params();
# $ck['lifetime'] = $ck['lifetime'] + COOKIE_EXPR_TIME;
session_set_cookie_params( COOKIE_EXPR_TIME, COOKIE_PATH );
session_cache_expire( COOKIE_EXPR_TIME / 60 );


// Globally, start or resume current session
session_start();

# We've already got our cookie set.
$is_session_available = false;
if( isset( $_COOKIE[SESS_NAME] ) ) { 
    $is_session_available = true;
    setcookie(
        SESS_NAME,
        session_id(),
        time()+COOKIE_EXPR_TIME,
        COOKIE_PATH
    );
}

perf_exit( "session_setup" );


$start_microtime = microtime(FALSE);

$is_header_generated = false;


function gen_header($pg_title = null, $path = null) {


    perf_enter( "gen_header" );

    return _gen_header( 
        array( 
            'pg_title'          => &$pg_title,
            'meta_flag_array'   => &$meta_flag_array,
            'path'              => &$path,
            'renderer'          => 'gen_header',
        ) 
    ) . perf_exit( "gen_header" );
}

function release_session() {
    GLOBAL $is_session_available;

    if( $is_session_available ) {
        session_write_close();
    }

    return '';
}

function _gen_header( $opts = array() ) {

    GLOBAL $is_header_generated;

    perf_enter( "_gen_header" );

    $pg_title =         ( isset( $opts['pg_title'] ) ? $opts['pg_title'] : null );
    $path =             ( isset( $opts['path'] ) ? $opts['path'] : null );
    $meta_flag_array =  ( isset( $opts['meta_flag_array'] ) ? $opts['meta_flag_array'] : array( 'JAVASCRIPT' => '/v3/js/prototype.js' ) );
    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_header'); 

    $is_header_generated = true;

    // TODO move this out of here.
    $latest_user_commit = null;
    if( is_logged_in() ) {
        if( ( $a = author_or( $_SESSION['usr']['git_user'], false ) ) !== false ) {
            $_commit = git_history( 1, null, $a  );

            if( count( $_commit ) > 0 ) {
                $latest_user_commit = array_shift( $_commit );
            }
        }
    }

    $status_db = false;
    if( is_logged_in() ) {
        $status_db = maintain_status( 
            array(
                'user'          =>  $_SESSION['usr']['name'],
                'page_title'    =>  $pg_title,
                'path'          =>  $path
            )
        );
    }

    $draft_count = null;
    $snippet_count = null;
    if( is_logged_in() ) {
        $draft_count = get_draft_list_count( $_SESSION['usr']['name'] );

        $snippet_count = snippet_count( $_SESSION['usr']['name'] );
    }

    $head_commit = git_head_commit();


    $puck = array(
        'pg_title'          =>  &$pg_title,
        'path'              =>  &$path,
        'latest_user_commit'=>  &$latest_user_commit,
        'status_db'         =>  &$status_db,
        'head_commit'       =>  &$head_commit,
        'draft_count'       =>  &$draft_count,
        'snippet_count'     =>  &$snippet_count,
        'meta_flag_array'   =>  &$meta_flag_array,
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_header" ) . release_session();
}

function get_statuses( ) {

    return unserialize( file_get_contents( TMP_DIR . '/' . STATUS_DB ) );

}

function maintain_status( $opts = array() ) {

    perf_enter( 'maintain_status' );

    $ret = false;

    if( STATUS_ENABLE && is_array( $opts ) ) {
        $status_db_path = TMP_DIR . '/' . STATUS_DB;
        if( !file_exists( $status_db_path ) ) {
            if( file_put_contents( $status_db_path,  serialize( array() ) ) === false ) {
                die( "Unable to maintain status DB: $status_db_path" );
            }
        }

        $fh = fopen( $status_db_path, 'r+' );
        if( !flock( $fh, LOCK_EX ) ) {
            fclose( $fh );
            return $ret;
        }

        $status_db = array();

        $contents = fread( $fh, 10000 );

        if( $contents  !== false ) {
            # echo "contents: '$contents'";
            $status_db = unserialize( $contents );

        } else {
            die( "Unable to read '$status_db_path'" );
        }

        if( $opts['user'] ) {
            $status_db[ $opts['user'] ] = array(
                'time'          =>  time(),
                'page_title'    =>  ( $opts['page_title'] ? $opts['page_title'] : false ),
                'path'          =>  ( $opts['path'] ? $opts['path'] : false )
            );
        }

        if( ftruncate( $fh, 0 ) && fseek( $fh, 0 ) == 0 && fwrite( $fh,  serialize( $status_db ) ) === false ) {
             flock( $fh, LOCK_UN );
            die( "Unable to commit status DB: $status_db_path" );
        }

        flock( $fh, LOCK_UN );
        fclose( $fh );

        $ret = $status_db;

    }

    context_log( $opts['user'], $_SERVER['REQUEST_URI'] );

    perf_exit( 'maintain_status' );

    return $ret;

}

function maintain_breadcrumb( $file ) {

    $file = file_or( $file, null );
    $file = undirify( $file );

    if( $file != null ) {

        if( !isset( $_SESSION['breadcrumb'] ) ) {

            $_SESSION['breadcrumb'] = array( $file    =>  time() );
        } else {

            $_SESSION['breadcrumb'][$file] = time();

        }

    }

    if( 
        isset( $_SESSION['breadcrumb'] ) 
        &&
        is_array( $_SESSION['breadcrumb'] ) 
        && 
        count( $_SESSION['breadcrumb'] ) > 0 
    ) {
        uasort( 
            $_SESSION['breadcrumb'],
            function( $a, $b ) {
                # echo "a: $a, b: $b";
                return ( $a - $b );
            }
        );
    }

    while( 
        isset( $_SESSION['breadcrumb'] )
        && 
        count( $_SESSION['breadcrumb'] ) > 10  
    ) {
        array_shift( $_SESSION['breadcrumb'] );
    }

}

function _breadcrumb_li( $f, $clazz = null ) {
    $b = basename( $f );
    $fd = dirify( $f );

    $clazz = ( $clazz != null ? " class=\"$clazz\"" : '' );

    $head_commit        = git_file_head_commit( dirify( $fd ) );
    $show               = null;
    $parent_commit      = null;
    $show_diff_links    = false;

    if( commit_or( $head_commit, false ) !== false ) {
        $show_diff_links = true;

        $show = git_show( $head_commit );

        $parent_commit = $show['parent_commit'];
    }

    # $show = git_show( $hc );
    
    if( $b == TALK_PAGE ) {
        return "<li$clazz>" . 
            linkify( 
                '[[' . undirify( $f ) . '|' . "Talk:" . undirify( basename( dirname( $f ) ), true ) . ']]', 
                array(
                    'separator' =>  '/',
                    'minify'    =>  true ,
                    'suffix'    =>  ( !$show_diff_links 
                                        ? '' 
                                        : '<a title="Show differences introduced by latest change" class="diff" href="diff.php?commit_before=' 
                                            . urlencode( $parent_commit ) 
                                            . '&commit_after=' 
                                            . urlencode( $head_commit ) 
                                            . '&file=' . urlencode( $f ) 
                                            . '&plain=yes'
                                            .  '">*</a>'
                                    )
                )
            )
        . "</li>";
    }

    if( $b == STORYBOARD_PAGE ) {
        echo "here";
        return "<li$clazz>" . 
            linkify( 
                '[[' . undirify( $f ) . '|' . "Storyboard:" . undirify( basename( dirname( $f ) ), true ) . ']]', 
                array(
                    'separator' =>  '/',
                    'minify'    =>  true ,
                    'suffix'    =>  ( !$show_diff_links 
                                        ? '' 
                                        : '<a title="Show differences introduced by latest change" class="diff" href="diff.php?commit_before=' 
                                            . urlencode( $parent_commit ) 
                                            . '&commit_after=' 
                                            . urlencode( $head_commit ) 
                                            . '&file=' . urlencode( $f ) 
                                            . '&plain=yes'
                                            .  '">*</a>'
                                    )
                )
            )
        . "</li>";
    }

    if( $b == ANNOTATORJS_FILE ) {
        return "<li$clazz>" . 
            linkify( 
                '[[' . undirify( $f ) . '|' . "Anno:" . undirify( basename( dirname( $f ) ), true ) . ']]', 
                array(
                    'separator' =>  '/',
                    'minify'    =>  true ,
                    'suffix'    =>  ( !$show_diff_links 
                                        ? '' 
                                        : '<a title="Show differences introduced by latest change" class="diff" href="diff.php?commit_before=' 
                                            . urlencode( $parent_commit ) 
                                            . '&commit_after=' 
                                            . urlencode( $head_commit ) 
                                            . '&file=' . urlencode( $f ) 
                                            . '&plain=yes'
                                            .  '">*</a>'
                                    )
                )
            )
        . "</li>";
    }
    
    return "<li$clazz>" . 
        linkify( 
            '[[' . undirify( $f ) . '|' . basename( $f ) . ']]', 
            array(
                'separator' =>  '/', 
                'minify'    =>  true,
                'suffix'    =>  ( !$show_diff_links 
                                    ? '' 
                                    : '<a title="Show differences introduced by latest change" class="diff" href="diff.php?commit_before=' 
                                        . urlencode( $parent_commit ) 
                                        . '&commit_after=' 
                                        . urlencode( $head_commit ) 
                                        . '&file=' . urlencode( $f ) 
                                        . '&plain=yes'
                                        .  '">*</a>'
                                )

            )
        )
    . "</li>";
}

function gen_breadcrumb( $opts = array() ) {

    $ret = '';
    $tmp = array();
    $already_seen = array();

    if( isset( $_SESSION['breadcrumb'] ) ) {

        uasort( 
            $_SESSION['breadcrumb'],
            function( $a, $b ) {
                return ( $b - $a );
            }
        );


        $tmp = array();
        foreach( $_SESSION['breadcrumb'] as $f => $timestamp ) {
            $tmp[] = _breadcrumb_li( $f, "visited" );

            $already_seen[ dirify( $f ) ]  = 1;
        }
    }

    if( count( $tmp ) < 10 && is_logged_in() ) {
        $tmp_recently_modified = array();

        $hist = git_history( 20 );

        usort( $hist, function( $a, $b ) {
            return $b['epoch'] - $a['epoch'];
        } );


        foreach( $hist as &$h ) {

            if( is_array( $h['pages'] ) && count( $h['pages'] ) > 0 ) {
                foreach( $h['pages'] as $page ) {

                    if( isset( $already_seen[ $page ] ) ) {
                        continue;
                    }

                    $already_seen[ $page ] = 1;

                    if( isset( $_SESSION['breadcrumb'][ $page ] ) ) {
                        continue;
                    }

                    if( ASSOC_ENABLE ) {
                        if( has_directory_prefix( ASSOC_DIR, $page ) ) {
                            continue;
                        }
                    }

                    if( ALIAS_ENABLE ) {
                        if( has_directory_prefix( ALIAS_DIR, $page ) ) {
                            continue;
                        }
                    }


                    if( !git_file_exists( $page ) ) {
                        continue;
                    }


                    if( count( $tmp ) >= 10 ) {
                        // Break out completely
                        continue 2;
                    }


                    $tmp[] = _breadcrumb_li( $page, "recent" );
                }
            }
        }
    }

    $ret = 'Last ' 
        . plural( count( $tmp ), "recent page", "s" ) 
        . ' [<a href="clear_breadcrumb.php?redirect=' 
            . ( urlencode( $_SERVER['REQUEST_URI'] ) ) 
        . '" title="Clear Breadcrumb History">X</a>]: '
        . '<ul class="' . ( $opts['ul-class'] ? $opts['ul-class'] : '' ) . '">'
            . join( '', $tmp ) 
        . '</ul>';


    return $ret;
}

function _gen_breadcrumb( $opts = array() ) {

    $ret = array();
    $already_seen = array();

    $renderer = 'gen_breadcrumb';

    if( isset( $_SESSION['breadcrumb'] ) ) {

        uasort( 
            $_SESSION['breadcrumb'],
            function( $a, $b ) {
                return ( $b - $a );
            }
        );


        $ret = array();
        foreach( $_SESSION['breadcrumb'] as $f => $timestamp ) {
            $ret[] = array(
                'file'  =>  $f,
                'class' =>  'visited'
            );
            # _breadcrumb_li( $f, "visited" );

            $already_seen[ dirify( $f ) ]  = 1;
        }
    }

    if( count( $ret ) < 10 && is_logged_in() ) {
        $tmp_recently_modified = array();

        $hist = git_history( 20 );

        usort( $hist, function( $a, $b ) {
            return $b['epoch'] - $a['epoch'];
        } );


        foreach( $hist as &$h ) {

            if( is_array( $h['pages'] ) && count( $h['pages'] ) > 0 ) {
                foreach( $h['pages'] as $page ) {

                    if( isset( $already_seen[ $page ] ) ) {
                        continue;
                    }

                    $already_seen[ $page ] = 1;

                    if( isset( $_SESSION['breadcrumb'][ $page ] ) ) {
                        continue;
                    }

                    if( ASSOC_ENABLE ) {
                        if( has_directory_prefix( ASSOC_DIR, $page ) ) {
                            continue;
                        }
                    }

                    if( ALIAS_ENABLE ) {
                        if( has_directory_prefix( ALIAS_DIR, $page ) ) {
                            continue;
                        }
                    }


                    if( !git_file_exists( $page ) ) {
                        continue;
                    }

                    if( !can( "read", $page ) ) {
                        continue;
                    }


                    if( count( $ret ) >= 10 ) {
                        // Break out completely
                        continue 2;
                    }


                    # $tmp[] = _breadcrumb_li( $page, "recent" );
                    $ret[] = array(
                        'file'  =>  $page,
                        'class' =>  'recent'
                    );

                }
            }
        }
    }

    # $ret = 'Last ' 
    #     . plural( count( $tmp ), "recent page", "s" ) 
    #     . ' [<a href="clear_breadcrumb.php?redirect=' 
    #         . ( urlencode( $_SERVER['REQUEST_URI'] ) ) 
    #     . '" title="Clear Breadcrumb History">X</a>]: '
    #     . '<ul class="' . ( $opts['ul-class'] ? $opts['ul-class'] : '' ) . '">'
    #         . join( '', $tmp ) 
    #     . '</ul>';

    return render( 
        $renderer,
        array(
            'files' =>  $ret
        )
    );
}



?>
