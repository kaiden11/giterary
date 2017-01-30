<?php
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");

function _epub_display( &$epub, $file, $contents, &$zip ) {
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

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = true;

    $doc->loadHTML( $contents );

    // Remove any tags not allowed in epub XHTML docs
    if( $epub_removed_tags && count( $epub_removed_tags ) > 0 ) {

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

        libxml_clear_errors();
    }


    // Image processing...
    if( true ) {

        foreach( $xpath->query( "//img" ) as $img_node ) {
            // $node->parentNode->removeChild( $node );
            if( $img_node->hasAttributes() ) {
                foreach( $img_node->attributes as $attr_name => $attr ) {
                    if( $attr_name != "src" ) {
                        continue;
                    } else {
                        // Data URI image / screenshot
                        if( preg_match( '/^data:/', $attr->textContent )  ) {

                            list( $dummy, $c ) = explode( ':', $attr->textContent, 2 );

                            list( $mime, $dummy ) = explode( ';', $c, 2 );
                            $dummy = '';

                            $c = file_get_contents( "data://$c" );

                            $finfo = new finfo(FILEINFO_MIME);
                            $mime_type = array_shift( explode( ";", $finfo->buffer( $c ) ) );


                            $new_file = _image_to_file( $zip, $mime_type, $c );

                            $img_node->setAttribute( 'src', $new_file );
                            $img_node->setAttribute( 'alt', $new_file );

                            $epub['images'][] = array(
                                'file'      =>  $new_file,
                                'mimetype'  =>  $mime_type
                            );
                        
                            continue;
                        }


                        // Wiki-local image
                        if( preg_match( '/^raw\.php\?/', $attr->textContent ) ) {

                            $qs = parse_url( $attr->textContent, PHP_URL_QUERY );

                            $params = proper_parse_str( $qs );

                            if( isset( $params['file'] ) ) {

                                if( ( file_or( $params['file'], false ) ) !== false ) {
                               
                                    if( git_file_exists( $params['file'] ) ) {
                                        $c = git_file_get_contents( dirify( $params['file'] ), null, false );

                                        $finfo = new finfo(FILEINFO_MIME);
                                        $mime_type = array_shift( explode( ";", $finfo->buffer( $c ) ) );

                                        $new_file = _image_to_file( $zip, $mime_type, $c );

                                        $img_node->setAttribute( 'src', $new_file );
                                        $img_node->setAttribute( 'alt', $new_file );

                                        $epub['images'][] = array(
                                            'file'      =>  $new_file,
                                            'mimetype'  =>  $mime_type
                                        );
                                    }
                                }
                            }

                            continue;
                        }

                        // Externally sourced image
                        if( preg_match( '/^http:\/\//', $attr->textContent ) ) {

                            // Do nothing, we don't know what kind of behavior to
                            // expect here, and just going out to the web to get
                            // an image seems more insecurely than helpful.
                        }


                        // We don't know how to handle the image, so we will
                        // remove it from the output
                        $img_node->parentNode->removeChild( $img_node );
                    }
                }
            }
        }

        libxml_clear_errors();
    }

    $contents = $doc->saveXML( 
        null, 
        LIBXML_HTML_NODEFDTD
    );
    
    return $contents;
}

function _image_to_file( &$zip, &$mime_type, &$content ) {

    $ext = 'jpg';

    switch( $mime_type ) {
        case "image/png":
            $ext = "png";
            break;
        case "image/jpeg":
        case "image/jpg":
            $ext = "jpg";
            break;
        case "image/gif":
            $ext = "gif";
            break;
        default:
            break;
    }

    $md5 = md5( $content );

    $new_file = path_to_filename( "$md5.$ext" );

    if( $zip != null ) {
        $zip->addFromString(
            "OEBPS/" . $new_file,
            $content
        );
    }

    return $new_file;
}

function epub_archive( $file, $contents ) {

    global $epub_allowed_tags;
    global $application_name;
    global $instance_name;

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

    // Required
    $zip->addFromString(
        "OEBPS/giterary.metadata",
        "
            Application Name:   $application_name
            Instance Name:      $instance_name
            ePub Definition:    $file
            Querystring:        " . $_SERVER['QUERY_STRING'] . "
            Time:               " . gmdate( "c" ) . "
        "
    );


    // META-INF
    $zip->addFromString(
        "META-INF/container.xml",
        render( 'gen_epub_container_xml', $ret )
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

        $y = git_file_get_contents( $ret['cover'] );

        $zip->addFromString(
            "OEBPS/" . path_to_filename( $ret['cover'] ),
            // More explicit file_get_contents to account for
            // potential of caching mechanism to fail for larger
            // files. Skip caching for this particular call.
            git_file_get_contents( 
                $ret['cover'], 
                null,  // Latest commit
                false  // Do not cache
            )
        );
    }

    // files
    foreach( $ret['files'] as $f ) {

        // render all content, and any requisite sub-files
        // necessary (images, etc.)
        $content = _epub_display( 
            $ret,
            $f,
            git_file_get_contents( $f['file'] ),
            $zip
        );

        $zip->addFromString(
            "OEBPS/" . $f['path'] . '.xhtml',
            $content
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

    // content.opf
    $zip->addFromString(
        "OEBPS/content.opf",
        render( 'gen_epub_content_opf', $ret )
    );


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
        'files'     =>  array(),
        'images'    =>  array()
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
                $ret['title'] = funcify( $b, $file );
                break;
            case "cover":
                $ret['cover'] = dirify( $b );
                break;
            case "authors":
            case "author":
                $ret['authors'][] = funcify( $b, $file );
                break;
            default:
                $ret['files'][] = array(
                    'file'      =>  dirify( $a ),
                    'title'     =>  funcify( $b, $file ),
                    'path'      =>  path_to_filename( dirify( $a ) ),
                    'params'    =>  ( trim( $c ) == "" ? null : proper_parse_str( $c ) )
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
