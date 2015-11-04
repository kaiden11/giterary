<? renderable( $p ); ?>
<? if( $p['cleared'] && count( $p['cleared'] ) > 0 ) { ?>
<pre><code><? print_r( $p['cleared'] ); ?></code></pre>
<? } ?>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                Current Cache Keys
            </div>
            <div class="panel-body">
               <?= plural( count( $p['keys'] ), " cache key" ) ?>
            </div>
            <table class="table table-hover table-striped tabulizer">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Discriminator</th>
                    </tr>
                </thead>
                <tbody>
                    <? asort( $p['keys'] ); ?>
                    <? foreach( $p['keys'] as $i => $key ) { ?>
                        <?
                            $component = explode( ".", $key, 2 );
                            $tag = $component[0];
                            $discriminator = $component[1];
                        ?>
                        <tr>
                            <td>
                                <a 
                                    href="cache.php?clear_tag=<?= $tag ?>"
                                >
                                    <?= $tag ?>
                                </a>
                            </td>
                            <td>
                                <a 
                                    href="cache.php?clear_key=<?= $key ?>"
                                >
                                    <?= $discriminator ?>
                                </a>
                            </td>
                        </tr>
                    <? } ?>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
