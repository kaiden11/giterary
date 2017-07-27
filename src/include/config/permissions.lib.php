<?
require_once( dirname( __FILE__ ) . '/../util.php' );
require_once( dirname( __FILE__ ) . '/../collection.php' );
require_once( dirname( __FILE__ ) . '/../git.php' );

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

class CollectionPermissions {

    private $rules_cache = array();
    private $glob_cache = array();
    private $tag_cache = array();

    function __construct( $path ) {

        if( is_null( $path ) ) {
            die( 'Invalid path passed to CollectionPermissions' );
        }

        if( !file_exists( $path ) ) {
            die( "CollectionPermissions file does not exist '$path'" );
        }

        $content = file_get_contents( $path );

        if( $content === false ) {
            die( "Unable to read from '$path' when initializing CollectionPermissions" );
        }

        $rules = $this->_parse( $content );


        if( $rules === false ) {
            die( 'Problem parsing CollectionPermissions rules' );
        }


        $this->rules_cache = $rules;

    }

    function _parse( &$content ) {

        $ret = array();

        foreach( preg_split( "/(\r)?\n/", $content ) as $line ) {

            // Remove any comments 
            $line = preg_replace( "/#.*$/", '', $line );

            // Trim
            $line = trim( $line );

            if( $line == "" ) {
                continue;
            }

            list( $file_spec, $tag_spec, $user_spec, $perm_spec ) = preg_split( "/\s*:\s*/", $line, 4 );

            $ret[] = array(
                'file_spec' =>  trim( $file_spec ),
                'tag_spec'  =>  trim( $tag_spec ),
                'user_spec' =>  trim( $user_spec ),
                'perm_spec' =>  trim( $perm_spec ),
            );
        }


        return $ret;
    }

    function can ( $verb, $thing ) {

        $u = trim( strtolower( $_SESSION['usr']['name'] ) );

        if( !$this->rules_cache || !is_array( $this->rules_cache ) || count( $this->rules_cache ) <= 0 ) {
            // No rules, then no permissions to enforce.
            return true;
        }

        foreach( $this->rules_cache as $rule ) {

            $file_matched   = false;
            $tag_matched    = false;
            $user_matched   = false;
            $perm_matched   = false;

            // Check user spec first

            if( $rule['user_spec'] == "*" ) {
                $user_matched = true;
            }

            if( !$user_matched ) {
                
                $users = preg_split( "/\s*,\s*/", $rule['user_spec'] );

                $users = array_map(
                    function( $a ) {
                        return trim( strtolower( $a ) );
                    },
                    $users
                );

                if( in_array( $u, $users ) ) {
                    $user_matched = true;
                }
            }

            if( $rule['user_spec'] == "?" ) {
                // Only match if user is not logged in (anonymous usser)
                $user_matched = !is_logged_in();
            }

            if( !$user_matched ) { continue; }

            if( $rule['file_spec'] == "*" ) {
                $file_matched = true;
            }

            if( !$file_matched ) {

                if( preg_match( '@' . $rule['file_spec'] . '@', $thing ) === 1 ) {
                    $file_matched = true;
                }
            }

            if( !$file_matched ) { continue; }

            if( $rule['tag_spec'] == "" || $rule['tag_spec'] == "*" ) {
                $tag_matched = true;
            }

            if( !$tag_matched ) {
                // $tag_matched = false;    // TODO: Implement tag permissions

                $tags_to_search = array();
                $file_collection = array();

                foreach( preg_split( '/,\s*/', $rule['tag_spec'] ) as $tag ) {
                    
                    $tag = preg_replace( '/^~/', '', $tag );

                    if( tag_or( $tag, false ) !== false ) {
                        $tags_to_search[] = $tag;
                    }
                }

                if( count( $tags_to_search ) > 0 ) {

                    $match_results = git_tags( 
                        $tags_to_search, 
                        null            //  No file / directory to search under
                    );

                    foreach ($match_results as $file => $result ) {
                        $file_collection[] = $file;
                    }
                }

                if( in_array( $thing, $file_collection ) ) {
                    $tag_matched = true;
                }
            }

            if( !$tag_matched ) { continue; }


            // echo 'checking perms';

            if( $rule['perm_spec'] == "*" ) {
                return true; // User has all permissions for thing
            }

            if( $rule['perm_spec'] == "" ) {
                return false; // Users without a specified verb / permission will not be given permission
            }

            $verbs = preg_split( "/\s*,\s*/", $rule['perm_spec'] );

            $verbs = array_map(
                function( $a ) {
                    return trim( strtolower( $a ) );
                },
                $verbs
            );

            // Matched everything up until this point. We return true
            // or false depending on whether we are given permissions, and 
            // do not fall through as with other matching failures.
            if( in_array( $verb, $verbs ) ) {
                return true;
            } else {
                return false;
            }
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
