var nav = {

    toggle_quicknav: function() {
        var q = $('.meta.pane .quick-nav.container');

        if( q.hasClass('quick-nav-on') ) {
            $('#quick-nav').blur();
            q.removeClass( 'quick-nav-on' );

        } else {
            q.addClass( 'quick-nav-on' );
            $('#quick-nav').focus();
        }

        // q.toggle_class('quick-nav-on');
        
    },
    setup: function() {
        $('body').on( 'click', '.navigation .activities .quick input', nav.toggle_quicknav );


        if( navigator.platform.indexOf( "Mac" ) > -1 ) {
            $("#term").text_suggest( "ctrl-opt-s..." );
        } else {
            if( navigator.platform.indexOf( "Win" ) > -1 ) {
                $("#term").text_suggest( "alt-shift-s..." );
            } else {
                $("#term").text_suggest( "alt-shift-s..." );
            }
        }

        var q = $('.quick-nav.container');

        var cache = {};

        $("#quick-nav").autocomplete({
            minLength: 2,
            source: function( request, response ) {
                var term = request.term.toLowerCase();

                if ( term in cache ) {
                    response( cache[ term ] );
                    return;
                }

                $.getJSON( "a_search.php", request, function( data, status, xhr ) {
                    cache[ term ] = data;
                    response( data );
                });
            },
            messages: {
                noResults: null,
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
            appendTo: '.quick-nav.container'
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
        });

        $('#quick-nav').blur( function() {
            q.removeClass("quick-nav-on");
        });

    }
};


$(document).ready(
    function() {
        nav.setup();
        


    }
);
