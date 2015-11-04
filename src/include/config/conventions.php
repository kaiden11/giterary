<?

# Unique suffix to apply to underlying filesystem
# directories when establishing hierarchical pages.
define( 'DIRIFY_SUFFIX', "dir" );

# The character encoding scheme for the site
define( 'ENCODING', "UTF-8" );


# Regular expression patterns defining what characters can
# be used for naming within the wiki
$wikiname_pattern = '-_a-zA-Z0-9\.\s';

# Pattern for files
$wikifile_pattern = "@^([$wikiname_pattern]+)(\\/[$wikiname_pattern]+)*$@";

# Character class for name portion of pattern
$wikilink_name_pattern = "[\w\s()\?\!'\.\,\"/:-]";

# Pattern for full wikilinks
$wikilink_pattern = "@(\\\)?\[\[([$wikiname_pattern]+(\\/[$wikiname_pattern]+)*)(\|($wikilink_name_pattern+))?\]\]@";

# Pattern for functional links;
$functionlink_pattern = "@(\\\)?\[\[([[:space:]]*)([a-zA-Z]+):([[:space:]]*)(([^\]|,]+=[^\]|,]+)([[:space:]]*)(,[^\]|,]+=[^\]|,]+)*)?([[:space:]]*)(\|($wikilink_name_pattern+))([[:space:]]*)\]\]@";

# Pattern to match for git authors ( "John Doe <john@doe.com>" )
$git_author_pattern = "@^([^<>]+) \<([^>]+)\>$@";

# Pattern for representing tags
$php_tag_pattern    = '^(~)([a-zA-Z_0-9]+)\s*$';
$tag_pattern        = "^~[a-zA-Z_0-9]\+\s*$";
$tag_name_pattern   = "@^[a-zA-Z_0-9]+$@";

$php_meta_header_import_pattern = "@([!\\\])?(\[\[%([^%:]+?)\]\])@";
$php_meta_header_pattern = '@^(!)?(%([^%:]+?):\s*([^\s]+?.*)\s*)$@';
$php_meta_empty_pattern = '@^%([^%:]+?):\s*$@';
$git_meta_header_pattern = '^(?<!\!)%([^%:]+?):\s*([^\s]+?.*)\s*$';


# Determine whether to force inclusion of word count in commit notes
define( INCLUDE_WORD_COUNT, 1 );
define( WORD_COUNT_NOTES_REF, "giterary.word_count" );

# Determine whether to force inclusion of estimated work time in commit notes
define( INCLUDE_WORK_TIME, 1        );
define( INCLUDE_WORK_TIME_IN_LOG, 0 );
define( WORKING_TIME_NOTES_REF, "giterary.working_time" );

# Display EXIF/IPTC information with images
define( IMAGE_META_DISPLAY, 1 );

# Use the fancier Perl regular expressions to perform Git greps
define( USE_LIBPCRE_FOR_GIT_GREP, 1 );

# The page name used for "Talk" pages
define( TALK_PAGE, "Talk.talk" );

# The page name used for "Storyboard" pages
define( STORYBOARD_PAGE, "Storyboard.storyboard" );

# Whether to perform "does this exist" linking withinin wiki links
define( WIKILINK_DETECT_EXISTS, false );

# Note ref to be used for storing commit responses
define( COMMIT_RESPONSE_REF, "giterary.response" );

# Prefix for commit responses
define( COMMIT_RESPONSE_PREFIX, "####" );

# Symbol for Table of Contents pre/post processing
define( TOC_REPLACEME, "@@@TOC@@@" );

// Allowed tags during epub generation. Other tags
// will be removed, but their contents will remain.
$epub_allowed_tags = array(
    // 'A',
    'COMMENT',
    'BLOCKQUOTE',
    // 'BODY',
    'CODE',
    // 'DIV',
    'EM',
    'H1',
    'H2',
    'H3',
    'H4',
    'H5',
    'H6', // We keep this in, so that we can strip it later
    // 'HEAD',
    'HR',
    'BR',
    // 'HTML',
    // 'LINK',
    // 'META',
    'P',
    'PRE',
    // 'SCRIPT',
    // 'SPAN',
    'STRONG',
    // 'STYLE',
    // 'TITLE',
    'UL',
    'OL',
    'DL',
    'LI',
    'DT',
    'DD',
    'TABLE',
    'TBODY',
    'THEAD',
    'TFOOT',
    'TR',
    'TD',
    'TH',
    'IMG'
);

// Tags that will be removed from epub output, in addition
// to the tag's contents
$epub_removed_tags = array(
    'COMMENT',
    'H6'
);

# Names of files to check under username directories
# to see if a user-specific highlight file exists
$highlightify_filenames = array(
    "highlights",
    "highlights.txt",
    "highlights.csv",
    "highlight.txt",
    "highlight.csv"
);

# List of users that can be recipients of a snippet
# Users must exist in user list, as well as be in
# this list (unless the list has zero entries, in that
# case all known users will be presented)
$snippet_recipients = array(
    // 'username'
);

?>
