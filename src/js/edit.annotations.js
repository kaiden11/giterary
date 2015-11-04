(function( ) {
    CodeMirror.defineMode(
        "giterary_markdown", 
        function(config, parserConfig) {
            var giterary_overlay = {
                startState: function() {
                    return {
                        found_annotation: false
                    };
                },
                token: function(stream, state) {
                    var ch;
                    if( stream.match("{") ) {
                        state.found_annotation = true;
                        while( (ch = stream.next()) != null ) {
                            if( ch == "}" ) {
                                break;
                            }
                        }
                        stream.eat("}");
                        return "annotation";
                    }

                    if( stream.match("(") && state.found_annotation ) {
                        while( (ch = stream.next()) != null ) {
                            if( ch == ")" ) {
                                break;
                            }
                        }
                        stream.eat(")");
                        state.found_annotation = false;
                        return "annotation-comment";
                    }

                    if( stream.match("[") && state.found_annotation ) {
                        while( (ch = stream.next()) != null ) {
                            if( ch == "]" ) {
                                break;
                            }
                        }
                        stream.eat("]");
                        state.found_annotation = false;
                        return "annotation-ref";
                    }


                    while (stream.next() != null && !stream.match("{", false)) {
                        state.found_annotation = false;
                    }
                    return null;
                }
            };
            return CodeMirror.overlayMode(
                CodeMirror.getMode(config, "text/x-markdown" ), 
                giterary_overlay
            );
        }
    );
})();
