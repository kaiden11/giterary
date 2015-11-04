<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/assoc.php');
require_once( dirname( __FILE__ ) . '/include/collection.php');

$collectors = array();
$references = array();

$collectors_needing_rebuild = array();

perf_enter( "total" );
perf_enter( "maintain_collectors" );

$is_unattended = strtolower( trim( $argv[1] ) ) == "unattended";

if( !$is_unattended && !is_logged_in() ) {
    echo "You must be logged in.";
} else {

    perf_enter( 'find_list_functionlinks_needing_rebuild' );
    $list_functionlink_pattern = '\[\[list:[^\]]+\]\]';

    $files_with_list_functionlinks = git_grep( $list_functionlink_pattern, true );

    foreach( $files_with_list_functionlinks as $path => &$match ) {
        if( $match['type'] == "contents match" ) {
            $collectors = array_merge(
                $collectors,
                array( $path )
            );
        }
    }


    perf_exit( 'find_list_functionlinks_needing_rebuild' );

    perf_enter( "find_collectors_needing_rebuild" );
    
    $collectors = array_merge( 
        $collectors, 
        collect_files( "*.list" ) ,
        collect_files( "*.collection" ) 
    );

    perf_exit( "find_collectors_needing_rebuild" );

    perf_enter( "find_current_refs" );
    
    foreach( $collectors as $collector ) {
        $refs = array();

        $ext = detect_extension( $collector, null );


        $content = git_file_get_contents( $collector );

        if( in_array( $ext, array( "collection", "list" ) ) ) {
    
            foreach( preg_split( '/(\r)?\n/', $content ) as $line ) {
                $refs = array_merge( $refs,  collect_files( $line ) );
            }
    
            $references[$collector] = $refs;
        } else {
            
            $assocs = collect_function_link_associations( $collector, $content );


            if( isset( $assocs['collect'] ) && count( $assocs['collect'] ) > 0 ) {

                $references[$collector] = $assocs['collect'];
            }
        }
    }

    perf_exit( "find_current_refs" );

    perf_enter( "compare_refs" );
    
    foreach( $references as $collector => &$refs ) {
        
        $current_collect_targets = file_assoc_targets( $collector, 'collect', false );
    
        foreach( $refs as $ref ) {
            $is_found = false; 
            foreach( $current_collect_targets as $target ) {
               if( $target['path'] == $ref ) {
                    $is_found = true;
                    break;
               }
            }
    
            if( !$is_found  ) {
    
                $collectors_needing_rebuild[] = $collector;
                break;
            }
        }
    }
    
    perf_exit( "compare_refs" );
    

    perf_enter( "fix_collectors_needing_rebuild" );
    foreach( $collectors_needing_rebuild as $collector ) {
    
        perf_enter( "rebuild.$collector" );
        build_assoc( $collector );

	if( !$is_unattended ) {
	    break;
	}
        perf_exit( "rebuild.$collector" );
    }
    
    if( count( $collectors_needing_rebuild ) >= 2 ) {
        echo count( $collectors_needing_rebuild )-1 . " collectors left to rebuild:\n";
        echo implode( "\n", $collectors_needing_rebuild );
    }
    
    perf_exit( "fix_collectors_needing_rebuild" );
}

perf_exit( "maintain_collectors" );
perf_exit( "total" );

echo perf_print();


?>
