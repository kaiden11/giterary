<? renderable( $p ) ?>
<?
$snippet_types = array(
    "Wording",
    "Spelling",
    "Capitalization",
    "Grammar",
    "Punctuation",
    "Formatting",
    "Repetition",
    "Reminder",
    "Add",
    "Remove",
    "Replace",
    "Consistency",
    "Question",
    "Word Choice",
);
?>
<li id="snippets-activity" class="no-selection"  >
    <div 
        class="input-group" 
        style="width: 100px"
    >
        <span class="input-group-btn">
            <button
                id="add-to-snippets"
                class="btn btn-primary navbar-btn"
                title="Add selected text to 'snippets' for later review"
            >
                Snip!
                <span id="add-to-snippets-word-count"></span>
            </button>
        </span>
        <input 
            id="snippet-type" 
            type="text" 
            class="form-control navbar-btn" 
            aria-label="Text input with segmented button dropdown"
            style="width: 200px;"
            placeholder="Optional snippet tag..."
        />
        <span id="snippet-dropdown" class="input-group-btn dropup">
            <button 
                type="button" 
                class="btn btn-default dropdown-toggle" 
                data-toggle="dropdown" 
                aria-haspopup="true" 
                aria-expanded="true"
            >
                <span class="caret"></span>
                <span class="sr-only">Snippet Types</span>
            </button>
            <ul class="dropdown-menu">
                <li 
                    role="presentation" 
                    class="dropdown-header"
                >
                    Preset type for this snippet:
                </li>
                <? foreach( $snippet_types as $t ) { ?>
                    <li>
                        <a 
                            href="javascript:;"
                            class="snippet-type-preset" 
                        >
                            <?= he( $t ) ?>
                        </a>
                    </li>
                <? } ?>
            </ul>
        </span>

    </div>
</li>
