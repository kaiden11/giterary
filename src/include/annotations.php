<?
require_once( dirname( __FILE__ ) . '/util.php' );

function annotateify( $contents ) {

    $ret_annotations = array();

    $annotation_pattern = '@<(annotate)[^>\n]*>(.+?)</\1>@i';

    $matches = array();

    preg_match_all(
        $annotation_pattern,
        $contents, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $offset = 0;


    # print_r( $matches );
    foreach( $matches as $match ) {

        $k = md5( $match[2][0] . $match[0][1] );

        $replacement = '<a class="internal annotation" name="' . $k . '"></a>' . '<' . $match[1][0] .  ' name="' . $k . '"' . '>' . $match[2][0] . '</' . $match[1][0] . '>';

        $contents = substr_replace( 
            $contents, 
            $replacement, 
            $offset + $match[0][1],
            strlen( $match[0][0] )
        );

        $offset += strlen($replacement) - strlen($match[0][0]);

        $comment_match = array();
        $comment = null;

        $comment_pattern = '@<(comment)[^>\n]*>(.+?)</\1>@i';

        if( preg_match( $comment_pattern, $match[2][0], $comment_match ) != 0 ) {
            # print_r( $comment_match );
            $comment = $comment_match[ 2 ];
        }

        $ret_annotations[] = array(
            'key'       =>  $k,
            'content'   =>  excerpt( 
                                preg_replace( $comment_pattern, '', $match[2][0] ),
                                100 
                            ),
            'comment'   =>  excerpt( 
                                ( $comment == null ? "(none)" : $comment ), 
                                100 
                            )
        );
    }

    return array( $contents, $ret_annotations );
}



?>
