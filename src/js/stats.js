var stats = {
    meta_toggle:    function() {
        $('.stats.container').toggle_class( 'meta-on meta-off' );
    },
    setup:  function() {

        $('body.giterary').on( 'click', '.stats .meta.container .toggle a', stats.meta_toggle );
    }
};


$(document).ready( 
    function() {
        stats.setup();
    }
);
