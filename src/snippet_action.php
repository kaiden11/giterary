<? 
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/footer.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/auth.php');
require_once( dirname( __FILE__ ) . '/include/snippet.php');
require_once( dirname( __FILE__ ) . '/include/git_html.php');

# $query = proper_parse_str($_SERVER['QUERY_STRING']);

$confirm =  ( isset( $_POST['confirm'] ) && $_POST['confirm'] == "yes" ? $_POST['confirm'] : "no" );
$snippet    = file_or(  $_POST['snippets'], false );

$give       = set_or(   $_POST['give'], false );
$delete     = set_or(   $_POST['delete'], false );


if( !is_logged_in() ) {

    echo layout(
        array(
            'header'            => gen_header( "Not logged in" ), 
            'content'           => gen_not_logged_in()
        )
    );
} else {

    $action = false;

    if( $delete !== false ) {

        $action = "delete";


    } elseif( $give !== false ) {


        $action = "give";

    }


    switch( $action ) {

        case "delete":
            die( "Delete" );
            break;
        case "give":

            $userlist = userlist();

            if( !in_array( $give, $userlist ) ) {
                die( "Unknown user for snippet gift: '$give'");
            }

            if( $snippet === false ) {
                die( "No snippets selected" );
            }

            if( !is_array( $snippet ) ) {
                $snippet = array( $snippet );
            }

            $operable = array();


            foreach( $snippet as $s ) {

                $snippet = snippet_get( $s );

                if( is_null( $snippet ) ) {
                    die( "Can't find: '$s'" );
                }

                if( $snippet[ 'username' ] != $_SESSION['usr']['name'] ) {
                    die( "Unable to open '$s'" );
                }

                $operable[] = $s;
            }

            if( $confirm !== "yes" ) {

                /*
                $qs = implode( 
                    "&", 
                    array_map(
                        function( $a ) {
                            return $a['name'] . '=' . urlencode( $a['value'] );
                        },
                        array_merge(
                            array(
                                array(  
                                    "name"  =>  "give",
                                    "value" =>  $give
                                )
                            ),
                            array_map(
                                function( $a ) {
                                    return array(
                                        "name"  =>  "snippet",
                                        "value" =>  $a
                                    );
                                },
                                $operable
                            )
                        )
                    )
                );
                */

                $form = '';
                $form .= '<form action="snippet_action.php" method="post">';
                $form .= '<input type="hidden" name="give" value="' . he( $give ) . '"/>';
                $form .= '<input type="hidden" name="confirm" value="yes"/>';

                $form .= '<select name="snippets[]" style="display: none" multiple>';
                foreach( $operable as $op ) {
                    $form .= '<option value="' . he( $op ) . '" selected/>';
                }

                $form .= '</select>';
                $form .= '
                    <div>
                        I want to gift these snippets, a thousand times 
                        <input type="submit" value="YES" />
                    </div>'
                ;


                $form .= '</form>';

                echo layout(
                    array(
                        'header'    => gen_header( "Confirm snippet gift" ), 
                        'content'   => note( 
                            "Are you sure you want to gift these " . count( $operable ) . " snippet(s) to $give?",
                            "<div>
                                <a href=\"snippets.php\">No, I've made a terrible mistake.</a>
                            </div>
                            $form
                            "
                        )
                    )
                );

            }
            else {
            // Confirmed. Gift the snippets

                foreach( $operable as $s ) {
                    // $ret = snippet_delete( $_SESSION['usr']['name'], $snippet );

                    $snippet = snippet_get( $s );

                    $ret = snippet_update( 
                        $give,
                        $snippet['file'],
                        $snippet['commit'], 
                        $snippet['snippet'], 
                        $snippet['context'], 
                        $snippet['type'],
                        $_SESSION['usr']['name']
                    );

                    $ret_message = '';

                    if( $ret !== true ) {
                        $ret_message = "A problem has occurred while gifting the snippet '$ret'";

                        die( $ret_message );

                    } else {
                        $ret_message = note( 
                            "Your snippet has been gifted",
                            'Hope it was a good one. Back to <a href="snippets.php">snippets</a>.'
                        );
                    }
                }

                echo layout(
                    array(
                        'header'    => gen_header( "Snippet gifted" ), 
                        'content'   => $ret_message
                    )
                );

            }
            
            break;
        default:
            die( "Unknown action: '$action'" );
    }
}


