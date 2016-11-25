<?php
renderable( $p );

$stash['css'][] = 'pandoc.css';


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



?><?php foreach( $p['pan']['files'] as $file ) { ?>
<?= _clean_file( $file ) ?>
<?php } ?>

