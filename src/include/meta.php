<?
require_once( dirname( __FILE__ ) . "/config/conventions.php");
require_once( dirname( __FILE__ ) . "/util.php");

function metaify( $contents, $file, &$headers = array() ) {
    GLOBAL $php_meta_header_pattern;

    // $php_meta_header_pattern = '@^%([^%:]+):\s*(.*)$@';

    $lines = preg_split( '/\r?\n/', $contents );

    foreach( $lines as &$line ) {
        $line = callback_replace( 
            $php_meta_header_pattern,
            $line, 
            function( $match ) use( $file, &$headers ) {

                $escape     = $match[1][0];
                $wo_escape  = $match[2][0];

                if( $escape == "!" ) {
                    return $wo_escape;
                }

                $key        = $match[3][0];
                $val        = $match[4][0];

                if( !isset( $headers[ $key ] ) ) {
                    $headers[ $key ] = array();
                }

                $headers[ $key ][] = $val;

                return '';
            }
        );
    }

    return implode( $lines, "\n" );
}


function metaify_postprocess( &$contents, &$file, &$meta ) {

    GLOBAL $php_meta_header_import_pattern; // = "@(\\\)?\[\[%([^%:]+?)\]\]@";

    return callback_replace( 
        $php_meta_header_import_pattern,
        $contents, 
        function( $match ) use( $file, $meta ) {

            $escape     = $match[1][0];
            $wo_escape  = $match[2][0];
            $key        = $match[3][0];

            if( $escape == "!" ) {
                return $wo_escape;
            }
            
            if( isset( $meta[ $key ] ) && $meta[ $key ] != null && is_array( $meta[ $key ] ) ) {
                return implode( 
                    ",", 
                    array_map(
                        function( $a )  {

                            if( filter_var( $a, FILTER_VALIDATE_URL ) ) {
                                return '<a href="' . $a . '">' . he( $a ) . '</a>';
                            }

                            return $a;
                        },
                        $meta[ $key ] 
                    )
                );
            }

            // Did not find corresponding key
            return $match[0][0];
        }
    );
}

function metaify_import_strip( &$contents ) {

    GLOBAL $php_meta_header_import_pattern; // = "@(\\\)?\[\[%([^%:]+?)\]\]@";

    return callback_replace( 
        $php_meta_header_import_pattern,
        $contents, 
        function( $match ) {
            return '';
        }
    );
}

function metaify_empty_strip( &$contents ) {

    GLOBAL $php_meta_empty_pattern;

    return callback_replace( 
        $php_meta_empty_pattern . 'm', // Multiline
        $contents, 
        function( $match ) {
            return '';
        }
    );
}



?>
