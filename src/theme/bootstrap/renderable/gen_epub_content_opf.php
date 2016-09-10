<?php renderable( $p );?><?= '<?' ?>xml version="1.0"<?= '?>' ?>

<package version="2.0" xmlns="http://www.idpf.org/2007/opf" unique-identifier="<?= he( _epub_xml_name( $p['title'] ) ) ?>">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title><?= he( $p['title'] ) ?></dc:title>
        <dc:language>en</dc:language>
        <dc:identifier id="<?= he( _epub_xml_name( $p['title'] ) ) ?>" opf:scheme="ISBN">?</dc:identifier>
        <? foreach( $p['authors'] as $a ) { ?>
            <dc:creator opf:file-as="<?= he( $a ) ?>" opf:role="aut"><?= he( $a ) ?></dc:creator>
        <? } ?>
        <?if( $p['cover'] && git_file_exists( $p['cover'] ) ) { ?>
            <meta name="cover" content="cover_image" />
        <? } ?> 
    </metadata>
    
    <manifest>
        <?if( $p['cover'] && git_file_exists( $p['cover'] ) ) { ?>
            <?
                // Grab the mime type real quick
                $finfo = new finfo(FILEINFO_MIME);
                $cover_content_type = array_shift( explode( ";", $finfo->file( GIT_REPO_DIR . '/' . dirify( $p['cover'] ) ) ) );
            ?>
            <item 
                id="cover_image" 
                href="<?= he( path_to_filename( $p['cover'] ) )?>"
                media-type="<?= he( $cover_content_type ) ?>"
            />
            <item 
                id="cover"
                href="cover.xhtml"
                media-type="application/xhtml+xml"
            />

        <? } ?>
        <? foreach( $p['files']  as $f ) { ?>
            <item 
                id="<?= he( _epub_xml_name( $f['file'] ) ) ?>" 
                href="<?= he( $f['path'] ) ?>.xhtml" 
                media-type="application/xhtml+xml"
            />

        <? } ?>
        <? foreach( $p['images']  as $f ) { ?>
            <item 
                id="<?= he( _epub_xml_name( $f['file'] ) ) ?>" 
                href="<?= he( $f['file'] ) ?>" 
                media-type="<?= $f['mimetype'] ?>"
            />
        <? } ?>

        <? /*
        <item id="stylesheet" href="style.css" media-type="text/css"/>
        <item id="ch1-pic" href="ch1-pic.png" media-type="image/png"/>
        <item id="myfont" href="css/myfont.otf" media-type="application/x-font-opentype"/>
        */ ?>
        <item 
            id="ncx" 
            href="toc.ncx" 
            media-type="application/x-dtbncx+xml"
        />

        <? if( isset( $p['css'] ) && count( $p['css'] ) > 0 ) { ?>
            <? foreach( $p['css'] as $css ) { ?>
                <item 
                    id="<?= he( _epub_xml_name( $css ) ) ?>" 
                    href="<?= he( path_to_filename( $css ) ) ?>" 
                    media-type="text/css"
                />
            <? } ?>
        <? } ?>
    </manifest>
    
    <spine toc="ncx">
        <?if( $p['cover'] && git_file_exists( $p['cover'] ) ) { ?>
            <itemref idref="cover" linear="no" />
        <? } ?>
        <? foreach( $p['files'] as $f ) { ?>
            <itemref idref="<?= he( _epub_xml_name( $f['file'] ) ) ?>" />

        <? } ?>

    </spine>
    
</package>
