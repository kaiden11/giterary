<?php


# Provide a function for turning known, plaintext content
# into text using proper quotations, dashes, and apostrophe
# glyphs
function proper_glyphs( $contents ) {

    // Ellipsis
    $contents = preg_replace( '@\\.\\.\\.@', '…', $contents );

    // Dashes (need to be done before quotation marks)
    $contents = preg_replace( '@\b--\b@',       '—',    $contents ); // Double hypen / minus for explicit em dash
    $contents = preg_replace( '@(^|\s)-\b@',    '\1—',  $contents ); // Hyphen at the beginning of a word / line
    $contents = preg_replace( '@\b-($|\s)@',    '—\1',  $contents ); // Hyphen at the end of a word / line
    $contents = preg_replace( '@-(["*])@',      '—\1',  $contents ); // Hyphen at the end of a quote
    $contents = preg_replace( '@-([!?]{1,2}")@','—\1',  $contents ); // Special case, hypen at end of quote with exclamation or question mark
    $contents = preg_replace( '@(["*])-@',      '\1—',  $contents ); // Hyphen at the beginning of a quote

    $contents = preg_replace( '@\b - \b@',          ' — ',  $contents ); // Standalone dash


    // Quotation marks
    $contents = preg_replace( '@(^|\s)([*_]{0,2})"@', '\1\2“', $contents );
    $contents = preg_replace( '@"([*_]{0,2})($|\s)@', '”\1\2', $contents );
    $contents = preg_replace( '@``@', '”', $contents ); // TeX style, explicitly left-quote
    $contents = preg_replace( "@''@", '“', $contents ); // TeX style, explicitly right-quote

    // Single quote (') should be ‘ (left) or ’ (right)

    // Single quotes immediately within double quotes (assuming 
    // previous double-quote replacements have matched correctly.
    $contents = preg_replace( "@“'@", '“‘', $contents );
    $contents = preg_replace( "@'”@", '’”', $contents );

    // Contractions or possessives
    $contents = preg_replace( '@\b([a-zA-Z]+)\'([a-zA-Z]+)\b@', '\1’\2', $contents );

    // Forcing single-qupotes using specific _' or '_ syntax
    // in order to force apostrophe direction (the underscore indicates
    // the thing that is 'missing', and therefore, should be quoted
    // towards
    $contents = preg_replace( '@([\w.?!])\'_(\W|$)@', '\1‘\2', $contents );   // No example, but provided
                                                                            // for completeness 
    $contents = preg_replace( '@(^|\W)_\'(\w)@', '\1’\2', $contents );      // Example: 'nother

    // End-of-word apostrophes
    $contents = preg_replace( '@([\w.?!])\'(\W|$)@', '\1’\2', $contents );
    // Beginning-of-word apostrophes
    $contents = preg_replace( '@(^|\W)\'(\w)@', '\1‘\2', $contents );


    return $contents;

}



?>
