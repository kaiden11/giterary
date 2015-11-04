importScripts( 'showdown.js', 'showdown.annotate.js', 'csv.js', 'storyboard.js' );


var preview = {
    markdown_converter: new Showdown.converter( { extensions: [ "annotate" ] } ),
    pipeline: null,
    get_position:   function( content, pos ) {
        if( typeof pos === 'number' ) {
            return pos;
        }

        if( typeof pos === 'object' ) {
            if( pos.line != null && pos.ch != null ) {
           
                var ret = 0;
                var lines = content.split( /\n/, pos.line );

                for( var i in lines ) {
                    
                    ret += lines[i].length + 1; // +1 for the \n
                }

                ret += pos.ch;

                return ret;
            }

            return 0;
        }
    },
    preview_helper: function( extension, content, position ) {
        var i = 0;
        position = preview.get_position( content, position );
        for( i in preview.pipeline[extension] ) {

            content = preview.pipeline[extension][i]( content, position );
        }

        return { 
            content:    content,
            extension:  extension
        };
    },
    linkify: function( text ) {

        return text
            .replace(
                /(\\)?\[\[([-_a-zA-Z0-9\.\s]+(\/[-_a-zA-Z0-9\.\s]+)*)(\|([\w\s\.\,\/-]+))?\]\]/g, 
                function( match, escaping, content, m1, m2, display ) {
                    if( escaping == '\\' ) {
                        return match.substr( 1 );
                    } else {
                        if( typeof display == "undefined" || display == '' ) {
                            return '<a class="wikilink" href="' + content + '">' + content + '</a>';
                        } else {
                            return '<a class="wikilink" href="' + content + '">' + display + '</a>' ;
                        }
                    }
                }
            )

    },
    funcify: function( text ) {

        return text
            .replace(
                /(\\)?\[\[([a-zA-Z]+):(([^\]|,]+=[^\]|,]+)(,[^\]|,]+=[^\]|,]+)*)?(\|([\w\s?!'\.\,\"/-]+))\]\]/g,
                function( match, escaping, func, m1, m2, m3, m4, display ) {
                    if( escaping == '\\' ) {
                        return match.substr( 1 );
                    } else {
                        return '<a class="wikilink" href="' + func + '">' + display + '</a>' ;
                    }
                }
            )
    },
    caret: '@C@A@R@E@T@',
    caret_preprocess: function( content, position ) {

        return ( content.substring( 0, position ) + 
            preview.caret +
            content.substring( position, content.length )
        ).replace( 
            new RegExp( '(\r?\n)(.*' + preview.caret + '.*)(\r?\n)' ), 
            function( match, m1, line, m2 ) { 

                var wo_caret = line.replace( preview.caret, '' );
        
                var matches = null;
                // Caret on a blank line...
                if( matches = wo_caret.match( /^$/ ) ) {
                    return m1 + wo_caret + m2;
                }

                if( matches = wo_caret.match( /^([ ]*)([\*-+]|[0-9]+\.)([ ]+)(.*)$/ ) ) {
                    return m1 + matches[1] + matches[2] + matches[3] + preview.caret + matches[4] + m2;
                }

                if( matches = wo_caret.match( /^(.*)([ ][ ])$/ ) ) {
                    return m1 + preview.caret + matches[1] + matches[2] + m2;
                }

                if( matches = wo_caret.match( /^(#+)(.*)$/ ) ) {
                    return m1 + matches[1] + preview.caret + matches[2] + m2;
                }

                return match; 
            }  
        );
    },
    caret_postprocess:  function( content ) {
        return content.replace( preview.caret, '<span id="caret"></span>' );
    },
    /*
    str_getcsv: function(input, delimiter, enclosure, escape) {
        // http://kevin.vanzonneveld.net
        // +   original by: Brett Zamir (http://brett-zamir.me)
        // *     example 1: str_getcsv('"abc", "def", "ghi"');
        // *     returns 1: ['abc', 'def', 'ghi']
        var output = [];
        var backwards = function (str) { // We need to go backwards to simulate negative look-behind (don't split on
            //an escaped enclosure even if followed by the delimiter and another enclosure mark)
            return str.split('').reverse().join('');
        };

        var pq = function (str) { // preg_quote()
            return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
        };
        
        delimiter = delimiter || ',';
        enclosure = enclosure || '"';
        escape = escape || '\\';
       
        // Stripping whitespace before/after enclosures
        input = input.replace(new RegExp('^\\s*' + pq(enclosure)), '').replace(new RegExp(pq(enclosure) + '\\s*$'), '');
        
        // PHP behavior may differ by including whitespace even outside of the enclosure
        input = backwards(input).split(new RegExp(pq(enclosure) + '\\s*' + pq(delimiter) + '\\s*' + pq(enclosure) + '(?!' + pq(escape) + ')', 'g')).reverse();
        
        for (var i = 0; i < input.length; i++) {
            output.push(backwards(input[i]).replace(new RegExp(pq(escape) + pq(enclosure), 'g'), enclosure));
        }
        
        return output;
    },
    */
    storyboard: function( content, position ) {

        var lines   = content.split( /\r?\n/ );
        var line    = null;
        var ret     = [];

        var indices = {};

        var current_line = ( 
            // Count the number of newlines before the current character offset
            content
                .substr( 0, position + 1 )  // Up to position
                .split( /\r?\n/ )           // Split on newlines
                .length - 1                 // Length, -1 (as lines are zero-indexed
        );

        for( var i = 0; i < lines.length; i++ ) {

            if( i == 0 ) {

                indices = storyboard.get_indices( 
                    lines[ i ] 
                );

                // indices = {
                //     flag:           0,
                //     subject:        1,
                //     description:    2
                // };
                continue;
            }

            line = lines[i];

            var card = {};

            if( line == "" ) {
                continue;
            }

            try {
                card = storyboard.parse_line( line, indices );
                card[ 'line' ] = i;
                card['is_current_line'] = ( i == current_line );
            } catch( ex ) {
                card = {}
            }


            ret.push( card );

        }

        var html = '';

        // html += '<div class="row">';

        var cards = {
            'enabled': ret.filter(
                function( a ) {
                    return !( a.disabled == true );
                }
            ),
            'disabled': ret.filter(
                function( a ) {
                    return ( a.disabled == true );
                }
            )
        };

        for( var i in cards ) {

            html += '<div class="' + ( i == "enabled" ? "col-md-9" : "col-md-3" ) + '">';
                html += '<fieldset>';
                    html += '<legend>';
                        html += ( i == "enabled" ? "Cards" : "Discards" );
                    html += '</legend>';
                    html += '<div class="cards ' + ( i ) + '">';

                        for( var j = 0; j < cards[ i ].length; j++ ) {
                            var card = cards[ i ][ j ];

                            html += '<div ' +
                                        ( card.is_current_line ? 'id="caret"' : '' ) + ' ' +
                                        'class="card col-md-2 ' + ( card.questioned == true ? "questioned" : '' ) + '"' +
                                        'data-line="' + card.line + '"' +
                                        'data-questioned="' +  ( card.questioned == true ? "1" : "0" ) + '"' +
                                        'data-disabled="' +  ( card.disabled == true ? "1" : "0" ) + '"' +
                                    '>';
                                html += '<div class="panel ' + ( card.disabled ? 'panel-danger' : 'panel-default' ) + ' line-' + card.line + '">';
                                    html += '<div class="panel-heading">';
                                        html += '<span class="panel-title">';
                                            html += ( card.subject == null || card.subject == "" ? card.description : card.subject );
                                        html += '</span>';
                                    html += '</div>';

                                    html += '<div class="panel-body">';
                                        html += card.description;
                                    html += '</div>';
                                html += '</div>';
                            html += '</div>';
                        }

                    html += '</div>';

                html += '</fieldset>';
            html += '</div>';
        }

        // html += '</div>';

        return html;

    },
    csv: function( content, position ) {

        var lines   = content.split( /\r?\n/ );
        var line    = null;
        var ret     = [];
        for( var i in lines ) {
            line = lines[i];

            if( line == "" ) {
                continue;
            }

            // $line = str_getcsv( $line );
            var is_error = false;
            try {
                line = csv.str_getcsv( line );
            } catch( ex ) {
                line = [ line ];
            }

            for( var k in line ) {
                line[k] = preview.linkify( 
                    preview.funcify(
                        line[k]
                    )
                );
            }

            ret.push( line );
        }
        var head = '';
        var body = '<tbody>';
        
        for( var i in ret ) {
            if( i == 0 ) {
                head += '<thead>';
                head += '<tr class="header">';
        
                for( var j in ret[i] ) {
                     
                    head += '<th>' + '<span>' + ret[i][j] + '</span>' + '</th>';
        
                }
                head += '</tr>';
                head += '</thead>';
            } else {
                body += "\n<tr>";
        
                for( var j in ret[i] ) {
                     
                    body += '<td>' + ret[i][j] + '</td>';
        
                }

                body += '</tr>';
            }
        }
        
        body += '</tbody>';
       
        return '<div id="csv-output"><table class="tabulizer">' + head + body + '</table></div>';
    },
    setup: function() {

        preview.pipeline = {
            "markdown": [
                preview.caret_preprocess,
                preview.funcify, 
                preview.linkify,
                preview.markdown_converter.makeHtml,
                preview.caret_postprocess
            ],
            "csv": [
                preview.csv
            ],
            "storyboard": [
                preview.storyboard
            ],
            "talk": [
                preview.caret_preprocess,
                preview.funcify, 
                preview.linkify,
                preview.markdown_converter.makeHtml,
                preview.caret_postprocess
            ]

        };
    }
};

preview.setup();

onmessage = function( event ) {
    try {
        postMessage( 
            preview.preview_helper( 
                event.data.extension, 
                event.data.content, 
                event.data.position 
            )
        );
    } catch( ex ) {
        throw ex;

    }
};
