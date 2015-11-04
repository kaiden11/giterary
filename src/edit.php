<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');
require_once( dirname( __FILE__ ) . '/include/edit.php');
require_once( dirname( __FILE__ ) . '/include/drafts.php');

$file = null;
$parameters = null;
$as = $_GET['as'];

$from_draft = false;
$draft_discard = ( $_GET['draft_discard'] == "yes" ? true : false );

function display_edit_normally() {
    GLOBAL $file;
    GLOBAL $parameters;

    echo layout(
        array(
            'header'            => gen_header( "Editing " . basename( $file ) ), 
            'content'           => gen_edit( $parameters  )
        ),
        array(
            'renderer'          => 'edit_layout'
        )
    );
}

if( $_GET['draft'] ) {

    if( is_logged_in() ) {

        if( file_exists( DRAFT_DIR . "/" . $_GET['draft'] ) ) {

            $draft = unserialize( file_get_contents( DRAFT_DIR . "/" .  $_GET['draft'] ) );

            if( $draft['user'] == $_SESSION['usr']['name'] ) {

                $file =  file_or( undirify( $draft['filename'], null ) );
                
                $parameters = array(
                    'edit_contents'     =>  $draft['contents'],
                    'submit'            =>  'Preview',
                    'commit_notes'      =>  $draft['notes'],
                    'existing_commit'   =>  commit_or( $draft['commit'], null ),
                    'synchronize'       =>  true,
                    'file'              =>  $file
                ); 

                $from_draft = true;
            }
        }
    }

} else {
    $file               =  file_or( $_POST['file'], file_or(  $_GET['file'], null ) );
    $position           =  numeric_or( $_POST['position'], 0 );
    $preview_position   =  numeric_or( $_POST['preview_position'], 0 );
    $caret_position     =  numeric_or( $_POST['caret_position'], 0 );
    $height             =  $_POST['height'];

    $talk_append        =  ( set_or( $_GET['talk_append'], null ) == "yes" ? true : false );

    if( is_array( $file ) ) {
        $file = array_shift( $file );
    }
    
    $parameters = array(
        'edit_contents'     =>  ( hed( $_POST['edit_contents'] ) ),
        'submit'            =>  $_POST['submit'],
        'commit_notes'      =>  ( $_POST['commit_notes'] ),
        'existing_commit'   =>  commit_or( $_POST['existing_commit']. null ),
        'synchronize'       =>  $_POST['synchronize'],
        'file'              =>  $file,
        'as'                =>  $as,
        'position'          =>  $position,
        'preview_position'  =>  $preview_position,
        'caret_position'    =>  $caret_position,
        'height'            =>  $height,
        # Whether to "append" standard date/timestamp 
        'talk_append'       =>  $talk_append
    );
}


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

    if( $file == null || $file == "" ) {
        echo layout( array( 'content' => "No file submitted" ) );
    } else {

        if( is_dirifile( $file ) ) {

            echo layout( 
                array( 
                    'content' => "Cannot 'edit' a directory file. You're welcome to <a href=\"index.php?file=" 
                                . undirify( $file ) 
                                . "\">view its contents</a>, or <a href=\"edit.php?file=" 
                                . undirify( $file, true ) 
                                . "\">edit its file equivalent</a>." 
                )
            );

        } else {

            if( in_array( $parameters['submit'], array( "Commit", "Commit and Edit" ) ) && $parameters['edit_contents'] ) {
            
                echo layout(
                    array(
                        'header'            => gen_header( "Committing $file" ), 
                        'content'           => gen_commit( $parameters  )
                    ),
                    array(
                        'renderer'          => 'edit_layout'
                    )
                );
            } else {

                # TODO: De-deduplicate "normal" editing

                # You've submitted, but not a commit
                if( in_array( $parameters['submit'], array( "Preview" ) ) ) {

                    draft_update( 
                        $parameters['file'],
                        $parameters['existing_commit'],
                        $parameters['edit_contents'],
                        $parameters['commit_notes']
                    );


                    display_edit_normally();

                    /*
                    echo layout(
                        array(
                            'header'            => gen_header( "Editing $file" ), 
                            'content'           => gen_edit( $parameters  )
                        ),
                        array(
                            'renderer'          => 'edit_layout'
                        )
                    );
                    */
                } else {

                    # You are neither Previewing or Commiting

                    if(  $from_draft ) {
                        # You are attempting an edit by specifying a draft name (and your
                        # contents have already been rewritten above

                        display_edit_normally();

                        /*
                        echo layout(
                            array(
                                'header'            => gen_header( "Editing $file" ), 
                                'content'           => gen_edit( $parameters  )
                            ),
                            array(
                                'renderer'          => 'edit_layout'
                            )
                        );
                        */

                    } else {

                        # Editing without submitting anything or specifying a draft,
                        # but we need to check if a draft already exists so we don't
                        # overwrite it without the user's knowledge

                        $head_commit = ( 
                            git_file_exists( dirify( $file ) )
                            ?
                            commit_or( 
                                git_file_head_commit( dirify( $file ) ),
                                git_head_commit()
                            ) 
                            :
                            git_head_commit()
                        );

                        $draft_name = draftify( $_SESSION['usr']['name'], $file, $head_commit );

                        if(  file_exists( DRAFT_DIR . '/' . $draft_name ) && !$draft_discard ) {

                            # If a draft exists and we aren't explicitly discarding it, display a warning
                            # for the user with options on what to do.
                            echo layout(
                                array(
                                    'header'            => gen_header( "Warning: Draft exists for $file" ), 
                                    'content'           => gen_draft_warning( $draft_name, $parameters  )
                                )
                            );
                        } else {
                            # No draft detected, editing normally.

                            display_edit_normally();

                            /*
                            echo layout(
                                array(
                                    'header'            => gen_header( "Editing $file" ), 
                                    'content'           => gen_edit( $parameters  )
                                ),
                                array(
                                    'renderer'          => 'edit_layout'
                                )
                            );
                            */
                        }
                    }
                }
            }
        }
    }
}

?>
