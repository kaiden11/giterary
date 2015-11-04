var giterary_sharejs = {
    toggle_element: null,
    status_element: null,
    cm:             null,
    sharejs_url:    null,
    file:           null,
    opts:           null,
    sharejs_doc:    null,
    active:         false,
    setup:  function( toggle_element, status_element, cm, sharejs_url, file, opts ) {
        console.log( "Begin init sharejs." );
       
        if( ! sharejs ) {
            console.log( "ShareJS not loaded." );
            return;
        }

        if( !cm ) {
            console.log( 'CodeMirror editor reference is invalid.' );
            return;
        }

        if( !sharejs_url ) {
            console.log( 'ShareJS URL appears invalid.' );
            return;
        }

        if( !file ) {
            console.log( 'File appears invalid:' + file );
            return;
        }

        giterary_sharejs.toggle_element =   toggle_element;
        giterary_sharejs.status_element =   status_element;
        giterary_sharejs.cm             =   cm;
        giterary_sharejs.sharejs_url    =   sharejs_url;
        giterary_sharejs.file           =   file;
        giterary_sharejs.opts           =   opts;

        giterary_sharejs.active         =   false;


        $( giterary_sharejs.toggle_element ).click(
            function() {
                if( giterary_sharejs.active ) {
                    giterary_sharejs.off();
                    giterary_sharejs.active = false;

                } else {
                    giterary_sharejs.on();
                    giterary_sharejs.active = true;
                }

                $( giterary_sharejs.status_element ).html( ( giterary_sharejs.active ? "Active" : "Inactive" ) );
            }
        );

        return;

    },
    on:  function( ) {
        console.log( "Begin on sharejs." );
       

        var ret = sharejs.open(
            giterary_sharejs.file,
            'text', 
            giterary_sharejs.sharejs_url,
            function(error, doc) {
                giterary_sharejs.sharejs_doc = doc; // Ugly, but...

                doc.attach_cm( 
                    giterary_sharejs.cm,
                    true // Keep editor contents
                );
            }
        );

        console.log( ret );

        console.log( "End on sharejs." );

    },
    off: function() {
        giterary_sharejs.sharejs_doc.detach_cm();
    }
};
