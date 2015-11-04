var read = {
    furthest: 0,
    furthest_visible: 0,
    file: null,
    commit: null,
    bm: null,
    last_bm: null,
    $paragraphs: null,
    pos: function() {

        if( read.visible() > read.furthest_visible ) {
            read.furthest           = read.top();
            read.furthest_visible   = read.visible();
        }
    },
    report: function() {
        // console.log( 'Reporting bookmark position' );

        var this_bookmark = read.bm;

        if( 
            this_bookmark &&
            this_bookmark.scroll_offset != null &&
            this_bookmark.scroll_offset == 0
        ) {
            // console.log( 'Skipping bookmark save, no position' );
            return;
        }

        if( 
            read.last_bm != null &&
            this_bookmark.first_visible == read.last_bm.first_visible && 
            this_bookmark.scroll_offset == read.last_bm.scroll_offset 
        ) { 
            // console.log( 'No change since last bookmark update' );
            return;
        }

        var r = $.ajax( 
            "a_bookmark.php",
            {
                type: 'POST',
                data: {
                    'file':     read.file,
                    'commit':   read.commit,
                    'bookmark': JSON.stringify( this_bookmark )
                }
            }
        ).done(
            function( data ) { 

                if ( data.match(/^success$/)) {
                    var d = new Date();
                    $('#error').html( 
                        ''
                    );

                    read.last_bm = this_bookmark;

                } else {
                    $('#error').html( 
                        'Unable to save bookmark to server.' 
                    );
                }
            }
        ).fail(
            function() { 
                // Do nothing
                $('#error').html( 'Unable to save bookmark to server' );
            }
        );

    },
    persist: function() {

        if( typeof( storage ) == 'undefined' || storage == null ) {
            return;
        }

        if( typeof( read.file ) == 'undefined' || read.file == null || read.file == "" ) {
            return;
        }

        if( typeof( read.commit ) == 'undefined' || read.commit == null || read.commit == "" ) {
            return;
        }

        if( storage.local_storage ) {

            // console.log( 'Bookmarking: ' + read.furthest );
            var el_id = $( 
                read.$paragraphs.filter( '.visible' ).get( 0 ) 
            ).data( 'paragraph-id' );

            read.bm = {
                scroll_offset:  read.furthest,
                first_visible:  el_id
            }

            storage.set_item( 
                'Reading',
                'Bookmark',
                read.file,
                read.commit,
                read.bm
            );
        }
    },
    top: function() {

        // http://stackoverflow.com/questions/3464876/javascript-get-window-x-y-position-for-scroll
        var doc = document.documentElement;
        var ret = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

        return ret;
    },
    visible: function() {
        return read.top() + window.innerHeight;
    },
    progress: function() {

        var num_paragraphs = 0;
        var num_seen_paragraphs = 0;

        read.$paragraphs.each( function( k, v ) {

            v = $(v);
            num_paragraphs++;

            if( v.hasClass('seen') ) {
                num_seen_paragraphs++;
            }

            if( read.top() <= v.offset().top && v.offset().top <= read.visible() ) {
                v.addClass( 'visible' ); 
            } else  {
                v.removeClass( 'visible' ); 
            }
        });

        var percentage = '-';

        if( num_paragraphs > 0 ) {
            percentage = 100*(num_seen_paragraphs / num_paragraphs);
            percentage = Math.floor( percentage.toPrecision( 2 ) );
        }

        /*
        $( '#read-percentage' ).html(
            percentage
        );

        // http://stackoverflow.com/questions/2387136/cross-browser-method-to-determine-vertical-scroll-percentage-in-javascript
        $( '#scroll-percentage').html(
            Math.floor( (100*( read.top() / (document.body.clientHeight - window.innerHeight))).toPrecision( 2 ) ) + '%'
        );
        */

        $( '#read-percentage' )
            .html( percentage + '%' )
            .css( 'width', percentage + '%' )
        ;
    },
    mark: function() {


        read.$paragraphs.each( function( k, v ) {

            v = $(v);
            var offset = v.offset();
            
            // console.log( 'offset: ' + offset.top + ', furthest: ' + read.furthest );

            if( offset.top < read.furthest_visible ) {
                v.addClass( 'seen' );
            } else {
                v.removeClass( 'seen' );
            }
        } );

    },
    scroll_change: function( evt ) {

        // Update for current position
        read.pos();

        // Mark paragraphs as read/unread
        read.mark();

        // Update progress percentages
        read.progress();

        // Persist bookmark
        read.persist();

    },
    bookmark: function( ) {
        // Reset to current position
        read.furthest           = read.top();
        read.furthest_visible   = read.visible();

        $('p.recover').removeClass( 'recover' );

        read.persist();
        read.report();
        read.mark();
        read.progress();

        // Set the latest paragraph as 'recover'
        var p = read.$paragraphs.filter( function( k ) {
            return $(this).data( 'paragraph-id' ) == read.bm.first_visible
        });

        if( p.size() > 0 ) {
            // console.log( 'scrolling to: ' + furthest.first_visible );

            (function() {

                $( p.get( 0 ) ).addClass( 'recover' );
            }).defer();
        } 
    },
    recover: function() {

        var no_known = function() {
            if( read.top() > 0 ) {
                read.bookmark();
                read.progress();
            }
        };

        $('p.recover').removeClass( 'recover' );

        if( typeof( storage ) == 'undefined' || storage == null ) {
            no_known();
            return;
        }

        if( typeof( read.file ) == 'undefined' || read.file == null || read.file == "" ) {
            no_known();
            return;
        }

        if( typeof( read.commit ) == 'undefined' || read.commit == null || read.commit == "" ) {
            no_known();
            return;
        }

        var furthest = null;

        if( read.bm == null ) {

            if( storage.local_storage ) {

                furthest = storage.get_item( 
                    'Reading',
                    'Bookmark',
                    read.file,
                    read.commit
                );
            }
        } else {
            furthest = read.bm;
        }

        if( furthest != null ) {

            // console.log( 'Scrolling to: ' + furthest.scroll_offset );

            if( furthest.first_visible != null ) {
                var p = read.$paragraphs.filter( function( k ) {
                    return $(this).data( 'paragraph-id' ) == furthest.first_visible;
                });

                if( p.size() > 0 ) {
                    // console.log( 'scrolling to: ' + furthest.first_visible );

                    (function() {

                        $( p.get( 0 ) ).addClass( 'recover' );
                        $.scrollTo( p.get( 0 ) );
                        read.scroll_change();
                        // read.pos();
                        // read.mark();
                        // read.progress();
                    }).defer();
                    return;
                } else {
                    console.log( 'unable to find: ' + furthest.first_visible );
                }
            } else {
                console.log( 'furthest record missing first_visible' );
            }

            if( furthest.scroll_offset != null ) {
                // console.log( 'scrolling to offset: ' + furthest.scroll_offset );
                window.scrollTo( 
                    0,
                    furthest.scroll_offset
                );
                return;
            }
        } else {
            console.log( 'no furthest record for ' + read.file + ':' + read.commit );
        }

        no_known();

    },
    setup: function( commit, file, opts ) {
        // console.log( 'Read setup...' );

        read.file = file;
        read.commit = commit;

        read.$paragraphs = $( '.view.read p' );

        if( opts ) {
            if( opts.bookmark ) {
                // console.log( opts.bookmark );
                read.bm = opts.bookmark;
            }
        }

        read.$paragraphs.each( function( k, v ) {

            v = $(v);
            v.data( 
                'paragraph-id', 
                file + ':' + commit + ':' + k
            );

        });

        // Delayed "seen" updating
        $( document ).when_settled( 
            read.scroll_change,
            5000,
            null,
            'scroll'
        );

        // Immediate progress updating
        $( document ).on( 'scroll', read.progress );
        
        $( window ).resize( read.mark );

        $('#bookmark').click( read.bookmark );
        $('#recover').click( read.recover );

        read.recover();

        setInterval( 
            read.report,
            10*1000
        );

        // $.fn.when_settled = function( func, timeout, selector, events ) {

    }
};
