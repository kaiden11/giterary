<?
require_once( dirname( __FILE__ ) . '/include/header.php');
require_once( dirname( __FILE__ ) . '/include/util.php');
require_once( dirname( __FILE__ ) . '/include/git.php');
require_once( dirname( __FILE__ ) . '/include/drafts.php');

function get_route( $uri ) {

    $ret = false;

    $annotatorjs_uri_prefix = '/' . ANNOTATORJS_PREFIX;

    if( strpos( $uri, $annotatorjs_uri_prefix ) === 0 ) {
        
        $ret = substr( $uri, strlen( $annotatorjs_uri_prefix ) );

        if( ( $i = strpos( $ret , "?" ) ) !== false ) {
            $ret = substr( $ret, 0, $i );
        }

        if( substr_count( $ret, "/" ) > 1 ) {
            
            $ret = substr( $ret, 0, strpos( $ret, "/", 1 ) );
        }
    }


    return $ret;
}

header( "Content-type: application/json" );

if( !is_logged_in() ) {
    http_response_code( 403 );
    echo json_encode( 
        array(
            "rows"  =>  array()
        )
    );
} else {

    $request_uri    =   $_SERVER['REQUEST_URI'];
    $request_method =   $_SERVER['REQUEST_METHOD'];

    $route = get_route( $request_uri );

    $action = "$request_method:$route";

    $ret = array();

    switch( $action ) {
        case "GET:/":               # API Versioning
            $ret['name']    = "Giterary Annotator Endpoint";
            $ret['version'] = "0.0.1";

            break;
        case "GET:/annotations":    # Get all annotations
            $ret['rows'] = array();

            break;
        case "DELETE:/annotations":
            // http_response_code( 204 );
            # $ret['debug'] = 'here';
            $json_payload = file_get_contents('php://input');
            $json_obj = json_decode( $json_payload, true );

            if( git_file_exists( $json_obj['uri'] ) ) {

                maintain_status( 
                    array(
                        'user'          =>  $_SESSION['usr']['name'],
                        'page_title'    =>  "Annotating " . $json_obj['uri'],
                        'path'          =>  undirify( $json_obj['uri'] )
                    )
                );

                $anno_content = '';

                $draft_exists = draft_exists( 
                    $_SESSION['usr']['name'], 
                    $json_obj['uri'] . "/" . ANNOTATORJS_FILE
                );

                if( $draft_exists !== false && count( $draft_exists ) == 1 ) {
                    
                    $draft_detail = _draft_get_path( $draft_exists[ 0 ] );

                    $anno_content = $draft_detail['contents'];
                } elseif( count( $draft_exists ) > 1 ) {

                    // Read in all drafts, and choose the one with the latest timestamp
                    $latest_draft  = find_latest_draft( $draft_exists );
            
                    $anno_content = json_decode(
                        $latest_draft['contents'],
                        true
                    );

                } else {
                    $anno_content = git_file_get_contents( $json_obj['uri'] . "/" . ANNOTATORJS_FILE );
                }

                $anno_obj = json_decode( $anno_content, true );

                $new_anno_obj = array();
                foreach( $anno_obj as $ao ) {
                    if( $ao['id'] !== $json_obj['id'] ) {
                        $new_anno_obj[] = $ao;
                    }
                }

                draft_update( 
                    undirify( $json_obj['uri'] . "/" . ANNOTATORJS_FILE ),
                    null,
                    json_encode(
                        $new_anno_obj,
                        JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK 
                    ),
                    "Annotations"
                );
            }

            exit;

            break;
        case "POST:/annotations":   # Create
        case "PUT:/annotations":    # Update (it seems to pass along all values, so why not?)

            $json_payload = file_get_contents('php://input');
            $json_obj = json_decode( $json_payload, true );

            $time = time();

            maintain_status( 
                array(
                    'user'          =>  $_SESSION['usr']['name'],
                    'page_title'    =>  "Annotating " . $json_obj['uri'],
                    'path'          =>  undirify( $json_obj['uri'] )
                )
            );

            if( $request_method == "POST" ) {
                # Only generate an ID during creation.
                $id = md5( $json_payload );
                $uri = $json_obj['uri'];
                $json_obj['id'] = "$uri:$id";

                # Only update create date during creation.
                $json_obj['created']    = date( "c", $time );
            }

            $json_obj['updated']    = date( "c", $time );
            $json_obj['user']       = $_SESSION['usr']['name'];

            # Should be a list of annotation objects
            $draft_contents = array();

            $draft_exists = draft_exists( 
                $_SESSION['usr']['name'], 
                $json_obj['uri'] . "/" . ANNOTATORJS_FILE
            );

            if( $draft_exists === false ) {
                if( git_file_exists( $json_obj['uri'] . '/' . ANNOTATORJS_FILE ) ) {
                    
                    $draft_contents = json_decode( 
                        git_file_get_contents( 
                            $json_obj['uri'] . '/' . ANNOTATORJS_FILE 
                        ),
                        true
                    );

                }
            } elseif( count( $draft_exists ) > 1 ) {
            
                // Read in all drafts, and choose the one with the latest timestamp
                $latest_draft  = find_latest_draft( $draft_exists );
            
                $draft_contents = json_decode(
                    $latest_draft['contents'],
                    true
                );

            } else {

                $draft_details = _draft_get_path( $draft_exists[0] );

                $draft_contents = json_decode( 
                    $draft_details['contents'] ,
                    true
                );


            }

            $is_found = false;
            foreach( $draft_contents as &$annotation ) {
               
                if( !isset( $annotation['id'] ) ) {
                    continue;
                }

                list( $orig_file, $draft_id     ) = explode( ":", $annotation['id'] );
                list( $orig_file, $current_id   ) = explode( ":", $json_obj['id']   );

                # If found, update the draft with the latest contents
                if( $draft_id == $current_id ) {
                    $is_found = true;
                    $annotation = $json_obj;
                    break;
                }
            }

            if( !$is_found ) {
                $draft_contents[] = $json_obj;
            }
    
            $draft_written = draft_update( 
                undirify( $json_obj['uri'] . "/" . ANNOTATORJS_FILE ),
                null,
                json_encode(
                    $draft_contents,
                    JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK 
                ),
                "Annotations"
            );

            # Communicate which draft was used to write this annotation
            $json_obj['source'] = 'draft';
            $json_obj['draft']  = basename( $draft_written );

            $ret = $json_obj;

            break;

        case "GET:/search":
            # $ret['querystring'] = $_SERVER['QUERY_STRING'];
            $limit  = set_or( $_GET['limit'], 1000 );
            $uri    = file_or( $_GET['uri'], false );

            # $ret['limit'] = $limit;
            # $ret['uri'] = $uri;

            $ret['total'] = 0;
            $ret['rows'] = array();

            if( $uri !== false ) {
            
                if( git_file_exists( $uri ) ) {

                    $anno_file = dirify( $uri . "/" . ANNOTATORJS_FILE );

                    if( git_file_exists( $anno_file ) ) {

                        $anno_content = git_file_get_contents( $anno_file );

                        $rows = json_decode( $anno_content, true );

                        $ret['rows']    = $rows;
                        foreach( $ret['rows'] as &$row ) {
                            $row['source'] = 'file';
                        }

                        $ret['total']   = count( $rows );

                    }

                    # But if we have a draft, it overrides.
                    if( ( $exists = draft_exists( $_SESSION['usr']['name'], $anno_file ) ) ) {

                        $draft_details = _draft_get_path( $exists[ 0 ] );

                        $rows = json_decode( $draft_details['contents'] , true );

                        $ret['rows']    = $rows;
                        foreach( $ret['rows'] as &$row ) {
                            $row['source']  = 'draft';
                            $row['draft']   = basename( $exists[ 0 ] );
                        }

                        $ret['total']   = $ret['total'] + count( $rows );
                    }
                }
            }

            break;

        default:
            $ret['badroute'] = "Unable to find $action in routing table";
    }

    /*
    $ret['debug'] = array(
        'route'         =>  $route,
        'action'        =>  $action,
    );
    */

    echo json_encode( 
        $ret
    );

}




?>
