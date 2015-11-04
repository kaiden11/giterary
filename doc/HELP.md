# Giterary User Manual

The following document is a user's manual of sorts for day-to-day user interaction with a working Giterary instance. If you're having issues with configuration or installation, you should look at the [[INSTALL]] document.

This document takes you through basic understanding of the function of Giterary as a wiki, how to navigate and interact with document objects within the application, and how to create, modify, and view the change history of them.

## Giterary Concepts

Giterary belongs to a family of computer software that calls themselves "wikis."

### What is a wiki?

A wiki is an application that lets you navigate and edit a web of documents and document references from a single interface. The most famous wiki being "[Wikipedia](http://wikipedia.org)." You may have heard of them.

Wikis are powerful tools because they allow you to efficiently organize and navigate your information while providing multiple users the ability to update and maintain the content. They vary in ubiquity and in features, given special use cases, extensibility, and popularity. Giterary's wiki features are inspired largely by [Mediawiki](http://www.mediawiki.org/wiki/MediaWiki) and [MoinMoin](http://moinmo.in/) (both excellent examples of full featured and extensible wiki software).

### File Structures and naming

Giterary employs a hierarchical structure to its file naming conventions, allowing for the user to organize their file content in logical folder structures while being relatively lax with regards to file name formats (compared to other wikis).

#### Naming Files

File names can include almost any normal filesystem character (alphanumerics, slash, underscore, spaces) minus a colon (":"), slash or 'solidus' ("/"), and non-printing characters (newline characters, etc.).

Like files on your computer, all files exist within directories. You can choose to operate without directories, but this guide recommends you take advantage of this feature in order to organize your content.

All files can be referenced using a *file path*. This file path refers to the "path" one must traverse through the directory structure of your Giterary repository in order to get to your actual file. Examples would be like the following:

    Home                       (the "home page" for your wiki)
    bobby                      (the "user page" for the user "bobby")
    My Story/Chapters/Chapter1 (Chapter 1 of many chapters of "MyStory")
    bobby/jot/2013/01/19-Sat   (user bobby's "jot" for 2013/01/19)


#### Files, Directories, and "Dirification"

There comes a dilemma when creating a hierarchical wiki document naming scheme.

Let's say you have a directory structure like this:

    My Story/Chapters/Chapter 1
    My Story/Chapters/Chapter 1/Notes
    My Story/Chapters/Chapter 1/Citations

The question becomes: *What type of object is the thing called **Chapter 1**?*

*Chapter 1* is certainly a file, but it's referred to in the paths to Notes and Citations as a directory path component.

Giterary solves this problem with the concept of "directorification" or "dirification." It is an abuse of file paths to make it *seem* like *Chapter 1* is both a file **and** a directory. By way of "dirification" Giterary will treat the following two file paths as equivalently referencing the exact same file:

    My Story/Chapters/Chapter 1/Notes
    My Story.dir/Chapters.dir/Chapter 1.dir/Notes

In the first file path, it is implied that the path components *My Story*, *Chapters*, and *Chapter 1* have the suffix ".dir" appended to them because they are being treated as a directory. In the second file path, the ".dir" suffixes are explicit, but refer to the true, "dirified" path.

In this way, there can still be two objects with the "same name," but only by a quick sleight of hand on Giterary's part. In this way, you can ask to edit *My Story/Chapters/Chapter 1* and *My Story/Chapters/Chapter 1/Notes* and have it do the right thing.

#### Relative Paths

While descriptive, it is sometimes tedious to detail the full path for a file. Given a large or complicated enough structure (or even just a lengthy directory or file name), having to specify the full path to something can be redundant. This can also be a pain when file or directory renames occur, as the file links prior to a structural move can be broken (and usually as great scale, given how many files were moved).

To help with this, the concept of *relative paths* is available. These are useful to help your structures remain intact across renaming, as well as reduce repetition when creating document references. For instance, if you're viewing the following file:

    Working Title/Bookmaking/Book1

...It is possible to create a link with the path specified like so:

    [[./Act2/Scene3|Book 1, Act 2, Scene 3]]

Note the `./` prefix at the beginning of the path. The `./` prefix refers to the *current* document being viewed/rendered, replacing the `./` with the path prefix `Working Title/Bookmaking/Book1`. This is translated on the fly, and as it is relative to the document the link is generated from, it will survive a rename of your story from "Working Directory" to whatever you choose.

Another relative path function exists, which is to "go up" a directory. For instance, in the same `Book1` document, one could have the following link:

    [[../Book2|Next Book]]

...which will translate the file path to be effectively the following:

    [[Working Title/Bookmaking/Book2|Next Book]]

This `../` prefix can be repeated multiple times to "go up" as many directories as is necessary. However, note that you cannot "go up" past the root of the directory, and is considered an invalid path specification.

Relative paths can work where the question can be answered "What document am I viewing currently?" This means that all document links and all function links being rendered in a document can use relative paths rather than absolute paths.

### Creating New Files and Directories

Per wiki software convention, the most common way to create a new file is to edit an existing one with a link to the new page, and then navigating to that link. Giterary tries to adopt this convention, erring on the side of good organizational structure rather than simplicity of *just creating a new file*. That said, there is nothing to stop a user from just looking at the URL of the file referenced in their browser window and change it to their desired new filename.

An alternate route exists, however: if you browse to any directory, a text input is available, pre-populated with a data structure relevant to the currently viewed directory, and something akin to *NewFile* appended at the end. This will allow you to easily create files under known directory structures.

Additionally, one can hit the "Jot" button when logged in, which immediately puts the user in the editor for a file that is date-stamped to the current date. This is another way to quickly write in a new file while retaining some information about when that file was created.

    myusername/jot/2013/01/21-Mon

A final note on creating new files: if a new file references a path component that does not exist, when finally committing that file Giterary will *create all necessary directories* in order to place that file on the path specified. For instance, in a blank repository, edits and submits the file "My Story/Chapters/Chapter 5/Notes" it will create the following directories:

    My Story.dir
    My Story.dir/Chapters.dir
    My Story.dir/Chapters.dir/Chapter 5.dir

File structures can be moved and deleted, so mistakes made during this process are not the end of the world.

### Searching

The search bar at top left can search on both file name, file path, and file contents. The search page will indicate the type of match next to the search results.

There are also a number of ways that external portions of Giterary make use of the searching interface. For example, there are two special searches you can perfrom:

* From a given document display, you can select **Other Options > Search for ...What Links Here**, and return all documents that have wikilinks pointing to this page.
* From the top navigation "TODO" hyperlink when logged in, you can view a list of documents that contain special "TODO" flags within them that indicate work needing done at certain parts of a document. These work for both "TODO" and "TBD" patterns found within documents.

## Types of Objects within Giterary

There are some things to know about dealing with files, directories, and navigating them within Giterary.

### File types are determined by extension

If a file has an extension (*MyFile.txt*, *MyTable.csv*, *MyNotes.md*, etc.) that extensions handling (as determined by Giterary) will be used by default when displaying the page (*txt* or *text* for purely textual documents, *md* or *markdown* for Markdown syntax documents, *csv* for Comma Separated Values formatted files, and so on).

For files with no extension (*Chapter1*, *01-Wednesday*, *HelpFile*) a default renderer is chosen, and that renderer is configured to be the Markdown renderer by default. See later in this document for explanations of Markdown.

You can also override the renderer by specifying different "as=someextensionname" on the URL of a document. This is useful for rendering something as its underlying source code (*as=text*), readable text (*as=wrap*). For instance, this document as:

* [[index:file=HELP,as=text|Plain text]]
* [[index:file=HELP&as=wrap|Hard-wrapped plain text]]

### Directories are for browsing

On every document, there is a tab called "Directory" that will go to a page listing the contents of the directory with that document's same "dirified" name. For instance, viewing *My Story* and clicking on "Directory" will list anything under the directory called *My Story.dir*.

This is useful for navigation your organizational document structures.

### Document Decorations

In addition to the formatting provided by Markdown syntax and other HTMl formatting tags, Giterary also provides additional "decorations" to a document. These decorations are "hidden" by default, but are present in the Giterary interface by selecting the "Decorations" checkbox at the top of a document display, or by hitting the letter "d."

The decorations currently available are:

 * Annotations (described in the Editing section)
 * Dialog highlighting (described in the Editing section)
 * Invisible headings (these will be described in the Table of Contents section).

### Document Tables of Contents

Giterary takes it upon itself to create a table of contents based on the elements within your documents. These help to navigate within documents (particularly in the case of large documents). In Markdown format, if you have a line that looks like this:

    Header Title
    ------------

...or like this...

    Header Title
    ============

...or like this...

    ## Header Title

...you have specified that there should be a "header" element with the text *Header Title*. This will generate the appropriate HTML element (the h1, h2, h3, h4, h5, or h6 tags) according to the "level" of header. Refer to the Markdown syntax documentation for more complex header generation. 

Each of these header elements are then detected by Giterary upon rendering, and can be turned into "bookmarks" as part of an automatically generated table of contents. Depending on your screen geometry (window width of your browser), this table of contents will appear either at the top of your document, or to the right of your document.

Giterary makes one departure from Markdown's normal "header" output, in that it treats the h6 (header level 6) elements differently. Rather than displaying h6 elements like the rest of the headers, they will be invisible, but still be treated as "bookmarking" elements. With this, you can treat h6 headings as invisible placemarkers within larger documents. This is useful if you want to maintain bookmarks within a document, but not disrupt the formatting of the document with headings.

For example:

   ###### This is a hidden heading

Will generate a *This is a hidden heading* table of contents item, but not render within the document (as you should not see below)

###### This is a hidden heading

As part of the decorations functionality, enabling decorations on a document with invisible headings will display the headings at their position within the document.

### Comma Separated Values Files, or Tables (*.csv files)

A CSV file (Comma Separated Value) file is a file whose content consists of tabular data stored in a text document, with line of text representing a "row" of data, and different columns specified by a list of fields, separated by commas.

For example:

    Year,Month,'Day of Month',Note
    2013,01,18,'Today I drank beer. Perhaps I drank too much.'
    2013,01,19,'Today I drank tea. It's not the same as coffee.'

These files will be rendered as dynamic tables within your document, like so:

<table class="tabulizer">
        <thead><tr class="header"><th class="header headerSortDown"><span>Year</span></th><th class="header"><span>Month</span></th><th class="header"><span>Day of Month</span></th><th class="header"><span>Note</span></th></tr></thead>        <tbody><tr class="odd"><td>2013</td><td>01</td><td>18</td><td>Today I drank beer. Perhaps I drank too much.</td></tr><tr class="even"><td>2013</td><td>01</td><td>19</td><td>Today I drank tea. It's not the same as coffee.</td></tr></tbody>    </table>

These CSVs will assume the first row of data is "header" data. It will then put it in an HTML table that can be sorted by the user.

One can also specify default sorting methods with the following syntax:

* !Year sorts the column "Year" in least-to-greatest order.
* ^Year sorts the column "Year" in greatest-to-least order.
* !#Year sorts the column "Year" in least-to-greatest order, treating all elements as numbers.
* !@Year sorts the column "Year" in least-to-greatest order, treating all elements as strings.

You can mix and match the sorting "hints" as you wish, though, they will be evaluated from left-to-right in the header specification.

CSV tables can also be "included" with function links, using the function link syntax as follows:

    [[table:file=MyTable.csv|MyTable]]

This will include the `MyTable.csv` in the current document without having to view it as part of a collection, or using the explicit HTML table syntax (table,th,tr, and td tags).

### Collections

Collections have the purpose of rendering multiple documents within your Giterary repository at once. This is done as a special rendering of the *collection* or *collect* file extensions. Contents of these files are simply lists of other file paths (*MyFile*, *Wherever/I/Put/My/File.txt*).

For example:

    /DarkAndStormyNight/Chapters/Chapter1
    /DarkAndStormyNight/Chapters/Chapter2
    /DarkAndStormyNight/Chapters/Chapter3

Will render the files *Chapter1*, *Chapter2*, and *Chapter3* in succession on a single page. This is useful for stitching together a larger document (the entirety of a book, for instance), of which you have broken into smaller, more manageable pieces (chapters, in this case).

By editing the collection, you can re-order its component parts by changing the order of the paths referenced in the collection document. For instance, if you decided to put Chapter3 before Chapter 2:

    /DarkAndStormyNight/Chapters/Chapter1
    /DarkAndStormyNight/Chapters/Chapter3
    /DarkAndStormyNight/Chapters/Chapter2

Time travel novels are notoriously difficult to write, but this should help to order your components without having to change the underlying contents.

You can also use the collection documents to include an entire subtree of your Giterary repository. For instance, for the user "jrhoades," they may have a collection that contains the following:

    jrhoades/jot/201*/*

Use of the asterisk wildcard lets you match all documents matching the collection query. Documents will be rendered in order of directory traversal, and in alphanumeric order. The above example will render all "jot" files for the user "jrhoades" for the years 2010-2019.

Collections can also specify "tags" (described [[HELP#Tags|later]]), which are ways to categorize and later search for documents. You can specify any number of tags to search for under a certain collection path specification, like the following:

    /DarkAndStormyNight/*:~scene,~dark,~storm

This will match only documents that are tagged with *~scene*, *~dark*, **and** *~storm* (excluding those that contain none, or fewer than all three of the tags).

### Lists (.list files)

Lists are a functional counterpart to collections. While collections display a series of documents in their entirety, lists simply show which files are matched by a collection. Lists are a fairly powerful way to create dynamic interfaces based on the contents of your Giterary repository.

List files use the exact same syntax as collections, however, are rendered differently (as a matter of fact, any collection can be displayed as a list in the *Other Options* dropdown, or vice versa).

List files can also be rendered as sorted tables (much like CSV files), which helps to organize files (particularly if they are named in a way that can be sorted reasonably).

### Talk pages

Similar to Wikipedia, Giterary tries to support discussion surrounding a document that does not take place within the document in question. However, rather than a different document namespace, we instead place a special file, called a "Talk" file. The only thing truly special about Talk file are that they are always named `Talk.talk`, and that they just in the directory just "below" the directory in question. You can access or edit a talk file by choosing "Other" options, and hitting "Talk."

By default, Talk files will be listed as "Talk:FILE", where FILE is the base name of the file they refer to.

### Document Statistics

Document statistics are your basic "word count"-types of metrics that Giterary will calculate for you. For instance, you can view [[stats:file=HELP|the statistics for this very help file]].

Statistics calculations also work on collection files, such that the statistics will be calculated for the collection document *after* all of the components of the collection have been collected.

## Document Import

A simple tool is available to "convert" content from other sources into Markdown. You can use it by selecting the "Write" menu, and choosing "Import into New Jot", or opening or editing a new file and choosing the same option under "Other."

### Partitioning

If you do manage to get your document into a textual format suitable for Giterary, it is likely that this document is sufficiently large (in the case that you're submitting a manuscript, or something of that scale).

It may be the case that you want to split up this document into smaller parts for you, your co-authors, or your editors to work on. In this case, Giterary *does* have a tool for easily "partitioning" files that makes this type of organization much easier.

Partitioning works by finding "potential boundaries" by determining where there are blank lines (or lines with only whitespace) within the body of work. As these blank lines have no content, they are likely things like spaces between paragraphs, chapters, etc.

The partitioning interface presents these boundaries, and allows you to select which boundaries you want to use. It then provides you with a summary of your new partitions, and allows you to name according to the Giterary filename semantics.

To see an example, try to [[partition:file=HELP|partition this file]].

## Document Editing



### The editor

While logged in to Giterary, viewing any object (except for directories) there will be an "Edit" tab at the top of the document. This link will take you to an editor for that particular page. An editor does generally what you expect: it shows you the text contents of a file, and allows you to modify the contents, and save your changes. There are, however, a few things to note about the editor.

1.  **The editor is *contextually aware***.

    That is, the editor detects the type of file that you are editing, and attempts to show an editing interface that is tailored for that type of file. The most apparent (and at time of writing, the most developed) of these contextually aware editing interfaces is that for Markdown. When editing a Markdown file, the interface will display the source text and a "live preview" side-by-side. It will even attempt to provide keyboard shortcuts and formatting buttons that reflect the editor's capabilities. However, for more generic files, the editor will display a "plain" editing interface without the live preview.
      

2.  **The editor tries to minimize the amount of scrolling you have to do**.

    Both the Markdown and generic editors attempt to "synchronize" the editing and preview windows to the best of their ability:

    *   The generic editor attempts to show the approximately similar scroll distance between both the editing window and the preview window.
    *   The Markdown interface attempts to always highlight the *bounding element* which you are editing. For instance: if your editing is in a paragraph, it will highlight that paragraph, if you are editing an item in a list, it will highlight that item in the list. This works for both keyboard navigation as well as mouse clicks within the editing area.

    The editor will also "save your place" in the editing window, allowing you to quickly preview a document without having to scroll back to your place.

3.  **The editor is aware of the version of the file you were originally acting against, and tries to help accordingly**.

    With the possibility of multiple users operating on the same Giterary instance, there is the possibility that two users can edit the same file at the same time. This has the potential to cause conflicts, as well as lost work due to one person's edits overwriting another's.

    If ever the editor detects that the file has changed out from underneath you, it will drop into the cherrypicking interface that displays the differences between your version and that which was committed underneath you.

### Working within the Editor

The editor allows you to write whatever textual contents you wish. However, learning a few keyboard and syntactical tricks can help to create sharp, well-structured documents very quickly.

#### Shortcuts and Formatting

Depending on the type of document you're editing, the editor might give you a number of "shortcut" buttons at the bottom of the editor. These are similar to the toolbars you might see in other editors, but are tailored for the type of document you're editing.

Each "shortcut" is a combination of either the **Control** or **Alt** keys on a keyboard and another single keypress (pressing control or alt first, and then at the same time another key). This "meta" key will be different depending which operating system you're accessing Giterary from. This is because some systems have other, critical functionality already mapped to these key combinations (and some critical functionality that you cannot or should not customize). For Giterary, Windows and Linux browsers will rely on the Alt key, while Mac OS X browsers will rely on Control. The shortcut bar will indicate which beginning key you should press (either "Control + ..." or "Alt + ..." at the bottom of the editor).

Each shortcut button shows two things:

* The letter after Control or Alt you need to press in order to use that shortcut ( Control + B, Control + I ).
* The approximate formatting of the formatting in question (bold, italics, etc.).

Most buttons also have two modes associated with them, which are determined by whether you have highlighted something, or if you just have a normal cursor.

If you have highlighted something, the shortcut buttons will usually attempt to apply the corresponding formatting to the text you have selected. This is useful if you've already typed out text and wish to apply formatting at a later time.

If you haven't highlighted anything and just have a normal cursor, an example of the corresponding formatting will be inserted into your editing window with a section highlighted that you need to edit yourself to complete the formatting. This is useful if you want to make use of formatting, but either don't want to type the syntax required to do so, or forget how the syntax works.

Hovering your mouse over any of the buttons will show a longer textual description of the formatting referenced.

These shortcuts are optional, just the same as using the editor within the Giterary interface is optional. However, if you intend to become proficient at Markdown syntax, these can be useful in learning the syntax as well as becoming proficient at using it. It is highly recommended you explore these shortcuts, as well as review the Markdown syntax documentation, as it will save you time in the long run.

#### Wikilinks

Wikilinks are a special syntax that you include in the text of your document that allow you to generate a hyperlink to a different document when your original document is rendered.

For example:

    \[[ANameOfYourDocument]]

Turns into something like this:

>   [[ANameOfYourDocument]]

You can also specify "display" text along with your link, like so:

    \[[ANameOfYourDocument|Click here to view my document]]

Which turns into something like this:

>   [[ANameOfYourDocument|Click here to view my document]]

Depending on whether that document *exists* (that is, is present in the latest and greatest version of your wiki content), the link may be colored differently. This is to show which pages you can expect to see content from when clicking on them, and if you would expect to create new content when clicking on them.

The most editor interfaces have a helper function to quickly generate wikilinks.

#### Functional Links

In addition to "wiki"-style links, you can use "functional" linking syntax to generate links that perform functions around Giterary. These involve using a known "prefix" based on Giterary functions, and specifying the parameters that would be used within that link. For instance, determining the "diff" between two commits within Giterary might look like this.

    \[[diff:commit_before=HEAD^,commit_after=HEAD,plain=yes|Changes caused by HEAD commit]]

Would render to...

>   [[diff:commit_before=HEAD^,commit_after=HEAD,plain=yes|Changes caused by HEAD commit]]

Many functional links exist, some to provide simple navigational links, but also those to provide macro-type functionality to a page's display. Some interesting function links include:

*   *blame*: Creates a hyperlink to the blame for the given file

        \[[blame:file=MyFile|Blame for MyFile]]

*   *cherrypick*: Creates a hyperlink to be able to cherrypick a file and/or given commits

        \[[cherrypick:file=MyFile,commit_before=HEAD~~,commit_after=HEAD|Cherrypick between head and two revisions past]]

*   *clear_cache*: Creates a hyperlink to be able to clear the cache for a page.

        \[[clear_cache:file=MyFile|Clear MyFile's cache]]

*   *diff*: Creates a hyperlink to show the diff for a file, between different revisions

        \[[diff:file=MyFile,commit_before=HEAD~~,commit_after=HEAD|Diff between HEAD and two revisions prior for MyFile]]

*   *history*: Creates a hyperlink to show history

        \[[history:file=MyFile,num=100|Show last 100 edits for MyFile]]

*   *partition*: Creates a hyperlink to partition a file.

        \[[partition:file=MyFile|Partition!]]

*   *move*: Creates a hyperlink to move a file

        \[[move:file=MyFile|Move MyFile]]

*   *revert*: Create hyperlink to revert a commit

        \[[revert:commit=HEAD|Revert the head commit]]

*   *search*: Create a hyperlink to perform a search

        \[[search:terms=something|Search for the term 'something']]

*   *show_commit*: Create a hyperlink to show a given commit

        \[[show_commit:commit=HEAD|Show the HEAD commit]]

*   *stats*: Create a hyperlink to go to the stats for a given document/collection

        \[[stats:file=MyFiles.collection|Wordcount for all files in MyFiles.collection]]

*   *whatlinkshere*: Create a hyperlink to search on "What Links Here"

        \[[whatlinkshere:file=MyFile|Search for files that link to MyFile]]

*   *todo* or *todos*: Create a hyperlink to TODOs specific to a certain directory.

        \[[todos:file=MyFile.dir|TODOs under MyFile]]

*   *tag* or *tags*: Create a hyperlink to search for documents with all of the tags specified,possibly limited to a given subdirectory.

        \[[tags:file=MyDirectory,tag=scene,tag=dark,tag=stormy|A dark and stormy scenes under MyDirectory]]

*   *table* or *csv*: Render an external file as a CSV/Table. 

        \[[table:file=MyTable.csv|My table]]

    Optionally, if you want to render a collection or a list as a table, a specialized tabular output for collections/lists is available.

        \[[table:file=MyList.list|Specialized, sorted list output]]

*   *list*: Render a list (inline, or using an external reference) as an HTML list of document links.

        \[[list:file=MyDirectory/*|All files under MyDirectory]]

    You can also specify an external list/collection rather than using the "file" inline list specifier

        \[[list:list=MyList.list|All files matching MyList.list]]

    You can also limit your matches to documents with certain tags:

        \[[list:file=MyDirectory/*,tag=scene|All scenes under MyDirectory]]

    You can also change the sorting direction of the list:

        \[[list:file=MyDirectory/*,sort=ascending|Files under MyDirectory, listed in order]]
        \[[list:file=MyDirectory/*,sort=descending|Files under MyDirectory, listed in reverse order]]

    You can also specify an "excerpt" length, the number of characters from the matched document that a list will include after every match.

        \[[list:file=MyDirectory/*,excerpt=100|First 100 characters of all files in MyDirectory]]

    You can also change how many "levels" deep a directory will display.

        \[[list:file=MyDirectory/*,display=basename|Only show file names]]
        \[[list:file=MyDirectory/*,display=-2|Show file name and containing folder (but nothing above)]]


*   *edit*: Create a hyperlink to edit a file (and to optionally specify a template)

        \[[edit:template=MyTemplate,file=MyFileToEdit|Edit a file with a given template]]

*   *jot*: Create a hyperlink to a page that has date and time pieces as its path components

        \[[jot:file=MyJournal|Year,Month,Day of Month dash Day of Week]]
        \[[jot:file=MyJournal,format=%Y,format=%m,format=%d|Year,Month,Day]]

*   *template*: Create a small form widget to create a new page with a template

        \[[template:template=MyTemplate,file=Name/Of/New/File|Creates a form to enter a new file name and edit it with MyTemplate template]]

    Additionally, if you do not specify the template, the widget will change to add a drop-down list of all documents tagged with `~template`.

        \[[template:file=Name/Of/New/File|Create new file from list of templates]]

*   *image*: Creates a link to an internal image

        \[[image:file=Path/to/my/image|Image alt-text]]
        
*   TODO: *assoc*

*   TODO: *transclude*

#### Annotations

Annotations in documents are ways to highlight and provide notes to a document without necessarily changing it. This is useful for editing and document feedback, providing additional information for other authors, or being able to refer to other portions of your Giterary repository without disrupting the document's readability.

**Note**: Annotations are "hidden" by default, and only appear when you enable decorations. If you are browsing from Giterary, hit the "Decorations" button at top, or hit "d" to enable the examples below.

    <annotate>
        This text is annotated.
        <comment>This text is "annotating" the original text.</comment>
    </annotate>

Renders to this:

>   <annotate>This text is annotated.<comment>This text is "annotating" the original text.</comment></annotate>

Note that with the above syntax, you need not put the "comment" tags at the end of the "annotate" content, nor are you limited to one comment tag within the annotation.

There is also a simplified syntax for this that allows you to provide  minimally disruptive annotation syntax in your content, and specify your annotation text either explicitly next to the annotate or later in the document (allowing for a "reference" section, similar to the Markdown feature of allowing the definition of a URL to be apart from its usage.

    {The quick brown fox jumped over the lazy dog.}(This is my note.)
    
    ...or...

    {The quick brown fox jumped over the lazy dog}[referencetag]

    ...and elsewhere in the document...

    {referencetag}: This is my note.

Would generate the following:

>    {This is some annotated text.}(This text is annotating)
    
>    ...or...

>    {This is some annotated text.}[referencetag]

{referencetag}: This text is annotating.

The editor has a helper function to quickly generate annotations. While highlighting a section of text, clicking on the "Annotate Selected Text" hyperlinks will prompt you for your annotation comments.

Annotations are displayed as "collapsed" within the CodeMirror editor. To expand them, either move the keyboard cursor into the collapsed region, or hit the Caret in the CodeMirror gutter at left, corresponding to the line in which the annotation resides. An alternative is to hit 'Alt-H'.

#### Dialog Highlighting

Dialog highlighting attempts to show a user the approximate balance between dialog content and non-dialog content in a document.

Dialog content generally follows the format of:

>   "Something something," she said.

Which consists of:

* Content with double quotes, and...
* The quoted content ending with a punctuation mark (commas, periods, exclamation point, question mark, ellipsis,etc.), and...
* Not part of a "list" ("Item 1," "Item 2," and "Item 3").

Note that these are very generic and unsophisticated dialog detection mechanisms, meant to be useful only in common cases. Some consideration is taken for different quoting characters (UTF-8 left quotes and right quotes), but it can only guess as to the different ways you may write dialog.

#### Markdown

[Markdown syntax](http://daringfireball.net/projects/markdown/syntax) is a useful way to write without having to worry about syntax. Markdown is the default syntax when not specifying a file extension as part of its name.

To quote the author:

>   *Markdown is intended to be as easy-to-read and easy-to-write as is feasible.*

>   *Readability, however, is emphasized above all else. A Markdown-formatted document should be publishable as-is, as plain text, without looking like itâ€™s been marked up with tags or formatting instructions.*

This Help document is written in Markdown format. You can view its contents by selecting **Other Options > ...Readable Text** when displayed in Giterary.

There are many great features to Markdown for document formatting. It is highly recommended you familiarize yourself with its function.

**Additional Note**: One of Markdown's features is that it will allow HTML tags to be embedded within it (to provide features that Markdown can't provide). Giterary supports this "HTML pass-through," however, only for a limited subset of HTML (for security reasons).

### Commit Notes

On every editor there will be a "commit notes" field. This field consists of plain text, without any formatting or special syntaxes. This is because this field is intended to record the reasons or reasoning behind your current modifications. Feel like you're using the word "bustle" too many times? Put it in the notes. Moving some things around to sound more punchy? Put it in the notes. Did it start snowing while you were writing? *Put it in the notes*.

Why do this because even if 1 in 10 notes are actually useful, it still means that more than zero notes will be useful to you *in the future*. Over the course of a project you may have hundreds or thousands of edits. If you ever need to sleuth back through your history, you will want to be able to have your notes indicate what your intentions were at the time, or, whatever valuable information you can think to include that is valuable to display along with the changes. Additionally, if annotations do not suffice, the commit notes can serve as a place for your editor to put their notes to describe their overall intent for a change.

However, if meticulous notekeeping isn't your style, you can keep your commit notes blank, and Giterary will report on the added or subtracted word count from your previous version. This is sufficient for small, non-critical edits, but are less useful over time if you only ever leave the commit notes field blank.

### Preview vs. Commit vs. Commit and Edit

While the commit notes for editing are not mandatory, an immediate preview of your document before committing your changes**is**. This is for a few reasons:

* You should be reviewing your edits to make sure your changes render correctly (sometimes Markdown syntax is a tricky business).
* There may be in the future elements in the editor that require server-side processing (and therefore, require a "Preview" to be able to see the render properly).
* To make sure the file hasn't changed out from under you.

You can commit in one of two ways:

* **Commit**, which commits your modifications and then redirects you back to view the file normally.
* **Commit and Edit**, which commits your modifications and then places you back in the editor with your editing place saved. This is useful for continuously writing a document, or saving your modifications directly to the repository rather than relying on the drafts.

### Drafts

When editing a document, a timer will periodically trigger to determine if you have made significant changes in the editor. If so, it will send a "draft" portion back to the Giterary server, containing the contents and the commit against which the draft was being written.

These drafts can be used to recover writing lost after the disruption of an Internet connection, power failure, or a failure to distinguish "Control-W" from "Control-Q" as a keyboard command.

These commits can be recovered by logging in and visiting the "My Drafts" page, which will list all recent unsaved drafts. Clicking on "edit" for a draft from here will load the editor page with the last known draft contents against the document you were editing.

## The Wonderful World of Version Control

git provides powerful tools to allow you to see and manage the changes to your documents over time. Giterary's functionality only brushes the surface of git's myriad uses, and this author would highly recommend you explore its features for use in your writing projects and any others where text could benefit from version control.

### git in a Nutshell

git is a file versioning database. Its sole lot in life is to look at a directory structure, find similarities and differences, record them for the user, and report on them later.

git stores its data as specially organized files alongside the files you ask it to track. This means that while git is a database, its contents are still *files*, which can be stored, moved, and copied, just like any other files on your computer. This specially organized git directory structure is normally under a directory called *.git*. Locations on your computer with these *.git* directories are called *repositories*.

Giterary allows you to edit and maintain your git repository from a web interface. However, many git client applications are available to do this, and implement larger portions of git's functionality. We recommend investigating some of these clients in order to take advantage of git's more advanced features.

### Terms from git

Below are terms from git that Giterary borrows and uses throughout its interface:

*   **commit**: A "commit" is a set of changes successfully made and recorded to a git repository. Commits can contain many types of changes, including:

    * File modifications (to one or more files)
    * File creation (for one or more files)
    * File deletion (for one or more files)
    * Moving a file (or a directory structure)
    * Branch merges (referring to a feature of git called "branching," where totally different versions of a file structure are stored in the same git repository, and they are combined togther). Note that branching is not currently supported in Giterary.
    * Reverting one or more commits by restoring the files modified by the commits to their original states.

*   **log** or **history**: A list of commits stored in the repository, often listed in chronological order.

*   **difference** or **diff**: A feature of git which can compare any two files and programmatically determine similarities and differences, and display them to the users. Often the output of this feature is called a *diff* as well as the process for generating a *diff*.

*   **blame**: A function of git which calculates for a file, line-by-line, the commit, author, and date/timestamp responsible for a version of that line.

### History

Giterary can query your repository's historical log of changes, listing them by commit in reverse chronological order (latest changes first). The history page (available by clicking "Log" in the top navigational element of the Giterary interface) queries the log and displays the results that match your criteria. By default, it shows all changes for all files and all users.

The history log can be filtered in a few ways to show you more specific information:

* You can click on "My Commits" while logged in to see a list of *only* commits which you were responsible for. ("Show me only my commits.")
* While viewing a file, you can click on the "Revision History" to see a list of commits involving *only your viewed file*. This works for all files, as well as files which have been renamed at some point in their turbulent past. ("Show me everything that happened to this file.")
* While browsing history, you can click on either the "hist" link on a given row to see a history of the files included in that commit ("Show me the history of all the things that this change touched.")

You can also use the history/log page to view prior versions of files, as well as calculate diffs and document statistics for a given commit.

To demonstrate, you can [[history:file=HELP|view the history of this file.]].

### Reversion

As the git repository stores versions of files, it is not unreasonable to need to revert a version of a file back to a previous version. In Giterary, a feature is available to revert specific commits in the system.

To do so, use the repository history to find the commit which you wish to revert. Select the commit's unique SHA number (under the **commit** heading). This will bring up a details page on the commit itself, showing author, notes, and the contents of the changes in the commit. If you are logged in, at the bottom of this display is a "Revert this commit" button, which after confirmation, will revert all changes within the specified commit back to their original states. This does not, however, eliminate the history of the repository afterward, it instead generates a **new commit** whose contents revert the changes.


### Comparing Different Versions, or "Diffs"

As described, a "diff" is a programmatic determination of the similarities and differences between two files, or two directory structures. Diffs are used frequently in software development and configuration management scenarios when file modifications must be performed in an automated fashion.

For Giterary, diffs are valuable because they can show the differences between two versions of a file, demonstrated as either additions and subtractions as a result of the modifications necessary to bring one version of the file in sync with the other version of the file. To make viewing and manipulation of diffs easier, Giterary calculates diffs on a "longest word sequence" basis, saying "I added this sentence to the end of this paragraph" rather than "I added 20 words to the end of this paragraph." This becomes important when using diffs in conjunction with other features (cherrypicking, specifically). 

You can calculate diffs in a few ways:

* When viewing a file, you have the option of selecting **Other options > Compare ...to previous version** or **Other options > Compare ...to previous version (no formatting)**. The former version attempts to format the resulting additions and subtractions for a diff in the renderer appropriate for the file's extension, and the latter formats the diff in plain text. Formatting a diff sometimes helps with readability, but additions and subtractions to formatting elements can cause rendering artifacts, in which case, plain text diffs are more readable.
* When viewing history (and with any of the available history filtering options) you can select specific "before" and "after" versions beneath the "show diff" button, and show the resulting diff calculation by hitting the "show diff" button.
* When viewing history, you can select the "head" hyperlink to compare your selected version to the latest "head" version of a file.
* When viewing history, you can select the "prev" hyperlink to compare that commit's "parent" commit against the "child" you selected.

To demonstrate, you can [[diff:file=HELP,commit_before=HEAD^,commit_after=HEAD|view the changes that last occurred to this file]].

### Cherrypicking

Cherrypicking is the concept of taking the output of a diff (with its "additions" and "subtractions") and choosing to keep or discard any number of modifications. This allows you to "revert" changes to a file but on very specific basis (down to reverting single word changes, if necessary).

While logged in and viewing a document, you can select **Options > Cherrypick last change** to bring up a similar interface to the plain diff action. By selecting the adds/removes in the text in the left column, you toggle whether you will keep or discard that change, and the display in the right column will reflect your choices. When you are done, you can hit the "Put changes in editor" button to put your newly kept/discarded elements into a text editor for further editing.

Even while not intending to *cherrypick*, this can still be useful for reviewing changes to a document as it provides a "Next Difference" button, which cycles through the changes in the document in order.

### Blame

Blame, despite its negative connotation, is a very useful too for seeing the history of a document. Selecting **Options > Show ...blame for this file** renders a page that assigns a commit number, timestamp, author, and color code for each to go with each line of the file. This helps to determine the relative "age" of a line in a file, as well as who made the change.

To demonstrate, you can see the [[blame:file=HELP|blame display for this HELP file]].

## Other Features

Other features that may be useful to you.

### RSS Feeds

There is a general RSS feed (Really Simple Syndication) located at:

    http://YOUR_HOST_NAME/YOUR_GITERARY_PATH/rss.php

It shows each commit as a single RSS entry.



### Templates

Giterary allows for a fairly basic form of "templating," by allowing one document to be used as the basis for a creation of another. This is useful for quickly creating a number of documents with similar content.

On any document, you can choose from the "Other Options" drop down the "Use this as a template" item. This wil allow you to use the source page as the initial contents of a chosen target page.

If you have a number of templates which require repeated use (or that their use only differs slightly), there is a function link helper available to create a template form on a given page. For instance:

    \[[template:file=Path/To/File,template=Path/To/Template|Create a new file]]

...will render as the following:

[[template:file=Path/To/File,template=Path/To/Template|Create a new file]]

This allows the user to enter in the target for the template's contents without being forced to navigate to the template each time.

### Relative time display

Often it's less important knowing the date something happened, and more important to know something's relative age.

For most date displays, and particularly those in space constrained elemented on the Giterary interface, dates and times of events (commits, etc.) are reduced to relative time values. For instance:

* *+11min* means something happened between 11 and 12 minutes ago (as of last page refresh)
* *+2H* means something happened between 2 and 3 hours ago.
* *+4M* means something happened between 4 and 5 months ago.

In most places, if the date isn't already displayed, a mouseover tooltip is provided to show the actual date used for the relative time.

### "Jotting"

While there are numerous ways to use Giterary as a journal, or to quickly find a place to write quick notes, there is one way which was implemented to make such things a little less tedious. The "Jot" navigation hyperlink (presented when logged in) will immediately drop you into the editing interface of a file with a naming convention that is convenient for keeping time-based data. For instance, if the date is January 21st, 2013, hitting "Jot" on this date will drop you into an editor for the file:

    myusername/jot/2013/01/21-Mon

These files are useful for recording random thoughts or information without having to worrying about exactly where you want to put it.

You can generate your own "jot" links with the jot functional link syntax. For instance:

    \[[jot:file=yourusername/jot|My Jot]]

...will generate the same "jot" link as is provided by the navigation "Jot" link. The `file` parameter is optional, and can be used to determine the prefix under which the jot file will be targeted. In this case, we want to create our files under `yourusername/jot`

[[jot:file=yourusername/jot|My Jot]]

You can also modify the formats used by the jot function by specifying the format you would like to use for the path elements. For instance, if you wanted to explicitly define the same format as is used in the navigational "Jot" link, you can specify the format like so:

    \[[jot:file=yourusername/jot,format=%Y,format=%m,format=%d-%a|My Jot]]

For each `format` parameter, the jot functional link syntax will generate a new path element.

[[jot:file=yourusername/jot,format=%Y,format=%m,format=%d-%a|Year,Month,DD-Day]]

The "%_" parameters used in the format are those used in PHP's [`strftime` function](http://php.net/manual/en/function.strftime.php). Any format available to that function will be available as a path element on the jot functional link syntax.

Additionally, with functionality similar to as described for Templates, you can specify a template to be used to pre-populate the contents of your target jot. Specify your template path with the `template` parameter like so:

    \[[jot:file=yourusername/jot,template=My/Journal/Template|Next Journal Entry with Pre-populated Content]]

...which will render like so:

[[jot:file=yourusername/jot,template=My/Journal/Template|Next Journal Entry with Pre-populated Content]]

### Tags

Tags are ways to categorize and group certain documents. For instance, you might be organizing your chapters and component scenes into their own folders:

    MyBook/Chapter01/SupportingDocs/Playlist
    MyBook/Chapter01/SupportingDocs/Imagery/Image01.jpg
    MyBook/Chapter01/SupportingDocs/Imagery/SceneLayout.jpg
    MyBook/Chapter01/01-IntroScene
    MyBook/Chapter01/02-SuddenActionSequence
    ...
    MyBook/Chapter02/01-WakeUpInAStrangePlace
    MyBook/Chapter02/02-Amnesia

However, you want to find all scenes that contain characters X and Y.

You can perform a search for a character's name, but that isn't always ideal (what if your character doesn't have a name?). You can try to reorganize your folder structures, but this becomes unwieldy, and can cause a lot of extra work if you end up needing to organize again, or organize in multiple ways depending on what you're looking for.

To address this, Giterary has the concept of "tags," which are the rough equivalent of "categories" in other wiki software. You can tag a document by entering a "~" (a tilde) at the beginning of a line, followed by a word (no spaces). A document can have as many tags as you like. For example:

    Lorum ipsum, etc. Blah, blah

    ~scene
    ~character_name
    ~angry

This makes this document appear when searching for tags "scene," "character_name," or "angry."

You can search for tags by clicking on the "Tags" hyperlink in the navigation bar. This will provide a list of all available tags (which you can then click on to search on documents tagged as such). You can also navigate to a directory view of a particular page, and search for a tag, limiting the scope of the returned tags to that particular directory structure.

Additionally, there is a function link you can use to create automatic tag searches based on specifications you provide. For instance:

    \[[tags:tag=scene|All Scenes]]

Would render to something like:

[[tags:tag=scene|All Scenes]]

Note that you can "combine" tags, such that documents returned by the search have to contain *all* of the tags you specify (not just one). For instance:

    \[[tags:tag=authorbias,tag=fireflyreferences|All docs referencing Firefly, with Author Bias]]

...will search for all documents that have both the tag for **authorbias** and the tag for the **fireflyreferences**:

[[tags:tag=authorbias,tag=fireflyreferences|All docs referencing Firefly, with Author Bias]]

Tags can be a powerful mechanism for quickly accessing documents across multiple file structures. They are, however, only useful for documents that are tagged, and tagged consistently. It is advised that some forethought is put into how you may want to tag/categorize your documents in the future, particularly as a project scales in size and complexity.

Some potential uses for tagging include:

*   Tagging scenes/chapters with the characters involved
*   Showing which documents refer to a particular plot thread, foreshadowing, or later reveal.
*   Organizing scenes/chapters by their setting ("The Battle Room," "At the office")

### TODO and TBD searching

Sometimes it is daunting to approach a document or a series of documents all at once. Case in point: Giterary's help documentation. When writing the documentation you will think of things that need to be written, but aren't part of your focus at the immediate moment. Switching gears will make you lose your train of thought, but it might slip your mind if you ignore it for too long.

This is where "TODO" patterns are useful. When logged in, you can visit the [TODO](todos.php) link next to the "Jot" link in the navigation next to the search bar. This displays all lines in documents that contain either "TODO" or "TBD".

These lines can serve as anchors for later work. You can also use them to describe the basic intent of a section, and then work through your "TODOs" as time allows. As you eliminate the items in your TODO list, you get closer to completing your work.

It should be noted, though, that plain TODO listings **will** display in the document rendering.

## Associations

TODO

## Aliases

TODO

----
