(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    console.log( 'loading funcify autocomplete' );

    var up_to_cursor_regex = /(\[\[)([^\]]*)$/;
    var after_cursor_regex = /^([^\[])*\]\]/;

    var function_links = [
        "ref:file=FILE|REF_TYPE",
        "transclude:file=FILE,strip=tags,as=MARKDOWN|DISPLAY",
        "blame:file=FILE|DISPLAY",
        "cherrypick:file=FILE|DISPLAY",
        "clear_cache:file=FILE|DISPLAY",
        "diff:file=FILE|DISPLAY",
        "history:file=FILE|DISPLAY",
        "index:file=FILE|DISPLAY",
        "partition:file=FILE|DISPLAY",
        "move:file=FILE|DISPLAY",
        "revert:file=FILE|DISPLAY",
        "search:file=FILE|DISPLAY",
        "show_commit:file=FILE|DISPLAY",
        "users:file=FILE|DISPLAY",
        "view:file=FILE|DISPLAY",
        "stats:file=FILE|DISPLAY",
        "todos:file=FILE|DISPLAY",
        "metrics:file=FILE,list=LIST,metric=METRIC1,metric=METRIC2,as=LIST,sort=TRUE|display",
        "progress:",
        "tags:file=FILE,tags=TAG1,tags=TAG2,tags=TAG3|DISPLAY",
        "table:file=FILE,filter=STRING,show_search=TRUE|DISPLAY",
        "assoc:file=FILE,type=ASSOC_TYPE,show_type=false,direction=ascending,sort=default,which=targets|DISPLAY",
        "list:file=FILE,list=LIST|DISPLAY",
        "edit:file=FILE|DISPLAY",
        "jot:file=BASE,template=FROM_TEMPLATE,format=%Y,format=%m,format=%d|DISPLAY",
        "template:file=FILE,template=TEMPLATE|DISPLAY",
        "image:file=FILE,list=FILE|DISPLAY",
        "toc:file=FILE,show=H1,noshow=H2|DISPLAY",
    ].sort();

    for( var i = 0; i < function_links.length; i++ ) {
        function_links[ i ] = '[[' + function_links[ i ];
        function_links[ i ] += ']]';
    }

    var funcify_hint = function(editor, options) {

        var list = [];

        var cur = editor.getCursor();

        var replace_ch = cur.ch;

        if( cur.ch > 1 ) {
        
            var up_to_cursor = editor.getRange( 
                {
                    line:   cur.line,
                    ch:     0
                },
                cur
            );

            var matches = [];

            if( matches = up_to_cursor.match( up_to_cursor_regex ) ) {
                var beginning_index = matches.index;

                // We need to check if this isn't already a completed
                // pattern
                var after_cursor = editor.getRange( 
                    {
                        line:   cur.line,
                        ch:     cur.ch
                    },
                    {
                        line:   cur.line,
                        ch:     editor.getLine( cur.line ).length
                    }
                );

                if( !after_cursor.match( after_cursor_regex ) ) {

                    var contents = editor.getRange( 
                        { line: cur.line, ch: beginning_index+2 },
                        { line: cur.line, ch: cur.ch }
                    );

                    list = list.concat( 
                        // Return matching function links
                        function_links.filter( function( a ) {
                            if( contents.length > 0 ) {
                                return a.indexOf( contents ) == 2;
                            }

                            return true;
                        })
                    );

                    if( typeof nav != 'undefined' && nav != null ) {

                        if( nav.local_search != null ) {
                            list = list.concat(
                                nav.local_search( contents ).map( function( a ) {
                                    return '[[' 
                                        + a.value 
                                        + '|' 
                                        + a.value.split( '/' ).pop()
                                        + ']]'
                                    ;
                                })
                            );
                        }
                    }

                    return {
                        list:   list, 
                        from:   CodeMirror.Pos( cur.line, beginning_index   ), 
                        to:     CodeMirror.Pos( cur.line, cur.ch            )
                    };
                }
            }
        }

        return false;
    }


    CodeMirror.registerHelper(
        "hint", 
        "markdown",
        funcify_hint
    );

    CodeMirror.registerHelper(
        "hint", 
        "csv",
        funcify_hint
    );

});
