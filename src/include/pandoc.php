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
        'mode'      =>  'normal',
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
            case "mode":
                $ret['mode'] = $b;
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

function _render_pandoc_output( $file, $renderer, $pan ) {

    $puck = array(
        'file'          =>  $file,
        'pan'          =>  &$pan
    );

    // Render output using render mechanism
    $markdown_output = render( $renderer, $puck );

    $r = '';
    // Switch on the pandoc output format
    switch( strtolower( $pan[ 'format'] ) ) {

        case 'icml':

            $r = _pandoc_markdown_to_icml( 
                $markdown_output, 
                $pan['format'], 
                $pan['variables'],
                $pan['includes']
            ) ;
            break;

        case 'docx':

            $r = _pandoc_markdown_to_docx( 
                $markdown_output, 
                $pan['format'], 
                $pan['variables'],
                $pan['includes']
            ) ;
            break;


        case 'latex':
            
            $r = _pandoc_markdown_to_latex( 
                $markdown_output, 
                $pan['format'], 
                $pan['variables'],
                $pan['includes']
            ) ;
            break;

        case 'markdown':
        default:

            // No-op
            $r = $markdown_output;
            break;

    }

    # TODO: Revisit this to determine if there's a way
    # to fix the pandoc memory leak. Currently, is annoying
    # but appears innocuous.
    $r = preg_replace( 
        '/pandoc: unable to decommit memory: Invalid argument(\r?\n)?/',
        '',
        $r
    );

    return $r;

}

function section_header( $file ) {

    $lvl = 1;

    if( $file[ 'title' ] ) {
        if( $file[ 'params' ] ) {


            if( is_array( $file['params'] ) ) {

                if( isset( $file['params']['no'] ) ) {

                    $no = array();

                    if( !is_array( $file['params']['no'] ) ) {
                        $no[] = $file['params']['no'];
                    } else {
                        $no = $file['params']['no'];
                    }
                
                    if( in_array( 'title', $no ) ) {
                        // Don't print the title
                        return '';
                    }

                }

                if( isset( $file['params']['level'] ) ) {
                    switch( $file['params']['level'] ) {
                        case 'chapter':
                            $lvl = 1;
                            break;
                        case 'section':
                            $lvl = 2;
                            break;
                        case 'subsection':
                            $lvl = 3;
                            break;
                        case 'subsubsection':
                        case 'paragraph':
                        case 'subparagraph':
                            $lvl = 4;
                            break;
                        default:
                            $lvl = 1;
                            break;
                    }
                }
            }
        }

        return str_repeat( '#', $lvl ) . ' ' . $file[ 'title' ];
    }

    return '';
}

function _clean_output( $file ) {

    return gen_clean( $file['file'] );

}

function _clean_file( $file ) {

    if( git_file_exists( $file['file'] ) ) {

        return section_header( $file ) 
            . "\n\n"
            . _clean_output( $file ) 
            . "\n\n"
        ;
    }


    return '';

}


function gen_pandoc_output( $file, $contents, $opts = array() ) {

    perf_enter( "_gen_pan_output" );

    $renderer   =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_pan_output'); 

    $ret = _pan_parse( $file, $contents );

    $r = '';
    switch( strtolower( trim( $ret[ 'mode'] ) ) ) {

        case "archive":

            if( ( $temp_archive = tempnam( TMP_DIR, "pandoc_archive" ) ) == false ) {
                die( "Unable to create $temp_archive" );
            }

            $zip = new ZipArchive();

            if( $zip->open( $temp_archive, ZipArchive::CREATE ) !== TRUE ) {
                die( "Cannot open '$filename' as zip archive\n" );
            }

            $i = 1;

            foreach( $ret['files'] as $panfile  ) {

                $ext = 'tex';
                switch( $ret['format'] ) {

                    case 'markdown':
                        $ext = 'md';
                        break;

                    case 'icml':
                        $ext = 'icml';
                        break;

                    case 'docx':
                        $ext = 'docx';
                        break;

                    case 'latex':
                    default:
                        $ext = 'tex';
                        break;
                }
                
                $z = _render_pandoc_output( 
                    $file, 
                    $renderer, 
                    array(
                        'format'    => $ret['format'],
                        'variables' => $ret['variables'],
                        'includes'  => $ret['includes'],
                        'files'     => array( $panfile )
                    )
                );

                // Adding to ZIP archive
                $zip->addFromString(
                    (
                        str_pad( $i , 3, "0", STR_PAD_LEFT ) 
                        .
                        "-" 
                        . 
                        path_to_filename( 
                            basename( 
                                $panfile[ 'title' ]
                            ) 
                        ) 
                        . 
                        "."
                        . 
                        $ext
                    ),
                    $z
                );

                $i++;
            }

            $zip->close();

            $r = file_get_contents( $temp_archive );

            unlink( $temp_archive );

            break;

        case "normal":
        default:
            $r = _render_pandoc_output( $file, $renderer, $ret );
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

            // If in your ICML / InDesign layout you plan to use dropcaps, it's 
            // sometimes a pain to have to go back and remove all starting 
            // quotations in order to make sure that the leading character of
            // a given document is always an alphanumeric, and not a quotation
            // mark, etc.
            if( 
                isset( $variables['dropcap'] ) 
                && in_array( 
                    trim( strtolower( $variables['dropcap'] ) ), 
                    array( 'true', 'yes' ) 
                ) 
            ) {
                $contents = mb_ereg_replace( 
                    '^(\s*)[”“\']', // Leading whitespace followed by a quotation-mark-ish-thing
                    '',                     // Leave the whitespace
                    $contents,
                    1               // Replace no more than 1 match
                ); 
            }

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

function _pandoc_markdown_to_docx( $contents, $format, $variables, $includes = null ) {

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

            if( ( $out_file = tempnam( TMP_DIR, "out" ) ) == false ) {
                die( "Unable to create 'out' file for pandoc docx generation" );
            } 

            $pandoc_cmd = " -s "
                . $include_cmd . ' '
                . $var_cmd . ' '
                . ' -f markdown '
                . ' -t ' . escapeshellarg( $format ) . ' '
                . ' -o ' . escapeshellarg( $out_file ) . ' '
                . escapeshellarg( $clean_file )
            ;

            pandoc(
                $pandoc_cmd,
                $output // ignored, as docx only outputs to file
            );

            unlink( $clean_file );

            $contents = file_get_contents( $out_file );

            unlink( $out_file );

        }
    }

    return $contents;
}
?>
