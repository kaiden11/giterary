<?
require_once( dirname( __FILE__ ) . "/include/config.php");
require_once( dirname( __FILE__ ) . "/include/git.php");

$ret = git_history( 10, null, null );

$xml = "";

$only_show_latest = true;

header( 'Content-Type: text/xml' );

// create the xml processing instruction
$xml .=  '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>' . SITE_NAME . ' rss feed</title>
        <link>' . BASE_URL . '</link>
        <description>giterary: syndicated</description>
        ';

if($ret != 0) {

    $seen_file_collections = array();

    foreach($ret as $commit ) {

        $show = git_show( $commit['commit'] );

        if( ASSOC_ENABLE ) {
            $all_files_are_associative = true;

            foreach( $show['file_list'] as $f ) { 
                if( !has_directory_prefix( ASSOC_DIR, $f ) ) {
                    $all_files_are_associative = false;
                    break;
                }
            }

            if( $all_files_are_associative ) {
                # If all files in this commit are part of the associations directory tree,
                # do not display them in the RSS feed.
                continue;
            }
        }

        if( ALIAS_ENABLE ) {
            $all_files_are_aliases = true;

            foreach( $show['file_list'] as $f ) { 
                if( !has_directory_prefix( ALIAS_DIR, $f ) ) {
                    $all_files_are_aliases = false;
                    break;
                }
            }

            if( $all_files_are_aliases ) {
                # If all files in this commit are part of the alias directory tree,
                # do not display them in the RSS feed.
                continue;
            }
        }


        if( $only_show_latest ) {
            // Only show the latest update for each file on a given day

            //Sun, 7 Apr 2013 14:03:51 -0400
            //Sun, 7 Apr 2013
            $date_str = implode( 
                "_", 
                array_splice(
                    explode( 
                        " ", 
                        $show['author_date'] 
                    ),
                    0,
                    4
                )
            );

            # Unique on file listing, date (truncated hour/minutes), and author name
            $file_collection = implode( ",", $show['file_list'] ) . $date_str . $show['author_name'];



            if( isset( $seen_file_collections[ $file_collection ] ) ) {
                // Skip if we've seen this file before.
                continue;
            } 

            $seen_file_collections[ $file_collection ] = 1;
        }

        $title = '';
        $tmp = array();
        foreach( $show['file_list'] as &$file ) {
              $tmp[] = undirify( $file );
        }

        $notes = array();
        if( INCLUDE_WORD_COUNT ) {
            $notes[ WORD_COUNT_NOTES_REF ] = git_notes( $commit['commit'], WORD_COUNT_NOTES_REF );
        }

        if( INCLUDE_WORK_TIME ) {
            $notes[ WORKING_TIME_NOTES_REF ] = git_notes( $commit['commit'], WORKING_TIME_NOTES_REF );
        }

        if( COMMIT_RESPONSE_REF ) {
            $notes[ COMMIT_RESPONSE_REF ] = git_notes( $commit['commit'], COMMIT_RESPONSE_REF );
        }

        $xml .= '
        <item>
            <guid>' . BASE_URL . '/show_commit.php?commit=' . $commit['commit'] . '</guid>
            <title>' . join(',', $tmp ) . '</title>
            <link>' . BASE_URL . '/show_commit.php?commit=' . $commit['commit'] . '</link>
            <description>' . join(',', $tmp ) . '</description>
            <content:encoded><![CDATA[
                <pre><code>' . 
                    $show['body']
                    . ( 
                        INCLUDE_WORD_COUNT && $notes[ WORD_COUNT_NOTES_REF ]  
                            ? "\n" . "Word Count: "   . $notes[ WORD_COUNT_NOTES_REF ]
                            : "" 
                    )
                    . ( 
                        INCLUDE_WORK_TIME && $notes[ WORKING_TIME_NOTES_REF ]
                            ? "\n" . "Work Time: "    . $notes[ WORKING_TIME_NOTES_REF ]
                            : "" 
                    )
                    . ( 
                        COMMIT_RESPONSE_REF && $notes[ COMMIT_RESPONSE_REF ]
                            ? "\n" . "Responses: \n\n". $notes[ COMMIT_RESPONSE_REF ]
                            : "" 
                    )
             . '</code>
                </pre>]]>
            </content:encoded>
            <author>' .      $commit['author'] . '</author>
            <category>New Change</category>
            <pubDate>' .  strftime( "%a, %d %b %Y %H:%M:%S %z", $commit['epoch'] ) . '</pubDate>
        </item>
            ';
    }
}

$xml .=  '
    </channel>
</rss>';
 
echo $xml;
?> 
