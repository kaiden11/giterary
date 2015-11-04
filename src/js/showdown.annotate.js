//
//  Markdown extension to allow for annotation syntax
//  {annotated text}[id]
//  {annotated text}(annotations)
//  {id}
//

(function( Showdown ){

    var annotation_storage = {
        annotation_ids: {}
    };

    var writeAnnotationTag = function(wholeMatch,m0,m1,m2,m3,m4,m5,m6,m7) {
            if (m7 == undefined) m7 = "";
            var whole_match         = m0;
            var annotation_text     = m2;
            var escaping            = m1;
            var annotation_id       = m3.toLowerCase();
            var annotation          = m4;
            var title               = m7;

            if( typeof escaping != undefined &&  escaping == '\\' ) {
                return whole_match.substring( 1 );
            }
    
            if ( annotation == "" ) {
                if (annotation_id == "") {
                        // lower-case and turn embedded newlines into spaces
                        annotation_id = annotation_text.toLowerCase().replace(/ ?\n/g," ");
                }
                // url = "#"+link_id; ???
    
                if (annotation_storage.annotation_ids[annotation_id] != undefined) {
                    annotation = annotation_storage.annotation_ids[annotation_id];
                    /*
                    if (g_titles[link_id] != undefined) {
                            title = g_titles[link_id];
                    }
                    */
                } else {
                    if (whole_match.search(/\(\s*\)$/m)>-1) {
                        // Special case for explicit empty url
                        annotation = "";
                    } else {
                        return whole_match;
                    }
                }
            }
    
            // url = escapeCharacters(url,"*_");
            var result = "<annotate>";
    
            result += annotation_text + "<comment>" + annotation + "</comment>" + "</annotate>";
    
            return result;
    }


    var annotate = function(converter) {
        return [

            /*
            // First, handle reference-style links: [link text] [id]
            text = text.replace(/(\[((?:\[[^\]]*\]|[^\[\]])*)\][ ]?(?:\n[ ]*)?\[(.*?)\])()()()()/g,writeAnchorTag);

            text = text.replace(/(\[((?:\[[^\]]*\]|[^\[\]])*)\]\([ \t]*()<?(.*?(?:\(.*?\).*?)?)>?[ \t]*((['"])(.*?)\6[ \t]*)?\))/g,writeAnchorTag);

            // Last, handle reference-style shortcuts: [link text]
            text = text.replace(/(\[([^\[\]]+)\])()()()()()/g, writeAnchorTag);
            */

             // Annotation defs are in the form: ^{id}: Annotation text...
            { 
                type: 'lang', 
                regex: /^[ ]{0,3}\{(.+)\}:[ \t]*\n?[ \t]*(.+)(?:\n+|(?=~0))/gm , 
                replace: function(match, annotation_id, annotation) {

                    annotation_id = annotation_id.toLowerCase();
                    annotation_storage.annotation_ids[annotation_id] = annotation; // Encode?

                    return '';
                }
            },
            // First, handle reference-style annotations: {annotated text} [id]
            { 
                type: 'lang', 
                regex: /((\\)?\{((?:\{[^\}]*\}|[^\{\}])*)\}[ ]?(?:\n[ ]*)?\[([^\[\]]*?)\])()()()()/g,
                replace: writeAnnotationTag
            },
            // Next, inline-style links: {annotated text}(notes)
            { 
                type: 'lang', 
                regex: /((\\)?\{((?:\[[^\}]*?\}|[^\{\}])*?)\}\([ \t]*()([^\)]+)[ \t]*\))/g,
                replace: writeAnnotationTag
            },
            // Last, handle reference-style shortcuts: {anchor id}
            { 
                type: 'lang', 
                regex: /((\\)?\{([^\{\}]+)\})()()()()()/g,
                replace: writeAnnotationTag
            }
        ];
    };

    // Client-side export
    if (typeof window !== 'undefined' && window.Showdown && window.Showdown.extensions) { window.Showdown.extensions.annotate = annotate; }
    // Server-side export
    if (typeof module !== 'undefined') module.exports = annotate;
    if (typeof Showdown !== 'undefined' ) Showdown.extensions.annotate = annotate;

}( Showdown ));
