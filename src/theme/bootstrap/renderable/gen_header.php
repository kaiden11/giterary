<? renderable( $p ) ?>
<?

# $header_string .= '
#     <div class="container-fluid" >';

$header_string .= '
        <div class="defaults navbar-header" >
            <button 
                type="button" 
                class="navbar-toggle" 
                data-toggle="collapse" 
                data-target="#collapse-gen-nav"
            >
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a 
                tabindex="10"
                class="default-file navbar-brand" href="index.php"
                title="' . DEFAULT_FILE . '"
            >
                <span class="glyphicon glyphicon-home"></span>
                <span class="sr-only">' . DEFAULT_FILE . '</span>
            </a>
';

# $header_string .= '
#         <div class="login">';

if (!is_logged_in()) { 
    $path = file_or( $p['path'], false );
    $redirect_file = ( $path !== false && $path !== DEFAULT_FILE ? $path : '' );

    $header_string .= '
            <form method="post" action="login.php" class="navbar-form login">
                <div class="form-group">
                    <input tabindex="1" class="form-control" name="uname" type="text" id="uname" maxlength="15" size="16" />
                    <input tabindex="2" class="form-control" name="pass" type="password" id="pass" maxlength="30" size="16" />
                </div>
                <input type="hidden" name="redirect_to" value="' . $redirect_file . '" />
                <button 
                    tabindex="3"
                    class="btn btn-default navbar-btn" 
                    type="submit" 
                    name="Submit" 
                >
                    Log In
                </button>
            </form> 
            <script type="text/javascript">
                $(document).ready(
                    function() {
                        $("#uname").text_suggest(   "username..." );
                        $("#pass").text_suggest(    "password..." );
                    }
                );
            </script>
            ';

} else {

    if( $p['status_db'] && is_array( $p['status_db'] ) && count( $p['status_db'] ) > 0 ) {

        $online = '';
        $online_count = 0;
        $online_statuses = array();
        $fifteen_minutes_ago = 60*15;
        $now = time();
        foreach( $p['status_db'] as $username => &$status ) {
            if( $_SESSION['usr']['name'] == $username ) {
                # We've only found ourselves, don't care.
                continue;
            }

            if( $now - $status['time'] > $fifteen_minutes_ago ) {
                # Skip if more than a certain time period has elapsed
                continue;
            }

            $online_statuses[] = array( $username, ":", $status['page_title'], ' @', short_time_diff( $status['time'], $now, " ago" ) );
        }
    }

    $header_string .= '
                <ul class="nav navbar-nav">
                    <li class="dropdown btn-group">
                        <button
                            title="' . implode( ",", array_map( function( $a ) { return implode( "", $a ); }, $online_statuses ) ) . '"
                            class="btn ' . ( count( $online_statuses ) > 0 ? 'btn-info' : 'btn-default' ) . ' navbar-btn clickable"
                            value="online.php"
                        >
                            <span class="glyphicon glyphicon-user" title="' . $_SESSION['usr']['name'] . '"></span>
                            <span class="sr-only">' . $_SESSION['usr']['name'] . '</span>
                        </button>
                        <button
                            class="btn btn-default navbar-btn dropdown-toggle"
                            data-toggle="dropdown"
                        >
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a
                                    href="online.php"
                                    title="View users online"
                                >
                                    <span class="glyphicon glyphicon-user"></span>
                                    Users online
                                </a>
                            <li>
                                <a
                                    href="index.php?file=' . $_SESSION['usr']['name'] . '"
                                >
                                    <span class="glyphicon glyphicon-heart"></span>
                                    My user page
                                </a>
                            </li>
                            <li>

                                <a title="View your uncommited drafts" href="drafts.php">
                                    <span class="glyphicon glyphicon-file"></span>
                                    My ' . plural( $p['draft_count'], 'draft' ) . '
                                </a>
                            </li>
                            <li>
                                <a
                                    href="history.php?author=' . urlencode( 
                                        author_or( 
                                            $_SESSION['usr']['git_user'], 
                                            "bad author <bad_author_format@badauthor.com>" 
                                        ) 
                                    ) . '">
                                    <span class="glyphicon glyphicon-th-list"></span>
                                    My Commits
                                </a>
                            </li>
                            <li>
                                <a href="snippets.php">
                                    <span class="glyphicon glyphicon-saved"></span>
                                    ' . plural( $p['snippet_count'], 'snippet' ) . '
                                </a>
                            </li>

                            <li>
                                <a href="scratch.php">
                                    <span class="glyphicon glyphicon-asterisk"></span>
                                    Scratch Area
                                </a>
                            </li>
                            <li role="presentation" class="divider"></li>
                            <li>
                                <a href="logout.php">
                                    <span class="glyphicon glyphicon-log-out"></span>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>';
}

$header_string .= '
        </div>
        '; // navbar-header

# $header_string .= '
#     </div>'; // login

# $header_string .= '
#     </div>'; // container

echo $header_string;

?>
<!-- <? /* print_r( $p['status_db'] ) */ ?> -->

