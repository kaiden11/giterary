<? 
# require_once( dirname( __FILE__ ) . '/include/header.php');
# require_once( dirname( __FILE__ ) . '/include/footer.php');
# require_once( dirname( __FILE__ ) . '/include/cache.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/config.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');

perf_enter( 'total' );

$head_commit = git_head_commit();

echo "Priming for head commit: $head_commit\n";

$ls_tree = git_ls_tree( '/', $head_commit, true );

$path_presence = array();
$not_existing_paths = array();

foreach( $ls_tree as $k => &$file ) {
    
    // Mark each file as "true", given that its
    // return from git ls-tree implies that it
    //exists
    $path_presence[ $file['file'] ] = true;

    // We can also assume that all path components
    // that are a part of this file can be assume to
    // exist

    $components = array_filter(
        explode( "/", $file['file'] ),
        function( $a ) {
            return $a != '';
        }
    );

    for( $i = 1; $i < count( $components ); $i++ ) {
        $path_presence[ 
            implode( 
                "/", 
                array_slice( 
                    $components, 
                    0, 
                    $i 
                ) 
            )
        ] = true;
    }
}

foreach( $ls_tree as $k => &$file ) {

    $undirified_path = undirify( $file['file'] );

    $components = array_filter(
        explode( "/", $undirified_path ),
        function( $a ) {
            return $a != '';
        }
    );

    for( $i = 1; $i < count( $components ); $i++ ) {
        $interstitial_path = dirify( 
            implode( 
                "/", 
                array_slice( 
                    $components, 
                    0, 
                    $i 
                ) 
            )
        );

        if( $path_presence[ $interstitial_path ] !== true) {
            $path_presence[ $interstitial_path ] = false;
        }
    }
}

echo    "Existing/not-existing : " 
        . count( 
            array_filter( $path_presence ) 
        ) 
        . "/" 
        . count( 
            array_filter( 
                $path_presence, 
                function( $a ) { 
                    return $a === false; 
                } 
            ) 
        )
        . "\n";

echo "Caching git_file_exists results";
foreach( $path_presence as $path => $exists ) {
    encache( 'git_file_exists', "$head_commit:$path", $exists );
}


// print_r( $path_presence );

$rev_list = git_rev_list( 
    array( 
        'reverse'   => true ,
        'notes'     => false 
    ) 
);

# echo "Rev count:" . count( $rev_list ) . "\n";
# echo "First rev:" . $rev_list[0] . "\n";
# echo "Last rev:" . $rev_list[ count( $rev_list ) - 1 ] . "\n";

$latest_file_commits = array();

$rev_i = 0;
foreach( $rev_list as $rev ) {
    $file_list = git_commit_file_list( $rev );

    # echo $rev . ': ' . count( $file_list ) . "\n";

    foreach( $file_list as $f ) {
        if( $path_presence[ $f ] === true ) {
            # echo "'$f': $rev\n";
            $latest_file_commits[ path_to_filename( $f ) ] = $rev;
        }
    }

    $rev_i++;

    # echo "$rev_i\n";

    # if( $rev_i > 10 ) {
    #     break;
    # 
    # }
}

echo "Caching file_head_commit results";
foreach( $latest_file_commits as $path_to_filename => $commit  ) {
    encache( 'file_head_commit', $path_to_filename, $commit );
}

# Remove all in-memory caches.
$latest_file_commits = null;
$path_presence = null;

foreach( $ls_tree as $i => $file ) {

    # Files...
    if( $file['type'] == "blob" ) {

        echo $file['file'] . "\n";

        # Skips security checks...
        # echo $file['file'] . "\n";

        perf_enter( $file['file'] );

        git_file_get_contents( $file['file'] );

        perf_exit( $file['file'] );

    } else {
        continue;
    }

}

if( ASSOC_ENABLE ) {
    echo "begin associations";
    assoc_orphans();
    assoc_wanted();
    echo "end associations";
}

perf_exit( 'total' );

echo perf_print() . "\n";
?>
