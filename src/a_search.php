<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');

$term =   trim( stripslashes( $_GET['term'] ) );

$ret = array();

perf_enter( 'total' );

if( is_logged_in() ) {

    perf_enter( 'a_search.process'  );
    
    if( strlen( $term ) >= 2 ) {
   

        perf_enter( 'a_search.retrieval'  );
    
        $head_files = git_head_files();
        
        $regex = '/';
    
        $term = str_replace( ' ', '', $term );

        perf_enter( 'a_search.regex_build'  );
    
        for( $i = 0; $i < strlen( $term ); $i++ ) {
            $regex .= "(" . preg_quote( $term[$i], "/" ) . ").*";
        }
        
        $regex .= '/i';

        perf_exit( 'a_search.regex_build'  );

        perf_exit( 'a_search.retrieval'  );
    
        foreach( $head_files as $file ) {

            perf_enter( 'a_search.file'  );
    
            if( ASSOC_ENABLE ) {
                perf_enter( 'a_search.is_assoc'  );
                $has = has_directory_prefix( ASSOC_DIR, $file );
                perf_exit( 'a_search.is_assoc'  );
                if( $has ) {

                    perf_exit( 'a_search.file'  );
                    continue;
                }
            }
    
            if( ALIAS_ENABLE ) {
                perf_enter( 'a_search.is_alias'  );
                $has = has_directory_prefix( ALIAS_DIR, $file );
                perf_exit( 'a_search.is_alias'  );
                if( $has ) {
                    perf_exit( 'a_search.file'  );
                    continue;
                }
            }
    
    
            if( can( "read", $file ) ) {

                perf_enter( 'a_search.match_test' );
    
                $ufile = undirify( $file );
    
                $matches = array();

                preg_match_all(
                    $regex,
                    $ufile, 
                    $matches, 
                    PREG_SET_ORDER | PREG_OFFSET_CAPTURE
                );

                perf_exit( 'a_search.match_test' );
    
                $offset = 0;
    
                if( count( $matches ) > 0 ) {
   
                    perf_enter( 'a_search.label_build' );

                    $label = $ufile;
    
                    $offsets = $matches[0];
    
                    array_shift( $offsets );
    
                    foreach( $offsets as $k => $o ) {
                        $orig_string    = $o[0];
                        $orig_offset    = $o[1];
    
                        $replacement = "@$orig_string@";
    
                        $label = substr_replace( 
                            $label, 
                            $replacement, 
                            $offset + $orig_offset,
                            strlen( $orig_string )
                        );
    
                        $offset += strlen($replacement) - strlen( $orig_string );
    
                    }
    
                    $weight = substr_count( $label, "@@" );
    
                    $ret[] = array( 
                        "label"     =>  $label,
                        "value"     =>  $ufile,
                        "weight"    =>  $weight
                    );

                    perf_exit( 'a_search.label_build' );
    
                }
            }

            perf_exit( 'a_search.file'  );

        }

        usort(
            $ret,
            function( $a, $b ) {
                $r =  $b['weight'] - $a['weight'];
    
                return ( $r != 0 ? $r : ( strcmp( $a['label'], $b['label'] ) ) );
            }
        );

    }

    perf_exit( 'a_search.process'  );
}

perf_enter( 'a_search.json' );
$json = json_encode( $ret );
perf_exit( 'a_search.json' );

echo $json;

perf_exit( 'total' );

/*
if( $_SESSION['usr']['name'] == 'jrhoades' ) {
    echo perf_print();
}
*/

?>
