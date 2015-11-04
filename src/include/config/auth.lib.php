<?
# require_once( dirname( __FILE__ ) . '/../util.php' );

function password_hash_compare( $pass_plaintext, $pass_hashed ) {
    
    $hash_components = explode( "$", $pass_hashed, 2 );

    $hash = null;
    $method = null;
    
    if( count( $hash_components ) == 1 ) {
        $hash   = $hash_components[0];
        $method = 'md5'; // Default hashing method

    } else {
        
        $method     = $hash_components[0];
        $hash       = $hash_components[1];
    }

    if( !in_array( $method, hash_algos() ) ) {
        return false;
    } else {
        return hash( $method, $pass_plaintext ) == $hash;
    }
}


class PasswordFile {

    private $file = null;

    function __construct( $password_file ) {
        $this->file = $password_file;
    }

    function userlist() {

        $ret = false;
        if( !file_exists( $this->file ) ) {
            die( "The PasswordFile configuration for " . $this->file . " will always return an empty userlist because the password file does not exist." );
        } else {
            $contents = file_get_contents( $this->file );

            if( strlen( $contents ) <= 0 ) {
                return false;
            } else {
                $ret  = array();
                foreach( preg_split( '/\r?\n/', $contents ) as $line ) {
                    if( $line != "" && strpos( $line, "#" ) !== 0  ) {
                        
                        list( $username, $git_user_name, $git_user_email, $password_hash ) = str_getcsv( $line );

                        $username = trim( $username );

                        $ret[] = $username;
                    }
                }
            }

            return $ret;
        }
    }

    function validate_login($uname, $pass) {

        if( !file_exists( $this->file ) ) {
            die( "The PasswordFile configuration for " . $this->file . " will always fail because the file does not exist." );

        } else {
            $contents = file_get_contents( $this->file );

            if( strlen( $contents ) <= 0 ) {
                return false;
            } else {
                foreach( preg_split( '/\r?\n/', $contents ) as $line ) {
                    if( $line != "" && strpos( $line, "#" ) !== 0  ) {
                        
                        list( $username, $git_user_name, $git_user_email, $password_hash ) = str_getcsv( $line );

                        if( trim( $username ) == $uname ) {
                            
                            if( password_hash_compare( $pass, $password_hash ) ) {
                                return array(
                                    'name'      =>  $username,
                                    'git_user'  =>  array( 
                                        "user.name"     => trim( $git_user_name ),
                                        "user.email"    => trim( $git_user_email )
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

}

class StaticUserList {

    public static $users = array(
        "somebody" => array(
            'git_user'  =>  array( 
                "user.name"     => "Somebody",
                "user.email"    => "somebody@somewhere.com",
            ),
            'password'  =>  'sha512$ce5d28dfa5dfd7b4da715d1f0f3e273c59276143b008b4e808dded81cf4585af620064b5c7137b1e9c3c21b676bc475262c76dad14e7e0057c21893474d4eca8'
        ),
        /*
            Other users...
        */
    );

    function validate_login($uname, $pass) {

        if( self::$users && 
            isset( self::$users[$uname] ) && 
            isset( self::$users[$uname]['password'] ) && 
            password_hash_compare( $pass, self::$users[$uname]['password'] )
        )  {
            return array_merge( self::$users[$uname], array( 'name'   =>  $uname ) );
        }
        
        return false;
    }
}



function register_login_call( $login_obj, $login_method ) {
    GLOBAL $registered_login_calls;

    $registered_login_calls[] = array( $login_obj, $login_method );

    return;
}

function register_userlist_call( $userlist_obj, $userlist_method ) {
    GLOBAL $registered_userlist_calls;

    $registered_userlist_calls[] = array( $userlist_obj, $userlist_method );

    return;
}


?>
