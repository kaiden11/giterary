var wordcount = {
    wordcount_regex: /\b([\w\']+)\b/gm,
    wordcount_helper: function( content ) {
    
        var word = null;
        var count = 0;
        
        while( ( word = wordcount.wordcount_regex.exec( content ) ) !== null ) {
            count++;
        }

        wordcount.wordcount_regex.lastIndex = 0;

        return count;
    }
};

onmessage = function( event ) {
    postMessage( wordcount.wordcount_helper( event.data ) );
};
