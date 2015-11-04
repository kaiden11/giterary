<? renderable( $p ) ?>
<?
$stash['css'][] = 'blame.css';
$stash['js'][]  = 'blame.js';

function user_color( $author ) {
    static $user_colors = array();

    if( !array_key_exists( $author, $user_colors ) ) {
        $background = '#' . substr( md5( $author ), 0, 6 );

        $foreground = '#';
        $tmp = '';
        for( $i = 1; $i < strlen( $background ); $i++ ) {
            $tmp .= $background[$i];

            if( strlen( $tmp ) < 2 ) {
                continue;      
            }

            $foreground .= sprintf( "%02x", (  256 - hexdec( $tmp ) ) );
            $tmp = '';
        }

        $user_colors[$author] = array( $background, $foreground );
    }

    return $user_colors[$author];
}

?>
<div class="blame">
    <nav class="navbar navbar-default navbar-fixed-bottom meta">
        <div class="container-fluid">
            <ul class="nav navbar-nav navbar-left activities" >
                <li>
                    <a 
                        href="index.php?file=<?= urlencode( $p['file'] ) ?>">
                        Back to file...
                    </a>
                </li>
                <li>
                    <div class="btn-group" data-toggle="buttons">
                        <label 
                            class="btn btn-default navbar-btn"
                            for="checkbox-enable-wrapping"
                        >
                            <input 
                                type="checkbox" 
                                class="checkbox-enable-wrapping" 
                                id="checkbox-enable-wrapping"
                            />
                            Wrap Lines
                        </label>
                    </div>
                <li>
                </div>
            </ul>
        </div>
    </nav>

    <div id="gen-blame" class="blame display">
        <table class="table table-striped table-hover table-condensed">
            <tr>
                <th>commit</th>
                <th>time</th>
                <th>author</th>
                <th>line</th>
            </tr>
            <?
            
            foreach( $p['blame'] as &$line ) { ?>
                    <? list( $bg, $fg ) = user_color( $line['author'] ); ?>
                    <tr class="<?= rotate( "alt1", "alt2" ) ?>">
                        <td style="border-right: 5px solid #<?= commit_excerpt( $line['commit'] ) ?>">
                            <a href="show_commit.php?commit=<?= $line['commit'] ?>" title="Show details on this commit." ><?= commit_excerpt( $line['commit'] ) ?></a>
                        </td>
                        <td>
                            <?= short_time_diff( $line['timestamp'], time() ) ?>
                        </td>
                        <td style="border-right: 5px solid <?= $bg ?>;">
                            <div class="blame-author">
                                <a href="history.php?author=<?= urlencode( $line['author'] ) ?>"><?= he( $line['author'] ) ?></a>
                            </div>
                        </td>
                        <td>
                            <div class="blame-line" title="Line #<?= $line['line_number'] ?>">
                                <samp><?= he( $line['line_in_question'] ) ?></samp>
                            </div>
                        </td>
                    </tr>
            <? } ?>
        </table>
    </div>
</div>

