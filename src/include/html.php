<?php

require_once( dirname( __FILE__ ) . '/util.php' );


function html_short_time_diff( $t1, $t2, $opts = null ) {

    $title = false;
    $classes = array( 'live-timestamp' );

    if( $opts && is_array( $opts ) ) {

        if( isset( $opts['classes'] ) ) {
        
            if( !is_array( $opts['classes'] ) ) {
                $opts['classes'] = array( $opts['classes'] );
            }
            
            if( count( $opts['classes'] ) > 0 ) {
                $classes = array_merge( $classes, $opts['classes'] );
            }
        }

        if( isset( $opts['title'] ) ) {
            if( !is_array( $opts['title'] ) ) {
                $title = $opts['title'];
            }
        }
    }


    return '<span ' 
        . (
            'data-time="' . $t1 . '" '
        )
        . (
            $title !== false 
            ? ' title="' . he( $title ) . '" ' 
            : ''
        )
        . ( $classes !== false 
            ?   ' class="' . implode( " ", $classes ) . '" '
            :   ''
        ) . '>' . short_time_diff( $t1, $t2 ) . '</span>';
}



?>
