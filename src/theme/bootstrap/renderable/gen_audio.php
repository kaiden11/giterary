<? renderable( $p ) ?>
<div class="audio">
    <div>
        <span><?= he( undirify( $p['file'] ) ) ?></span>
    </div>
    <audio controls>
        <source src="raw.php?file=<?= urlencode( $p['file'] ) ?>" type="<?= $p['content_type'] ?>">
    </audio>
    <div>
        <span><a href="raw.php?file=<?= urlencode( $p['file'] ) ?>&download=yes">Download</a></span>
    </div>
</div>
