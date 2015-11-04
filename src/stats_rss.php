<?
require_once( dirname( _FILE_ ) . "/include/config.php");
require_once( dirname( _FILE_ ) . "/include/git.php");
require_once( dirname( _FILE_ ) . "/include/stats.php");

$file       = file_or(    $_GET['file'], false );
$stat       = set_or(     $_GET['stat'], 'total_words' );
$obscure    = set_or( $_GET['obscure'], false );

if( $obscure == "true" ) {
    $obscure = true;
} else {
    $obscure = false;
}

$xml = "";

header( 'Content-Type: text/xml' );

$site_name = SITE_NAME;

$http_s = $_SERVER['HTTPS'] ? 'https' : 'http';

$base_url = "$http_s://$_SERVER[HTTP_HOST]/";

if( $obscure === true ) {
    $base_url = "$http_s://" . md5( $base_url ) . '.com/';
    $site_name = md5( $site_name );
}

// create the xml processing instruction
$xml .=  '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" >
    <channel>
        <title>' . $site_name . ' rss stats feed</title>
        <link>' . $base_url . '</link>
        <description>giterary stats rss</description>
        <atom:link href="' . "$http_s://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . '" rel="self" type="application/rss+xml"/>
        ';

if( git_file_exists( $file ) && !is_dirifile( $file ) ) {

    sudo(); // Access to files to create stats
    $file = dirify( $file );
    $file_head_commit = git_file_head_commit( $file );
    $head_commit = git_head_commit( );
    $show = git_show( $head_commit );
    $stats = _document_stats( $file, $head_commit );
    unsudo(); // relinquish privileges

    if( !isset( $stats[ $stat ] ) ) {
        $stat = 'total_words';
    }

    if( $obscure === true ) {
        $file = basename( $file );
    }

    $xml .= '
        <item>
            <guid>' 
                . $base_url . 'view.php?file=' . urlencode( $file ) . '&amp;commit=' . $file_head_commit . '#' . to_si( $stats[ $stat ] ) .
            '</guid>
            <title>'
                . undirify( $file ) . ' - ' . $stat . ': ' . to_si( $stats[ $stat ] ) .
            '</title>
            <link>'
                . $base_url . 'stats.php?file=' . urlencode( $file ) . "&amp;$stat=" . md5( to_si( $stats[ $stat ] ) ) .
            '</link>
            <description>'
                . $stat . ': ' . to_si( $stats[ $stat ] ) .
            '</description>
            <content:encoded><![CDATA[
                <pre><code>' 
                    . 
                    undirify( $file ) . ' - ' . $stat . ': ' . to_si( $stats[ $stat ] )
                    . '</code></pre>]]>
            </content:encoded>
            <author>' 
                . $show['author_email'] . ' (' . $show['author_name'] . ')' . 
            '</author>
            <category>Stats Update</category>
            <pubDate>' 
                .  strftime( "%a, %d %b %Y %H:%M:%S %z", $show['author_date_epoch'] ) .
            '</pubDate>
        </item>
    ';
}

$xml .=  '
    </channel>
</rss>';
 
echo $xml;
?> 
