<?
require_once( dirname( __FILE__ ) . '/../util.php' );

class AllPlay {
    public function can( $verb, $thing ) {
        return true;
    }
}

class MustBeLoggedIn {
    public function can( $verb, $thing ) {
        if( is_logged_in() ) {
            return true;
        }

        return false;
    }
}

class AllPlayExcept {

    private $exceptions = array();

    private static $write_verbs  = array(
        "edit",
        "commit",
        "move",
        "delete"
    );

    function __construct( $exceptions ) {
        if( is_array( $exceptions ) ) {
            $this->exceptions = $exceptions;
        }
    }

    function can ( $verb, $thing ) {

        # Not writing to this file? We don't care...
        if( !in_array( $verb, self::$write_verbs ) ) {
            
            return true;
        }

        # Only paying attention to the "source" for now
        list( $thing, $chaff ) = explode( ":", $thing, 2 );

        $thing = dirify( $thing );

        $is_sensitive_thing = false;

        foreach( $this->exceptions as $file => $specification ) {

            $file = dirify( $file );

            if( $file == $thing ) {
                $is_sensitive_thing = true;
                break;
            }
        }

        if( !$is_sensitive_thing ) {
            return true;
        } else {

            $specification = $this->exceptions[ $thing ];

            if( $specification === false ) {
                # Simply can't write to this file
                return false;
            }

            if( !is_array( $specification ) ) {
                if( $specification == $_SESSION['usr']['name'] ) {
                    return true;
                }
            }

            if( is_array( $specification ) ){
                foreach( $specification as $specified_user ) {
                    if( $_SESSION['usr']['name'] == $specified_user ) {
                        return true;
                    }
                }

                return false;
            }
        }
    }
}

class WorkArea extends MustBeLoggedInToWrite {

    private $users = array();

    private static $critical_verbs  = array(
        "read",
        "edit",
        "commit",
        "show",
        "diff",
        "partition",
        "cherrypick",
        "move",
        "delete",
    );


    function __construct( $users ) {
        if( is_array( $users ) ) {
            $this->users = $users;
        }
    }


    function can( $verb, $thing ) {
        $ret = parent::can( $verb, $thing );

        if( $ret === false ) {
            return false;
        }


        $u = $_SESSION['usr']['name'];

        // Deny any unspecified users
        if( !isset( $this->users[ $u ] ) ) {
            return false;
        }

        $perm = $this->users[ $u ];
        if( !is_array( $perm ) ) {
            $perm = array( $perm );
        }

        // Are we doing something that this class
        // deems "critical?"
        if( in_array( $verb, self::$critical_verbs ) ) {

            foreach( $perm as $p ) {
                if( is_bool( $p ) ) {
                    // For better or for worse,
                    // grant/deny this person access
                    // to all verbs/things.
                    return $p;
                } 

                // This shouldn't ever succeed
                if( $p == "" ) {
                    continue;
                }

                if( is_callable( $p ) ) {

                    if( $p( $u, $verb, $thing ) ) {
                        return true;
                    }

                    continue;
                }

                // Now we start to check if this is a valid-looking file
                if( file_or( $p, false ) !== false ) {

                    $p = dirify( $p );

                    if( is_dirifile( $p ) ) {
                        // If a directory is specified, assume that
                        // all files underneath this directory are
                        // part of the allowed work area
                        if( has_directory_prefix( $p, $thing ) ) {
                            return true;
                        }
                    } else {
                        // If a file is specified, assume that only
                        // this file is the work area.
                        if( dirify( $thing ) == $p ) {
                            return true;
                        }
                    }
                }

                // TODO: Handling for diffs, and being able to
                // calculate diffs for files that you can read,
                // but not others.

                if( $verb == "diff" ) {
                    return true;
                }

            }
        } else {
            // Non-critical verb, allow
            return true;
        }

        return false;
    }
}


class SensitiveFiles extends MustBeLoggedInToWrite {

    private $exceptions = array();

    function __construct( $exceptions ) {
        if( is_array( $exceptions ) ) {
            $this->exceptions = $exceptions;
        }
    }

    function can ( $verb, $thing ) {
        $ret = parent::can( $verb, $thing );

        $thing = dirify( $thing );
        $is_sensitive_thing = false;

        if( $ret === false ) {
            return false;
        }

        foreach( $this->exceptions as $file => $specification ) {

            switch( $verb ) {

                case "diff":
                    list( $before, $after, $thing ) = explode( ":", $thing );

                    $thing = dirify( $thing );

                    if( $file == $thing ) {
                        $is_sensitive_thing = true;
                        break 2;
                    }

                    break;


                default:
                    $file = dirify( $file );

                    if( $file == $thing ) {
                        $is_sensitive_thing = true;
                        break 2;
                    }
                    break;
            }
        }

        if( !$is_sensitive_thing ) {
            return $ret;
        } else {

            $specification = $this->exceptions[ $thing ];

            if( $specification === false ) {
                # Simply can't write to this file
                return false;
            } 

            if( !is_array( $specification ) ) {
                if( $specification == $_SESSION['usr']['name'] ) {
                    return true;
                }
            }

            if( is_array( $specification ) ){
                foreach( $specification as $specified_user ) {
                    if( $_SESSION['usr']['name'] == $specified_user ) {
                        return true;
                    }
                }

                return false;
            }
        }
    }
}

class MustBeLoggedInToWrite {

    private static $write_verbs  = array(
        "edit",
        "commit",
        "partition",
        "cherrypick",
        "move",
        "delete",
    );


    public function can( $verb, $thing ) {


        if( in_array( $verb, self::$write_verbs ) ) {
            if( is_logged_in() ) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}


function register_auth_call( $auth_obj, $auth_method ) {
    GLOBAL $registered_auth_calls;

    $registered_auth_calls[] = array( $auth_obj, $auth_method );

    return;
}


?>
