
var csv = {
    str_getcsv_nonquoted_point: function( input, opts, state ) {
        var point = '';


        while( state.i < input.length ) {


            // Lookahead to see if this is an escaped quote
            if( input[state.i] == ',' ) {
                break;
            }

            point += input[state.i];

            state.i++;
        }


        return point;

    },
    str_getcsv_quoted_point: function( input, opts, state ) {

        var point = '';

        if( input[state.i] != '"' ) {
            throw "Quoted point did not start with a quote";
        }

        // Skip the first quote
        state.i++;

        var finished_quote = false;

        while( state.i < input.length ) {

            if( input[state.i] == '"' ) {
                // Lookahead to see if this is an escaped quote
                if( input[state.i+1] == '"' ) {
                    point   += '"';
                    state.i += 2;
                    continue;
                }

                // Otherwise, finishing quote
                state.i++;
                finished_quote = true;
                break;
            }

            point += input[state.i];

            state.i++;
        }

        if( !finished_quote ) {
            throw "Unable to finish quoted point";
        }

        return point;


    },
    str_getcsv_point: function( input, opts, state ) {

        var point = '';

        
        if( input[state.i] == '"' ) {
            point = csv.str_getcsv_quoted_point( input, opts, state );
        } else {
            point = csv.str_getcsv_nonquoted_point( input, opts, state );
        }

        while( state.i < input.length && input[ state.i ] == ' ' ) {
            state.i++;
        }

        // throw JSON.stringify( [ input, state, point ] );

        return point;

    },
    str_getcsv_helper: function( input, opts, i ) {
        var ret = []; 

        var state = { i: i };

        var last_comma = null;


        while( state.i < input.length ) {

            if( input[ state.i ] == ' ' ) {
                state.i++;
                continue;
            }

            if( opts.max_columns != null ) {
                if( ret.length < opts.max_columns ) {
                    ret.push( csv.str_getcsv_point( input, opts, state ) );
                } else {
                    // We have all of our columns, and can't fill any more
                    // so we append the rest of the string ot the last column
                    ret[ ret.length-1 ] += input.substr( last_comma );

                    // Set the pointer to the end of the string.
                    state.i = input.length;
                }
            } else {
                ret.push( csv.str_getcsv_point( input, opts, state ) );
            }

            // throw ;

            if( state.i < input.length && input[state.i] != ',' ) {
                throw "Point terminated without field separator:" + JSON.stringify( [ input, state, input[state.i] ] )
            } else {
                last_comma = state.i;
                state.i++;
            }

        }

        if( state.i < input.length ) {
            throw "Trailing characters.";
        }

        /*
        if( opts.max_columns != null && ret.length > opts.max_columns ) {
            // Merge the last columns into a single column
            var remainder = ret.splice( 
                (opts.max_columns-1),
                (ret.length-1)-(opts.max_columns-1)+1
            );

            // console.log( remainder.join( ',' ) );

            ret.push( 
                remainder.join( "," )
            );
        }
        */

        return ret;
    },
    str_getcsv: function( input, opts ) {
        var ret = [];

        opts = ( typeof opts == 'undefined' ? {} : opts );

        if( input ) {

            try {
                ret = csv.str_getcsv_helper( input, opts, 0 );
            } catch( ex ) {
                throw ex;
            }
        }

        return ret;
    }
}

