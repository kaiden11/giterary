
( function( Annotator, $ ) {
    if( !Annotator ) {
        console.log( "Annotator not loaded" );
        return;
    }

    Annotator.Plugin.Draft = function( element, opts ) {
        var plugin = {};

        plugin.pluginInit = function() {

            var warn_about_drafts = function( annotation ) {
                
                console.log( 'warning about drafts' );
                // console.log( annotation );
                $('.annotator-notice').addClass( 'annotator-notice-show' ).html( 
                    'Your annotation is saved in your drafts. <a href="drafts.php">Save your draft to save your annotation!</a>' 
                );

                setTimeout(
                    function() {
                        console.log( 'hide warning' );
                        $('.annotator-notice').removeClass( 'annotator-notice-show' ).html( '' );
                    },
                    5000
                );
            };

            this.annotator.subscribe( 'annotationCreated', warn_about_drafts);
            this.annotator.subscribe( 'annotationUpdated', warn_about_drafts);
            this.annotator.subscribe( 'annotationDeleted', warn_about_drafts);
        };

        return plugin;
    };

})( Annotator, jQuery );
