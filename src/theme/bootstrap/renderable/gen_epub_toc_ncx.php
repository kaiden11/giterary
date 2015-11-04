<? renderable( $p ); ?><?= '<?' ?>xml version="1.0" encoding="UTF-8" <?= '?>' ?>

<!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN"
"http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">
 
<ncx version="2005-1" xml:lang="en" xmlns="http://www.daisy.org/z3986/2005/ncx/">
    <head>
        <!-- 
            The following four metadata items are required for all NCX documents,
            including those that conform to the relaxed constraints of OPS 2.0 
        -->
        <meta name="dtb:uid" content="?"/>     <!-- same as in .opf -->
        <meta name="dtb:depth" content="1"/>            <!-- 1 or higher -->
        <meta name="dtb:totalPageCount" content="0"/>   <!-- must be 0 -->
        <meta name="dtb:maxPageNumber" content="0"/>    <!-- must be 0 -->
    </head>
    <docTitle>
        <text><?= he( $p['title'] ) ?></text>
    </docTitle>
    
    <docAuthor>
        <text><?= he( join( ", ", $p['authors'] ) ) ?></text>
    </docAuthor>
    
    <navMap>
        <? $i = 1; ?>
        <? foreach( $p['files'] as $f ) { ?>
            <navPoint class="chapter" id="<?= he( _epub_xml_name( $f['file'] ) ) ?>" playOrder="<?= he( $i ) ?>">
                <navLabel>
                    <text><?= he( funcify( $f['title'], $p['epub_file'] ) ) ?></text>
                </navLabel>
                <content 
                    src="<?= he( $f['path'] ) ?>.xhtml" 
                />
            </navPoint>
            <? $i++ ?>
        <? } ?>
    </navMap>
</ncx>
