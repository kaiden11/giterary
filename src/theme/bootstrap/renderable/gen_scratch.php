<? renderable( $p ); ?>
<?
$stash['css'][] = 'scratch.css'; 

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Scratch Contents
                (<span id="scratch-entry-count">
                </span>)
            </div>
            <div class="panel-body">
                <p>
                    The &quot;scratch&quot; area is temporary storage for things
                    like highlights, drafts, notes, etc. When dealing with information
                    that could potentially be lost, or easily discarded, or forgotten,
                    Giterary will copy content into the scratch area. This content is local
                    to your web browser, and will persist between page accesses, but is not
                    stored on the server. It will be lost if you clear your browser's
                    cache and/or history.
                </p>
                <div id="scratch-entries">
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function( storage, $ ) {
        var scratch = {
            show_highlight:   function( evt ) {
                var tr = $(this).closest( '.scratch-entry' );

                var key = tr.data( 'key' );

                if( key ) {
                    if( typeof( layout ) != 'undefined' && layout != null ) {
                        layout.modal(
                            'Highlight Contents',
                            storage.get_item( key )
                        );
                    }
                }
            },
            show_selected:   function( evt ) {
                var tr = $(this).closest( '.scratch-entry' );

                var key = tr.data( 'key' );

                if( key ) {
                    if( typeof( layout ) != 'undefined' && layout != null ) {
                        layout.modal(
                            'Selected Contents',
                            $( '<pre/>' ).append( '<code/>' )
                                .html( storage.get_item( key ) )
                        );
                    }
                }
            },

            show_draft:   function( evt ) {
                var tr = $(this).closest( '.scratch-entry' );

                var key = tr.data( 'key' );

                if( key ) {
                    if( typeof( layout ) != 'undefined' && layout != null ) {
                        layout.modal(
                            'Draft Contents',
                            $( '<pre/>' ).append( '<code/>' )
                                .html( storage.get_item( key ).draft_contents )
                        );
                    }
                }
            },
            highlight_row: function( tbody, v, item, timestamp ) {
                $(tbody).append( 
                    $( '<tr/>' )
                        .addClass( 'scratch-entry' )
                        .data( 'key', v )
                        .append( 
                            [
                                $( '<td/>' )
                                    .html( 
                                        v[1]
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        $( '<a/>' )
                                            .click( scratch.show_highlight )
                                            .html( 
                                                v.slice( 2 ).join( ',' ) 
                                            )
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        ( timestamp == null ? '?' : timestamp.toLocaleString() )
                                    )
                                ,
                                $( '<td/>' )
                                    .html( item.length )
                            ]
                        )
                );
            },
            selected_row: function( tbody, v, item, timestamp ) {
                $(tbody).append( 
                    $( '<tr/>' )
                        .addClass( 'scratch-entry' )
                        .data( 'key', v )
                        .append( 
                            [
                                $( '<td/>' )
                                    .html( 
                                        v[1]
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        $( '<a/>' )
                                            .click( scratch.show_selected )
                                            .html( 
                                                v.slice( 2 ).join( ',' ) 
                                            )
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        ( timestamp == null ? '?' : timestamp.toLocaleString() )
                                    )
                                ,
                                $( '<td/>' )
                                    .html( item.length )
                            ]
                        )
                );
            },

            draft_row: function( tbody, v, item, timestamp ) {
                $(tbody).append( 
                    $( '<tr/>' )
                        .addClass( 'scratch-entry' )
                        .data( 'key', v )
                        .append( 
                            [
                                $( '<td/>' )
                                    .html( 
                                        v[1]
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        $( '<a/>' )
                                            .click( scratch.show_draft )
                                            .html( 
                                                v.slice( 2 ).join( ',' ) 
                                            )
                                    )
                                ,
                                $( '<td/>' )
                                    .html( 
                                        ( timestamp == null ? '?' : timestamp.toLocaleString() )
                                    )
                                ,

                                $( '<td/>' )
                                    .html( item.draft_contents.length )
                            ]
                        )
                );
            },

            redraw: function() {
                var scratch_keys = [];
                if( ( scratch_keys = storage.search( 'Scratch' )).length <= 0 ) {
                    $( '#scratch-entries' ).html( 'No entries have been entered' );
                    $( '#scratch-entry-count' ).html( scratch_keys.length + ' scratch entries' );

                    return;
                }

                scratch_keys.sort(
                    function( a, b ) {
                        return storage.get_timestamp( a ) - storage.get_timestamp( b );
                    }
                );

                $( '#scratch-entry-count' ).html( scratch_keys.length + ' scratch entries' );

                var table = $( '<table/>' )
                    .append( 
                        $( '<thead/>' )
                            .append( 
                                $( '<tr/>' )
                                    .append( [
                                        $( '<th/>' )
                                            .html( 'Type' ),
                                        $( '<th/>' )
                                            .html( 'Key' ),
                                        $( '<th/>' )
                                            .html( 'Timestamp' ),
                                        $( '<th/>' )
                                            .html( 'Content Length' )
                                    ] )
                            )
                    )
                    .addClass( 'table' )
                    .addClass( 'table-hover' )
                    .addClass( 'table-striped' )
                ;

                var tbody = $( '<tbody/>' );

                table.append( tbody );

                $.each( scratch_keys, function( i, v ) {

                    var item = storage.get_item( v );
                    var timestamp = storage.get_timestamp( v );

                    if( v[1] == 'Highlights' ) {
                        scratch.highlight_row( tbody, v, item, timestamp );
                    }

                    if( v[1] == 'Drafts' ) {
                        scratch.draft_row( tbody, v, item, timestamp );
                    }

                    if( v[1] == 'Selected' ) {
                        scratch.selected_row( tbody, v, item, timestamp );
                    }

                } );

                $( '#scratch-entries' ).html (
                    table
                );

            },
            setup:  function() {
                if( typeof( storage ) == 'undefined' || storage == null || !storage.local_storage ) {
                    $( '#scratch-entries' ).html( 'Local storage is not available on your browser.' );
                    return;
                }

                scratch.redraw();

                $(window).on( 'storage', function() {
                    scratch.redraw();
                } );
            }
        };


        $(document).ready( function() {
            scratch.setup();
        } );
    })( storage, jQuery );
</script>
