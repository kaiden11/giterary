<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
# require_once( dirname( __FILE__ ) . '/include/cache.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');



$backout_commit = git_head_commit();

# craig armstrong

$all_associations = array();

$base_assoc_dir = dirify( ASSOC_DIR, true );

$base_assoc_dir_pattern = '/^' . preg_quote( $base_assoc_dir ) . '/';

$all_files = git_glob( "*" ); 
$all_assoc_files = git_glob( "$base_assoc_dir/*" ); 

# Exclude anything in the Associations base directory
$all_normal_files = array_filter( 
    $all_files, 
    function( $v ) { 
        GLOBAL $base_assoc_dir_pattern;
        return preg_match( $base_assoc_dir_pattern, $v ) == 0;
    }
);

$all_actions = array();

foreach( $all_normal_files as $file ) {

    $associations = collect_associations( $file );

    $actions = maintain_associations( $file, $associations );

    $all_actions = array_merge( $all_actions, $actions );

}

# Remove all association sources that do not exist
foreach( $all_assoc_files as $file ) {

    if( is_dirifile( $file ) ) {
        # Ignore for the time being
        continue;
    }

    if( count( explode( "/", $file ) ) > 2 ) {
        # echo "skipping '$file'";
        continue;
    }

    $f = assoc_file_denormalize( $file );

    if( ( $f = file_or( $f, false ) ) !== false ) {
        if( !git_file_exists( $f, $backout_commit, false ) ) {
            # Make sure we aren't already planning on removing this
            if( !in_array( "rm:$file", $all_actions ) ) {
                # echo "cleanup remove: $file => $f";
                $all_actions[] = "rm:$file";
                
                // Check whether the dirifile equivalent exists,
                $d = dirify( $file, true );

                if( git_file_exists( $d ) ) {

                    if( !in_array( "rm:$d", $all_actions ) ) {

                        # echo "dir cleanup remove: $d => $f";
                        $all_actions[] = "rm:$d";
                    }
                }
            }
        }
    }
}

$git_ret = null;
$err = false;

if( git_is_working_directory_clean() ) {
    # Do nothing
    die( layout(
        array(
            'header'            => gen_header( "Rebuilding Associations" ), 
            'content'           => "git repo is clean after maintenance, no actions performed"
        )
    ) );

} else {

    # $out = '';
    # git( "reset --hard $backout_commit" );
    # git( "clean -fd" );
    # die( "short circuiting: $out" );

    # print_r( $all_actions );

    foreach( $all_actions as $a ) {
        
        list( $prefix, $_file ) = explode( ":", $a, 2 );

        if( $prefix == "add" ) {
           
            $git_ret = git( "add " . escapeshellarg( $_file ) );

        } elseif( $prefix == "rm" ) {

            $git_ret = git( "rm " . escapeshellarg( $_file ) );
                        
        } else {
            git( "reset --hard $backout_commit" );
            git( "clean -fd" );
            die( "Unhandled prefix: $prefix" );
        }

        if( $git_ret['return_code'] != 0 ) {
            $err = $git_ret['out'];
            break;
        }
    }

    if( $err !== false ) {

        git( "reset --hard $backout_commit" );
        git( "clean -fd" );
        die( "Error: $err" );

    } else {

        $commit_notes = "Rebuilding all file associations";

        list( $commit_ret, $commit_ret_message ) = git_commit(
            $_SESSION['usr']['git_user'],
            $commit_notes 
        );

        if( !$commit_ret ) {
            git( "reset --hard $backout_commit" );
            git( "clean -fd" );
            die( "Error: $err" );
        }
    }
}

echo layout(
    array(
        'header'            => gen_header( "Rebuilding Associations" ), 
        'content'           => "Rebuild complete: " . plural( count( $all_actions ), "action", "s" )
    )
);


?>
