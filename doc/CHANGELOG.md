*   2013/11/06: Document transclusion supported by way of "transclude" functionlink.
*   2013/10/20: Diff interface now supports "n" key shortcut, which iterates through visible differences being displayed. Useful for quickly reviewing differences in a file without having to scroll through large documents.
*   2013/10/20: Support for user-selected theming with the user/Theme special files. Using CodeMirror as editor. Supporting new theme that allows toggling of "meta" elements (navigation, document activities, etc.)
*   2013/09/23: Draft feature now attempts to track "working time," where between draft saves, the editing interface calculates the number of seconds in which it saw you typing (with a maximum being the number of seconds between the latest and the prior draft saves, in order to avoid cheating). Similar to word count, the time accumulated in a draft will be added to the commit notes (with a configuration option to turn it off at a later date).
*   2013/07/31: Identified issue with Git where anything that requires concurrent access to a repo causes blocking-type performance behavior (serialized access). Implemented caching around some of the base functions to eliminate this.
*   2013/07/28: With "excerpt=line" parameter for list function link, you can now include up until the first Markdown horizontal rule ("line"), rather than a character count excerpt. Useful for displaying abstracts in a list.
*   2013/07/27: Commit conflicts now result in cherrypicking screen to resolve differences between the user's submission and the conflicting commit.
*   2013/06/12: Numerous performance improvements in orphan/wanted page searches, as well as getting rid of a number of bugs in association maintenance and finding orphans that do not already have links. UI improvements for searching by association type.
*   2013/06/09: Page associations are now functional, and reorganized for efficiency. Still need to find a way to make them less overt on the interface/history log, but that's more aesthetics than anything.
*   2013/06/02: Lists can now be displayed "as" tables, allowing them to be used as the target for table function links. Also, added appropriate caching heads to image response to improve page load times for images, image collections, etc.
*   2013/05/31: "list" function link syntax now supports "sort" parameter, which currently if set to "reverse," will reverse the sort order of the list. Works with explicit list definitions, external list references, and external collection references.
*   2013/05/30: Performance improvements in terms of being able to tell files exist without hammering git calls.
*   2013/05/27: Rudimentary file upload capability, file moving now gives the option to move "counterpart" directory/files along with source to target.
*   2013/05/02: TODO now also matched TBD. Plus, TODO search highlights matched items.
*   2013/04/28: Directory output now lists most recent commit with every file/directory.
*   2013/04/16: History commit messages now try to "collapse" rather than extend multiple lines. Cleaner output.
*   2013/04/07: TODO searching now more lax, and displays as red when decor
*   2013/02/05: Quickjumping, with formatting to indicate matches.
*   2013/02/03: Adding cleaner, more functional Markdown buttons to editing interface.
*   2013/01/31: Adding button to disable live preview.
*   2013/01/27: Adding "Table of Contents" checkbox and hotkey, similar to decorations.
*   2013/01/21: "Commit and Edit," for committing changes thus far but keeping on editing while saving place in the editor.
*   2013/01/20: TODO syntax and searching.
*   2013/01/19: Implementing "escaping" of wikilink/functionlink syntax.
*   2013/01/19: Implementing the ability to have a CSV password file.
*   2013/01/19: Modularizing login mechanisms.
*   2013/01/17: Live preview works with functions.
*   2013/01/16: "Plain" diff output, additional wikilink syntax for "functions" links (works on backend, still needs frontend work for live preview).
*   2013/01/14: "Raw" output of files.
*   2013/01/14: CSV handling.
*   2013/01/13: Added "partitioning" of files to easy split large documents into component parts and automatically generating collections to "stitch" them together.
*   2012/12/17: Added "Cherrypicking" of changes.
*   2012/12/16: Improved caching, added access key combo to search box.
*   2012/12/13: Implemented low-level caching. De-hoverized the diff output pages.
*   2012/12/10: Better cursor/caret indicator to the right (causes less errors). Better blame coloring.
*   2012/12/07: Changing scroll synchronization semantics, replacing the less accurate scroll position synchronization with the synchronization of a more accurate cursor position.
*   2012/12/06: Modified TOC to be "snappable," depending on browser window width.
*   2012/12/04: Adding "Decorations" checkbox, as well as "Other options" dropdown for economic use of space.
*   2012/12/02: Live-preview word count, plus improved decorations to show dialog/annotations reasonably in live preview window.
*   2012/11/29: {Introduced new annotation syntax.}[a7b]
*   2012/11/28: Added resize window height persistence, as well as additional styling tweaks.
*   2012/11/25: Added "live preview," along with side-by-side editing of Markdown documents.
*   2012/11/18: Adding a "What Links Here" button to articles.
*   2012/11/12: Improving/coloring diff output.
*   2012/11/11: Adding annotation shortcuts from editing interface (control-backtick).
*   2012/11/10: Previewing edits now attempts to scroll edit/preview windows to relevant edited content (last scroll position)
*   2012/11/09: Adding "as" parameter to index.php page viewers to allow for overrides on renderer of choice.
*   2012/11/05: Adding "wrap" suffix for outputting 80-character width column wrapping mode.
*   2012/11/04: Adding version-aware stats page.
*   2012/10/23: Hover highlighting of "dialog" elements.
*   2012/09/08: Added directory counts to views. Also, drafts might be working.
*   2012/09/05: Halfway through drafts. Saving, but no way to retrieve.
*   2012/08/29: Search now searches on both file name and file contents. File name search is case sensitive.
*   2012/08/28: Moving directories around no longer puts working directory in invalid state.
*   2012/08/19, part 2: Search box in non-stupid place. Last visited listing in navigation bar.
*   2012/08/19: Corrected Git repo configuration to support pushes into a non-bare repository.
*   2012/08/15: Got internal linking working with table of contents generation. Also, searching.
*   2012/08/12: Adding basic pieces/parts for collections.
*   2012/08/09: Changed linking syntax from single square brackets to double square brackets for mysterious reasons.
*   2012/08/06: Delete and move appears to be working.
*   2012/08/05: Basic delete functionality working.
*   2012/08/04: Directory views and navigation appear to work. 

{a7b}: Following some of the conventions of the link references already existing in Markdown.
