<? renderable( $p ) ?>
<? perf_enter( 'render.gen_history' ); ?>
<?
# $stash['css'][] = 'simpler.v2.sequence.css';
$stash['css'][] = 'history.css';
$stash['js'][]  = 'history.js';

function pager_url( $file, $num, $skip ) {
    $ret = 'history.php?';

    if( file_or( $file, false ) !== false ) {
        $ret .= "file=" . urlencode( $file ) . "&";
    }

    $ret .= "skip=" . urlencode( $skip );

    return $ret;
}

function number_to_helper_class( $num ) {
    $translation = array(
        'default',
        'primary',
        'success',
        'info',
        'warning',
        'danger'
    );

    return $translation[ $num % count( $translation ) ];

}

function commit_pages( $commit ) {
    if( !is_array( $commit['pages'] ) || count( $commit['pages'] ) <= 0 ) {
        return '<span>None</span>';
    } else {
        if( is_array( $commit['pages'] ) &&  count( $commit['pages'] ) == 1 ) {
            $exists = git_file_exists( join( '', $commit['pages'] ) );

            return '
                <div class="filename-box">
                    <a class="' . ( !$exists ? "edit" : "" ) . '" title="'
                        . undirify(
                            join(
                                '',
                                $commit['pages']
                            )
                        )
                        . '" href="index.php?file='
                        . undirify(
                            join(
                                '',
                                $commit['pages']
                            )
                        )
                        . '">' .
                        page_linktext( join('', $commit['pages'] ) )
                        . '
                    </a>
                </div>
            ';

        } else {

            $ret = '';
            foreach( $commit['pages'] as $page ) {
                $ret .= '
                    <li>
                        <a
                            href="index.php?file=' . undirify( $page ) . '"
                        >
                            ' . page_linktext( $page ) . '
                        </a>
                    </li>'
                ;
            }

            return '
                <div class="btn-group">
                    <button
                        class="btn btn-default btn-xs dropdown-toggle"
                        data-toggle="dropdown"
                    >
                        ' . plural(count($commit['pages'] ), "page" ) . '
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        ' . $ret . '
                    </ul>
                </div>
                '
             ;
        }
    }
}


function page_linktext( $page ) {
    if( ASSOC_ENABLE && has_directory_prefix( ASSOC_DIR, $page )  ) {
        return '(' .
            basename(
                undirify(
                    assoc_file_denormalize(
                        basename(
                            $page
                        )
                    )
                )
            )
            . ')'
        ;
    }

    if( ALIAS_ENABLE && has_directory_prefix( ALIAS_DIR, $page ) ) {
        return '(' .
            basename(
                undirify(
                    alias_file_denormalize(
                        basename(
                            $page
                        )
                    )
                )
            )
            . ')'
        ;
    }


    if( basename( $page ) == TALK_PAGE ) {
        return "(Talk:" .
            basename(
                dirname(
                    undirify(
                        $page
                    )
                )
            ) . ")"
        ;
    }

    if( basename( $page ) == STORYBOARD_PAGE ) {
        return "(Storyboard:" .
            basename(
                dirname(
                    undirify(
                        $page
                    )
                )
            ) . ")"
        ;
    }


    if( basename( $page ) == ANNOTATORJS_FILE ) {
        return "(Anno:" .
            basename(
                dirname(
                    undirify(
                        $page
                    )
                )
            ) . ")"
        ;
    }

    return basename(
        undirify(
            $page
        )
    );
}

function diff_url( $commit_before, $commit_after, $pages, $plain ) {
    return 'diff.php?commit_before=' . urlencode( $commit_before ) .
        '&commit_after=' . urlencode( $commit_after ) .
        ( $plain == "yes" ? '&plain=yes' : '' ) .
        ( is_array( $pages ) && count( $pages ) == 1 ? '&file=' . urlencode( undirify( array_shift( $pages ) ) ) : '' ) .
        ''
    ;
}

function diff_anchor( $display, $commit_before, $commit_after, $pages, $plain, $title ) {

    $url = diff_url( $commit_before, $commit_after, $pages, $plain );

    return '<a href="' . $url .'" title="' . he( $title ) . '">' . he( $display ) . '</a>';
}



?>
<div class="history">
    <nav class="navbar navbar-default navbar-fixed-bottom meta">
        <div class="container-fluid">
            <?
                $next_skip = $p['skip'] + count( $p['history'] );
                $prev_skip = max( $p['skip'] - $p['num'], 0 );
            ?>
            <ul class="nav navbar-nav navbar-left activities" >
                <li class="btn-group">
                    <button
                        class="btn btn-default navbar-btn clickable"
                        value="<?= pager_url( $p['file'], $p['num'], $prev_skip ) ?>">
                        &lt;&lt;
                    </button>
                </li>
                <li class="dropdown">
                    <button
                        class="btn btn-default navbar-btn dropdown-toggle"
                        data-toggle="dropdown"
                    >
                        Options
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-form">
                        <? if( ASSOC_ENABLE ) { ?>
                        <li id="assoc-list-item">
                            <span class="nav-checkbox">
                                <label for="checkbox-enable-assoc">
                                    <input
                                        type="checkbox"
                                        class="checkbox-enable-assoc"
                                        id="checkbox-enable-assoc"
                                        title="Show hidden page association related commits"
                                    />
                                    Show <kbd>A</kbd>ssociations
                                </label>
                            </span>
                        </li>
                        <? } ?>
                        <? if( ALIAS_ENABLE ) { ?>
                            <li id="alias-list-item">
                                <span class="nav-checkbox">
                                    <label for="checkbox-enable-alias">
                                        <input
                                            type="checkbox"
                                            class="checkbox-enable-alias"
                                            id="checkbox-enable-alias"
                                            title="Show hidden page alias related commits"
                                        />
                                        Show A<kbd>l</kbd>iases
                                    </label>
                                </span>
                            </li>
                        <? } ?>
                    </ul>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right activities" >
                <li>
                    <button
                        class="btn btn-default navbar-btn clickable"
                        value="<?= pager_url( $p['file'], $p['num'], $next_skip ) ?>">
                        &gt;&gt;
                    </button>
                </div>
            </ul>
        </div>
    </nav>

    <div id="gen-history" class="history container-fluid meta-off">
        <div class="row">
            <div class="history display col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-condensed">
                        <thead>
                            <tr>
                                <th class="before-after-btn">
                                    &Delta; between
                                </th>
                                <th class="commit-btn">
                                    commit / diff
                                </th>
                                <th class="author">author</th>
                                <th class="filename">page(s)</th>
                                <th class="message">message</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?

                    $count = 0;
                    $latest_commit = 'HEAD';
                    $second_latest_commit = 'HEAD';
                    foreach( $p['history'] as &$commit ) {
                        if( $count >= $p['num'] ) {
                            break;
                        }

                        if( $commit['pages'] && count( $commit['pages'] ) > 0 ) {
                            foreach( $commit['pages'] as $f ) {
                                if( !can( "read", $f ) ) {
                                    continue 2;
                                }
                            }
                        }

                        if( $count == 0 ) {
                            $latest_commit = $commit['commit'];
                        }

                        if( $count == 1 ) {
                            $second_latest_commit = $commit['commit'];
                        }

                        $row_classes = array();
                        $is_assoc = false;
                        $is_alias = false;

                        perf_enter( 'render.gen_history.row' );

                        if( ASSOC_ENABLE ) {
                            if( is_array( $commit['pages'] ) && count( $commit['pages'] ) > 0 ) {
                                foreach( $commit['pages'] as $f ) {
                                    if( has_directory_prefix( ASSOC_DIR, $f ) ) {
                                        $is_assoc = true;
                                        $row_classes[] = "assoc";
                                        break;
                                    }
                                }

                                if( !$is_assoc ) {
                                    $row_classes[] = "not-assoc";
                                }
                            }
                        }

                        if( ALIAS_ENABLE ) {
                            if( is_array( $commit['pages'] ) && count( $commit['pages'] ) > 0 ) {
                                foreach( $commit['pages'] as $f ) {
                                    if( has_directory_prefix( ALIAS_DIR, $f ) ) {
                                        $is_alias = true;
                                        $row_classes[] = "alias";
                                        break;
                                    }
                                }

                                if( !$is_alias ) {
                                    $row_classes[] = "not-alias";
                                }
                            }
                        }

                        $notes = array();
                        if( INCLUDE_WORK_TIME && !$is_assoc ) {
                            if( ( $n = git_notes( $commit['commit'], WORKING_TIME_NOTES_REF ) ) !== false ) {
                                $notes[] = trim( "Working time: $n" );
                            }
                        }
                        $response_exists = false;
                        if( COMMIT_RESPONSE_REF && !$is_assoc ) {
                            if( ( $n = git_notes( $commit['commit'], COMMIT_RESPONSE_REF ) ) ) {
                                $response_exists = substr_count( $n, COMMIT_RESPONSE_PREFIX );

                                /*
                                $latest_response = array_pop(
                                    explode( COMMIT_RESPONSE_PREFIX, $n )
                                );

                                $latest_response_author = array_shift(
                                    explode( "\n", $latest_response )
                                );

                                $author_hash = substr( md5( $latest_response_author ), 0, 6 );
                                */
                            }
                        }

                        ?>
                            <tr class="<?= implode( " ", $row_classes ) ?>">
                                <td class="before-after-btn">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default btn-xs before-after-radio">
                                            <input
                                                type="radio"
                                                name="commit_before"
                                                id="commit_before_<?= $commit['commit'] ?>"
                                                value="<?= $commit['commit'] ?>"
                                            >
                                            <span
                                                title="Find difference between two commits, using this as the 'before' commit"
                                            >
                                                &laquo;
                                            </span>
                                        </label>
                                        <?

                                            $file_prior_commit = git_file_prior_commit(
                                                is_array( $commit['pages'] )
                                                    ? implode( '', $commit['pages'] )
                                                    : false
                                                ,$commit['commit']
                                            );
                                            $is_single_page = is_array( $commit['pages'] ) && count( $commit['pages'] ) == 1;
                                        ?>
                                        <label class="btn btn-default btn-xs before-after-radio file-prior <?= ( !$is_single_page || !$file_prior_commit  ? 'disabled' : '' ) ?>">
                                            <? if( $file_prior_commit !== false ) { ?>
                                                <input
                                                    type="radio"
                                                    name="commit_before"
                                                    id="commit_before_<?= $file_prior_commit ?>"
                                                    value="<?= $file_prior_commit ?>"
                                                >
                                            <? } ?>
                                            <span
                                                title="Find difference between two commits, using this specific file's commit prior to this one (rather than just the previous change in the repository, which may or may not have been against this file)"
                                            >
                                                <?= ( !$is_single_page || !$file_prior_commit  ? '&nbsp;' : '&crarr;' ) ?>
                                            </span>
                                        </label>
                                        <label class="btn btn-default btn-xs before-after-radio">
                                            <input
                                                type="radio"
                                                name="commit_after"
                                                id="commit_after_<?= $commit['commit'] ?>"
                                                value="<?= $commit['commit'] ?>"
                                            >
                                            <span
                                                title="Find difference between two commits, using this as the 'after' commit"
                                            >
                                                &raquo;
                                            </span>

                                        </label>
                                    </div>
                                </td>
                                <td class="dropdown commit-btn">
                                    <div class="btn-group">
                                        <a
                                            class="btn btn-<?= number_to_helper_class( $response_exists ) ?> btn-xs combo-btn"
                                            href="show_commit.php?commit=<?= $commit['commit'] ?>"
                                            title="<?= commit_excerpt( $commit['commit'] ) ?> @ <?= strftime( '%Y-%m-%d %H:%M:%S', $commit['epoch' ] ) ?>: Show details on this commit."
                                        >
                                            <samp
                                                class="live-timestamp"
                                                data-time="<?= $commit['epoch'] ?>"
                                            ><?= short_time_diff(
                                                $commit['epoch'],
                                                time()
                                            ) ?></samp>

                                            <?php if( $response_exists > 0 ) { ?>
                                                <span
                                                    class="badge"
                                                >
                                                    <?= $response_exists ?>
                                                </span>
                                            <?php } ?>
                                        </a>
                                        <a
                                            class="btn btn-default btn-xs"
                                            href="<?= diff_url( $commit['parent_commit'], $commit['commit'], $commit['pages'], 'yes' ) ?>"
                                            title="Difference between this commit and its parent"
                                        >
                                            diff
                                        </a>
                                        <button
                                            class="btn btn-default btn-xs dropdown-toggle"
                                            <? if( false ) { ?>
                                                style="background-color: #<?= $author_hash ?>"
                                            <? } ?>
                                            data-toggle="dropdown"
                                        >
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <? if( $response_exists ) { ?>
                                            <li class="dropdown-header">
                                                <span class="label label-info">A note has been made on this commit</span>
                                            </li>
                                            <? } ?>
                                            <li>
                                                <a href="view.php?commit=<?= $commit['commit'] ?>" title="View all files in this commit" >View Commit Contents</a>
                                            </li>
                                            <? if( is_array( $commit['pages'] ) && count( $commit['pages'] ) >= 0 ) { ?>
                                                <? if( count( $commit['pages'] ) > 1 ) {
                                                    $tmp_a = array();
                                                    foreach( $commit['pages'] as $page ) {
                                                        $tmp_a[] = "file[]=" . undirify( $page );
                                                    }
                                                ?>
                                                    <li>
                                                        <a href="history.php?<?= join('&', $tmp_a ) ?>" title="History for files in this commit." >History for files in this commit</a>
                                                    </li>
                                                    <li>
                                                        <a href="stats.php?<?= join('&', $tmp_a ) ?>" title="Stats for files in this commit" >Stats for files in this commit</a>
                                                    </li>
                                                <?  } elseif( count( $commit['pages'] ) == 1 ) { ?>
                                                    <li>
                                                        <a href="history.php?file=<?= undirify( join( '',  $commit['pages'] ) ) ?>" title="History for files in this commit" >History for files in this commit</a>
                                                    </li>
                                                    <li>
                                                        <a href="stats.php?file=<?= undirify( join( '',  $commit['pages'] ) ) ?>&commit=<?= $commit['commit'] ?>" title="Stats for files in this commit" >Stats for files in this commit</a>
                                                    </li>
                                                <? } ?>
                                            <? } ?>
                                            <li role="separator" class="divider"></li>
                                            <li>
                                                <?= diff_anchor( 'Difference between this commit and previous', $commit['parent_commit'], $commit['commit'], $commit['pages'], "yes", "Difference between this commit and its parent" ) ?>
                                            </li>
                                            <li>
                                                <?= diff_anchor( 'Difference between this commit and latest', $commit['commit'], $latest_commit, $commit['pages'], "yes", "Difference between this commit and the latest commit." ) ?>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="author <?= he( $commit['author'] ) ?>">
                                    <a
                                        href="history.php?author=<?= $commit['author'] ?>"
                                    >
                                        <?= $commit['author'] ?>
                                    </a>
                                </td>
                                <td class="filename dropdown">
                                    <?= commit_pages( $commit ) ?>
                                </td>
                                <td class="message">
                                    <samp ><?= clickable_urls( $commit['message'] ) ?></samp>
                                </td>
                            </tr>
                        <?
                        $count++;
                        perf_exit( 'render.gen_history.row' );
                    }

                    ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<? perf_exit( 'render.gen_history' ); ?>
