var util = {
    workout:    function( script, callback ) {
        var ret = null;

        ret = new Worker( script );

        if( ret != null ) {
            ret.onmessage = callback;
            ret.onerror = function( err ) {
                console.log( 'webworker error: ' + err );
            };
        }

        return ret;
    },
    short_time_diff: function( t1, t2 ) {

        var $difference = Math.abs( t2 - t1 );
        if($difference > 31556926) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 31556926 ) + 'Y';
        } 

        if($difference > 2629743) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 2629743 ) + 'M';
        } 

        if($difference > 604800) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 604800 ) + 'W';
        } 

        if($difference > 86400) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 86400 ) + 'D';
        } 

        if($difference > 3600) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 3600 ) + 'H';
        } 

        if($difference > 60) {
            return ($difference >= 0 ? '+' : '' ) + Math.floor($difference / 60 ) + 'min';
        } 

        return ($difference >= 0 ? '+' : '' ) + $difference + 'S';
    }
};

Function.prototype.defer = function() {

    var __method = this, args = Array.prototype.slice.call(arguments, 1);

    return window.setTimeout(
        function() {
            return __method.apply(__method, args);
        }, 
        0
    );
}

String.prototype.trim=function(){

    return this.replace(
        /^\s+|\s+$/g,
        ''
    );
};

String.prototype.pad_right = function(l,c) {
    return this+Array(l-this.length+1).join(c||" ")
};

String.prototype.pad_left = function(l,c) {
    return Array(l-this.length+1).join(c||" ") + this;
};


$.fn.toggle_class = function( clazz ) {
    if( clazz ) {
        
        var clazzes = clazz.split( /\s+/ );
        for( var c in clazzes ) {
            if( $(this).hasClass( clazzes[c] ) ) {
                $(this).removeClass( clazzes[c] )
            } else {
                $(this).addClass( clazzes[c] );
            }
        }
    }
}

$.fn.when_settled = function( func, timeout, selector, events ) {

    if( typeof( timeout ) == 'undefined' || timeout == null || timeout == '' ) {
        timeout = 500;
    }

    if( typeof( events ) == 'undefined' || events == null || events == '' ) {
        events  = 'keyup keydown change';
    }

    var handle = this;

    var settler = {
        timeout_id: null,
        mutex:      null,
        handle:     handle,
        func:       func
    };

    if( !handle.settlers ) {
        handle.settlers = [];
    }

    handle.settlers.push( settler );

    var helper = function() {
        if( settler.timeout_id != null ) {
            clearTimeout( settler.timeout_id );
        }

        if( settler.mutex == null ) {
            settler.mutex = false;
        }

        settler.timeout_id = setTimeout(
            function() {
                if( !settler.mutex ) {
                    settler.mutex = true;
                    settler.func.apply( settler.handle );
                    settler.mutex = false;
                } else {
                    console.log( 'Settled mutex contention' );
                }
            },
            timeout
        );
    };

    if( typeof( selector ) == 'undefined' || selector == null || selector == '' ) {
        $(this).on( 
            events, 
            helper
        );
    } else {
        $(this).on( 
            events, 
            selector,
            helper
        );
    }
};


var table = {
    search: function( tbl, term ) {

        if( term == "" ) {
            $(tbl).find('tbody tr').removeClass( 'hidden' );
        } else {

            term = term.trim();
            
            $(tbl).find('tbody tr').each( 
                function(k,v) {

                    var components = term.split( /\s*,\s*/ );

                    var success = true;

                    var html = $(v).html().toLowerCase();

                    for( var i = 0; i < components.length; i++ ) {


                        var c = components[i].toLowerCase().trim();

                        if( c == "" ) {
                            continue;
                        }
                        
                        if( html.indexOf( c ) == -1 ) {
                            success = false;
                            break;
                        }
                    }

                    if( !success ) {

                        $(v).addClass( 'hidden' );

                    } else {

                        $(v).removeClass( 'hidden' );

                    }
                }
            );
        }
    }
};

$.fn.btn_clickable = function() {
    $(this).each( function(k,v) { 
        $(v).click( function() {
            window.location = $(v).val();
        } );
    } );
};

$.fn.tabulizer = function() {

    $(this).each(
        function( k, tbl ) {

            var do_sort = !$(tbl).hasClass( 'no-sort' );

            $(tbl).wrap( 
                '<div class="table-container"/>'
            );

            // alert( $(tbl).parent().attr("class") );

            if( $(tbl).hasClass( 'show-search' ) ) {

                $(
                    [
                        '<div class="csv-filter">',
                        '    <div>',
                        '       <label for="search">Search</label>',
                        '       <input class="search" type="text" value="" accesskey="f" />',
                        '       <input class="reset"  type="button" value="Reset" />',
                        '       [<span class="help">Help</span>]',
                        '    </div>',
                        '    <div class="help" style="display: none;">',
                        '        <p>Filter on multiple terms by separating required matches with commas.</p>',
                        '        <p>Example: <em>a,e,i,o,u,y</em> matches rows containing all vowels, case insensitive.</p>',
                        '        <p>Sort by clicking on a column header. Sort on multiple columns by holding shift and selecting multiple columns.</p>',
                        '        <p>',
                        '            You can provide default searched by editing your header rows to be prefixed with special characters that determine',
                        '            sorting type and sort order. For instance:',
                        '            <ul>',
                        '                <li><em>!Year</em> sorts the column &quot;Year&quot; in least-to-greatest order.</li>',
                        '                <li><em>^Year</em> sorts the column &quot;Year&quot; in greatest-to-least order.</li>',
                        '                <li><em>!#Year</em> sorts the column &quot;Year&quot; in least-to-greatest order, treating all elements as numbers.</li>',
                        '                <li><em>!@Year</em> sorts the column &quot;Year&quot; in least-to-greatest order, treating all elements as strings.</li>',
                        '            </ul>',
                        '            The special prefix characters will be removed upon display.',
                        '        </p>',
                        '        <p>',
                        '            Double-clicking on any value in the table will filter the table by that value.',
                        '        </p>',
                        '   </div>',
                        '</div>'
                    ].join("\n")
                ).insertBefore( tbl );
            }

            sort_list = [];
            sort_type = {};

            // alert( $(container).html() );

            param_regex = /^([^{}]+)\{(([a-zA-Z]+=[^{}= ]+?)(\s+[a-zA-Z]+=[^{}= ]+?)*)\}/;
            arg_regex_all = /([a-zA-Z]+)=([^{}= ]+)/g;
            arg_regex = /([a-zA-Z]+)=([^{}= ]+)/;

            arg_f = function( text ) {
                ret = {};
                matches = text.match( arg_regex_all );
                
                // console.log( matches );
                if( matches ) {
                    for( var i in matches ) {
                        arg_ret = matches[i].match( arg_regex );

                        arg_ret[1] = arg_ret[1].toLowerCase();

                        switch( arg_ret[1] ) {
                            case 'offset':
                                ret[ arg_ret[1] ] = arg_ret[2];
                                break;
                            default:
                                console.log( 'Unknown parameter: ' + arg_ret[1] );
                                break;
                        };
                    }
                }


                return ret;
            };

            $(tbl).find('thead th span').each(

                function( k, v ) {
                    heading = $(v).text().trim();
            
                    sorted = null;
                    hinted = false;
            
                    while( heading.length > 0 && ( heading[0] == "!" || heading[0] == "@" || heading[0] == "#" || heading[0] == "^" ) ) {
            
                        hinted = true
                         var flag = heading.substring( 0, 1 );
            
                         heading = heading.substring( 1 );
            
                         if( flag == "!" ) {
                             sorted = [ k, 0 ];
                         }
            
                         if( flag == "^" ) {
            
                             sorted = [ k, 1 ];
                         }
            
                         if( flag == "#" ) {
                             sort_type[k] = { sorter: 'digit' };
                         }
            
                         if( flag == "@" ) {
                             sort_type[k] = { sorter: 'alpha' };
                         }
                    }
   
                    if( hinted ) {
                        if( sorted != null ) {
                            sort_list.push( sorted );
                        } else {
                            console.log( 'sorted ignored');
                        }
                    }

                    if( param_ret =  heading.match( param_regex  ) ) {
                        // console.log( param_ret );
                        heading = heading.replace( 
                            param_regex, 
                            function( whole, heading_only, args, first_arg, last_args ) {

                                arg_values = arg_f( args );

                                if( typeof( arg_values.offset ) != 'undefined' ) {
                                    $(tbl).find( 'tbody tr td:nth-child( ' + (k+1) + ' )' ).each( 
                                        function( row, td ) {
                                            // console.log( $(td).html() );

                                            c = ( $(td).text() - 0 );
                                            if( typeof( c ) != 'undefined' ) {

                                                $(td).wrapInner( '<div class="offset" title="' + (  c - arg_values.offset ) + '" />' );
                                                
                                            }
                                        }
                                    );
                                }

                                // console.log( arg_values );

                                return heading_only;
                            }
                        );
                    }

                    
                    $(v).html( heading );
                }
            );
            
            if( sort_list.length == 0 ) {
                sort_list.push( [ 0, 0 ] );
            }

            var te = function( node ) {
                return $(node).text();
            };

            try {
                if( do_sort ) {
                    $(tbl).tablesorter(
                        {
                            sortList:       sort_list,
                            headers:        sort_type,
                            textExtraction: te
                        }
                    );
                } else {
                    $(tbl).tablesorter(
                        {
                            textExtraction: te
                        }
                    );
                }
            } catch( err ) {
                console.error( err );
            }

            $(tbl).find('tbody td').dblclick(
                function() {
                     console.log( $(this).text() );
                    $(tbl).parent().find('input.search').val( $(this).text() );
            
                    table.search( tbl, $(tbl).parent().find('.csv-filter input.search').val() );
                }
            );
            
            $(tbl).parent().find('.csv-filter input.search').keyup( 
                function() {

                    table.search( tbl, $(tbl).parent().find('.csv-filter input.search').val() );
                }
            );
            
            $(tbl).parent().find('.csv-filter input.reset').click(
                function() {
                    table.search( tbl, '' );
                    $(tbl).parent().find( '.csv-filter input.search').val( '' );
                }
            );

            $(tbl).parent().find('.csv-filter span.help').click( 
               function() {
                   $(tbl).parent().find('div.help').toggle();
               }
            );
            
            if( navigator.platform.indexOf( "Mac" ) > -1 ) {
                $(tbl).parent().find(".csv-filter input.search").text_suggest( "ctrl-opt-f..." );
            } else {
                if( navigator.platform.indexOf( "Win" ) > -1 ) {

                   $(tbl).parent().find(".csv-filter input.search").text_suggest( "alt-shift-f..." );
                } else {
                    //  Likely more...
                    $(tbl).parent().find(".csv-filter input.search").text_suggest( "alt-shift-f..." );
                }
            }

            cancel_keycombos = function(event){
                event.stopPropagation();
            };


            $(tbl).parent().find(".csv-filter input.search").keypress( cancel_keycombos );  
            $(tbl).parent().find(".csv-filter input.search").keydown( cancel_keycombos );  
        }
    );
};

$.fn.text_suggest = function( suggestion_text ) {

    function placeholderIsSupported() {
        var test = document.createElement('input');
        return ('placeholder' in test);
    }

    if( placeholderIsSupported() ) {
        // Use browser supported placeholders if capable
        $(this).attr( 'placeholder', suggestion_text );
    } else {
        $(this).blur( 
            function() {
                if( $(this).val() == '' ) {
                    $(this).val( suggestion_text );
                }
            }
        ).focus( 
            function () {
                if( $(this).val() == suggestion_text ) {
                    $(this).val( '' );
                }
            }
        ).attr(
            'title',
            suggestion_text
        ).addClass(
            'suggestible'
        ).val(
            suggestion_text
        );
    }
}

$.fn.disable_backspace = function ( f ) {

    var cancel = null;

    if( typeof f == "undefined" ) {
        cancel = function (e) {
            if (e.which == 8) { //"backspace" key
                e.preventDefault();
                return false;
            }

            return true;
        };
    } else {
        cancel = f;
    }


    $(this).keypress(cancel);
    $(this).keydown(cancel);
}

// From: http://www.gmarwaha.com/blog/2009/06/16/ctrl-key-combination-simple-jquery-plugin/
$.fn.meta = function(key, callback, args) {

    var possibilities = [];

    if( typeof key == "string" ) {
        possibilities.push( key.toLowerCase().charCodeAt( 0 ) );
        possibilities.push( key.toUpperCase().charCodeAt( 0 ) );
    } else {
        possibilities.push( key );
    }

    var is_meta = function( e ) {

        if( navigator.platform.indexOf( "Mac" ) > -1 ) {
            return e.ctrlKey;
        }

        if( navigator.platform.indexOf( "Win" ) > -1 ) {
            return e.altKey;
        }

        if( navigator.platform.indexOf( "Lin" ) > -1 ) {
            return e.ctrlKey;
        }

        // Best effort
        return ( e.altKey || e.ctrlKey );
    };

    // console.log( possibilities );

    $(this).keydown(function(e) {
        if(!args) args=[]; // IE barks when args is null

        // console.log( e.keyCode );

        if( $.inArray( e.keyCode, possibilities ) >= 0 && is_meta( e ) ) {
            callback.apply(this, args);
            return false;
        }
    });
};

// From http://stackoverflow.com/questions/6450336/regex-for-visible-text-not-html
$.fn.highlightify = function( pattern, classes ) {
    var repl = '<span class="highlightify ' + ( classes ? classes : [] ).join( ' ' ) + '">' + '$&' + '</span>';

    this.each(function() {

        $(this).contents().each(function() {

            if(this.nodeType === 3 && pattern.test(this.nodeValue)) {
                $(this).replaceWith(this.nodeValue.replace(pattern, repl));
            }
            else if(!$(this).hasClass('highlightify')) {
                $(this).highlightify( pattern, classes );
            }
        });
    });
    return this;
};

// From http://stackoverflow.com/questions/2593637/how-to-escape-regular-expression-in-javascript
RegExp.quote = function(str) {
    return (str+'').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
};

var storage = {
    max_storage:    2500000, // 5 megabytes / 2
    max_entries:    100,
    maintenance: function() {
        
        if( storage.local_storage ) {
           
            var needs_maintenance = false;
            var stored_bytes = 0;

            if( storage.local_storage.length > storage.max_entries ) {
                needs_maintenance = true;
            }

            var key_dates = [];

            for( var i = 0; i < storage.local_storage.length; i++ ) {
                var k = storage.local_storage.key( i );

                stored_bytes += ( storage.local_storage[ k ] != null 
                    ? storage.local_storage[ k ].length
                    : 0
                );

                if( stored_bytes > storage.max_storage ) {
                    needs_maintenance = true;
                }

                try {
                    key_dates.push(
                        {
                            key:    k,
                            date:   storage.get_timestamp( storage._devalify( k ) )
                        }
                    );
                } catch( ex ) {
                    console.log( 
                        'Unable to find date while doing maintenance for key: ' + k 
                    )
                }
            }

            if( needs_maintenance ) {
                // Remove the oldest entries until storage is less than max_storage

                key_dates = key_dates.sort( function( a, b ) {
                    return a.date - b.date;
                });

                for( 
                    var i = 0; 
                    i < key_dates.length && ( stored_bytes > storage.max_storage || storage.local_storage.length > storage.max_entries ); 
                    i++ 
                ) {

                    var kd = key_dates[ i ];

                    console.log( 'Removing ' + kd.key );

                    var length = storage.local_storage.getItem( kd.key ).length;

                    stored_bytes -= length;

                    storage.local_storage.removeItem( kd.key );
                }
            }

        }
    },
    local_storage:  (function() { 
        var uid = new Date;
        var my_storage;
        var result;
        try {
            (my_storage = window.localStorage).setItem(uid, uid);
            result = my_storage.getItem(uid) == uid;
            my_storage.removeItem(uid);

            (function() {
                storage.maintenance();
            }).defer();

            return result && my_storage;
        } catch (exception) { console.log( exception ); }
    }()),
    _keyify:    function( a ) {
        return JSON.stringify( a );
    },
    _valify:    function( a ) {
        return JSON.stringify( a );
    },
    _devalify:    function( a ) {
        try {
            return JSON.parse( a );
        } catch( ex ) {
            throw ex + ":" + a;
        }
    },
    set_item: function( ) {

        if( arguments.length < 2 ) {
            throw "set_item argument error: requires at least 2 parameters";
        }

        if( storage.local_storage ) {
            var args = Array.prototype.slice.call( arguments );
            var val = args.pop();

            return storage.local_storage.setItem( 
                storage._keyify( args ),
                storage._valify( 
                    {
                        value:      val,
                        timestamp:  new Date()
                    }
                ) 
            );
        }

        return undefined;
    },
    get_item: function() {

        if( arguments.length < 1 ) {
            throw "set_item argument error: requires at least 1 parameters";
        }

        if( storage.local_storage ) {

            
            var args = Array.prototype.slice.call( arguments );

            if( args.length == 1 && Array.isArray( args[ 0 ] ) ) {
                args = args[ 0 ];
            }

            var val = null;
            try {
                val = storage._devalify( 
                    storage.local_storage.getItem( 
                        storage._keyify( args )
                    )
                );
            } catch( ex ) { 
                console.log( 'Unable to extract timestamp from record: ' + JSON.stringify( args ) ); 
            }

            if( 
                val != null 
                && ( typeof( val.timestamp ) != 'undefined' && typeof( val.value ) != 'undefined' )
            ) {
                // This is a timestamped value.
                return val.value
            }

            return val;
        }

        return null;
    },
    get_timestamp: function() {
        var ret = null;
        if( storage.local_storage ) {
            var args = Array.prototype.slice.call( arguments );
            if( args.length == 1 && Array.isArray( args[ 0 ] ) ) {
                args = args[ 0 ];
            }
    
            var item = null;

            try {
                item = storage._devalify( 
                    storage.local_storage.getItem( 
                        storage._keyify( args )
                    )
                );
            } catch( ex ) { 
                console.log( 'Unable to extract timestamp from record: ' + JSON.stringify( args ) ); 
            }

            if( item == null ) {
                return item;
            }

            if( typeof( item.timestamp ) != 'undefined' && typeof( item.value ) != 'undefined' ) {
                ret = new Date( item.timestamp );
            }
        }

        return ret;
    },
    length: function() {
        if( storage.local_storage ) {
            return storage.local_storage.length;
        }

        return 0;
    },
    key: function( i ) {
        if( storage.local_storage ) {
            return storage.local_storage.key( i );
        }

        return null;
    },
    keys:   function() {
        if( storage.local_storage ) {
            var ret = [];

            for( var i = 0; i < storage.local_storage.length; i++ ) {
                var k = storage.local_storage.key( i );

                try {

                    k = storage._devalify( k );

                } catch(e) { /* Ignore */ }

                ret.push( 
                    k
                );
            }

            return ret;
        }

        return [];
    },
    search: function() {
        var ret = [];

        if( storage.local_storage ) {
            var args = Array.prototype.slice.call( arguments );

            var keys = storage.keys();

            for( var i = 0; i < keys.length; i++ ) {
                if( args.length > keys.length  ) {
                    // search args are more specific than the key 
                    // args, so we can skip this without doing
                    // comparison
                    continue;
                }

                var is_match = true;
                for( var j = 0; j < args.length; j++ ) {
                    if( args[ j ] != keys[ i ][ j ] ) {
                        is_match = false;
                        break;
                    }
                }

                if( is_match ) {
                    ret.push( 
                        keys[ i ]
                    );
                }
            }
        }

        return ret;

    }

};

// console.log fallback, for IE
if (typeof console === "undefined" || typeof console.log === "undefined") {
    console = {};
    console.log = function() {};
}

