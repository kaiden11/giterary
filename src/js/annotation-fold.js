(function() {

    var annotation_regex = /((\\)?\{((?:\[[^\}]*?\}|[^\{\}])*?)\}\([ \t]*()([^\)]+)[ \t]*\))/g;
    var annotation_comment = /\([ \t]*([^\)]+)[ \t]*\)/g;

    /*
    RegExp.prototype.exec = (function (exec) {
        return function (str, callback, scope) {
            var results = exec.call(this, str);
            
            if (arguments.length > 1 && results) {
                callback.apply(scope, results);
            } else {
                return results;
            }
        };
    })(RegExp.prototype.exec);
    */


    /*
    function isAnnotation(lineNo) {
        var tokentype = cm.getTokenTypeAt(CodeMirror.Pos(lineNo, 0));
        return tokentype && /\bannotation-comment\b/.test(tokentype);
    }
    */

    CodeMirror.registerHelper("fold", "markdown", function(cm, start) {
    
    
        var line = cm.getLine( start.line );
        var annotation_match = null;
        var comment_match    = null;
        var line_index       = null;
        var comment_index    = null;
        var comment_length   = null;

        var ret = [];

        annotation_regex.lastIndex = 0;
        annotation_comment.lastIndex = 0;
    
        while( annotation_match = annotation_regex.exec( line ) ) {

            line_index = annotation_match.index ;

            if( comment_match = annotation_comment.exec( annotation_match[0] ) ) {
                
                comment_index   = comment_match.index;
                comment_length  = comment_match[0].length;

                ret.push(  
                    {
                        from:   CodeMirror.Pos( start.line, line_index + comment_index + 1 ),
                        to:     CodeMirror.Pos( start.line, line_index + comment_index + comment_length - 1 )
                    }
                );
            }

            annotation_comment.lastIndex = 0;
        }

        if( ret.length == 0 ) {
            return false;
        } else {
            if( ret.length == 1 ) {
                return ret[0];
            } else {
                return ret;
            }
        }
    });
})();
