<?php
require_once( dirname( __FILE__ ) . '/config/base.php');
require_once( dirname( __FILE__ ) . '/util.php');
require_once( dirname( __FILE__ ) . '/drafts.php');
require_once( dirname( __FILE__ ) . '/auth.php');


function snippetify( $username, $file, $commit, $snippet, $context ) {

    $ret = draftify( $username, $file, $commit );

    $s_md5 = md5( "$snippet $context" );

    return "snippet.$ret.$s_md5";
}

function snippet_update( $username, $file, $commit, $snippet, $context, $type, $from ) {
   
    $tmp_dir = TMP_DIR;

    if( !file_exists( $tmp_dir ) || !is_dir( $tmp_dir ) ) {
        die( "Invalid TMP_DIR: '$tmp_dir'" );
    }

   
    // Stealing from the drafts
    $snippet_file = snippetify( $username, $file, $commit, $snippet, $context );

    $ret = file_put_contents( 
        "$tmp_dir/$snippet_file",
        serialize( 
            array(
                "username"  => $username,
                "file"      => $file,
                "commit"    => $commit,
                "context"   => $context,
                "snippet"   => $snippet,
                "type"      => $type,
                "time"      => time(),
                "from"      => $from
            )
        )
    );

    if( $ret === false ) {
        return false;
    } 

    return true;
}

function snippet_count( $username ) {

    return count( snippet_list( $username ) );

}

function snippet_list( $username, $file = null, $commit = null ) {

    $glob = "snippet.";

    if( set_or( $username, false ) === false ) {
        die( "Must have username for snippet_list" );
    }

    $glob .= "$username.";

    if( file_or( $file, false ) !== false ) {
        $glob .= "$file.";
    }

    if( commit_or( $commit, false ) !== false ) {
        $glob .= "$commit.";
    }

    $glob .= "*";

    return array_map(
        function( $a ) {
            return basename( $a );
        },
        _snippet_glob( $glob )
    );

};

function _snippet_glob( $glob ) {

    $snippet_files = glob( TMP_DIR . "/" . $glob );
    return $snippet_files;
}

function snippet_delete( $username, $filename ) {
   
    $list = snippet_list( $username );

    if( !in_array( $filename, $list ) ) {
        return "File not among list of snippets: $filename";
    }

    $tmp_dir = TMP_DIR;

    $snippet_path = "$tmp_dir/$filename";

    if( !file_exists( $snippet_path ) ) {
        return "File does not exist: $snippet_path";
    }

    $ret = unlink(  $snippet_path );

    if( $ret === false ) {
        return "Unable to remove $snippet_path";
    }

    return true;
}

function snippet_get( $filename ) {
    

    $tmp_dir = TMP_DIR;

    if( !file_exists( $tmp_dir ) || !is_dir( $tmp_dir ) ) {
        echo "Invalid TMP_DIR: '$tmp_dir'";
        return null;
    }
   
    // Stealing from the drafts
    // $snippet_file = snippetify( $username, $file, $commit );

    if( file_or( $filename, false ) === false ) {
        echo "No a filename: $filename";
        return null;
    }

    $snippet_file = $filename;

    $snippet_path = "$tmp_dir/$snippet_file";

    if( !file_exists( $snippet_path ) ) {
        echo "Unable to find $snippet_path";
        return false;
    }

    $c = file_get_contents( $snippet_path );

    if( $c === false ) {
        echo "Unable to retrieve $snippet_file";
        return null;
    }

    $b = unserialize( $c );

    return $b;

}

function gen_snippets() {

    global $snippet_recipients;
    
    perf_enter( 'gen_snippets' );

    if( !is_logged_in() ) {
        return render('not_logged_in', array() );
    }

    $user = $_SESSION['usr']['name'];

    $list = snippet_list( $user );

    $snippets = array();


    foreach( $list as $l ) {

        $g = snippet_get( $l );

        if( is_null( $g ) || $g === false ) {
            continue;
        }

        $snippets[ $l ] = $g;
    }

    $userlist = userlist();

    if( isset( $snippet_recipients ) && is_array( $snippet_recipients )&& count( $snippet_recipients ) > 0 ) {
        foreach( $userlist as $k => $u ) {

            if( $u == $user ) {
                unset( $userlist[ $k ] );
            }
            if( !in_array( $u, $snippet_recipients ) ) {
                unset( $userlist[ $k ] );
            }
        }
    }

    return render( 
        'gen_snippets', 
        array(
            'snippets'  =>  $snippets,
            'userlist'  =>  $userlist,
        )
    ) . perf_exit( "gen_snippets" );

}


?>
