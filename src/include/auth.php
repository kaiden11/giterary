<?
require_once( dirname( __FILE__ ) . '/config/auth.php'  );
require_once( dirname( __FILE__ ) . '/git.php'          );

function establish_session( $usr ) {
    GLOBAL $themes;
    require_once('include/header.php');

    # session_register('usr'); 
    $_SESSION['usr'] = $usr; 

    $theme_paths_to_check = array(
        dirify( implode(    "/",    array( $_SESSION['usr']['name'], "Theme"        ) ) ),
        dirify( implode(    "/",    array( $_SESSION['usr']['name'], "theme"        ) ) ),
        dirify( implode(    "/",    array( $_SESSION['usr']['name'], "Theme.theme"  ) ) ),
        dirify( implode(    "/",    array( $_SESSION['usr']['name'], "theme.theme"  ) ) )
    );

    $theme_path = false;
    foreach( $theme_paths_to_check as $p ) {
    
        if( git_file_exists( $p ) ) {
            $theme_path = $p;
            break;
        }
    }

    $theme_contents = false;
    if( $theme_path !== false ) {
        $theme_contents = git_file_get_contents( $theme_path );
    }

    if( $theme_contents !== false ) {
        $theme_contents  = trim( strtolower( $theme_contents ) );

        if( isset( $themes[ $theme_contents ] ) ) {
            $_SESSION['usr']['theme'] = $theme_contents;
        }
    }
}

# We wrap around a series of objects that can hopefully
# tell us whether we have a valid login or not.
function validate_login( $uname, $password ) {
    GLOBAL $registered_login_calls;

    if( count( $registered_login_calls ) == 0 ) {

        echo "No authentication methods registered.";
        return false;
    }

    foreach( $registered_login_calls as $k => &$p ) {
        
        if( !is_object( $p[0] ) ) {
            echo "not an object!";
        } else {
            if( ! method_exists( $p[0], $p[1] ) ) {
                echo "method does not exist";
            } else {

                $ret = $p[0]->$p[1]( $uname, $password );

                if( $ret === false ) {
                    # Attempt another authentication mechanism
                    continue;
                } else {
                    return $ret;
                }
            }
        }
    }

    return false;
}

# Gather a list of users.
function userlist() {
    GLOBAL $registered_userlist_calls;

    if( count( $registered_userlist_calls ) == 0 ) {

        echo "No userlist methods registered.";
        return false;
    }

    foreach( $registered_userlist_calls as $k => &$p ) {
        
        if( !is_object( $p[0] ) ) {
            echo "not an object!";
        } else {
            if( ! method_exists( $p[0], $p[1] ) ) {
                echo "method does not exist";
            } else {

                $ret = $p[0]->$p[1]();

                if( $ret === false ) {
                    # Attempt another authentication mechanism
                    continue;
                } else {
                    return $ret;
                }
            }
        }
    }

    return false;
}




# $registered_calls = array();
# 
# function register_auth_call( $auth_obj, $auth_method ) {
#     GLOBAL $registered_calls;
# 
#     $registered_calls[] = array( $auth_obj, $auth_method );
# 
#     return;
# }
# 
# function can( $verb, $thing ) {
#     GLOBAL $registered_calls;
# 
#     if( count( $registered_calls ) == 0 ) {
#         # Return true is no objects are registered
#         # to be able to answer this question
#         return true;
#     }
# 
#     foreach( $registered_calls as $k => &$p ) {
#         
#         if( !is_object( $p[0] ) ) {
#             echo "not an object!";
#         } else {
#             if(  method_exists( $p[0], $p[1] ) ) {
# 
#                 if( $p[0]->$p[1]( $verb, $thing ) ) {
#                     return true;
#                 }
#             }
#         }
#     }
# 
#     return false;
# }

?>
