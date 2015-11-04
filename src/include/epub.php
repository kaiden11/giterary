<?php
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");

function _epub_display( $epub, $file, $contents ) {
    global $epub_allowed_tags;
    global $epub_removed_tags;
    global $php_tag_pattern;
    global $php_meta_header_pattern;

    $extension = detect_extension( $file['file'], null );

    // Remove tags (~tag)
    $contents = callback_replace( 
        "@$php_tag_pattern@m",
        $contents, 
        function( $match ) {
            return '';
        }
    );

    /*
    // Remove metadata headers (%Key: Value)
    $contents = callback_replace( 
        $php_meta_header_pattern . 'm',
        $contents, 
        function( $match ) {
            return '';
        }
    );
    */
    // Pre-rendering processing
    $meta = array();
    if( in_array( $extension, array( "markdown", "text", "print","read" ) ) ) {

        // $meta[ $commit_file_tag ] = array();
        $contents = metaify( 
            $contents,
            $file['file'],
            $meta
        );

        $contents = metaify_empty_strip( $contents );
    }


    $contents = _display( 
        $file['file'], 
        $contents 
    );
    // Post-rendering processing
    if( in_array( $extension, array( "markdown", "text", "print","read" ) ) ) {

        $contents = metaify_import_strip( // Strip any [[%Tag]] variables that haven't been replaced
            metaify_postprocess( 
                $contents,
                $file['file'],
                $meta
            )
        );
    }

    $contents = strip_tags(
        $contents,
        '<' . join( 
            '><', 
            $epub_allowed_tags 
        ) . '>' 
    );

    $contents = layout(
        array(
            'content'   =>  $contents,
            'epub'      =>  &$epub,
            'file'      =>  $file
        ),
        array(
            'renderer'  =>  'epub_xhtml_layout'
        )
    );


    if( $epub_removed_tags && count( $epub_removed_tags ) > 0 ) {


        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = true;

        $doc->loadHTML( $contents );
        $xpath = new DOMXPath( $doc );

        foreach( $epub_removed_tags as $t ) {

            // TODO: This seems inefficient... but whatever.
            $t = strtolower( $t );
            foreach( $xpath->query( "//$t" ) as $node ) {
                $node->parentNode->removeChild( $node );
            }

            $t = strtoupper( $t );
            foreach( $xpath->query( "//$t" ) as $node ) {
                $node->parentNode->removeChild( $node );
            }
        }

        $contents = $doc->saveXML( 
            null, 
            LIBXML_HTML_NODEFDTD 
        );
        // $contents = $doc->saveHTML();

        /* <?= '<?' ?>xml version="1.0" encoding="UTF-8" <?= '?>' ?> */

        libxml_clear_errors();
    }
    
    return $contents;
}

function epub_archive( $file, $contents ) {

    global $epub_allowed_tags;

    if( ( $temp_archive = tempnam( TMP_DIR, "conflict" ) ) == false ) {
        die( "Unable to create $temp_archive" );
    }

    $zip = new ZipArchive();

    if( $zip->open( $temp_archive, ZipArchive::CREATE ) !== TRUE ) {
        die( "Cannot open '$filename' as zip archive\n" );
    }

    $ret = _epub_parse( $file, $contents );

    // Remove any files that do not exist
    for( $i = 0; $i < count( $ret['files'] ); $i++ ) { 
        if( !git_file_exists( $ret['files'][$i]['file'] ) ) {
            unset( $ret['files'][$i] );
        }
    }

    // Required
    $zip->addFromString(
        "mimetype",
        'application/epub+zip'
    );

    // META-INF
    $zip->addFromString(
        "META-INF/container.xml",
        render( 'gen_epub_container_xml', $ret )
    );

    // content.opf
    $zip->addFromString(
        "OEBPS/content.opf",
        render( 'gen_epub_content_opf', $ret )
    );

    // toc.ncx
    $zip->addFromString(
        "OEBPS/toc.ncx",
        render( 'gen_epub_toc_ncx', $ret )
    );

    if( $ret['cover'] && git_file_exists( $ret['cover'] ) ) {

        $zip->addFromString(
            "OEBPS/" . 'cover' . '.xhtml',
            render( 'gen_epub_cover', $ret )
        );

        $zip->addFromString(
            "OEBPS/" . path_to_filename( $ret['cover'] ),
            git_file_get_contents( $ret['cover'] )
        );
    }

    // files
    foreach( $ret['files'] as $f ) {
        $zip->addFromString(
            "OEBPS/" . $f['path'] . '.xhtml',
            _epub_display( 
                $ret,
                $f,
                git_file_get_contents( $f['file'] )
            )
        );
    }

    if( isset( $ret['css'] ) ) {
        foreach ($ret['css'] as $c ) {
            $zip->addFromString(
                "OEBPS/" . path_to_filename( $c ),
                git_file_get_contents( $c )
            );
        }
    }

    $zip->close();

    $ret = file_get_contents( $temp_archive );

    unlink( $temp_archive );

    return $ret;
}

function _epub_parse( $file, $contents ) {
    $ret = array(
        'epub_file' =>  $file,
        'title'     =>  '',
        'cover'     =>  '',
        'authors'   =>  array(),
        'files'     =>  array()
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
            case "css":
                if( !isset( $ret['css'] ) ) {
                    $ret['css'] = array();
                }
                $ret['css'][] = $b;
                break;
            case "title":
                $ret['title'] = $b;
                break;
            case "cover":
                $ret['cover'] = $b;
                break;
            case "authors":
            case "author":
                $ret['authors'][] = $b;
                break;
            default:
                $ret['files'][] = array(
                    'file'  =>  dirify( $a ),
                    'title' =>  $b,
                    'path'  =>  path_to_filename( dirify( $a ) )
                );
                break;
        }

        $i++;

    }


    return $ret;
}


function gen_epub( $file, $contents ) {
    perf_enter( "gen_epub" );

    if( !can( "epub", $file ) ) {
        return render( 'not_logged_in', array() );
    }

    return _gen_epub( 
        array( 
            'file'            => $file,
            'contents'        => &$contents
        ) 
    ) . perf_exit( "gen_epub" );
}

function _gen_epub( $opts = array() ) {
    global $epub_allowed_tags;
    global $epub_removed_tags;

    perf_enter( "_gen_epub" );

    $file       =   ( isset( $opts['file'] ) ? $opts['file'] : null ); 
    $contents   =   ( isset( $opts['contents'] ) ? $opts['contents'] : null ); 

    $renderer   =   ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_epub'); 

    $ret = _epub_parse( $file, $contents );

    $puck = array(
        'file'          =>  $file,
        'epub'          =>  &$ret,
        'allowed_tags'  => '<' . join( '><', $epub_allowed_tags ) . '>',
        'removed_tags'  => '<' . join( '><', $epub_removed_tags ) . '>'
    );

    return render( $renderer, $puck ) .  perf_exit( "_gen_epub" );
}

function _epub_xml_name( $a, $prefix = 'x_' ) {
    return $prefix . md5( $a );

}

?>
