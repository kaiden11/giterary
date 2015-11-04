<? renderable( $p ) ?>
<div>
    Generated a new collection at <?= linkify( '[[' . $p['new_collection_name'] . ']]' ) ?>.
</div>
<div>
    <span>New collection consists of the following parts:</span>
    <ul>
    <? foreach( $p['partition_names'] as $n ) { ?>
        <li><?= linkify( "[[$n]]" ) ?></li>
    <? } ?>
    </ul>
</div>
