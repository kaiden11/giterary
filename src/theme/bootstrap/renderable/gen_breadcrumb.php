<? 
renderable( $p );
function __breadcrumb_li( $f, $clazz = null ) {
    $b = basename( $f );
    $fd = dirify( $f );

    $class_translation = array(
        'recent'    =>  'warning',
        'visited'   =>  'info'
    );

    $clazz = ( $clazz != null ? $clazz : '' );

    if( $class_translation[ $clazz ] ) {
        $clazz .= ' ' . $class_translation[ $clazz ];
    }

    $head_commit        = git_file_head_commit( dirify( $fd ) );
    $show               = null;
    $parent_commit      = null;
    $show_diff_links    = false;

    if( commit_or( $head_commit, false ) !== false ) {
        $show_diff_links = true;

        $show = git_show( $head_commit );

        $parent_commit = $show['parent_commit'];
    }

    $link       = undirify( $f );
    $display    = false;

    # $show = git_show( $hc );
    
    if( $b == TALK_PAGE ) {
        $display = "Talk:" . undirify( 
            basename( 
                dirname( 
                    $f 
                )
            ),
            true
        );
    }

    if( $b == STORYBOARD_PAGE ) {
        $display = "Storyboard:" . undirify( 
            basename( 
                dirname( 
                    $f 
                )
            ),
            true
        );
    }


    if( $b == ANNOTATORJS_FILE ) {
        $display = "Anno:" . undirify( 
            basename( 
                dirname( 
                    $f 
                ) 
            ),
            true
        );
    }

    /*
    $options = array();

    if( $show_diff_links ) {
        $clazz .= ' dropdown';

        $options[] = '<li><a 
            title="Different with previous commit" 
            class="diff" 
            href="diff.php?commit_before=' 
                . urlencode( $parent_commit ) 
                . '&commit_after=' 
                . urlencode( $head_commit ) 
                . '&file=' . urlencode( $f ) 
                . '&plain=yes'
                .  '">Difference with previous commit</a></li>'
        ;
    }
    */

    $linkified = linkify( 
        (
            $display !== false 
            ?  '[[' . $link . '|' . $display . ']]'
            :  '[[' . $link . '|' . $link . ']]'
        ),
        array(
            'minify'  =>  false
        )
    );
    
    return "<li class=\"$clazz\">" . 
        $linkified
    . "</li>";
}

?>
<ul class="nav navbar-nav btn-toolbar recent-files">
    <li class="drop-down btn-group ">
        <button
            class="btn btn-default navbar-btn dropdown-toggle"
            data-toggle="dropdown"
            title="<?= plural( count( $p['files'] ), "Recent page", "s" ) ?>"
        >
            <span class="glyphicon glyphicon-book"></span>
            <span class="sr-only">
                <?= plural( count( $p['files'] ), "Recent page", "s" ) ?>
            </span>
            <span class="badge"><?= count( $p['files'] ) ?></span>
            <b class="caret"></b>
        </button>
        <ul class="dropdown-menu">
            <? $class = ""; ?>
            <? foreach( $p['files'] as $f ) { ?>
                <? if( $class != $f['class'] ) { ?>
                    <li role="presentation" class="dropdown-header"><?= $f['class'] ?></li>
                    <? $class = $f['class'] ?>
                <? } ?>
                <?= __breadcrumb_li( $f['file'], $f['class'] ) ?>
            <? } ?>
                <li role="presentation" class="divider"></li>
                <li>
                    <a 
                        href="clear_breadcrumb.php?redirect=<?= ( urlencode( $_SERVER['REQUEST_URI'] ) ) ?>" 
                        title="Clear Breadcrumb History"
                    >
                        Clear History
                    </a>
                </li>
        </ul>
    </li>
</ul>
