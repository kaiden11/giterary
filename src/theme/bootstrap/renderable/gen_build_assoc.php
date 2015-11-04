<? renderable( $p );?>
<ul>
<? foreach( $p['associations'] as $assoc_type => $files ) ?>
    <li>
        Rebuilt <?= plural( count( $files ), "$assoc_type association", "s" ) ?>
    </li>
</ul>
