<? renderable( $p ); ?>
<?
// $stash['css'][] = 'scratch.css'; 

function context_highlight( $snippet, $context, $type ) {

    $snippet = trim( $snippet );

    if( $snippet == null || $snippet == "" ) {
        return $context;
    }

    $chars = str_split( $snippet );

    $chars = array_filter(
        $chars,
        function( $a ) {
            return trim( $a ) != "";
        }
    );

    $pattern = implode( 
        '\s*',
        array_map(
            function( $a ) {
                return preg_quote( $a );
            },
            $chars
        )
    );

    $pattern = "/$pattern/m";

    $count = 0;

    $context = preg_replace_callback( 
        $pattern,
        function( $m ) use( $type ) {
            return '<span class="highlight ' . ( $type ? he( strtolower( trim( $type ) ) ) : '' ) . '">' . $m[0] . '</span>';
        },
        $context
    );

    return $context;
}

uasort( 
    $p['snippets'],
    function( $a, $b ) {
        return $b['time'] - $a['time'];
    }
);

$time = time();

?>
<div class="row snippets">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Snippets Contents
                (<span id="snippet-count">
                    <?= plural( count( $p['snippets'] ), 'snippet' ) ?>
                </span>)
            </div>
            <div class="panel-body">
                <p>
                    The &quot;snippet&quot; area is storage for highlights that
                    you would like to review at a later date. A snippet is 
                    generated from either the "Readable" interface, or the main
                    document viewing interface after hitting the "Add to 
                    Snippets" button after highlighting a section of text.
                </p>
                <form method="post" action="snippet_action.php">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <label
                                        class="btn btn-info snippet-select-all"
                                    >
                                        <span 
                                            class="glyphicon glyphicon-ok-circle"
                                            title="Select/Deselect All"
                                        />
                                    </label>
                                </th>
                                <th>Snippet</th>
                                <th>Time</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>Commit</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?foreach( $p['snippets'] as $f => $snippet ) { ?>
                                <tr 
                                    data-row-id="<?= he( $f ) ?>"
                                >
                                    <td>
                                        <label
                                            class="btn btn-info snippet-selection snippet-select"
                                        >
                                            <input 
                                                type="checkbox" 
                                                name="snippets[]" 
                                                value="<?= he( $f ) ?>"
                                            />
                                            <span class="glyphicon glyphicon-check"></span>
                                            <span class="glyphicon glyphicon-unchecked"></span>
                                        </label>
                                    </td>

                                    <td>
                                        <?= isset( $snippet['from'] )
                                            ? "<kbd>" . $snippet['from'] . "</kbd>"
                                            : '' 
                                        ?>
                                        <samp>
                                            <?= context_highlight( 
                                                $snippet['snippet'], 
                                                $snippet['context'],
                                                $snippet['type']
                                            ) ?>
                                        </samp>
                                    </td>
                                    <td>
                                        <?= html_short_time_diff( 
                                            $snippet['time'], 
                                            $time,
                                            array( 'title' => strftime( '%Y-%m-%d %H:%M:%S', $snippet['time' ] ) )
                                        ) ?>
                                    </td>
                                    <td><?= linkify( '[[' . $snippet['file'] . '|' . basename( $snippet['file'] ) . ']]' ) ?></td>
                                    <td><?= he( $snippet['type'] ? $snippet['type'] : '-' ) ?></td>
                                    <td><code><?= commit_excerpt( $snippet['commit'] ) ?></code></td>
                                    <td>
                                        <div class="btn-group-vertical">
                                            <button 
                                                class="btn btn-success show-context"
                                                data-row-id="<?= he( $f ) ?>"
                                            >
                                                Show
                                            </button>
                                            <a 
                                                class="btn btn-danger"
                                                href="delete_snippet.php?snippet=<?= urlencode( $f ) ?>"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                        <textarea class="context" style="display: none;"><?= he( $snippet['context'] ) ?></textarea>
                                        <textarea class="snippet" style="display: none;"><?= he( $snippet['snippet'] ) ?></textarea>
                                    </td>

                                </tr>
                            <? } ?>
                        <tbody>
                    </table>
                    <div>
                        <div>
                            <div class="btn-group">
                                <button 
                                    class="btn btn-default dropdown-toggle" 
                                    type="button" 
                                    data-toggle="dropdown" 
                                    aria-haspopup="true" 
                                    aria-expanded="false"
                                >
                                    <span id="selected-snippets">No snippets selected</span>
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" style="min-width: 400px;">
                                    <li>
                                        <button 
                                            class="btn btn-danger" 
                                            type="submit" 
                                            name="delete" 
                                            value="delete" 
                                        >
                                            Delete
                                        </button>
                                        these snippets
                                    </li>
                                    <?php foreach( $p['userlist'] as $u ) { ?>
                                        <li>
                                            <button 
                                                class="btn btn-info" 
                                                type="submit" 
                                                name="give" 
                                                value="<?= he( $u ) ?>" 
                                            >
                                                Give 
                                            </button>
                                            these snippets to <?= he( $u ) ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style type="text/css">

    .snippets tr tr samp {
        opacity: 0.8;
    }
    .snippets .highlight {
        opacity: 1.0;
        color:              white;
        background-color:    #c9c;
    }

    .snippets .highlight.remove {
        background-color:   #f99;
    }

    .snippets .highlight.consistency {
        color:              #333;
        background-color:   #ff9;
    }

    .snippets .highlight.wording {
        color:              #f0f0f0;
        background-color:   #f09;
    }

    .snippets .highlight.spelling {
        color:              #333;
        background-color:   #f90;
    }

    .snippets .highlight.reminder {
        color:              #f0f0f0;
        background-color:   #66f;
    }

    .snippets .highlight.add {
        color:              #333;
        background-color:   #6ff;
    }

    .snippets .highlight.punctuation {
        color:              #333;
        background-color:   #3ff;
    }

    .snippets .highlight.formatting {
        color:              #f0f0f0;
        background-color:   #666;
    }

    .snippets .snippet-select input {
        display: none;
    }

    .snippets .snippet-selection .glyphicon-unchecked {
        display: inline;
    }

    .snippets .snippet-selection.active .glyphicon-unchecked {
        display: none;
    }


    .snippets .snippet-selection .glyphicon-check {
        display: none;
    }

    .snippets .snippet-selection.active .glyphicon-check {
        display: inline;
    }


</style>
<script type="text/javascript">
    (function( layout, $ ) {
        var snippets = {
            setup: function() {
                $( '.snippets tr .delete-context' ).on( 
                    'click',
                    function() {
                        window.location = 'delete_snippet.php?snippet=' + $(this).data( 'row-id' );
                    }
                );

                $( '.snippets .snippet-select-all' ).on(
                    'click',
                    function() {
                        $('.snippet-select input').trigger( 'click' );
                    }
                );

                $( '.snippets tr .snippet-select input, .snippet-select-all' ).on( 
                    'click',
                    function() {

                        if( $(this).parent().hasClass( 'active' ) ) {
                            $(this).parent().removeClass( 'active' );
                        } else {
                            $(this).parent().addClass( 'active' );
                        }

                        $('#selected-snippets').html(
                            $('.snippet-select input:checked').size() + ' snippet(s) selected'
                        );
                    }
                );

                if( typeof( layout ) != 'undefined' && layout != null && layout.modal != null ) {
                    $( '.snippets tr .show-context' ).on( 
                        'click',
                        function() {
                            var row_id = $(this).data( 'row-id' );

                            var row = $( '.snippets table tbody tr' ).filter( function() {
                                return $(this).data( 'row-id' ) == row_id;
                            } );

                            if( row.length > 0 ) {


                                var snippet = row.find( '.snippet' ).val();
                                var context = row.find( '.context' ).val()

                                layout.modal( 
                                    'Full Context',
                                    $('<div/>')
                                        .append(
                                            $( '<h3/>' ).html( 'Full Snippet' ),
                                            $( '<textarea/>' )
                                                .val( snippet )
                                                .addClass( 'form-control' )
                                                .attr( 'readonly', 'readonly' )
                                                .attr( 'rows', '10' )
                                            ,
                                            $( '<h3/>' ).html( 'Captured Context' ),
                                            $( '<textarea/>' )
                                                .val( context )
                                                .addClass( 'form-control' )
                                                .attr( 'readonly', 'readonly' )
                                                .attr( 'rows', '10' )
                                        )

                                );
                            }
                        }
                    );
                }
            }
        };

        $(document).ready( function() {
            snippets.setup();
        } );
    })( layout, jQuery );
</script>
