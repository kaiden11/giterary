//
//  Markdown extension to mark dialog-ish looking segments to be marked
//  with an appropriate span/class.
//  "Something, something," she said. -> <span class="dialog">"Something, something,"</span> she said.

(function(){



    var dialogify = function(converter) {
        return [
            { 
                type: 'lang', 
                regex: /("\s)?("[^"]+?[-,\.?!]")(\s")?/gm,
                replace: function(match, behind, dialogish, ahead ) {

                    if( behind || ahead ) {
                        return ( typeof behind == 'undefined' ? '' : behind ) + 
                            dialogish + 
                            ( typeof ahead == 'undefined' ? '' : ahead );

                    } else {
                        return '<span class="dialog">' + dialogish + '</span>';
                    }
                }
            }
        ];
    };

    // Client-side export
    if (typeof window !== 'undefined' && window.Showdown && window.Showdown.extensions) { window.Showdown.extensions.dialogify = dialogify; }
    // Server-side export
    if (typeof module !== 'undefined') module.exports = dialogify;

}());
