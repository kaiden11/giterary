<?
require_once( dirname( __FILE__ ) . "/config.php");
require_once( dirname( __FILE__ ) . "/util.php");
require_once( dirname( __FILE__ ) . "/collection.php");
# require_once( dirname( __FILE__ ) . "/transclude.php");
require_once( dirname( __FILE__ ) . "/funcify.php");
require_once( dirname( __FILE__ ) . '/cache.php' );
require_once( dirname( __FILE__ ) . '/config/conventions.php' );

function text_display( $contents, $wrap = false ) {

    $pattern = "/(\r)?\n/m";
    $separator = "\r\n";

    $lines = preg_split( $pattern, $contents); 
    $format_string = "%" . strlen( count($lines)*2 ) . "d | $separator";
    $line_numbers = '';
    $line_str = '';
    

    if( $wrap === false ) {
        for( $i = 0; $i < count($lines); $i++ ) { 
            $line_numbers .= sprintf( $format_string, $i );

            $line_str .= he( $lines[$i] ) . "$separator";
        }
    } else {
        if( !is_numeric( $wrap ) ) {
            $wrap = 80;
        }

        $line_number = 1;
        for( $i = 0; $i < count($lines); $i++ ) {
            $indent = null;
            $match = null;

            if( preg_match( '@^\s+@', $lines[$i], $match ) != 0 ) {
                $indent = $match[0];
                # echo strlen( $indent );
            }

            $wrapped_str = wordwrap( $lines[$i], $wrap, $separator );

            $wrapped_lines = preg_split( $pattern, $wrapped_str);

            for( $j = 0; $j < count( $wrapped_lines ); $j++ ) {
                $line_numbers .= sprintf( $format_string, $line_number );
                $line_number++;
            }

            if( $indent != null ) {
                $wrapped_str = implode( "$separator$indent", $wrapped_lines );
            }

            $line_str .= he( $wrapped_str  ) . "$separator";
        }
    }

   
    $ret = '';


    $ret .= '<table class="text-output" cellspacing="0" cellpadding="0">
        <tr>
            <td width="1px" class="noselect"><pre>' . $line_numbers . '</pre></td>
            <td style="vertical-align: top;"><pre>' . $line_str . '</pre></td>
        </tr>
    </table>
    ';

    return $ret;
}

/* Removed for the time being
function textile_display( $contents ) {
    perf_enter( "textile_display" );
    require_once( "classTextile.php" );
    $textile = new Textile();

    return $textile->TextileThis( $contents ) . perf_exit( "textile_display" );
}
*/

function str_putcsv($array, $callable, $delimiter = ",", $enclosure = '"', $escape = "\\") { 

    ob_start(); // buffer the output ...
    $fp = fopen('php://output', 'w'); // this file actual writes to php output

    foreach( $array as &$element ) {
        fputcsv(
            $fp, 
            $callable( 
                $element 
            ) 
        );
    }

    fclose($fp);

    return ob_get_clean();
} 

function storyboard_display( $file, $contents ) {
    perf_enter( "storyboard_display" );

    require_once( 'storyboard.php' );

    return gen_storyboard( 
        array(
            'file'      =>  $file, 
            'contents'  =>  &$contents 
        )
    ) . perf_exit( 'storyboard_display' );
}


function annotation_display( $file, $contents ) {
    perf_enter( "annotation_display" );

    $annotations = json_decode( $contents, true );

    $contents = '';

    $contents   .=  str_putcsv( 
                        array( array( 
                            # "File", 
                            "User", 
                            # "Created", 
                            # "Updated", 
                            'Annotated Text', 
                            'Tags', 
                            'Annotation' 
                        ) ),
                        function( $a ) { return $a; }
                    );

    $contents .= "\n";

    $contents   .=  str_putcsv( 
                        $annotations,
                        function( $a ) {
                            return array( 
                                # $a['uri'],
                                $a['user'],
                                # $a['created'],
                                # $a['updated'],
                                preg_replace( '/\r?\n/', '<br/>', $a['quote'] ),
                                implode( ',', $a['tags'] ),
                                preg_replace( '/\r?\n/', '<br/>', $a['text'] ),
                            );
                        }
                    );


    $contents .= "\n";

    # echo $contents;

    return csv_display( 
        $file, 
        $contents, 
        false 
    ) . perf_exit( "annotation_display" );
}

function markdown_display( $contents, $dialogify = false, $oob = null ) {
    perf_enter( "markdown_display" );

    require_once( "markdown.annotated.php" );
    //require_once( "markdown.php" );

    return Markdown( 
        $contents, 
        array( 
            "dialogify"  =>  $dialogify 
        ),
        $oob
    ) . perf_exit( "markdown_display" );
}

function span_markdown_display( $contents, $dialogify = false, $oob = null ) {
    perf_enter( "span_markdown_display" );

    require_once( "markdown.annotated.php" );
    //require_once( "markdown.php" );

    return Markdown( 
        $contents, 
        array( 
            "dialogify"  =>  $dialogify 
        ),
        $oob
    ) . perf_exit( "span_markdown_display" );
}


function csv_display( $file, $contents, $show_search = true, $sort = true ) {
    GLOBAL $php_tag_pattern;
    perf_enter( "csv_display" );

    require_once( "csv.php" );

    $contents = _strip( $contents );

    $ret = array();

    foreach( preg_split( '/\r?\n/', $contents ) as $line ) {
        if( $line == "" ) {
            continue;
        }

        # Skip "tag" lines.
        if( preg_match( "/$php_tag_pattern/", $line ) == 1 ) {
            continue;
        }

        $line = str_getcsv( $line );

        foreach( $line as $t => &$c ) {
            $c = _display_pipeline( 
                $file, 
                trim( $c ), 
                array( 
                    'todoify',
                    'linkify',
                    'span_markdown',
                    'highlightify'
                    // Too much of a performance hit
                )
            );

            /*
            $c = markdown_display(
                linkify( 
                    funcify(
                        $c,
                        $file
                    ),
                    array(
                        'separator'     =>  '/', 
                        'minify'        =>  false, 
                        'current_file'  =>  $file  
                    )
                )
            );
            */
        }

        $ret[] = $line;
    }

    $contents = '';

    return render( 
        'gen_csv', 
        array( 
            'file'          =>  $file,
            'contents'      =>  &$ret,
            'show_search'   =>  $show_search,
            'sort'          =>  $sort
        )
    ) . perf_exit( "csv_display" );
}

function image_metadata( $file ) {
    perf_enter( 'image_metadata' );

    $ret = array();
   
    $path = GIT_REPO_DIR . '/' . dirify( $file );

    $extension = null;
    $matches = array();
    if( preg_match( '/\.([a-zA-Z0-9_-]+)$/', $file, $matches ) == 1 ) {
        $extension = strtolower( $matches[1] );
    }

    $exif = null;
    if( in_array( $extension, array( 'jpg', 'jpeg' ) ) ) {
        $exif = exif_read_data( $path );
    }

    $info = array();
    $iptc = array();

    $size = getimagesize( $path, $info );
    
    if( isset( $info['APP13'] ) ) {
        $iptc = iptcparse( $info['APP13'] );
    }

    # print_r( $exif );
    # print_r( $iptc );

    $exif_comment = trim( isset( $exif['COMPUTED']['UserComment'] ) ? $exif['COMPUTED']['UserComment'] : '' );
    $iptc_comment = trim( isset( $iptc['2#120'] ) && is_array( $iptc['2#120'] ) ? implode( ",", $iptc['2#120'] ) : '' );

    $ret['comment'] = implode( 
        ",", 
        array_filter( 
            array( $exif_comment, $iptc_comment ),
            function( $a ) {
                return !is_null( $a ) && !$a == '';
            }
        )
    );

    perf_exit( 'image_metadata' );

    return $ret;

}

function audio_display( $file, $contents ) {
    perf_enter( "audio_display" );

    $finfo = new finfo(FILEINFO_MIME);

    $file_path = GIT_REPO_DIR . '/' . $file;
    $content_type = array_shift( explode( ";", $finfo->file( $file_path ) ) );

    # require_once( "csv.php" );
    return render( 
        'gen_audio', 
        array( 
            'file'          =>  $file,
            'content_type'  =>  $content_type
            # 'contents'  =>  &$ret
        )
    ) . perf_exit( "audio_display" );
}

function image_display( $file, $contents, $as_collection = false ) {
    perf_enter( "image_display" );

    $meta = array();

    if( IMAGE_META_DISPLAY ) {
        # $ext = detect_extension( $file );
        $finfo = new finfo(FILEINFO_MIME);
        $content_type = array_shift( explode( ";", $finfo->buffer( $contents ) ) );

        if( !in_array( $content_type, array( 'image/png', 'image/gif' ) ) ) {

            $meta = image_metadata( $file );

        }

    }

    # print_r( $meta );

    # require_once( "csv.php" );
    return render( 
        'gen_image', 
        array( 
            'file'      =>  $file,
            'metadata'  =>  $meta,
            'collection'=>  $as_collection
            # 'contents'  =>  &$ret
        )
    ) . perf_exit( "image_display" );
}



function minify( $text ) {
    return preg_replace( '/(?<=[A-Z0-9])[a-z ]+/', '', $text );
}


# Old definition
# function linkify( $text, $separator = "/", $minify = false, $current_file = null ) {

function linkify( $text, $opts = array() ) {
    GLOBAL $wikiname_pattern;
    GLOBAL $wikilink_pattern;

    perf_enter( "linkify" );

    if( !is_array( $opts ) ) {
        debug_print_backtrace();
        die( "Invalid opts parameter to linkify" );
    }
    
    $separator      =   set_or( $opts['separator'],     "/"     );
    $minify         =   set_or( $opts['minify'],        false   );
    $current_file   =   set_or( $opts['current_file'],  null    );
    $title          =   set_or( $opts['title'],         null    );
    $prefix         =   set_or( $opts['prefix'],        ''      );
    $suffix         =   set_or( $opts['suffix'],        ''      );

    $matches = array();

    # $wikiname_pattern = '-_a-zA-Z0-9\.';
    # $wikilink_pattern = "@\[\[([$wikiname_pattern]+(\/[$wikiname_pattern]+)*)(\|([\w\s\.\,-]+))?\]\]@";
    //'<a href="index.php?file=$1">$3</a>';

    preg_match_all(
        $wikilink_pattern,
        $text, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $offset = 0;

    # print_r( $matches );
    foreach( $matches as $match ) {

        $replacement = '';

        if( $match[1][0] == "\\" ) {

            $replacement = substr( $match[0][0], 1 );

        } else {

            if( file_or( $match[2][0], false, $current_file ) ) {
            
                $effective_path = file_or( $match[2][0], null, undirify( $current_file ) );

                # echo "effective path: $effective_path";

                $dirified_path = dirify( $effective_path );

                # echo "dirified path: $dirified_path";

                $components = explode( "/", undirify( $effective_path ) );

                if( $match[4] ) {

                    if( WIKILINK_DETECT_EXISTS && !git_file_exists( $dirified_path ) ) {

                        $replacement = $prefix .  '<a title="' . ( $title == null ? join( "/", $components ) : $title ) . '" class="edit wikilink" href="index.php?file=' . undirify( $effective_path ) . '">' . $match[5][0] . '</a>' . $suffix;


                    } else {

                        $replacement = $prefix . '<a title="' . ( $title == null ? join( "/", $components ) : $title ) . '" class="wikilink" href="index.php?file=' . undirify( $effective_path ) . '">' . $match[5][0] . '</a>' . $suffix;


                    }
                } else {

                    $path_to_now = array();
                    $links_to_now = array();
                    $i = 0;
                    $count = count( $components );

                    foreach( $components as $component ) {
                        if( $minify ) {
                            if(  $i < ( $count-1 ) ) {
                                $c = minify( $component );
                            } else {
                                $c = $component;
                            }
                        } 

                        $path_to_now[] = $component;

                        if( ( WIKILINK_DETECT_EXISTS && !is_dirifile( join('/', $path_to_now ) ) && !git_file_exists( dirify( join('/', $path_to_now ) ) ) ) ) {

                            $links_to_now[] = '<a ' . ( $minify ? 'title="' . $component . '"' : '' ) . ' class="edit wikilink component" href="index.php?file=' . join( "/", $path_to_now ) . '">' . ( $minify ? $c :  $component ) . '</a>';
                        } else {

                            $links_to_now[] = '<a ' . ( $minify ? 'title="' . $component . '"' : '' ) . ' class="wikilink component" href="index.php?file=' . join( "/" , $path_to_now ) . '">' . ( $minify ? $c : $component ). '</a>';

                        }

                        $i++;
                    }

                    $replacement = "<span class=\"linkify\">" . $prefix . join( "<span class=\"separator\">$separator</span>", $links_to_now ) . $suffix . '</span>';
                }
            }
        }

        $text = substr_replace( 
            $text, 
            $replacement, 
            $offset + $match[0][1],
            strlen( $match[0][0] )
        );

        $offset += strlen($replacement) - strlen($match[0][0]);

    }

    perf_exit( "linkify" );
    return $text;
}

function _strip( $text ) {
    GLOBAL $allowed_tags;

    return strip_tags( $text, join('', $allowed_tags ) );
}

function line_diff( $text ) {
    
    return preg_replace( 
        '/^\-(.*)/', 
        '<span class="diff remove">\1</span>', 
        preg_replace(
            '/^\+(.*)/', 
            '<span class="diff add">\1</span>', 
            preg_replace(
                '/^\\\ No newline at end of file$/',
                '', 
                $text 
            )
        )
    );
}


function word_diff( $text ) {

    return preg_replace( 
        '/\[-(.+?)-\]/s',
        '<span class="diff remove">\1</span>', 
        preg_replace(
            '/\{\+(.+?)\+\}/s', 
            '<span class="diff add">\1</span>', 
            preg_replace(
                '/@@[^@]+@@/s', 
                '', 
                $text 
            )
        )
    );
}

function word_diff_before( $text ) {

    return preg_replace( 
        '/\[-([^\]]+)-\]/',
        '<span class="diff remove">\1</span>', 
        preg_replace(
            '/\{\+([^}]+?)\+\}/', 
            '', 
            preg_replace(
                '/@@[^@]+@@/', 
                '', 
                $text 
            )
        )
    );
}

function word_diff_after( $text ) {

    return preg_replace( 
        '/\[-([^\]]+)-\]/',
        '', 
        preg_replace(
            '/\{\+([^}]+?)\+\}/', 
            '<span class="diff add">\1</span>', 
            preg_replace(
                '/@@[^@]+@@/', 
                '', 
                $text 
            )
        )
    );
}


function _display_pipeline( $file, $contents, $handlers = array(), $preview = false ) {

    foreach( $handlers as $h ) {

        switch( $h ) {
            case "span_markdown":
                $contents = span_markdown_display( $contents );
                break;
            case "span_markdown_dialogify":
                $contents = span_markdown_display( $contents, true );
                break;
            case "markdown":
                $contents = markdown_display( $contents );
                break;
            case "markdown_dialogify":
                $contents = markdown_display( $contents, true );
                break;
            case "highlightify":
                // Do nothing, we will plan on doing this from
                // the Javascript side of things
                // $contents = highlightify( $contents );
                break;
            case "linkify":
                $contents = linkify( 
                    $contents, 
                    array(
                        'separator'     =>  '/', 
                        'minify'        =>  false, 
                        'current_file'  =>  $file 
                    )
                );
                break;
            case "tagify":
                $contents = tagify( $contents );
                break;
            case "todoify":
                $contents = todoify( $contents );
                break;
            case "commentify":
                $contents = commentify( $contents );
                break;

            case "funcify":
                $contents = funcify( $contents, $file, $preview );
                break;
            # Done during pre-processing
            # case "metaify":
            #     $contents = metaify( $contents, $file );
            #     break;
            # case "dialogify":
            #     $contents = dialogify( $contents );
            #     break;
            case "htmlentity":

                // Ellipsis
                $contents = preg_replace( '@\\.\\.\\.@', '…', $contents );

                // Quotation marks
                $contents = preg_replace( '@(^|\s)[*_]{0,2}"@', '\1“', $contents );
                $contents = preg_replace( '@"[*_]{0,2}($|\s)@', '”\1', $contents );

                break;
            case "text": 
                $contents = text_display( $contents );
                break;

            default:
                die( "Invalid pipeline handler specified: $h" );
        }                
    }

    return $contents;
}

function epub_display( $file, $contents, $as_archive = false ) {
    require_once( 'epub.php' );


    if( $as_archive ) {
        $ret = epub_archive( $file, $contents );
    } else {
        $ret = gen_epub( $file, $contents );
    }

    return $ret;
}


function _display( $file, &$contents, $extension_override = null, $cache = true, $preview = false ) {

    perf_enter( '_display.' . basename( $file ) );
    $ret = '';

    $orig_extension = detect_extension( $file, null );
    $extension = detect_extension( $file, $extension_override );


    switch ( $extension ) {
        case "css": 
        case "js": 
        case "text": 
        case "xml": 
            # $ret = text_display( $contents );
            $ret = _display_pipeline( $file, $contents, array( 'text' ), $preview );
            break;
        case "raw": 

            switch( $orig_extension ) {
                case "collection":
                    $ret = collection_display( $file, $contents, "raw" );
                    break;
                case "pub":
                    $ret = epub_display( $file, $contents, true );
                    break;
                default:
                    $ret = $contents;
            }
            break;
        case "print": 

            if( $orig_extension == "collection" ) {
                $ret = collection_display( $file, $contents, "print" );
            } else {
                # Pass-through

                $ret = _display( $file, $contents, $orig_extension );
            }
            break;

        case "wraptext": 
            $ret = text_display( $contents, 80 );
            break;
        case "talk": 
        case "markdown": 
            $ret = _display_pipeline( 
                $file, 
                $contents, 
                array( 
                    'htmlentity',
                    'funcify',
                    'todoify',
                    'tagify',
                    'linkify',
                    'commentify',
                    // 'highlightify',      // We don't do highlightify here because 
                                            // otherwise multiple levels of transclusion could
                                            // mess this up.
                    'markdown_dialogify'
                ), 
                $preview 
            );

            break;
        case "collection": 
            $ret = collection_display( $file, $contents, "collection", true, $preview );
            break;
        case "list": 
            $ret = collection_display( $file, $contents, "list" );
            break;
        case "anno": 
            $ret = annotation_display( $file, $contents );
            break;
        case "sb": 
        case "storyboard": 
            $ret = storyboard_display( $file, $contents );
            break;
        case "csv": 

            if( $orig_extension == "list" || $orig_extension == "collection" ) {

                $files = collect_files( preg_split( '/\r?\n/', $contents ), $file );

                $max_cols = 0;

                // Find the max number of columns
                foreach( $files as &$f ) {
        
                    $c = substr_count( $f, '/' )+1; # TODO: Make this Windows friendly

                    if( $c > $max_cols ) {
                        $max_cols = $c;
                    }
                } 

                foreach( $files as &$f ) {
                    $f = undirify( $f );
                    $f = linkify( 
                        '[[' . $f . ']]',
                        array(
                            'separator' =>  ','
                        )
                    );

                    $c = substr_count( $f, "," ) + 1;

                    if( $c < $max_cols ) {
                        $f .= str_repeat( ",", ( $max_cols-$c ) );
                    }
                }

                $headers = array();
                for( $i = 1; $i <= $max_cols ; $i++ ) {
                    $headers[] = "!" . to_hexavigesimal( $i );
                }
                array_unshift( $files, implode( ",", $headers ) );

                $contents = implode( "\n", $files );

                $ret = csv_display( $file, $contents );

            } else {
                $ret = csv_display( $file, $contents );
            }
            break;
        case "pub":
            $ret = epub_display( $file, $contents, false );
            break;
        case "image": 
            $ret = image_display( $file, $contents );
            break;
        case "audio": 
            $ret = audio_display( $file, $contents );
            break;

        default: 
            $ret = _display_pipeline( 
                $file, 
                $contents, 
                array( 
                    'htmlentity',
                    'funcify',
                    'todoify',
                    'tagify',
                    'linkify',
                    'commentify',
                    'markdown_dialogify'
                ), 
                $preview 
            );
            break;
    }

    if( !in_array( $extension, array( "csv","raw","storyboard","print") ) ) {
        $ret = _strip( $ret );
    }


    return $ret . perf_exit( '_display.' . basename( $file ) );
}

/*
Moved to within the Markdown parser
function dialogify( $text ) {
    // echo "dialogify!";
    return preg_replace( 
        '/(?<!"\s)("[^"\n]+?[-,\.?!\x{2026}]")(?!\s")/u', 
        '<span class="dialog">\1</span>', 
        preg_replace( 
            '/(\x{201c}[^\x{201c}\x{201d}\n]+?[-,\.?!\x{2026}]\x{201d})/u', '<span class="dialog utf-quotes">\1</span>', 
            $text 
        )
    );

    // e2 80 9c, x201c
    // e2 80 9d, x201d
}
*/

function todoify( $text ) {

    // We aren't checking for CB for highlighting,
    // as this occurs way too often. Will appear in 
    // searches, however.
    $pattern = '/(?<![a-zA-Z0-9+\/])(!)?(TODO|TBD)(:)?/';

    return preg_replace_callback(
        $pattern,
        function( $m ) {
            if( $m[ 1 ] === '!' ) {
                return '<span class="todo done">' . $m[2] . $m[3] . '</span>';
            } else {
                return '<span class="todo">' . $m[2] . $m[3] . '</span>';
            }
        },
        $text
    );

    /*
    return preg_replace( 
        $pattern,                   // We aren't checking for CB for highlighting,
                                    // as this occurs way too often. Will appear in 
                                    // searches, however.
        '<span class="todo">\2\3</span>', 
        $text 
    );
    */
}

// Take care of // Comments
function commentify( $text ) {
    return preg_replace( 
        '/\/\/\s+(.*)$/m', 
        '<comment>\0</comment>', 
        $text 
    );
}


function tagify( $text ) {
    GLOBAL $php_tag_pattern;

    return preg_replace( 
        "/$php_tag_pattern/m", 
        '<h6 class="tag \2"><a href="tags.php?tag=\2">\2</a></h6>', 
        $text 
    );
}

function get_highlightify_content() {

    GLOBAL $highlightify_filenames;

    $found = false;

    if( is_logged_in() ) {

        // Search in user's directory first.
        foreach( $highlightify_filenames as $f ) {
            $f = dirify( $_SESSION['usr']['name'] . "/" . $f );

            if( git_file_exists( $f ) ) {
                $found = $f;
                break;
            }
        }
    }

    $c = false;
    if( $found !== false ) {
        $c = git_file_get_contents( $found );
    }

    return $c;
}

function highlightify( $text ) {

    GLOBAL $highlightify_filenames;

    perf_enter( "highlightify" );

    $c = get_highlightify_content();

    if( $c !== false ) {

        foreach( preg_split( '/\r?\n/', $c ) as $line ) {
            if( $line == "" ) {
                continue;
            }

            $line = str_getcsv( $line );

            $pattern = null;
            $bg = null;
            if( count( $line ) > 0 ) {

                $line[0] = trim( $line[0] );

                if( preg_match( "/^\/.+\/[i]*$/", $line[ 0 ] ) ) {
                    $pattern = $line[ 0 ];
                } else {
                    $pattern = "/" . preg_quote( $line[ 0 ], '/' ) . "/i";
                }

                $clazz = false;
                if( count( $line ) > 1 ) {
                    $clazz = array_slice( $line, 1 );
                }

                perf_enter( "highlightify.replace" );

                $text = preg_replace( 
                    $pattern, 
                    '<span class="highlightify ' . ( $clazz !== false ? he( implode( " ", $clazz ) ) : '' ) . '">\0</span>',
                    $text
                );

                /*
                $text = callback_replace( 
                    $pattern, 
                    $text, 
                    function( $match ) use ( $clazz ) {
                        return '<span class="highlightify ' . he( strtolower( $match[0][0] ) ) . ' ' . ( $clazz !== false ? he( $clazz ) : '' ) . '">' . $match[0][0] . '</span>';
                    }
                );
                */

                perf_exit( "highlightify.replace" );
            }
        }
    }

    perf_exit( "highlightify" );

    return $text;

}



# function gen_toc( $contents ) {
#     perf_enter( 'gen_toc' );
# 
#     $dom = str_get_html( $contents );
# 
#     $headers = $dom->find( 'h1, h2, h3, h4, h5, h6' );
# 
#     $puck = array(
#         'headers'            =>  &$headers,
#     );
#     return render( 'gen_toc', $puck ) .  perf_exit( "gen_toc" );
# 
# }

?>
