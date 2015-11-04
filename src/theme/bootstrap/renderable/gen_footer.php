<? renderable( $p ) ?>
<div class="footer">
    <span class="pull-right">
        <a href="COPYRIGHT.txt">&copy; 2013</a>, <a href="LICENSE.txt">GPLv3</a>
        <?= PERF_STATS ? ', ' . perf_elapsed() . 'ms' : ''  ?>
    </span>
</div>
<!--
<?= ( PERF_STATS ? perf_print() : '' ) ?>
-->
