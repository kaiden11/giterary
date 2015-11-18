<?

define( 'DEFAULT_THEME', 'bootstrap');

# Array of name-to-path associations for 'well-known'
# iterable files, usually having some critical function
# to the rendering of the site. If one were enterprising,
# this could almost be a reasonable entry point to piecemeal
# modify the overall layout/theme of idkfa.
$renderables = array(
    'gen_edit'              =>  'theme/bootstrap/renderable/gen_codemirror_edit.php',
    'gen_markdown_edit'     =>  'theme/bootstrap/renderable/gen_codemirror_edit.php',
    'gen_storyboard_edit'   =>  'theme/bootstrap/renderable/gen_storyboard_edit.php',
    'gen_import'            =>  'theme/bootstrap/renderable/gen_import.php',
    'edit_layout'           =>  'theme/bootstrap/renderable/layout/layout.php',       // All layouts the same, some sanity.
    'default_layout'        =>  'theme/bootstrap/renderable/layout/layout.php',
    'printable'             =>  'theme/bootstrap/renderable/layout/printable.php',
    'readable'              =>  'theme/bootstrap/renderable/layout/readable.php',
    'epub_xhtml_layout'     =>  'theme/bootstrap/renderable/layout/epub_xhtml_layout.php',
    'edit_layout'           =>  'theme/bootstrap/renderable/layout/layout.php',
    'show_layout'           =>  'theme/bootstrap/renderable/layout/layout.php',
    'gen_error'             =>  'theme/bootstrap/renderable/gen_error.php',
    'gen_template'          =>  'theme/bootstrap/renderable/gen_template.php',
    'note'                  =>  'theme/bootstrap/renderable/note.php',
    'gen_header'            =>  'theme/bootstrap/renderable/gen_header.php',
    'gen_breadcrumb'        =>  'theme/bootstrap/renderable/gen_breadcrumb.php',
    'gen_nav'               =>  'theme/bootstrap/renderable/gen_nav.php',
    'gen_footer'            =>  'theme/bootstrap/renderable/gen_footer.php',
    'gen_view'              =>  'theme/bootstrap/renderable/gen_view.php',
    'gen_toc'               =>  'theme/bootstrap/renderable/gen_toc.php',
    'gen_storyboard'        =>  'theme/bootstrap/renderable/gen_storyboard.php',
    'gen_print'             =>  'theme/bootstrap/renderable/gen_print.php',
    'gen_read'              =>  'theme/bootstrap/renderable/gen_read.php',
    'gen_epub'              =>  'theme/bootstrap/renderable/gen_epub.php',
    'gen_epub_xhtml'        =>  'theme/bootstrap/renderable/gen_epub_xhtml.php',
    'gen_epub_cover'        =>  'theme/bootstrap/renderable/gen_epub_cover.php',
    'gen_epub_content_opf'  =>  'theme/bootstrap/renderable/gen_epub_content_opf.php',
    'gen_epub_container_xml'=>  'theme/bootstrap/renderable/gen_epub_container_xml.php',
    'gen_epub_toc_ncx'      =>  'theme/bootstrap/renderable/gen_epub_toc_ncx.php',
    'gen_notes_list'        =>  'theme/bootstrap/renderable/gen_notes_list.php',
    'gen_history'           =>  'theme/bootstrap/renderable/gen_history.php',
    'gen_show'              =>  'theme/bootstrap/renderable/gen_show.php',
    'gen_diff'              =>  'theme/bootstrap/renderable/gen_diff.php',
    'gen_file_diff'         =>  'theme/bootstrap/renderable/gen_file_diff.php',
    'gen_search'            =>  'theme/bootstrap/renderable/gen_search.php',
    'gen_todos'             =>  'theme/bootstrap/renderable/gen_todos.php',
    'gen_todo_hierarchy'    =>  'theme/bootstrap/renderable/gen_todo_hierarchy.php',
    'gen_annotations'       =>  'theme/bootstrap/renderable/gen_annotations.php',
    'gen_tags'              =>  'theme/bootstrap/renderable/gen_tags.php',
    'gen_all_tags'          =>  'theme/bootstrap/renderable/gen_all_tags.php',
    'gen_all_meta'          =>  'theme/bootstrap/renderable/gen_all_meta.php',
    'gen_meta'              =>  'theme/bootstrap/renderable/gen_meta.php',
    'gen_timeline'          =>  'theme/bootstrap/renderable/gen_timeline.php',
    'gen_dir_view'          =>  'theme/bootstrap/renderable/gen_dir_view.php',
    'gen_move'              =>  'theme/bootstrap/renderable/gen_move.php',
    'gen_stats'             =>  'theme/bootstrap/renderable/gen_stats.php',
    'gen_cache'             =>  'theme/bootstrap/renderable/gen_cache.php',
    'gen_work_stats'        =>  'theme/bootstrap/renderable/gen_work_stats.php',
    'gen_blame'             =>  'theme/bootstrap/renderable/gen_blame.php',
    'gen_cherrypick'        =>  'theme/bootstrap/renderable/gen_cherrypick.php',
    'gen_partition'         =>  'theme/bootstrap/renderable/gen_partition.php',
    'gen_make_partitions'   =>  'theme/bootstrap/renderable/gen_make_partitions.php',
    'gen_assoc'             =>  'theme/bootstrap/renderable/gen_assoc.php',
    'gen_assoc_type'        =>  'theme/bootstrap/renderable/gen_assoc_type.php',
    'gen_all_assoc_type'    =>  'theme/bootstrap/renderable/gen_all_assoc_type.php',
    'gen_orphans'           =>  'theme/bootstrap/renderable/gen_orphans.php',
    'gen_wanted'            =>  'theme/bootstrap/renderable/gen_wanted.php',
    'gen_drafts'            =>  'theme/bootstrap/renderable/gen_drafts.php',
    'gen_new'               =>  'theme/bootstrap/renderable/gen_new.php',
    'gen_repo_stats'        =>  'theme/bootstrap/renderable/gen_repo_stats.php',
    'gen_draft_commit'      =>  'theme/bootstrap/renderable/gen_draft_commit.php',
    'gen_users_list'        =>  'theme/bootstrap/renderable/gen_users_list.php',
    'gen_scratch'           =>  'theme/bootstrap/renderable/gen_scratch.php',
    'gen_snippets'          =>  'theme/bootstrap/renderable/gen_snippets.php',
    'gen_snippet_widget'    =>  'theme/bootstrap/renderable/gen_snippet_widget.php',
    'gen_users_online'      =>  'theme/bootstrap/renderable/gen_users_online.php',
    'not_logged_in'         =>  'theme/bootstrap/renderable/not_logged_in.php',
    'gen_csv'               =>  'theme/bootstrap/renderable/gen_csv.php',
    'gen_image'             =>  'theme/bootstrap/renderable/gen_image.php',
    'gen_audio'             =>  'theme/bootstrap/renderable/gen_audio.php',
    'gen_list'              =>  'theme/bootstrap/renderable/gen_list.php',
    'gen_assoc_functionlink'=>  'theme/bootstrap/renderable/gen_assoc_functionlink.php',
    'gen_assoc'             =>  'theme/bootstrap/renderable/gen_assoc.php',
    'gen_assoc_type'        =>  'theme/bootstrap/renderable/gen_assoc_type.php',
    'gen_all_assoc_type'    =>  'theme/bootstrap/renderable/gen_all_assoc_type.php',
    'gen_orphans'           =>  'theme/bootstrap/renderable/gen_orphans.php',
    'gen_wanted'            =>  'theme/bootstrap/renderable/gen_wanted.php',
    'gen_build_assoc'       =>  'theme/bootstrap/renderable/gen_build_assoc.php',
    'gen_draft_warning'     =>  'theme/bootstrap/renderable/gen_draft_warning.php'
);


$themes = array(
    'bootstrap' =>  array(
        // 'RENDERABLE_NAME'    =>  'RENDERABLE PHP FILE'
    )
);


?>
