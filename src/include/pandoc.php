<?php

require_once( dirname( __FILE__ ) . "/config/pandoc.php" );
require_once( dirname( __FILE__ ) . "/util.php" );


function pandoc( $command, &$output = "", $env = null, $debug = null, $suppress_error_return = false ) {

    perf_enter( "pandoc" );

    # Establish paths
    $pandoc_path = PANDOC_PATH;

    if( !PANDOC_ENABLE ) {
        die( "pandoc called, but pandoc is not enabled!" );
    }

    if( !file_exists( $pandoc_path ) ) {
        die( "Path to pandoc ($pandoc_path) does not exist!" );

    }

    $pandoc_cmd = "$pandoc_path $command";

    if( $debug === true ) {
        echo "\n$git_cmd\n";
    }

    list( $result, $out ) = _call( $pandoc_cmd, $env );

    $output .= $out;
    
    if ($result !== 0 && !$suppress_error_return ) {
    	echo(
            "<h1>Error</h1>\n<pre>\n"
    	    . "" . he( $pandoc_cmd ) . "\n"
    	    . he( $out ) . "\n"
    	    . "Return code: " . $result . "\n"
    	    . "From: " . get_caller_method(1 ) . "\n"
    	    . "</pre>"
        );
    }

    perf_exit( "pandoc" );

    return array( "return_code" => $result, "out" => $out );;

}

function _pan_parse( $file, $contents ) {
    $ret = array(
        'pan_file' =>  $file,
        'format'    =>  'latex',
        'files'     =>  array(),
        'variables' =>  array(),
        'includes'  =>  array(),
    );

    $i = 0;
    foreach( preg_split( '/(\r)?\n/', $contents ) as $line ) {

        if( $i == 0 ) {
            // Skip the header
            $i++;
            continue;
        }
       
        if( $line == "" ) {
            $i++;
            continue;
        }

        list( $a, $b, $c ) = str_getcsv( $line );

        $a = trim( $a );
        $b = trim( $b );
        $c = trim( $c );

        switch( strtolower( $a ) ) {
            case "format":
                $ret['format'] = $b;
                break;
            case "variable":
                $ret['variables'][$b] = funcify( $c, $file );
                break;
            case "include":
            case "header":
            case "includes":
            case "headers":
                $ret['includes'][] = funcify( $b, $file );
                break;

            default:
                $ret['files'][] = array(
                    'file'      =>  dirify( $a ),
                    'title'     =>  funcify( $b, $file ),
                    'path'      =>  path_to_filename( dirify( $a ) ),
                    'params'    =>  ( $c == "" ? null : proper_parse_str( $c ) )
                );
                break;
        }

        $i++;

    }


    return $ret;

}

function gen_pan( $file, $contents ) {
    perf_enter( "gen_pan" );

    if( !can( "pan", $file ) ) {
        return render( 'not_logged_in', array() );
    }

    return _gen_pan( 
        array( 
            'file'            => $file,
            'contents'        => &$contents
        ) 
    ) . perf_exit( "gen_pan" );
}

function _gen_pan( $opts = array() ) {

    perf_enter( "_gen_pan" );

    $file       =   ( isset( $opts['file'] ) ? $opts['file'] : null ); 
    $contents   =   ( isset( $opts['contents'] ) ? $opts['contents'] : null ); 

    $renderer   =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_pan'); 

    $ret = _pan_parse( $file, $contents );

    $puck = array(
        'file'          =>  $file,
        'pan'          =>  &$ret
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_pan" );
}


function gen_pandoc_output( $file, $contents, $opts = array() ) {

    perf_enter( "_gen_pan_output" );

    $renderer   =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_pan_output'); 

    $ret = _pan_parse( $file, $contents );

    $puck = array(
        'file'          =>  $file,
        'pan'          =>  &$ret
    );

    // Render output using render mechanism
    $markdown_output = render( $renderer, $puck );

    $r = '';
    // Switch on the pandoc output format
    switch( strtolower( $ret[ 'format'] ) ) {

        case 'icml':

            $r = _pandoc_markdown_to_icml( 
                $markdown_output, 
                $ret['format'], 
                $ret['variables'],
                $ret['includes']
            ) ;
            break;

        case 'latex':
            
            $r = _pandoc_markdown_to_latex( 
                $markdown_output, 
                $ret['format'], 
                $ret['variables'],
                $ret['includes']
            ) ;
            break;

        case 'markdown':
        default:

            // No-op
            $r = $markdown_output;
            break;

    }

    perf_exit( "_gen_pan_output" );
    return $r;


}


function _pandoc_markdown_to_latex( $contents, $format, $variables, $includes = null ) {

    if( PANDOC_ENABLE ) {

        if( ( $clean_file = tempnam( TMP_DIR, "clean" ) ) == false ) {
            die( "Unable to create 'clean' file for pandoc generation" );
        } else {
            file_put_contents( $clean_file, $contents );

            $output = "";

            $var_cmd = '';

            if( is_array( $variables ) ) {
                foreach( $variables as $k => $v ) {
                    $var_cmd .= ' --variable ';
                    $var_cmd .= escapeshellarg( $k ) . '=' . escapeshellarg( $v );
                }
            }

            $include_cmd = '';

            if( $includes && is_array( $includes ) ) {

                foreach( $includes as $i ) {

                    if( git_file_exists( $i ) ) {

                        $i_path = GIT_REPO_DIR . dirify( $i );

                        if( file_exists( $i_path ) ) {

                            $include_cmd .= ' --include-in-header ';
                            $include_cmd .= escapeshellarg( $i_path );
                        }
                    }

                }
            }


            $pandoc_cmd = " -s "
                . $include_cmd . ' '
                . $var_cmd . ' '
                . ' -f markdown '
                . ' -t ' . escapeshellarg( $format ) . ' '
                . escapeshellarg( $clean_file )
            ;

            pandoc(
                $pandoc_cmd,
                $output 
            );

            unlink( $clean_file );

            $contents = $output;

        }
    }

    return $contents;
}

function _pandoc_markdown_to_icml( $contents, $format, $variables, $includes = null ) {

    if( PANDOC_ENABLE ) {

        if( ( $clean_file = tempnam( TMP_DIR, "clean" ) ) == false ) {
            die( "Unable to create 'clean' file for pandoc generation" );
        } else {
            file_put_contents( $clean_file, $contents );

            $output = "";

            $var_cmd = '';

            if( is_array( $variables ) ) {
                foreach( $variables as $k => $v ) {
                    $var_cmd .= ' --variable ';
                    $var_cmd .= escapeshellarg( $k ) . '=' . escapeshellarg( $v );
                }
            }

            $pandoc_cmd = " -s "
                . $include_cmd . ' '
                . $var_cmd . ' '
                . ' -f markdown '
                . ' -t ' . escapeshellarg( $format ) . ' '
                . escapeshellarg( $clean_file )
            ;

            pandoc(
                $pandoc_cmd,
                $output 
            );

            unlink( $clean_file );

            $contents = $output;

        }
    }

    return $contents;
}

?>
