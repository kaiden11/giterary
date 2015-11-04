<? renderable( $p ) ?>
<? if( $p['metadata'] ) { ?>
    <? if( $p['metadata']['comment'] ) { ?>
<div class="image-comment">
        <span><?= $p['metadata']['comment'] ?></span>
</div>
    <? } ?>
<? } ?>
<?= funcify(
    '[[image:file=' . $p['file'] . '|' . undirify( $p['file'] ) . ']]'
) ?>
