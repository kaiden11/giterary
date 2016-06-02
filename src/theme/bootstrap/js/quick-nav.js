var nav = {
    // quick_nav_id:   '#quick-nav',
    quick_nav_id:   '#term',
    toggle_quicknav_on: function() {
        var q = $('.quick-nav.handle');
        q.addClass( 'quick-nav-on' );
    },
    toggle_quicknav_off: function() {
        var q = $('.quick-nav.handle');
        q.removeClass( 'quick-nav-on' );
    },
    toggle_quicknav: function() {
        var q = $('.quick-nav.handle');

        if( q.hasClass('quick-nav-on') ) {
            nav.toggle_quicknav_off();
        } else {
            nav.toggle_quicknav_on();
        }
    },
    head_files_cache:       [],
    head_tags_cache:        [],
    reverse_head_tags_cache:{},
    remote_search_mutex:    false,
    regex_match_cache:      {},
    local_search_regex:     function( term, files ) {
        var ret = [];
        var remains = [];
        var regex = '';

        term = term.replace( / /g, '' );

        for( var i = 0; i < term.length; i++ ) {

            regex = regex + "(" + RegExp.quote( term[i] ) + ").*";
        }

        // regex = regex + '(.*)';
        
        regex = new RegExp( regex, 'i' );

        if( term in nav.regex_match_cache ) {
            return nav.regex_match_cache[ term ];
        } else {

            // Find  the key in the cache that is a) a substring of
            // the current term and b) has the least number of cached
            // records

            var least_matching_entry = null;
            for( var k in nav.regex_match_cache ) {
                if( nav.regex_match_cache.hasOwnProperty( k ) ) {

                    if( term.indexOf( k ) == -1 ) {
                        continue;
                    }

                    if( least_matching_entry == null ) {
                        least_matching_entry = k;
                        continue;
                    }

                    if( nav.regex_match_cache[ k ].length < nav.regex_match_cache[ least_matching_entry ].length ) {
                        least_matching_entry = k;
                    }
                }
            }

            if( least_matching_entry != null ) {
                files = nav.regex_match_cache[ least_matching_entry ].map( function( a, i ) { return a.value; } );
            }
        }

        for( var i = 0; i < files.length; i++ ) {

            regex.lastIndex = 0;
        
            var matches = files[i].match( regex );

            if( matches != null && matches.length > 0 ) {

                var label = '';
                var value = files[i];
                var weight = 0;
        
                // TODO: Label manipulation to allow for
                // matching highlights.

                ret.push({
                    label:  value,
                    value:  value,
                    weight: weight
                });

                regex.lastIndex = 0;

            } 
        }

        // We cache our successes, as well as
        // our values, in the likely event that the
        // next keystroke will need 
        nav.regex_match_cache[ term ] = ret;

        return ret;

    },
    // From: http://jsfiddle.net/KpCt2/94/
    array_intersect: function(a, b) {
        return $.grep(a, function(i) {
            return $.inArray(i, b) > -1;
        });
    },
    // From: http://jsfiddle.net/KpCt2/94/
    array_not: function(a, b) {
        return $.grep(a, function(i) {
            return $.inArray(i, b) <= -1;
        });
    },
    local_search_tag:   function( term, files, tags ) {

        var ret = [];
        var tilde = /^~/;
        
        if( term.match( tilde  ) ) {
            tilde.lastIndex = 0;

            // Collect all available keys
            var all_tags = Object.keys( tags );
            var matched_tags = {};

            var components = term.trim().split( /\s+/ );

            var non_tag_terms = [];

            // Collect the tags matched by the components
            for( var i = 0; i < components.length; i++ ) {

                if( components[i].match( tilde ) ) {

                    if( tags[ components[i] ] != null && Array.isArray( tags[components[ i ] ] ) ) {
                        matched_tags[ components[i] ] = true;
                        continue;
                    }

                    var partial_matches = all_tags.filter( function( a ) {
                        return a.indexOf( components[i] ) != -1;
                    } );

                    for( var j = 0; j < partial_matches.length; j++ ) {
                        matched_tags[ partial_matches[ j ] ] = true;
                    }
                } else {
                    non_tag_terms.push( components[ i ] );
                }

                tilde.lastIndex = 0;
            }

            matched_tags = Object.keys( matched_tags );
            var intersecting_files = [];
            for( var i = 0; i < matched_tags.length; i++ ) {

                if( i == 0 ) {
                    // Pre-populate initial intersections
                    intersecting_files = tags[ matched_tags[ i ] ];

                } else {

                    // Detect intersections
                    if( intersecting_files.length <= 0 ) {
                        break;
                    }

                    intersecting_files = nav.array_intersect(
                        intersecting_files,
                        tags[ matched_tags[ i ] ]
                    );
                }
            }

            if( non_tag_terms.length > 0 ) {
                ret = nav.local_search_regex( 
                    non_tag_terms.join( '' ),
                    intersecting_files
                );
            } else {
                ret = intersecting_files.map( function( a ) {

                    var remaining = false;
                    if( nav.reverse_head_tags_cache[ a ] ) {
                        remaining = nav.array_not( 
                            nav.reverse_head_tags_cache[ a ],
                            matched_tags
                        );
                    }

                    return {
                        label:  matched_tags.join(',') + ': ' + a + ( remaining && remaining.length > 0 ? ' (' + remaining.join( ',' ) + ')' : '' ) ,
                        value:  a,
                        weight: 0
                    };
                } );

            }
        }
        
        return ret;
    },
    local_search:   function( term ) {

        // var regex = '(.*)';
        term = ( term == null || term == '' ? '' : term );
        term = term.toLowerCase();

        if( term.match( /^~/ ) ) {
            return nav.local_search_tag( 
                term, 
                nav.head_files_cache, 
                nav.head_tags_cache 
            );
        } else {
            return nav.local_search_regex(
                term, 
                nav.head_files_cache 
            );
        }
    },
    setup: function( head_files, head_tags ) {

        if( typeof( head_files ) != 'undefined' && head_files != null && head_files.length > 0 ) {
            nav.head_files_cache = head_files;
        }

        if( typeof( head_tags ) != 'undefined' && head_tags != null && Object.keys( head_tags ).length > 0 ) {
            nav.head_tags_cache = head_tags;

            nav.reverse_head_tags_cache = {};

            for (var tag in nav.head_tags_cache ) {
                
                for( var i = 0; i < nav.head_tags_cache[ tag ].length; i++ ) {

                    var f = nav.head_tags_cache[ tag ][i];

                    if( !nav.reverse_head_tags_cache[ f ] ) {
                        nav.reverse_head_tags_cache[ f ] = [];
                    }

                    if( !$.inArray( tag,  nav.reverse_head_tags_cache[ f ] ) > -1 ) {
                        nav.reverse_head_tags_cache[ f ].push( tag );
                    }
                }
            }
        }

        $( nav.quick_nav_id ).text_suggest( 'Search...' );
        $( nav.quick_nav_id ).focus( nav.toggle_quicknav_on );
        $( nav.quick_nav_id ).blur( nav.toggle_quicknav_off );


        var q = $('.quick-nav.handle');

        var cache = {};

        $( nav.quick_nav_id ).autocomplete({
            minLength: 1,
            delay:  50,
            source: function( request, response ) {
                var term = request.term.toLowerCase();

                if ( term in cache ) {
                    response( cache[ term ] );
                    return;
                }

                var local_result = nav.local_search( term );

                if( local_result.length > 0 ) {
                    response( local_result );
                    return;
                }

                if( !nav.remote_search_mutex ) {
                    nav.remote_search_mutex = true;
                    $.getJSON( "a_search.php", request, function( data, status, xhr ) {
                        cache[ term ] = data;
                        response( data );
                        nav.remote_search_mutex = false;
                    });
                }
            },
            messages: {
                noResults: "No matches",
                results: function() {}
            },
            select: function( event, ui ) {
                var t = event.originalEvent.originalEvent.originalEvent.target;

                // Accounting for if someone clicks on the a, or the span.match
                if( $( t ).is( '.new' ) || $( t ).parent().is( '.new' ) ) {
                    // Do nothing
                } else {
                    window.location = "index.php?file=" + ui.item.value;
                }
                return true;
            },
            appendTo: '.meta'
        })
        .data( "uiAutocomplete" )._renderItem = function( ul, item ) {
            return $( "<li></li>" )
                    // .data( "item.autocomplete", item )
                    .append( 
                        $( '<a/>' ).html(
                            item.label.replace( 
                                /@(.+?)@/g, 
                                function( w, m ) { 
                                    return '<span class="match">' + m + '</span>';
                                } 
                            )
                        ).addClass(
                            'normal' 
                        ), 
                        $( '<a/>' ).html(
                            'Open New'
                        ).addClass(
                            'new'
                        ).attr( 
                            'href',
                            'index.php?file=' + item.value
                        ).attr(
                            'target',
                            '_blank'
                        )
                    )
                    .appendTo( ul )
            ;
        };
        
        $(document).meta( 192, function( e ) {
            nav.toggle_quicknav();
            $( nav.quick_nav_id ).focus();
        });
    }
};

