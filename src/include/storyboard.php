<?
require_once( dirname( __FILE__ ) . "/util.php");


function gen_storyboard( $opts = array()  ) {

    perf_enter( 'gen_storyboard' );

    $flag           = null;
    // $image          = 1;
    $subject        = null;
    $description    = null;

    $file       =   ( isset( $opts['file'] ) ? $opts['file'] : null );
    $contents   =   ( isset( $opts['contents'] ) ? $opts['contents'] : null );

    $renderer =         ( isset( $opts['renderer'] ) ? $opts['renderer'] : 'gen_storyboard'); 

    $cards = array();

    $i = 0;
    foreach( preg_split( '/\r?\n/', $contents ) as $line ) {
        if( $i == 0 ) {

            $line = str_getcsv( $line );

            if( count( $line ) == 1 ) {
                $flag = null;
                $subject = 0;
                $description = 0;
            } else {
                for( $j = 0; $j < count( $line ); $j++ ) {
                    $v = $line[ $j ];
    
                    $v = strtolower( trim( ltrim( trim( $v ), '@#^!' ) ) );
    
                    if( $v == 'flags' || $v == 'flag' ) {
                        $flag = $j;
                        continue;
                    }
    
                    if( $v == 'subject' ) {
                        $subject = $j;
                        continue;
                    } 
    
                    if( $v == 'description' ) {
                        $description = $j;
                        continue;
                    }
                }
    

            }


            // $flag           = is_null( $flag        ) ? null             : $flag ;
            $description    = is_null( $description ) ? 0               : $description ;
            $subject        = is_null( $subject     ) ? $description    : $subject ;

            $i++;
            continue;
        }

        if( $line == "" ) {
            $i++;
            continue;
        }

        $line = str_getcsv( $line );

        // TODO: Sort of a hack. Needs to replace with actual 
        // delimiter, not a hard-coded default
        if( count( $line ) > 3 ) {
            $remainder = array_splice(
                $line,
                3
            );

            $line[ 2 ] .= "," . implode( ", ", $remainder );
        }

        foreach( $line as $t => &$c ) {

            if( $t == $flag ) {
                $c = trim( $c );
                continue;
            }

            if( $t == $subject ) {
                $c = _display_pipeline( 
                    $file, 
                    trim( $c ), 
                    array( 
                        'todoify',
                        'linkify',
                    )
                );

                continue;

            }

            /*
            if( $t == $image ) {
                $c = _display_pipeline( 
                    $file, 
                    trim( $c ), 
                    array( 
                        'funcify'
                    )
                );

                continue;

            }
            */


            $c = _display_pipeline( 
                $file, 
                trim( $c ), 
                array( 
                    'funcify',
                    'todoify',
                    'linkify',
                    'span_markdown'
                    // Too much of a performance hit
                )
            );
        }

        /*
        for( $j = 0; $j < count( $line ); $j++ ) {
            $line[ $j ] = trim( $line[ $j ] );
        }
        */

        $card = array();

        if( !is_null( $flag ) && isset( $line[ $flag ] ) ) {

            $match = array();
            if( preg_match( '/^([!?]+)$/', $line[ $flag ], $match ) != 0 ) {
                $line[ $flag ] = $match[1];

                if( strpos( $match[1], '!' ) !== false ) {
                    $card[ 'disabled' ] = 1;
                }

                if( strpos( $match[1], '?' ) !== false ) {
                    $card[ 'questioned' ] = 1;
                } else {
                    $card[ 'questioned' ] = 0;
                }

            } else {
                $card[ 'disabled' ] = 0;
                $card[ 'questioned' ] = 0;
            }

            $card[ 'flag' ] = $line[ $flag ];
        }

        /*
        if( isset( $line[ $image ] ) ) {
            $card[ 'image' ] = $line[ $image ];
        }
        */

        if( !is_null( $subject ) && isset( $line[ $subject ] ) ) {
            $card[ 'subject' ] = $line[ $subject ];
        }

        if( isset( $line[ $description ] ) ) {
            $card[ 'description' ] = $line[ $description ];
        }

        $card['line'] = $i;

        $cards[] = $card;

        $i++;
    }


    $puck = array(
        'file'  =>  $file,
        'cards' =>  &$cards
    );

    return render( $renderer, $puck ) .  perf_exit( "gen_storyboard" );
}

?>
