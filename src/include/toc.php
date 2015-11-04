<?
require_once( dirname( __FILE__ ) . '/util.php' );
require_once( dirname( __FILE__ ) . '/config/conventions.php' );


$anchor_discriminator = array();
function anchorify( $name ) {
    GLOBAL $anchor_discriminator;

    $ret = urlencode( $name );

    if( !isset( $anchor_discriminator[ $ret ] ) ) {
        $anchor_discriminator[ $ret ] = 1;
    } else {
        $anchor_discriminator[ $ret ]++;
    }

    return $ret . ( $anchor_discriminator[$ret] > 1 ? '_' . $anchor_discriminator[$ret] : '' );
}

function toc_functionlink( $text, &$ret_headers ) {
    
    GLOBAL $functionlink_pattern;
    GLOBAL $tag_pattern;

    perf_enter( 'toc_functionlink' );

    $text = callback_replace(
        $functionlink_pattern,
        $text,
        function( $match ) use( $ret_headers ) {

            $orig = $match[0][0];

            $escape = $match[1][0];

            $func = $match[3][0];
            $params = $match[5][0];
            $display = $match[11][0];

            $replacement = '';

            $toc_html = render(
                'gen_toc',
                array(
                    'toc_headers'   =>  &$ret_headers,
                    'args'          =>  argify( $params )
                )
            );

            if( $escape == "\\" || $escape == "!" ) {
                $replacement = substr( $orig, 1 );
            } else {

                switch( $func ) {
                    case "toc":
                    case "tableofcontents":
                        $replacement = $toc_html;
                        break;
                    default:
                        $replacement = $orig;
                        break;
                }
            }

            return $replacement;
        }
    );

    return $text . perf_exit( 'toc_functionlink' );

}


function tocify( $contents ) {

    $ret_headers = array();

    $header_pattern = '@<([hH][1-6])[^>\n]*>(.+?)</\1>@';

    $matches = array();

    preg_match_all(
        $header_pattern,
        $contents, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $offset = 0;


    # print_r( $matches );
    foreach( $matches as $match ) {


        $a = anchorify( strip_tags( $match[2][0] ) );

        $replacement = '<a name="' . $a . '"></a>' . $match[0][0];

        $contents = substr_replace( 
            $contents, 
            $replacement, 
            $offset + $match[0][1],
            strlen( $match[0][0] )
        );

        $offset += strlen($replacement) - strlen($match[0][0]);

        $class = '';
        switch( strtolower( $match[1][0] ) ) {
            case "h1":
            case "h2":
            case "h3":
            case "h4":
            case "h5":
                $class = strtolower( $match[1][0] );
                break;
            case "h6":
                $class = "toc-hidden";
                break;
        }

        $ret_headers[] = array(
            'tag'   =>  $match[1][0],
            'text'  =>  '<a href="#' . $a . '" class="' . $class . '">' . excerpt( $match[2][0], 100 ) . '</a>'
        );

    }

    // Do post-processing of TOC_REPLACME elements (if they exist)

    $contents = toc_functionlink( $contents, $ret_headers );

    /*
    if( strpos( $contents, TOC_REPLACEME ) !== FALSE ) {
        $matches = array();

        preg_match_all(
            "/" . TOC_REPLACEME . "/",
            $contents, 
            $matches, 
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        $offset = 0;

        $replacement = 'hi there';

        $replacement = render(
            'gen_toc',
            array(
                'toc_headers'   =>  &$ret_headers
            )
        );

        foreach( $matches as $match ) {

            $contents = substr_replace( 
                $contents, 
                $replacement, 
                $offset + $match[0][1],
                strlen( $match[0][0] )
            );

            $offset += strlen($replacement) - strlen($match[0][0]);
        }
    }
    */

    return array( $contents, $ret_headers );

}



?>
