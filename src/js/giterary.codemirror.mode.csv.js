CodeMirror.defineMode("csv", function() {
    return {
        startState: function() {
            return {
                inString: false
            };
        },
        token: function(stream, state) {
            // If a string starts here
            if (!state.inString && stream.peek() == '"') {
                stream.next();            // Skip quote
                state.inString = true;    // Update state
            }
            
            if (state.inString) {

                if (stream.skipTo('"')) {   // Quote found on this line
                    stream.next();          // Skip quote
                    state.inString = false; // Clear flag
                } else {
                    stream.skipToEnd();     // Rest of line is string
                    state.inString = false;
                }

                return "string";            // Token style
            } else {
       
                if( stream.peek() != ',' && stream.peek() != '"' ) {
                    stream.next();
                    return "variable-2";
                }

                if( stream.peek() == ',' ) {
                    stream.next();
                    return "meta";
                }

                
                return "variable-2";
            }
        }
    };
});
