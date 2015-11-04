var partition = {
    submit: function() {
        partition.collect();
        $('form#edit').submit();
    },
    collect: function() {
        var ret = {};

        var partition_names = $('div.partition-preview-output div.partition-boundary input[type=text]').map(
            function(k,v) {
                return $(v).val()
            }
        ).toArray();

        var boundary_offsets = $('div#partition div.partition-boundary.enabled input[type=hidden]').map(
            function(k,v) {
                return parseInt( $(v).val() );
            }
        ).toArray();

        $('#json').val(
            JSON.stringify(
                {
                    filename:               partition.filename,
                    new_collection_name:    partition.new_collection_name,
                    partition_names:        partition_names,
                    boundary_offsets:       boundary_offsets
                }
            )
        )
    },
    content: null,
    filename: null,
    new_collection_name: null,
    clear: function() {
        $('.partition.source div.partition-boundary.enabled').removeClass( 'enabled' );
        partition.to_preview();
    },
    names: {},
    entered_names:  {},
    update_name: function( a ) {
        partition.names[$(a).attr('name')] = $(a).val();
    },
    update_collection_name: function( a ) {
        partition.new_collection_name = $(a).val();
    },
    to_preview: function() {

        $('div#partition-preview-output').empty();

        var t = 0;

        var partition_content = [];
        var seen_partitions = false;

        $('div#partition div.partition-boundary.enabled').each( 
            function( k,v ) {

                // var name    = $(v).find('input.partition-name'  ).val();
                var offset  = parseInt( $(v).find('input.partition-offset').val() );
                
                partition_content.push( 
                    {
                        content: partition.content.substring( t, offset ),
                        start_offset: t,
                        end_offset: offset
                    }
                );

                seen_partitions = true;

                t = offset + 1;
            }
        );

        // Grab the last partition
        if( seen_partitions ) {
            partition_content.push(
                {
                    content: partition.content.substring( t ),
                    start_offset: t,
                    end_offset: partition.content.length-1
                }
            );
        }

        if( partition_content.length <= 0 ) {
            
            $('div#partition-preview-output').append(
                '<div><span>Select a partition boundary at left</span></div>'
            );
        }

        for( var i = 0; i < partition_content.length; i++ ) {
            var c = partition_content[i];

            var starts_with = c.content.substring( 0, 50 );
            var ends_with   = c.content.substring( c.content.length - 50, c.length );

            var potential_title = null;
            var matches = null;

            if( matches = c.content.match( /^#{1,6}(\s)*(.*)$/m ) ) {
                potential_title = matches[2].trim().replace( /[^a-zA-Z0-9]/g, '_' );
            }


            if( !partition.entered_names['partition_name_'+i] ) {
                partition.names['partition_name_'+i] = partition.new_collection_name + '/' 
                + ( potential_title ? potential_title : "NewPartition" + i );
            }

            $('div#partition-preview-output').append(
                '<div class="partition-boundary">' +
                    '<div class="partition-name">' +
                        'Partition name: <input type="text" name="partition_name_' + i + '" value="' + ( partition.names['partition_name_'+i] ) + '" onkeyup="javascript:partition.update_name( this )" size="' + partition.names['partition_name_'+i].length + '" />' +
                    '</div>' +
                    '<div class="starts-with">' +
                        starts_with +          
                    '</div>' +
                    '<div class="ends-with">' +
                        ends_with +
                    '</div>' +
                '</div>'
            );
        }
    },
    setup: function( contents, filename, new_collection_name ) {

        partition.content   = contents;
        partition.filename  = filename;
        partition.new_collection_name = new_collection_name;

        $('.partition.source div.partition-boundary').click(
            function() {
                $(this).toggleClass( 'enabled' );

                partition.to_preview();

                partition.collect();
            }
        );

        $('.partition.source div.partition-boundary input[type=text]').click( 
            function( e ) {
                e.stopPropagation();
            }
        );

        $('body.giterary')
            .on( 
                'keyup',    
                '.partition-preview .partition-name input',
                function( e ) {
                    partition.entered_names[ $( e.target ).attr( 'name' ) ] = $( e.target ).val();
                }
            )
            .on( 
                'keyup',    
                '.partition-preview .partition-preview-meta input#collection_name',
                function( e ) {
                    partition.to_preview();
                }
            )
        ;

        partition.to_preview();

    }
};


