<?php renderable( $p ) ?>
<?php

function bootstrap_helper_class( $i, $helper_type ) {

    $helpers = array(
        'alert' =>  array(
            'success',
            'info',
            'warning',
            'danger',
        ),
        'badge' =>  array(
            'first',
            'second',
            'third',
            'fourth',
        )
    );

    return $helpers[$helper_type][ $i % count( $helpers[ $helper_type ] ) ];
}

?>
<div 
    id="collapse-gen-nav"
    class="navigation collapse navbar-collapse"
>
    <?php if( 
        isset( $_SESSION['breadcrumb'] ) 
        && 
        is_array( $_SESSION['breadcrumb'] ) 
        && 
        count( $_SESSION['breadcrumb'] ) > 0 
    ) { ?>
        <?= _gen_breadcrumb( 
            array(
                'ul-class'  =>  'nav navbar-nav'
            )
        )
        ?>
    <?php } ?>
    <form 
        method="get" 
        action="search.php" 
        role="search" 
        class="navbar-form"
    >
        <div class="form-group">
            <input 
                class="form-control quick-nav handle" 
                name="term" 
                type="text" 
                id="term" 
                maxlength="50" 
                size="15" 
                accesskey="s" 
                tabindex="4"
                <?= ( 
                    isset( $_GET['term'] ) 
                        ? 'value="' . he( $_GET['term'] ) . '"'
                        : ''
                ) ?>
            /> 
        </div>
        <button 
            class="btn btn-default" 
            type="submit" 
        >
            <span class="glyphicon glyphicon-search" title="Search"></span>
            <span class="sr-only">Search</span>
        </button>
    </form>
    <ul class="nav navbar-nav btn-toolbar navbar-right">
        <li class="dropdown btn-group">
            <button 
                class="btn <?= ( ( is_logged_in() && $p['latest_user_commit']['commit'] != $p['head_commit'] ) ? 'btn-danger' : 'btn-default' ) ?> navbar-btn clickable"
                title="Show the latest history for files in your repository"
                value="history.php"
            >
                <span class="glyphicon glyphicon-list-alt"></span>
                <span class="sr-only">Log</span>
                <?= html_short_time_diff( $p['since_latest_time'], time(), array( 'classes' => array( 'badge' ) ) ) ?>
            </button>
            <button 
                class="btn btn-default navbar-btn dropdown-toggle"
                title="More log / history options..."
                data-toggle="dropdown"
            >
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a 
                        href="history.php"
                        title="Show the latest history for files in your repository"
                    >
                        <span class="glyphicon glyphicon-list-alt"></span>
                        Commit History
                    </a>
                </li>
                <li>
                    <a href="show_commit.php">
                        <span class="glyphicon glyphicon-fire"></span>
                        Latest commit: <?= $p['since_latest'] ?>
                    </a>
                </li>
                <li>
                    <a href="notes.php">
                        <span class="glyphicon glyphicon-thumbs-up"></span>
                        Commit Responses
                    </a>
                </li>

                <?php if( is_logged_in() && $p['latest_user_commit']['commit'] != $p['head_commit'] ) { ?>
                    <li>
                        <a
                            class="bg-danger" 
                            href="history.php?since=<?= $p['latest_user_commit']['commit'] ?>"
                        >
                            Changes Since My Last Commit
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
        <?php if( is_logged_in() ) { ?>
            <li class="dropdown btn-group">
                <button
                    class="btn btn-default navbar-btn dropdown-toggle"
                    title="Start writing"
                    data-toggle="dropdown"
                >
                    <span class="glyphicon glyphicon-pencil"></span>
                    <span class="sr-only">Write</span>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a
                            href="new.php"
                            title="Create a new document"
                        >
                            <span class="glyphicon glyphicon-edit"></span>
                            New document
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a
                            href="edit.php?file=<?= join('/', array( $_SESSION['usr']['name'], 'jot', strftime( "%Y" ), strftime( "%m" ), strftime( "%d-%a" ) ) ) ?>"
                            title="Start editing a new, timestamped file (a 'jot') for today's date"
                        >

                            <span class="glyphicon glyphicon-calendar"></span>
                            Jot for the day
                        </a>
                    <li>

                        <a 
                            class="import"
                            title="Start editing a new, timestamped file (a 'jot') for the current hour"
                            href="edit.php?file=<?= join('/', array( $_SESSION['usr']['name'], 'jot', strftime( "%Y" ), strftime( "%m" ), strftime( "%d-%a" ), trim( strftime( "%l%p" ) ) ) ) ?>"
                        >
                            <span class="glyphicon glyphicon-time"></span>
                            Jot for the hour
                        </a>
                    </li>
                    <li>
                        <a 
                            class="import"
                            title="Import a file into a new jot"
                            href="import.php?file=<?= join('/', array( $_SESSION['usr']['name'], 'jot', strftime( "%Y" ), strftime( "%m" ), strftime( "%d-%a" ) ) ) ?>"
                        >
                            <span class="glyphicon glyphicon-import"></span>
                            Import into New Jot
                        </a>
                    </li>
                </ul>
            </li>
        <?php } ?>
        <li class="dropdown btn-group ">
            <button 
                class="btn btn-default navbar-btn clickable"
                value="todos.php" 
                title="Files with TODO lines in them"
            >
                <span class="glyphicon glyphicon-pushpin"></span>
                <?php if( $p['todo_count'] && $p['todo_count'] > 0 ) { ?>
                    <span class="badge badge-<?= bootstrap_helper_class( $p['todo_count'], 'badge' )?> " >
                        <?= $p['todo_count'] ?>
                    </span>
                <?php } ?>
            </button>
            <button 
                class="btn btn-default navbar-btn dropdown-toggle"
                data-toggle="dropdown"
                title="Other specialized searches"
            >
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <?php if( $p['todo_count'] && $p['todo_count'] > 0 ) { ?>
                    <li>
                        <a 
                            href="todos.php" 
                            title="Files with TODO lines in them"
                        >
                            <span class="glyphicon glyphicon-pushpin" aria-hidden="true"></span>
                            <?= plural( $p['todo_count'], "Document" ) ?> with TODOs
                        </a>
                    </li>
                    <li>
                        <a 
                            href="todo_hierarchy.php" 
                            title="Directories and the number of TODO/CB elements within them"
                        >
                            <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                            Directory TODO Count
                        </a>
                    </li>
                <?php } ?>
                <li>
                    
                    <a 
                        href="annotations.php" 
                        title="Files with annotations in them"
                    >
                        <span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
                        Annotations
                    </a>
                </li>
            </ul>
        </li>
        <li class="dropdown btn-group">
            <button 
                class="btn btn-default navbar-btn clickable"
                value="tags.php" 
                title="Display all document tags (~tag)"
            >
                <span class="glyphicon glyphicon-tags"></span>
            </button>
            <button 
                class="btn btn-default navbar-btn dropdown-toggle"
                data-toggle="dropdown"
                title="Show other document metadata options"
            >
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li role="presentation" class="dropdown-header">Pages themselves...</li>
                <li>
                    <a 
                        href="tags.php" 
                        title="Display all document tags (~tag)"
                        title="Show documents containing metadata headers (%Header: Value)"
                    >
                        <span class="glyphicon glyphicon-tags"></span>
                        Document Tags
                    </a>
                </li>
                <li>
                    <a 
                        href="meta.php" 
                        title="Show documents containing metadata headers (%Header: Value)"
                    >
                        <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>
                        Document Metadata
                    </a>
                </li>
                <?php if( ASSOC_ENABLE ) { ?>
                    <li role="presentation" class="dropdown-header">Between pages...</li>
                    <li class="dropdown btn-group">
                        <a
                            href="assoc.php"
                            title="Show file associations"
                        >
                            <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
                            Associations
                        </a>
                    </li>
                    <li>
                        <a 
                            href="orphans.php" 
                            title="Show orphaned (unreferenced) files"
                        >
                            <span class="glyphicon glyphicon glyphicon-zoom-out" aria-hidden="true"></span>
                            Orphaned Pages
                        </a>
                    </li>
                    <li>
                        <a 
                            href="wanted.php" 
                            title="Show wanted (referenced, but not existing) files"
                        >
                            <span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
                            Wanted Pages
                        </a>
                    </li>
                <?php } ?>

            </ul>
        </li>
    </ul>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            nav.setup(
                // Head files cache
                <?= json_encode( 
                    $p['head_files']
                ) ?>,
                // Head tags cache
                <?= json_encode(
                    $p['head_tags']
                ) ?>
            );
        }
    );
</script>
