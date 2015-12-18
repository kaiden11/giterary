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

class LDAPAuthenticator {

    private $url            = null;
    private $connection     = null;
    private $user_prefix    = null;
    private $username_attr  = null;
    private $base_dn        = null;
    private $required_group = null;

    function __construct( $ldap_url, $opts = array() ) {
        $this->url = $ldap_url;

        if( $opts[ 'domain' ] ) {
            $this->user_prefix = $opts[ 'domain'] . '\\';
        }

        if( $opts[ 'username_attr' ] ) {
            $this->username_attr = $opts[ 'username_attr'];
        }

        if( $opts[ 'base_dn' ] ) {
            $this->base_dn = $opts[ 'base_dn'];
        }

        if( $opts[ 'required_group' ] ) {
            $this->required_group = $opts[ 'required_group'];
        }

    }


    function validate_login($uname, $pass) {

        if( !isset( $this->connection ) ) {
            if( !$this->url ) {
                die( "URL for LDAP connection is unset!" );
            }

            $this->connect = ldap_connect( $this->url );

            if( !$this->connect ) {
                return false;
            }
        }

        $uname = trim( $uname );

        if( !preg_match( '/^[a-zA-Z]+$/', $uname ) ) {
            return false;
        }

        $bind_uname = $uname;

        if( $this->user_prefix ) {
            $bind_uname = $this->user_prefix . $uname;
        }

        $ret = ldap_bind( 
            $this->connect, 
            $bind_uname, 
            $pass 
        );

        if( !$ret ) {
            return false;
        }

        $default = array(
            'name'      =>  $uname,
            'git_user'  =>  array( 
                "user.name"     => $uname,
                "user.email"    => "$uname@giterary.com"
            )
        );


        if( !( $this->base_dn && $this->username_attr ) ) {
            return $default;
        }

        $filter = "($this->username_attr=$uname)";

        $search = ldap_search( 
            $this->connect,
            $this->base_dn,
            $filter,
            array(
                'cn',
                'mail',
                'memberOf'
            )
        );

        $first  = ldap_first_entry( $this->connect, $search );

        if( !$first  ) {
            return false;
        }

        $attributes = ldap_get_attributes( $this->connect, $first );

        if( isset( $this->required_group ) ) {

            if( !$attributes ) {
                return false;
            }

            if( !isset( $attributes['memberOf'] ) ) {
                return false;
            }

            if( !in_array( $this->required_group, $attributes['memberOf'] ) ) {
                return false;
            }

            if( !$attributes['cn'] || !$attributes['mail'] ) {
                return $default;
            }

            $ret = array(
                'name'      =>  $uname,
                'git_user'  =>  array( 
                    "user.name"     => $attributes['cn'][0],
                    "user.email"    => $attributes['mail'][0],
                    "user.group"    => $this->required_group
                )
            );

        } else {
            if( !$attributes ) {
                return $default;
            }

            if( !$attributes['cn'] || !$attributes['mail'] ) {
                return $default;
            }

            $ret = array(
                'name'      =>  $uname,
                'git_user'  =>  array( 
                    "user.name"     => $attributes['cn'][0],
                    "user.email"    => $attributes['mail'][0]
                )
            );

        }


        ldap_close( $this->connect );
        $this->connect = null;

        return $ret;

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
