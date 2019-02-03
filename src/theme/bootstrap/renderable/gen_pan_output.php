<?php
renderable( $p );

$stash['css'][] = 'pandoc.css';





?><?php foreach( $p['pan']['files'] as $file ) { ?>
<?= _clean_file( $file ) ?>
<?php } ?>

