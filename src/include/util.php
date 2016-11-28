<?
require_once( dirname( __FILE__ ) . '/config.php');
# require_once( dirname( __FILE__ ) . '/util.php');


//Determine if a user is logged in based on current
//cookie variables
//function are_cookies_set() {
//    global $cookie_vars;
//
//    $j = true;
//    foreach($cookie_vars as $var) {
//        $j = isset($_COOKIE[$var]);
//        if(!$j) {
//            //echo "You're not logged in!";
//            break;
//        }
//    }
//    return $j ;
//}

// Must be called within the context
// where session_start has already been
// called
function is_logged_in() {
  return isset($_SESSION['usr']);
}

function set_or( $a, $b ) {
    return ( isset( $a ) ? $a : $b );
}

function numeric_or( $a, $b ) {
    return ( is_numeric( $a ) ? $a : $b );
}

//Takes a recordset and turns it into a drop-down box

function gen_drop_down($r, $name, $val, $show, $selected_if=NULL, $any_val = NULL, $any_show = NULL, $other_attr = NULL, $id=NULL) {

    $s = '<select ' . $other_attr . ' name="' . $name . '">';
    
    foreach($r as $row) {
        $s .= '<option value="' . $row[$val] . '"';
        
        if($row[$val] == $selected_if) {
                $s .= " selected";
        }
        
        $s .= '>&raquo;' . $row[$show] . '</option>';
    
    }
    
    if($any_val != NULL || $any_id != NULL) {
    
    	$s .= '<option value="' . $any_val . '"';
    
    	if($any_val == $selected_if) {
    		$s .= " selected";
    	}
    
            $s .= '>*' . $any_show . '</option>';
    }
    
    $s .= '</select>';
    
    return $s;	

}

function path_to_filename( $path ) {
    return preg_replace( '/[^a-zA-Z0-9.]/', '_', $path );
}


function note($title, $message, $class="note") {

    return render( 
        'note', 
        array(
            'title'     =>  $title,
            'message'   =>  $message,
            'class'     =>  $class
        )
    );
}

function rotate() {
    $arg_list = func_get_args();
    static $i = 0;
    return $arg_list[$i++ % count($arg_list)];
}

function rss_excerpt($msg,$length=50) {
    $words = explode(
        ' ', 
        $msg
    );

    $str = array_shift($words);

    $i = 0;
    while(count($words) > 0 && (strlen($str) + strlen($words[$i]) + 1) < $length) {
       $str .= ' ' . $words[$i];
       $i++;
    }

    $ret = substr(
        trim(
            str_replace( 
                array("\r", "\n", "\t"), 
                " ",
                $str
            )
        ),
        0,
        $length
    );

    if( preg_match( '/^\s*$/', $ret ) ) {
        return "[...]";
    } else {
        return $ret;
    }
}

# Grab +/- ($length/2) characters of context around
# a matched string. For search results.
function match_excerpt( $msg, $pattern, $length=50, $as_regex = false) {

    # Minimum 1 character's worth of context.
    $dist = ( $length > 1 ? floor( $length / 2 ) : 1 );


    # Show context, but only on the first match.
    if( !$as_regex ) {

        $pq = preg_quote( $pattern, '/'  );
        $msg = preg_replace( "/(" . $pq  . ")/i", '@@\1@@', $msg );

        $index = strpos( strtolower( $msg ), strtolower( $pattern ) );

        $pre_ellipsis = ( $index - $dist > 0 ? '...' : '' );
        $aft_ellipsis = ( $index - $dist + $length < strlen( $msg ) ? '...' : '' );

        $msg = $pre_ellipsis . substr(
            $msg,
            ( $index - $dist > 0 ? ($index-$dist) : 0 ),
            $length
        ) . $aft_ellipsis;
    } else {

        # Massage the git-grep pattern to a PHP-grep pattern
        # \(TODO\|TBD\)\(:\)\? -> (TODO|TBD)(:)?
        # This is likely to be brittle, but alas, why git-grep
        # differs terrible from other greps is frustrating to
        # begin with.
        # $pattern = preg_replace( '/\\\([()?]?)/', '$1', $pattern );
        # $pattern = preg_quote( 

        $matches = array();
        
        preg_match_all( 
            "@$pattern@", 
            $msg, 
            $matches, 
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        if( count( $matches ) > 0  && isset( $matches[0][0][1] ) ) {

            # $group = $matches[0][0][0];
            $index = $matches[0][0][1];

            $pre_ellipsis = ( $index - $dist > 0 ? '...' : '' );
            $aft_ellipsis = ( $index - $dist + $length < strlen( $msg ) ? '...' : '' );

            $msg = $pre_ellipsis . substr(
                $msg,
                ( $index - $dist > 0 ? ($index-$dist) : 0 ),
                $length
            ) . $aft_ellipsis;
        }
    }

    return $msg;
}



function excerpt($msg,$length=50) {
    $words = explode(' ', 
        preg_repclickable_urls('/<img.*>/','[img]',
            strip_tags($msg, '<img>')
        )
    );

    $str = array_shift($words);

    $i = 0;
    while(count($words) > 0 && (strlen($str) + strlen($words[0]) + 1) < $length) {
       $str .= ' ' . $words[0];
       array_shift( $words );
    }

    $ret = substr(
        trim(
            str_replace( 
                array("\r", "\n", "\t"), 
                " ",
                $str
            )
        ),
        0,
        $length
    );

    if( preg_match( '/^\s*$/', $ret ) ) {
        return "[...]";
    } else {
        return $ret;
    }
}

function linkURL($text) {

    perf_enter( "linkURL" );
    $matches = array();
    preg_match_all(
        "`(<a[^>]+=\")?(https?://[-A-Za-z0-9+&@#/%?=~_|!:,.;]*[-:A-Za-z0-9+&@#/%=~_|\)\(])(\">|</a>|\"\s*/>)?`", 
        $text, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    
    $offset = 0;
    foreach( $matches as $match ) {


        if(strlen( $match[1][0] ) > 0 || strlen( $match[3][0] ) > 0 ) {
            # We're dealing with the case where somebody already has an anchor tag
            # in place.

            #if( $_SESSION['usr']['id'] == 118 ) {
            #    echo "\n(" .   $match[0][0]  . ") ";
            #    echo "(" .     $match[1][0]  . ") ";
            #    echo "(" .     $match[2][0]  . ") ";
            #    echo "(" .     $match[3][0]  . ")\n";
            #}



            if( $match[3][0] == "</a>" ) {
                # Sample: <a href="http://www.idkfa.com">http://idkfa.com</a>"
                #                                        _______________ ____
                # We can't match on the </a> in the last submatch because 
                # we need to handle it from the case where the last submatch is
                # the end of the *beginning* of the <a href...> tag.

               #$host_domain = explode('.', parse_url( $match[2][0], PHP_URL_HOST ) );
               #$host = array_pop( $host_domain );
               #$host = array_pop( $host_domain ) . ".$host";

               #$replacement = '</a> (<a href="http://' . $host . '">' . $host . '</a>)';

               #$text = substr_replace( 
               #    $text, 
               #    $replacement, 
               #    $offset + $match[3][1],
               #    strlen( $match[3][0] )
               #);

               #$offset += strlen( $replacement ) - strlen( $match[3][0] );
            } elseif( $match[1][0] == '<a href="' ) {

                #$host_domain = explode('.', parse_url( $match[2][0], PHP_URL_HOST ) );
                $host = abbreviate_hostname( parse_url( $match[2][0], PHP_URL_HOST ) );
                #$host = array_pop( $host_domain );
                #$host = array_pop( $host_domain ) . ".$host";

                $end_tag = '</a>';
                $replacement = '</a>';
                

                if( !is_ok_host( $host ) ) { 
                    $replacement .= ' (<a href="http://' . $host . '">' . $host . '</a>)';
                }

                # Look forward to find the first occurrence of the 
                # ending anchor tag (</a>) after this match.
                $text = substr_replace(
                    $text,
                    $replacement,
                    strpos(                             # Position of the next instance of $end_tag (</a>)
                        $text,                          # that we find after the occurence of this match.
                        $end_tag,                       # So we can turn ="http://blah.com/AA">Blah!</a>
                        $offset + $match[0][1]          # into ="http://blah.com/AA">Blah!</a> (<a href="http://blah.com">blah.com</a>)
                    ),
                    strlen( $end_tag )
                );

                $offset += strlen( $replacement ) - strlen( $end_tag );
                
            } else {
               #if( $_SESSION['usr']['id'] == 118 ) {
               #    echo "\n[" .   $match[0][0]  . "] ";
               #    echo   "[" .   $match[1][0]  . "] ";
               #    echo   "[" .   $match[2][0]  . "] ";
               #    echo   "[" .   $match[3][0]  . "]\n";
               #}


            }

            continue;
        } else {
            # We're dealing with a plaintext URL in the middle of a post.
            #$host = parse_url( $match[0][0], PHP_URL_HOST );

           #if( $_SESSION['usr']['id'] == 118 ) {
           #    #echo "here";

           #}

            $host = abbreviate_hostname( parse_url( $match[0][0], PHP_URL_HOST ) );
            #$host_domain = explode('.', parse_url( $match[0][0], PHP_URL_HOST ) );
            #$host = array_pop( $host_domain );
            #$host = array_pop( $host_domain ) . ".$host";
            
            
            #$text = str_replace_once($match[0], "<a href=\"$match[0]\">$match[0]</a>", $text);
            #$replacement = '<a href="' . $match[0][0] . '">' . $match[0][0] . '</a> (<a href="http://' . $host . '">' . $host . '</a>)';
            
            $replacement = '<a href="' . $match[0][0] . '">' . str_collapse( $match[0][0], 40 ) . '</a>';
            
            if( !is_ok_host( $host ) ) { 
                $replacement .= ' (<a href="http://' . $host . '">' . $host . '</a>)';
            }

            $text = substr_replace($text, $replacement, $match[0][1] + $offset, strlen($match[0][0]));
            
            $offset += strlen($replacement) - strlen($match[0][0]);
        }
    }
    
    perf_exit( "linkURL" );
    return $text;
}

function is_ok_host( $host ) {
    GLOBAL $ok_hosts;

    foreach( $ok_hosts as $ok ) {
        if( substr( $host, (-1*strlen( $ok ) ) ) == $ok ) {
            return true;
        }
    }

    return false;
}

function abbreviate_hostname( $url_host ) {
    #$host_domain = explode('.', $url_host );

   #$stop_after_names = array( 
   #    'co',
   #    'com',
   #    'org',
   #    'net',
   #    'coop',
   #    'biz'

   #$host = '';

   #$tmp = array_pop( $host_domain );
   #while( !in_array( $tmp, $stop_after_names ) ) {
   #    if( $host == '' ) {
   #        
   #    } else {
   #        $host = $tmp . ".$host";
   #    }
   #    $tmp = array_pop( $host_domain );
   #}
   #$host = . ".$host";

    return $url_host;
}

// From: http://stackoverflow.com/questions/1188129/replace-urls-in-text-with-html-links
function clickable_urls( $message ) {

    $rexProtocol = '(https?://)';
    $rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
    $rexPort     = '(:[0-9]{1,5})?';
    $rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
    $rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';

    $ret =  preg_replace_callback(
        "&\\b$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$))&",
        function( $match ) {
        
            // Prepend http:// if no protocol specified
            $completeUrl = $match[1] ? $match[0] : "http://{$match[0]}";
    
            return '<a href="' . $completeUrl . '">'
                . he( $completeUrl )
                . '</a>'
            ;
        }, 
        htmlspecialchars( $message )
    );


    // only allow hyperlinks coming back from the commit message
    $ret = strip_tags( $ret, '<a>' );

    return $ret;
}


function to_si( $num ) {
    if( !is_numeric( $num ) ) {
        return $num;
    } else {
        # Mega
        if( $num > 1000000 ) {
            return sprintf( "%.1fM", ( $num / 1000000 ) ); 
        }    
        
        # Kilo
        if( $num > 1000 ) {
            return sprintf( "%.1fk", ( $num / 1000) ); 
        }    

        return $num;

    }
}

# emulating the ".." range operator from perl...
function tristate( $a, $b, &$state ) {

    if( is_null( $state ) || !is_array( $state ) || !isset( $state['a'] ) || !isset( $state['b'] ) ) {
        $state = array( 'a' => false, 'b' => false, 'done' => false );
    }

    if( $a ) {
        if( !$state['a'] ) {
            $state['a'] = true;
        }
    }

    if( $b ) {
        if( !$state['a'] ) {
            return $state['ret'];
        }

        if( !$state['b'] ) {
            $state['b'] = true;
        }
    }

    if( $state['a'] || $state['b'] ) {
        $state['ret'] = true;
    } else {
        $state['ret'] = false;
    }

    if( $state['a'] && $state['b'] ) {
        $state['a'] = $state['b'] = false;
        $state['done'] = true;
    }

    return $state['ret'];

}

function plural( $i, $word = '', $plural = 's' ) {
    return ( $i == 1 ? "$i $word" : "$i $word$plural" );
}

function medium_time_diff($first, $second =  null) {

    if( is_null( $second ) ) {
        $second = time();
    }

    $difference = abs($second - $first);
    if($difference > 31556926) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 31556926 ), 'year' );
    } 

    if($difference > 2629743) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 2629743 ), 'month' );
    } 

    if($difference > 604800) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 604800 ), 'week' );
    } 

    if($difference > 86400) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 86400 ), 'day' );
    } 

    if($difference > 3600) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 3600 ), 'hour' );
    } 

    if($difference > 60) {
        return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference / 60 ), 'minute' ) ;
    } 

    return ($difference >= 0 ? '+' : '' ) . plural( floor( $difference ), 'second' );


}

function from_short_time_diff( $str ) {

    $match = array();

    if(  preg_match( '/^([+-])([0-9]+)([A-Za-z]+)$/', $str, $match) !== false ) {
        $sign = $match[1];
        $num = $match[2]+0;
        $unit = $match[3];
       
        if( $sign == '-' ) {
            $num = -$num;
        }

        switch( $unit ) {
            case "min":
                $num = $num * 60;
                break;
            case "H":
                $num = $num * 3600;
                break;
            case "D":
                $num = $num * 86400;
                break;
            case "S":
            default:
                $num = $num;
                break;
        }

        return $num;

    }
    return 0;
}

function short_time_diff($first, $second) {
    $difference = abs($second - $first);
    if($difference > 31556926) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 31556926 ) . 'Y';
    } 

    if($difference > 2629743) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 2629743 ) . 'M';
    } 

    if($difference > 604800) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 604800 ) . 'W';
    } 

    if($difference > 86400) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 86400 ) . 'D';
    } 

    if($difference > 3600) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 3600 ) . 'H';
    } 

    if($difference > 60) {
        return ($difference >= 0 ? '+' : '' ) . floor($difference / 60 ) . 'min';
    } 

    return ($difference >= 0 ? '+' : '' ) . $difference . 'S';


}

function long_time_diff($first, $second) {

	$total = abs($second - $first);

	if($total < 0) {
		return null;
	}
	else {
		$seconds_till = $total % 60;


		$total /= (int) 60;
		$minutes_till = $total % 60;

		$total /= (int) 60;
		$hours_till = $total % 24;

		$total /= 24;
		$days_till =  number_format( $total, 1 );

		$ret = "";
	        if( $days_till > 7 ) {
                    $ret = ( (int) ($days_till / 7) ) . " weeks";

                } else {
		    if($days_till >= 2) { 
		    	$ret = $days_till . " days";
		    }
		    else {
		    	if($days_till >= 1 && $days_till < 2 ) {
		    		$ret = "<i>Only one day</i> and ";
		    	}
		    	else {
		    		$ret = "A <u>scant</u> ";
		    	}
		    	
		    	if($hours_till >= 1) {
		    		$ret .= $hours_till . " hours";
		    	}
		    	else {
		    		if($minutes_till > 30) {
		    			$ret .= $minutes_till . " minutes";
		    		}
		    		else {
		    			$ret .= $minutes_till . " minutes and " . $seconds_till . " seconds";
		    		}
		    	}
		    }
                }
		
		return $ret;
	}
}

function calculate_bounds( $ids ) {
    //$first = array_shift($ids);
    
    $lower = $i = $upper = array_shift( $ids );
    
    $bounds = array();
    
    foreach($ids as $id ) {
        if(($i+1) == $id ) {
            # Sequential...
            $upper = ++$i;
        } else {
            array_push($bounds, array( 
                'lower' => $lower, 
                'upper' => $upper 
                ) 
            );
    
            $i = $lower = $upper = $id;
        }
    }
    
    if(!is_null($upper) ) {
        array_push($bounds, array( 
            'lower' => $lower, 
            'upper' => $upper
            )
        );
    }

    return $bounds;

}


function str_collapse( $str, $length ) {

    $l = strlen( $str );
    if( $l <= $length ) {
        return $str;
    } else {
        $r = '...';
        $to_reduce = ( $l - $length ) + strlen( $r );

        $str = substr_replace( $str, $r, ( floor($l / 2 ) - ( floor( $to_reduce / 2 ) ) ), $to_reduce );

        return $str;
    }
}


$perf_last = array();
$perf_results = array();
$perf_memory_snapshots = array();
$perf_enters = 0;
$perf_exits = 0;

function perf_elapsed( $name = "total" ) {
    GLOBAL $perf_results;

    $elapsed = null;
    if( ! PERF_STATS ) {
        return '';
    }

    if( isset( $perf_results[$name] ) ) {
        $elapsed = $perf_results[$name][1];
    }

    return $elapsed;
}


function perf_log( $msg ) {
    GLOBAL $perf_last;
    GLOBAL $perf_results;

    # echo str_repeat( " ", 1+$perf_enters-$perf_exits ) . "$name)\n";

    if( ! PERF_STATS ) {
        return '';
    }

    $perf_results[$msg][1] = 0;
    $perf_results[$msg][0]++;
    
    unset( $perf_last[$msg] );

    return '';
}

function perf_enter( $name = null ) {
    GLOBAL $perf_last;
    GLOBAL $perf_results;
    GLOBAL $perf_enters;
    GLOBAL $perf_exits;

    $perf_enters++;

    if( is_null( $name ) ) {
        $name = get_caller_method( 2 );
    }

    # echo str_repeat( " ", $perf_enters-$perf_exits ) . "($name\n";

    if( ! PERF_STATS ) {
        return '';
    }

    if( ! isset( $perf_results[$name] ) ) {
        $perf_results[$name] = array( 0, 0 );
    }
    if( isset( $perf_last[$name] ) ) {
        unset( $perf_last[$name] );
    }

    list($microsec, $sec ) = explode( " ", microtime(FALSE) );
    $perf_last[$name] = array( $microsec, $sec );

   #list($start_microsec, $start_sec) = explode(" ", $start_microtime);
   #list($end_microsec, $end_sec) = explode(" ", $end_microtime);
   #$elapsed = ceil(1000*(((float)$end_sec - (float)$start_sec) + ((float)$end_microsec - (float)$start_microsec ))) . ' millisec';

   
    return '';
}


function perf_mem_snapshot( $tag = null ) {
    GLOBAL $perf_memory_snapshots;

    $usage = memory_get_usage();

    if( is_null( $tag ) ) {
        $tag = microtime();
    }

    $perf_memory_snapshots[ $tag ] = $usage;

    return '';
}

function perf_exit( $name = null ) {
    GLOBAL $perf_last;
    GLOBAL $perf_results;
    GLOBAL $perf_enters;
    GLOBAL $perf_exits;

    $perf_exits++;

    if( is_null( $name ) ) {
        $name = get_caller_method( 2 );
    }

    # echo str_repeat( " ", 1+$perf_enters-$perf_exits ) . "$name)\n";

    if( ! PERF_STATS ) {
        return '';
    }

    list( $end_microsec, $end_sec ) = explode( " ", microtime(FALSE) );

    # if( isset( $perf_results[$name] ) && isset( $perf_last[$name] ) ) {
    if( isset( $perf_results[$name] ) ) {
        $elapsed = (
            1000 * (
                ( 
                    (float)$end_microsec - (float)$perf_last[$name][0] 
                ) + 
                (
                    (float)$end_sec - (float)$perf_last[$name][1] 
                )
            )
        );

        $perf_results[$name][1] += $elapsed;
        $perf_results[$name][0]++;
    }
    unset( $perf_last[$name] );

    return '';
}

function perf_print() {
    GLOBAL $perf_results;
    GLOBAL $perf_enters;
    GLOBAL $perf_exits;
    GLOBAL $perf_memory_snapshots;
    $ret = '';

    if( ! PERF_STATS ) {
        return '(perf stats disabled)';
    } else {
        $ret .= "enters: $perf_enters, exits: $perf_exits\r\n";
        ksort( $perf_results );

        $keys = array_keys( $perf_results );
        $max_key_length = 0;
        foreach( $keys as $k ) {
            if( strlen( $k ) > $max_key_length ) {
                $max_key_length = strlen( $k );
            }
        }

        foreach( $perf_results as $key => $result ) {
            $ret .= "\r\n   "
                . sprintf( '%-' . $max_key_length . 's', $key )
                . '(' 
                . $result[0] 
                . ') = '
                . sprintf( '%0.2f', $result[1] )
                . "ms (" 
                . sprintf( "%.2f", ( 100 * ( $result[1] / $perf_results['total'][1] ) ) ) 
                . "%, " 
                . sprintf( "%.2f", ( ( $result[1] / $result[0] ) ) ) 
                . 'ms avg'
                . ")";
        }

        foreach( $perf_memory_snapshots as $tag => $memory ) {
            $ret .= "
                memory@" . sprintf( "%-30s", $tag ) . "(" . sprintf( "%10dB", $memory ) .  ")";
        }

        $ret .= "
        load average: (" . implode( ",", sys_getloadavg() ) . ")
        ";

        return $ret;
    }
}

function renderable( &$p ) {

    if( isset( $p ) && isset( $p['i_am_renderable'] ) ) {
        $p['i_am_renderable']++;   
    }
}

function layout( $contents = array(), $opts = array() ) {

    $renderer = set_or( $opts['renderer'], 'default_layout' );

    return render( 
        $renderer, 
        array(
            'contents'  => &$contents,
            'opts'      => &$opts 
        )
    );
}

function render( $path, $p = array() ) {
    GLOBAL $renderables;
    GLOBAL $themes;

    static $stash;

    if( !isset( $stash ) ) {
        $stash = array();
    }

    $stash = array_merge( $stash, $p );

    if( !is_array( $p ) ) {
        return 'Passed argument is not an array.';
    }


    if( !isset( $p['i_am_renderable'] ) ) {
        $p['i_am_renderable'] = 0;
    } 

    if( isset( $renderables[$path] ) ) {

        # We check to see if the user's theme somehow overrides
        # one of the default renderables, provided the theme
        # exists, the renderable name exists, and the matching
        # name in that theme also exists.
        if( is_logged_in() ) {
            if( 
                !isset( $_SESSION['usr']['theme'] ) || 
                $_SESSION['usr']['theme'] == DEFAULT_THEME || 
                !isset( $themes[$_SESSION['usr']['theme']] )  ||
                !isset( $themes[$_SESSION['usr']['theme']][$path] )
            ) {
                $path = $renderables[$path];
            } else {
                # Found a thematic override, so use that.
                $path = $themes[ $_SESSION['usr']['theme'] ][$path];
            }
        } else {
            $path = $renderables[$path];
        }
    }

    if( !file_exists( $path ) ) {


        die( "Unable to render $path, does not exist." );
    } else {



        try {
            ob_start();

            $tmp = $p['i_am_renderable'];

            {
                require( $path ); 
            }

            $ret = '';
            
            if(  ($tmp+1) == $p['i_am_renderable'] ) {
                $ret = ob_get_contents();
                $p['i_am_renderable']--;
            } else {
                $ret = "renderable() assertion not called in $path.";
            }

            ob_end_clean();


            return $ret;

        } catch( Exception $e ) {
            ob_end_clean();

            die( 'Unable to render: $e' );
        }

    }
}

$user_agent_searches = array(
       'sony',
       'symbian',
       'nokia',
       'samsung',
       'mobile',
       'windows ce',
       'epoc',
       'opera mini',
       'nitro',
       'j2me',
       'midp-',
       'cldc-',
       'netfront',
       'mot',
       'up.browser',
       'up.link',
       'audiovox',
       'blackberry',
       'ericsson,',
       'panasonic',
       'philips',
       'sanyo',
       'sharp',
       'sie-',
       'portalmmm',
       'blazer',
       'avantgo',
       'danger',
       'palm',
       'series60',
       'palmsource',
       'pocketpc',
       'smartphone',
       'rover',
       'ipaq',
       'au-mic,',
       'alcatel',
       'ericy',
       'up.link',
       'vodafone/',
       'wap1.',
       'wap2.'
);

function is_mobile() {
    GLOBAL $user_agent_searches;

    $ret = false;

    $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

    if( $op != '' ) {
        $ret = true;
    } else {
        foreach( $user_agent_searches as $search ) {
            if( strpos($ua, $search ) !== false  ) {
                $ret = true;
                break;
            }
        }
    }

    return $ret;
}

function _call( $cmd, $env = null ) {

    perf_enter( "_call" );
    $old_umask = umask(0022);

    $cwd = SRC_DIR;

    // If you can't play nice, don't try
    // do something bad.
    if( !is_array( $env ) ) {
        $env = null;
    }
    $pipes = array();

    # $fp = fopen("/home/idkfac/paste_temp/lock.txt", "w+" );

    # flock( $fp, LOCK_EX );

    # echo "\ncommand: $cmd\n";
    
    $process = proc_open(
        "$cmd 1>&2; echo $? >&3",
        array(
           0 => array("pipe", "r"),  // STDIN
           1 => array("pipe", "w"),  // STDOUT (ignored due to buffering issues)
           2 => array("pipe", "w"),  // STDERR (used because it disables buffering)
           3 => array("pipe", "w")   // Reporting return code
        ),
        $pipes, 
        $cwd, 
        $env
    );

    $output = '';
    $exit_output = '';
    $result = 99;

    if( is_resource( $process ) ) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // Any error output will be appended to /tmp/error-output.txt
        # stream_set_blocking($pipes[2], 0);

        # perf_enter( '_call, read output' );
        $output .=      stream_get_contents($pipes[2]);
        # perf_exit( '_call, read output' );

        # perf_enter( '_call, read exit' );
        $exit_output .= stream_get_contents($pipes[3]);
        # perf_exit(  '_call, read exit' );

        $status = proc_get_status( $process );

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        fclose($pipes[3]);

        $exit_code = proc_close($process);

        $result = $exit_output + 0;

    }

    # flock( $fp, LOCK_UN );

    umask( $old_umask );
    perf_exit( "_call" );

    return array( $result, $output );
    

}

function commit_or( $commit, $or = null ) {
    if( is_null( $commit ) || $commit == "" ) {
        return $or;
    } else {
        if( preg_match( '/^(([0-9a-fA-F]{40})|(HEAD))((\^{1,9})|(\^[1-2]{1})|(~{1,9})|({~[0-9]{1}))?$/', $commit ) == 1 ) {
            return $commit;
        } else {
            return $or;
        }
    }
}

function mode_or( $mode, $or = null ) {
    if( $mode == "edit" ) {
        return $mode;
    }

    return $or;
}

function interpolate_relative_path( $file, $current_file ) {

    $started_with_cwd_pattern = '@^(\./)+@';
    $started_with_up_pattern = '@^\.\./@';

    # echo "current_file: '$current_file'";

    if( preg_match( $started_with_up_pattern, $file ) ) {

        # Strip the leading "relative" indicator
        $file = preg_replace( $started_with_up_pattern, '', $file );

        if( preg_match( $started_with_up_pattern, $file ) ) {
            return interpolate_relative_path( $file, dirname( $current_file ) );
        }

        if( $file != "" && strpos( $current_file, '/' ) === false ) {

            # Do nothing, we're at the root
            # echo "current file: '$current_file'";
            # $file = interpolate_relative_path( "./$file", $current_file );

            # echo "there: '$file'";

            return "$current_file/$file";

        } else {

            # echo "dirname: " . dirname( $current_file );
            # echo "up file: $file";


            $file =  interpolate_relative_path( "./$file", dirname( $current_file ) );


            return $file;
        }
    }
    
    // file = ./path/to/something/
    if( preg_match( $started_with_cwd_pattern, $file ) ) {
        if( $current_file == null ) {
            # No current file was passed, so we can't
            # interpolate the current file's containing directory
            # into the path
            # echo "help";
            return false;
        }
    
        if( preg_match( $started_with_cwd_pattern, $current_file ) && file_or( $current_file, false ) === false ) {
            return false;
        }
    
        # Strip the leading "relative" indicator
        $file = preg_replace( $started_with_cwd_pattern, '', $file );

        # echo "remainder: '$file'";
    
        if( $file != "" && strpos( $current_file, '/' ) === false ) {
    
            if( $current_file == DEFAULT_FILE ) {
                # Do nothing, we're at the root
            } else {
    
                $file = implode( 
                    '/',
                    array_merge(
                        array(
                            $current_file
                        ),
                        explode(
                            '/',
                            $file
                        )
                    )
                );
            }
            
        } else {

            # print_r( explode( '/', $current_file ) );
            # print_r( explode( '/', $file ) );
            # print_r( 
            #     array_merge( 
            #         explode( '/', $current_file ),
            #         explode( '/', $file )
            #     )
            # );
            # 
            # print_r( 
            #     implode(
            #     '/',
            #     array_merge( 
            #         explode( '/', $current_file ),
            #         explode( '/', $file )
            #     )
            #     )
            # );

            if( $file == "" ) {
                $file = implode( 
                    '/',
                    explode(
                        '/',
                        $current_file
                    )
                );
            } else {
                # Rebuild the file by inserting the containing directory
                # of the current_file at the begining of the file
                $file = implode( 
                    '/',
                    array_merge(
                        explode(
                            '/',
                            $current_file
                        ),
                        explode(
                            '/',
                            $file
                        )
                    )
                );
            }
        }
    }
        
    return $file;
}

function valid_file_or( $file, $or = null, $current_file = null ) {
    GLOBAL $wikifile_pattern;

    if( is_null( $file ) || $file == "" ) {
        return $or;
    } else {
        if( is_array( $file ) ) {

            $i = 0;
            $ret = array();

            for( $i = 0; $i < count( $file ); $i++ ) {
                $t = file_or( $file[$i], $or );
                if( !is_null( $t ) ) {
                    $ret[] = $t;
                }
            }

            return $ret;
        } else {
            $_err_file = $file;
            $file = interpolate_relative_path( $file, $current_file );

            if( $file === false ) {
                echo "Path interpolation issue: $current_file / $_err_file";
                return $or;
            }

            $possible_path = stripslashes( $file );
            $possible_path = trim( $possible_path );
            $possible_path = ltrim( $possible_path, '/' );

            # First, try to remove the double-slashes
            $possible_path = str_replace( '//', '', $possible_path );

            $stripped_components = array();

            # Iterate through the path components, trying to strip out
            # bullshit, or otherwise bad things or mistakes as we
            # go along
            foreach( explode( '/', $possible_path ) as $component ) {

                if( $component == "." || $component == ".." ) {
                    return $or;
                } else {
                    # make sure we aren't dealing with pieces we
                    # shouldn't (hidden files, git files, etc.
                    if( $component == ".git" ) {

                        echo "2";
                        return $or;
                    } else {
                        $stripped_components[] = $component;
                    }
                }
            }

            # $git_repo = GIT_REPO_DIR; 
            # if( preg_match( '#\/$#', GIT_REPO_DIR ) == 0 ) {
            #     $git_repo .= "/";
            # }

            $stripped_relative_path = join('/', $stripped_components );
            # $stripped_path = $git_repo . $stripped_relative_path;

            if( preg_match( $wikifile_pattern, $stripped_relative_path ) != 0 ) {
                return $stripped_relative_path;
            } else {
                # echo "3:$stripped_relative_path";
                return $or;
            }
        }
    }
}

function file_exists_under( $dir, $file ) {
    $file = file_or( 
        $file, 
        '' 
    );

    if( !is_array( $file ) ) {
        $file = array( $file );
    }

    $ret = true;

    foreach( $file as $f ) {
        if( !file_exists( "$dir/$f" ) ) {
            $ret = false;
            break;
        }
    }

    return $ret;
}

function has_directory_prefix( $dir, $file ) {
    $ret = false;

    // Attempt a short-circuit of the checking / normalizing
    // in the event that the directory and file are already
    // in good shape for comparison
    if( strpos( $file, $dir ) === 0 ) {
        return true;
    }

    $dir = file_or( $dir, false );
    $file = file_or( $file, false );

    if( $dir === false || $file === false ) {
        return $ret;
    }

    $dir = dirify( $dir, true );
    $file = dirify( $file );

    if( strpos( $file, $dir ) === 0 ) {
        $ret = true;
    }

    return $ret;

}

function file_title( $title ) {
    $title = preg_replace( '/(\.([a-zA-Z0-9_-)$/', '', $title );

    return $title;

}

function undirify( $path, $remove_last = false ) {

    if( !is_array( $path ) ) {

        $file = basename( $path );

        $components = explode( "/", $path );
        array_pop( $components );
        
        $tmp = array();
        foreach( $components as $component ) {
           
            $tmp[] = preg_replace( '/\.' . DIRIFY_SUFFIX . '$/', '',  $component );

        }

        if( $remove_last ) {
            $file = preg_replace( '/\.' . DIRIFY_SUFFIX . '$/', '',  $file );
        }

        $tmp[] = $file;

        return join( "/", $tmp );

    } else {
        $ret = array();

        foreach( $path as $p ) {
            $ret[] = undirify( $p );
        }

        return $ret;
    }
}

function dirify( $path, $add_last = false ) {

   if( !is_array( $path ) ) {

        $file = basename( $path );
        $components = explode( "/", $path );
        array_pop( $components );
        
        $tmp = array();
        foreach( $components as $component ) {

            if( preg_match( '/\.' . DIRIFY_SUFFIX . '$/', $component ) == 0 ) {
                $tmp[] = "$component." . DIRIFY_SUFFIX;
            } else {
                $tmp[] = $component;
            }
        }
       
        # If flagged to add last, add the directory suffix, provided it isn't already
        # appended
        if( $add_last && preg_match( '/\.' . DIRIFY_SUFFIX . '$/', $file ) == 0 ) {
            $file .= "." . DIRIFY_SUFFIX;
        }

        $tmp[] = $file;

        return join( "/", $tmp );
   } else {
        $ret = array();

        foreach( $path as $p ) {
            $ret[] = dirify( $p );
        }

        return $ret;

    }
}

function is_dirifile( $path ) {
    
    return preg_match( "/\.dir$/", basename( $path ) ) > 0;
}

function gen_not_logged_in() {

    return render( 'not_logged_in',  array()  );
}

function get_caller_method( $n = 1, $full = false ) { 
    $traces = debug_backtrace(); 

    if( !isset( $n ) || $n < 0 ) {
        $n = 1;
    } 

    if (isset($traces[$n])) { 
        if( $full === true ) {
            return implode( ":", array( basename( $traces[$n]['file'] ), $traces[$n]['function'], $traces[$n]['line'] ) ); 
        } else {
            return $traces[$n]['function'];
        }
    }

    return null; 
}


# $locks = array();
# function fwait( $name ) {
#     GLOBAL $locks;
# 
#     if( $name == null || $name == "" ) {
#         die( "Unable to fwait on empty name" );
#     } else {
#         if( isset( $locks ) && is_resource( $locks[$name] ) ) {
#             flock( $locks[$name], LOCK_EX );
#         } else {
#             
#         }
#     }
# }
# 
# function frelease( $name ) {
# 
# 
# }

$sudo = false;
function sudo() {
    global $sudo;

    $sudo = true;
}

function unsudo() {
    global $sudo;

    $sudo = false;
}

function context_log( $context, $message ) {
    GLOBAL $registered_loggers;

    if( count( $registered_loggers ) == 0 ) {
        # Return true is no objects are registered
        # to be able to answer this question

        # echo "No registerd auth calls.";
        return;
    }


    foreach( $registered_loggers as $k => &$p ) {
        
        if( !is_object( $p[0] ) ) {
            echo "not an object!";
        } else {
            if( ! method_exists( $p[0], $p[1] ) ) {
                echo "method does not exist";
            } else {
                $p[0]->$p[1]( $context, $message );
            }
        }
    }

    return;
}


function can( $verb, $thing ) {
    GLOBAL $registered_auth_calls;
    GLOBAL $sudo;

    # echo "$verb, $thing";
    $verb   = strtolower( trim( $verb   ) );

    if( !is_array( $thing ) ) {
        $thing = array( $thing );
    }

    /* Why was this a good idea?
    foreach( $thing as $i => &$t ) {
        $t  = strtolower( trim( $t  ) );
    }
    */

    if( count( $registered_auth_calls ) == 0 ) {
        # Return true is no objects are registered
        # to be able to answer this question

        # echo "No registerd auth calls.";
        return true;
    }

    if( $sudo === true ) {
        return true;
    }

    foreach( $registered_auth_calls as $k => &$p ) {
        
        if( !is_object( $p[0] ) ) {
            echo "not an object!";
        } else {
            if( ! method_exists( $p[0], $p[1] ) ) {
                echo "method does not exist";
            } else {

                $is_denied = false;

                foreach( $thing as $i => $t ) {
                    if( !$p[0]->$p[1]( $verb, $t ) ) {
                        # echo "go ahead!";
                        return false;
                    }
                }

                return true;
            }
        }
    }

    return false;
}

function he( $text ) {
    return htmlentities( $text, ENT_COMPAT, ENCODING );
}

# Pure, numerical encoding. For when you really, *really* don't want
# your characters to be parsed later
function ne( $text ) {

    $text   =   mb_convert_encoding( $text , 'UTF-32', ENCODING );
    $t      =   unpack("N*", $text );
    $t      =   array_map(
                    function($n) {
                        return "&#$n;"; 
                    },
                    $t
                );

    return implode("", $t);
}

function hed( $text ) {
    return html_entity_decode( $text, ENT_COMPAT, ENCODING );
}


function gen_error( $error ) {
    $puck = array(
        'error'              =>  &$error,
    );

    return render( 'gen_error', $puck );

}

function author_or( $author, $or = false ) {

    GLOBAL $git_author_pattern;

    if( is_array( $author ) ) {
        if( isset( $author['user.name'] ) && isset( $author['user.email'] ) ) {
            $author = $author['user.name'] . " <" . $author['user.email'] . ">";
        } 
    }

    if( !preg_match( $git_author_pattern, $author ) ) {
        return $or;
    }
   
    # Return the string representation of the author
    return $author;
}

function tag_or( $tag, $or = false ) {

    GLOBAL $tag_name_pattern;

    if( !preg_match( $tag_name_pattern, $tag ) ) {
        return $or;
    }
   
    # Return the string representation of the author
    return $tag;
}

function to_hexavigesimal( $n ) {

    $ret = '';

    while( $n > 0 ) {
        $n--;
        $ret .= chr( ord( 'A' ) + ( $n % 26 ) );

        $n = floor( $n /26 );
    }

    return strrev( $ret );

}

function detect_extension( $file, $extension_override ) {

    # Do some switching, or try to, based on file extension
    $file_basename = basename( $file );
    
    $matches = array();
    $extension = "md";

    if( preg_match( '/\.([a-zA-Z0-9_-]+)$/', $file_basename, $matches ) == 1 ) {
       
        $extension = strtolower( $matches[1] );
    }

    # Attempt to limit extension overrides to only sane combinations
    # based on original extension
    if( in_array( $extension, array( 'list', 'collection' ) ) ) {
        
        if( !is_null( $extension_override ) && in_array( $extension_override, array( "list", "collection","collect","raw","clean","text","wrap","csv" ) ) ) {
            $extension = $extension_override;
        }
    } else {
        if( is_null( $extension_override ) ) {
            # Do nothing
        } else {

            if( 
                in_array(
                    $extension_override, 
                    array( 
                        "collection",   // Can't force something to be a collection if it isn't a list/collection already
                        "list",         // Ditto
                        "image"         // Can't force something to be an image
                    )
                )
            ) {
                # Do nothing
            } else {

                if( 
                    in_array(               // Don't attempt to display images as alternate extensions
                        $extension,         // unless they are "raw"
                        array(
                            "jpg",
                            "jpeg",
                            "gif",
                            "png"
                        )
                    )
                ) {
                    // Only allow override for images if the override is "raw"
                    if( $extension_override == "raw" ) {
                        $extension = $extension_override;
                    }

                } else {
                    $extension = $extension_override;
                }
            }
        }
    }

    # Translate any aliases that might exist
    switch ( $extension ) {
        case "css": 
            $extension = "css";
            break;
        case "js": 
            $extension = "js";
            break;
        case "jpg": 
        case "jpeg": 
        case "gif": 
        case "png": 
            $extension = "image";
            break;
        case "wav": 
        case "mp3": 
        case "ogg": 
            $extension = "audio";
            break;
        case "txt": 
        case "text": 
        case "tex": 
        case "latex": 
            $extension = "text";
            break;
        case "raw": 
            $extension = "raw";
            break;
        case "clean": 
            $extension = "clean";
            break;
        case "read": 
        case "readable": 
            $extension = "read";
            break;

        case "print": 
        case "printable": 
            $extension = "print";
            break;
        case "wrap": 
        case "wtxt": 
        case "wraptext": 
            $extension = "wraptext";
            break;
        case "md": 
        case "markdown": 
            $extension = "markdown";
            break;
        case "sb": 
        case "storyboard": 
            $extension = "storyboard";
            break;
        case "collect": 
        case "collection": 
            $extension = "collection";
            break;
        case "list": 
            $extension = "list";
            break;
        case "disc": 
        case "talk": 
            $extension = "talk";
            break;
        case "anno": 
        case "annotation": 
            $extension = "anno";
            break;
        case "csv": 
            $extension = "csv";
            break;
        case "pub":
        case "publish":
            $extension = "pub";
            break;
        case "pan":
        case "pandoc":
            $extension = "pan";
            break;
        case "xml":
        case "aeonxml":
            $extension = "xml";
            break;

        case "tl":
        case "textile":
        default: 
            $extension = "markdown";
            break;
    }

    return $extension;
}

function callback_replace( $pattern, $text, $callback ) {

    if( !$pattern ) { die( "Must submit pattern" ); }
    if( !$callback ) { die( "Must submit callback" ); }

    $matches = array();

    preg_match_all(
        $pattern,
        $text, 
        $matches, 
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE
    );

    $offset = 0;

    foreach( $matches as $match ) {
        $orig = $match[0][0];
        $orig_offset = $match[0][1];

        $replacement = $callback( $match );

        $text = substr_replace( 
            $text, 
            $replacement, 
            $offset + $orig_offset,
            strlen( $orig )
        );

        $offset += strlen($replacement) - strlen( $orig );
    }

    return $text;
}

# C'mon, PHP...
function proper_parse_str($str) {
    # result array
    $arr = array();
    
    # split on outer delimiter
    $pairs = explode('&', $str);
    
    # loop through each pair
    foreach ($pairs as $i) {
        # split into name and value
        list($name,$value) = explode('=', $i, 2);
        
        # if name already exists
        if( isset($arr[$name]) ) {
            # stick multiple values into an array
            if( is_array($arr[$name]) ) {
                $arr[$name][] = urldecode( $value );
            }
            else {
                $arr[$name] = array($arr[$name], urldecode( $value ) );
            }
        }
        # otherwise, simply stick it in a scalar
        else {
          $arr[$name] = urldecode( $value );
        }
    }
    
    # return result array
    return $arr;
}

function giterary_word_count( $str ) {
    
    $pattern = '/\b([\w\']+)\b/m';

    return preg_match_all( $pattern, $str );
}


?>
