<? renderable( $p ) ?>
<h3>Warning:</h3>
<p>
    You have an unsaved draft that exists for this file. 
    <a title="" href="edit.php?draft=<?= urlencode( basename( $p['draft_name'] ) ) ?>">
        Click to edit this file with your previous draft.
    </a>
</p>
<p>
    Alternatively, you can choose to 
    <a href="edit.php?file=<?= urlencode( $p['parameters']['file'] ) ?>&draft_discard=yes">
        discard your draft outright
    </a>, which, while not recommended, will let you edit the most recently commited contents of the file.
</p>
<p>
    Finally, you can browse your <a href="drafts.php">full list of drafts</a> to see what all is available.
</p>

<? /* <a title="" href="delete_draft.php?draft=<?= urlencode( basename( $draft['filepath'] ) ) ?>">Delete</a> */ ?>

