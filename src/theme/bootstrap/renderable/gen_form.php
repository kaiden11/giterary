<? renderable( $p ); ?>
<?

# usort ( $p['drafts'], "epoch_sort" );

# $stash['css'][] = 'new.css';

$file       = file_or( $p['file'], false );
$template   = file_or( $p['template'], false );

if( $file !== false ) { $file = undirify( $file ); }
if( $template !== false ) { $template = undirify( $template ); }

?>

<div class="form container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            Form for a new document
        </div>
        <div class="panel-body">
            <p 
                
            >
                Based on the tags and meta headers of the selected template, 
                create a new file using the autocompleting form below.
            </p>
            <form 
                id="new-form"
                action="edit.php"
                method="post"
                class="form-group"
            >
                <div class="col-md-4">
                    <fieldset>
                        <legend>Selected Template</legend>
                        <input 
                            class="form-control" 
                            type="text" 
                            name="template" 
                            value="<?= he( $template ) ?>"
                            readonly="readonly"
                        />
                        <span class="help-block">
                            This is the template you are using to create a new file.
                        </span>

                    </fieldset>
                </div>
                <div class="col-md-4">
                    <fieldset>
                        <legend>New File Path</legend>
                        <input 
                            type="text" 
                            name="file" 
                            class="form-control" 
                            id="new_file_path"
                            data-original="<?= he( $file ) ?>"
                            value="<?= he( $file ) ?>"
                        />
                        <span class="help-block">
                            Enter the new path for the file you wish to create (do not leave as the suggested value).
                        </span>

                    </fieldset>
                </div>
                <?php if( count( $p['selected_tags'] ) > 0 ) { ?>
                    <div class="col-md-4">
                        <fieldset>
                            <legend>Tags</legend>
                            <div>
                                <select 
                                    id="tags" 
                                    class="form-control"
                                    style="height: 100%"
                                    multiple

                                >
                                <?php foreach( $p['selected_tags'] as $t ) { ?>
                                    <option 
                                        value="<?= he( $t ) ?>"
                                        selected
                                    ><?= he( $t ) ?></option>
                                <?php } ?>
                                </select>
                                <span class="help-block">
                                    Select/deselect a tag from the list above to include as part of the 
                                    new document.
                                </span>
                            </div>
                        </fieldset>
                    </div>
                <?php } ?>

                <?php if( count( $p['selected_meta'] ) > 0 ) { ?>
                    <div class="col-md-12">
                        <fieldset>
                            <legend>Meta</legend>
                            <div>
                                <span class="help-block">
                                    Enter a value for the meta headers below (or leave blank)
                                </span>
                                <?php foreach( $p['selected_meta'] as $k => $v ) { ?>
                                    <div>
                                        <label>
                                            <?= he( $k ) ?>
                                            <input
                                                type="text"
                                                class="form-control meta"
                                                data-meta="<?= he( $k ) ?>"
                                                value="<?= he( implode( ",", $v ) ) ?>"
                                            >
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>
                    </div>
                <?php } ?>

                <div class="col-md-12">
                    <input
                        type="submit"
                        class="btn btn-success"
                        value="Create!"
                    />
                    <span class="help-block">
                        When you hit "Create", you will be put into the editor with your
                        selected/entered tags and meta header values replaced in your 
                        template.
                    </span>
                </div>
                <div style="display: none;">
                    <textarea
                        class="form-control"
                        id="original_edit_contents"
                    ><?= he( $p['contents'] ) ?></textarea>
                    <textarea

                        class="form-control"
                        id="edit_contents"
                        name="edit_contents"
                    ></textarea>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    var form = {
        tag_regex:  /^(~[a-zA-Z_0-9]+)\s*$/,
        meta_regex: /^((%([^%:]+?):(\s*))([^\s]+?.*)?)$/,
        directories: <?= json_encode( $p['directories'] ) ?>,
        all_meta: <?= json_encode( $p['all_meta'] ) ?>,
        update: function() {

            var tags = $( '#tags' );
            var meta = $( 'input.meta' );

            var selected_tags = [];
            var entered_meta = {};

            if( tags.size() > 0 ) {
            
                if( tags.val() == null ) {
                    selected_tags = [];
                } else {
                    selected_tags = tags.val();
                }
            }

            if( meta.size() > 0 ) {
                meta.each( function( k, v ) {
                    entered_meta[ $(this).data( 'meta' ) ] = $(this).val();
                } );
            }

            form.replace( selected_tags, entered_meta );

        },
        replace: function( selected_tags, entered_meta ) {


            var contents = $( '#original_edit_contents' ).val();

            var new_contents = '';

            contents.split( /\r?\n/ ).forEach( function( v, i ) {

                form.tag_regex.index = form.meta_regex.index = 0;

                var match = null;

                // Test for tag
                if( match = form.tag_regex.exec( v ) ) {

                    if( $.inArray( match[1], selected_tags ) == -1 ) {
                        return;
                    }
                }

                match = null;

                if( match = form.meta_regex.exec( v ) ) {

                    if( entered_meta[ match[ 3 ] ] ) {
                        new_contents = new_contents + match[2] + entered_meta[ match[3] ] + '\r\n';
                        return;
                    }
                }

                new_contents = new_contents + v + '\r\n';
            } );

            
            $( '#edit_contents' ).val( new_contents );
        },
        setup: function() {

            $( '#tags' ).on( 'change', form.update )

            $( 'input.meta' ).on( 'change keyup autocompletechange', form.update );

            $('#new_file_path').autocomplete( 
                { source: form.directories } 
            );

            $('input.meta' ).each( function( i, v ) {

                var e = $(v);
            
                e.autocomplete( { 
                    minLength:  0,
                    source: function( request, response ) {

                        var suggested_values = [];
                        var m = null;
                        if( m = form.all_meta[ e.data( 'meta' ) ] ) {

                            for( var k in m ) {
                               if( m.hasOwnProperty(k) ) {
                                    for( var i = 0; i < m[k].length; i++ ) {

                                        if( request.term.length <= 0 ) {
                                            suggested_values.push( m[k][i] );
                                        } else {

                                            if( m[k][i].indexOf( request.term ) != -1 ) {
                                                suggested_values.push( m[k][i] );
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        response( $.unique( suggested_values ).sort() );

                    } 
                } );
            } );

            $( 'form#new-form' ).submit(
                function( evt ) {

                    var new_file = $(this).find( '#new_file_path' );

                    if( new_file ) {
                        if( $( new_file ).val() == new_file.data( 'original' ) ) {

                            evt.stopPropagation();
                            new_file
                                .parent().parent()
                                .addClass( 'has-error' )
                            ;

                            new_file
                                .focus()
                            ;
                            // alert( 'Please enter a new name for the file you wish to be created.' );
                            return false;
                        }
                    }
                }
            );


            setTimeout(
                form.update,
                0
            );
        }
    };

    $(document).ready( form.setup );

</script>
