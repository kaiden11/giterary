<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');

$file       = file_or( $_GET['file'], null );
$template   = file_or( $_GET['template'], null );


# Single editing for the time being...

if( !is_logged_in() ) {

    echo layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_not_logged_in()
        ),
        array(
            'renderer'          => 'edit_layout'
        )
    );

} else {

    if( $template == null || $template == "" ) {
        die( layout( array( 'content' => "No template submitted" ) ) );
    }
    if( $file == null || $file == "" ) {
        die( layout( 
                array( 
                    'content'   => gen_new( 
                        // Suggest new name based on path to template
                        dirname( 
                            undirify( 
                                $template,
                                true
                            )
                        ) . '/NewFileName',
                        $template 
                    )
                )
        ) );

    } else {

        if( is_dirifile( $file ) ) {

            die( layout( 
                array( 
                    'content' => "Cannot 'edit' a directory file. You're welcome to <a href=\"index.php?file=" 
                                . undirify( $file ) 
                                . "\">view its contents</a>, or <a href=\"edit.php?file=" 
                                . undirify( $file, true ) 
                                . "\">edit its file equivalent</a>." 
                )
            ) );

        } else {

            $dfile      =   dirify( $file );
            $dtemplate  =   dirify( $template );

            if( git_file_exists( $dfile ) ) {
                die( layout(
                    array(
                        'header'            => gen_header( "Cannot create file from template over existing file" ), 
                        'content'           => gen_error( "Sorry, but this file " . linkify( '[[' . $file . '|already exists]].' ) )
                    ),
                    array(
                        'renderer'          => 'edit_layout'
                    )
                ) );
            }

            if( !git_file_exists( $dtemplate ) ) {
                die( layout(
                    array(
                        'header'            => gen_header( "Invalid template" ), 
                        'content'           => gen_error( "Sorry, but this template file " . linkify( '[[' . $template . '|does not exist yet]].' ) )
                    ),
                    array(
                        'renderer'          => 'edit_layout'
                    )
                ) );
            }
            
            $dtemplate_contents = git_file_get_contents( $dtemplate );

            // Strip any ~template tags from the template
            $dtemplate_contents = preg_replace( 
                '/^~template\s*$/m', 
                '', 
                $dtemplate_contents 
            );

            $parameters = array(
                'edit_contents'     =>  $dtemplate_contents,
                'submit'            =>  'Preview',
                'commit_notes'      =>  "From template '" . $template . "'",
                'existing_commit'   =>  $head_commit,
                'synchronize'       =>  true,
                'file'              =>  $file
            ); 

            echo layout(
                array(
                    'header'            => gen_header( "Creating file from template" ), 
                    'content'           => gen_edit( $parameters )
                ),
                array(
                    'renderer'          => 'edit_layout'
                )
            );
        } 
    }
}

?>
