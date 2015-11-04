<? renderable( $p ) ?>
<div class="help">
<?
$extension = pathinfo( $p['file'],  PATHINFO_EXTENSION );

?>
    <? if( in_array( $extension, array( "", "tl", "textile" ) ) ) { ?>
        <div id="quickref">

            <h2>Textile Syntax (<a href="http://textile.thresholdstate.com/">more</a>)</h2>
            <h3>Phrase modifiers:</h3>
            <p>
            <em>_emphasis_</em><br>
            <strong>*strong*</strong><br>
            <i>__italic__</i><br>
            <b>**bold**</b><br>
            <cite>??citation??</cite><br>
            -<del>deleted text</del>-<br>
            +<ins>inserted text</ins>+<br>
            ^<sup>superscript</sup>^<br>
            ~<sub>subscript</sub>~<br>
            <span>%span%</span><br>
            <code>@code@</code><br>
            </p>
            
            <h3>Block modifiers:</h3>
            <p>
            <b>h<i>n</i>.</b> heading<br>
            <b>bq.</b> Blockquote<br>
            <b>fn<i>n</i>.</b> Footnote<br>
            <b>p.</b> Paragraph<br>
            <b>bc.</b> Block code<br>
            <b>pre.</b> Pre-formatted<br>
            <b>#</b> Numeric list<br>
            <b>*</b> Bulleted list<br>
            </p>
            
            <h3>Links:</h3>
            <p>
            "linktext":http://…<br>
            </p>
            
            <h3>Punctuation:</h3>
            <p>
            <b>"quotes"</b> → “quotes”<br>
            <b>'quotes'</b> → ‘quotes’<br>
            <b>it's</b> → it’s<br>
            <b>em -- dash</b> → em — dash<br>
            <b>en - dash</b> → en – dash<br>
            <b>2 x 4</b> → 2 × 4<br>
            <b>foo(tm)</b> → foo™<br>
            <b>foo(r)</b> → foo®<br>
            <b>foo(c)</b> → foo©<br>
            </p>
            
            <h3>Attributes:</h3>
            <p>
            (class)<br>
            (#id)<br>
            {style}<br>
            [language]<br>
            </p>
            
            <h3>Alignment:</h3>
            <p>
            &gt; right<br>
            &lt; left<br>
            = center<br>
            &lt;&gt; justify<br>
            </p>
            
            <h3>Tables:</h3>
            <p>
            |_. a|_. table|_. header|<br>
            |a|table|row|<br>
            |a|table|row|<br>
            </p>
            
            <h3>Images:</h3>
            <p>
            !imageurl!<br>
            !imageurl!:http://…<br>
            </p>
            
            <h3>Acronyms:</h3>
            <p>
            ABC(Always Be Closing)<br>
            </p>
            
            <h3>Footnotes:</h3>
            <p>
            See foo[<i>1</i>].<br>
            <br>
            fn1. Foo.<br>
            </p>
            
            <h3>Raw HTML:</h3>
            <p>
            ==no &lt;b&gt;textile&lt;/b&gt;==<br>
            <br>
            notextile. no &lt;b&gt;textile<br>
            here&lt;/b&gt;<br>
            </p>
            
            <h3>Extended blocks:</h3>
            <p>
            bq.. quote<br>
            &nbsp;<br>
            continued quote<br>
            &nbsp;<br>
            p. paragraph<br>
            </p>
        
        </div>        
    <? } ?>



</div>
