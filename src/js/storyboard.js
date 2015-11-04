
var storyboard = {
    opts:   {
        max_columns:    3
    },
    get_indices:function( line ) {

        var ret = {
            flag:           null,
            subject:        null,
            description:    null
        };
    
        try {
            line = csv.str_getcsv( 
                line, 
                storyboard.opts 
            );

        } catch( ex ) {
            line = [ line ];
        }
    
        for( var i = 0; i < line.length; i++ ) {
            line[ i ] = line[ i ].trim();
            line[ i ] = line[ i ].replace( /^[#@!^]+|[#@!^]+$/g, '' );
            line[ i ] = line[ i ].toLowerCase();
        }
    
        for( var i = 0; i < line.length; i++ ) {
            if( line[ i ] == "flag" || line[ i ] == "flags" ) {
                ret.flag = i;
                continue;
            }
    
            if( line[ i ] == "subject" ) {
                ret.subject = i;
                continue;
            }
    
            if( line[ i ] == "description" ) {
                ret.description = i;
                continue;
            }
        }

        return ret;
    
    },
    parse_line: function( line, indices ) {
    
        var ret = {
            flag:           null,
            subject:        null,
            description:    null,
            disabled:       null,
            questioned:     null
        };
    
        try {
            line = csv.str_getcsv( line, storyboard.opts );
        } catch( ex ) {
            console.log( ex + ":" + line );
            line = [ line ];
        }
  
        
        ret.flag            = ( indices.flag != null    ? line[ indices.flag ]      : null );


        if( ret.flag != null && ret.flag != '' ) {
            ret.disabled    = ret.flag.indexOf( '!' )       != -1;
            ret.questioned  = ret.flag.indexOf( '?' ) != -1;
        }

        ret.subject         = ( indices.subject != null     ? line[ indices.subject ]       : null      );
        ret.description     = ( indices.description != null ? line[ indices.description ]   : line[ 0 ] );
    
        return ret;
    }
}
