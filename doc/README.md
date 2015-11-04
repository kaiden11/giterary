## "Just write," they said, "It doesn't matter what tool you use..."

Writing is the hard part, everything else is just *formatting*. You could scratch your novel into a wall with a rock, or use a modal command line text editor, the point is: *you should be focusing on writing*. Your tool should be a secondary concern.

## ...says the README file trying to sell me on a writing tool...

Right? I'm losing the sale before the customer even drove the car, but Giterary isn't really selling something (it happens to free, open source software, which brings together [other][git], [powerful][markdown], [freely][php] [available][jquery] [tools][showdown]).

So, what does Giterary do as a writing tool? Giterary is a tool that suggests a better way to work, helps you if it can, concentrating on making the hard things easy, and keeping the simple stuff simple. It can get out of the way if you want to *just write*, but it'll make your life less complex down the road. 

## A novel is more work than you would ever think

Consider the scale of a novel. For the most part, published writing projects weigh in at around ~50,000 to 100,000 words (and often more). They can take years to write, after which they tend to require extensive editing, re-reads, re-writes, and whatever requisite iterations it takes to make a better product.

Now consider a machine with ~50,000 to 100,000 moving parts. A *novel-machine* that takes a human's attention as input and outputs compelling plot, worthwhile characters, and subtlety. As the engineer/author, it's your job to fabricate the machine parts, construct them correctly, and maybe apply a layer of paint. Sure, maybe it works for you the first time you run it, but when you ask your best friend to take a spin it seizes midway past the second-stage turbine spooling. You walk slowly through the smoldering wreckage. Your friend is alright. Your pride is not. And you forgot to take notes the first time around.

The point is: if *writing* a novel is hard, *maintaining* a novel is complex. The latter of which is overlooked when *just writing* is a primary concern.

**Giterary offers a set of *nice things* to write and maintain a novel**, managing complexity at both ends of the process, and hopefully without you noticing. How does it do this? Well...

## When programmers are lazy, everyone benefits.

Computer programmers look at your machine with 100,000 moving parts and laugh. Software weighs in at **tens of thousands to a few million** lines of source code. They don't even bother to count the words, were there even words to count.

They laugh because they solved the management of that complexity a long time ago. Lo, and they chanted in their sing-song way, *"A hundred people need to work on something with at least a million lines of code. And they should all be able to work at the same time, and be able to detect and communicate their changes efficiently, and maintain detailed histories of their changes, and gracefully solve conflicts, should they arise."* And thus it was so, and they've been improving on this concept ever since.

If your novel were like the programmers' source code, you'd be able to manage your novel with these all-powerful tools of the programmer gods. The thing is, your novel **is** source code, much like its words are part of your *novel-machine*. It's just a matter of *formatting*.

## That's nice. So what does this do, again?

Giterary is a wiki application with a cool synchronization tool. It helps you write, and helps you maintain your novel.

Giterary suggests you write your chapters in Markdown syntax (but you don't absolutely have to). Markdown is a text-based format that lets you do complex formatting by *just writing*, and largely ignoring things like formatting, markup, and tedium. It also extends Markdown's functionality by allowing textual annotations and "wikilinking" between your files, similar to Wikipedia, letting you build a web of references to information you need.

Giterary puts these text-based files into a hierarchical directory structure of your choosing, and manages these files using a git repository. The git repository stores all versions of all files, and Giterary wraps around the git repository to let you easily see information about your files, down to showing you when you swapped out "she said" with "she said, languorously."

The killer feature, though, is git's ability to act as a distributed database. You can take the entirety of your Giterary instance, clone it to any computer, make changes, and then push them back. Need to work offline on the plane, but submit your changes once you're back in civilization? Not a problem.

## Nobody has time for that noise, I just want to write. Why would I need that?

Three reasons:

*  **You are forgetting all the time. You just forgot something.**

   I barely remember last week. Yesterday's already fuzzy around the edges. Things I wrote a year ago might as well have been written by a different person, with different hopes and dreams. And while I can't talk to that person, using Giterary will let me see what that stranger did, and if they provided notes, see what they were thinking.

   Giterary helps you talk to the past, and leave messages for the future.

*  **It may not only ever be *you* making the changes.**

   You never thought about **that**, did you, hot shot? Well, yeah, I guess maybe you did. The possibility of multiple authors isn't unheard of, but that pales in comparison to the most common case: you will eventually need to turn your things over to an **editor**.

    You *can* submit a massive, monolithic Word document via email and hope *Track Changes* does the job. But what do you do while you're waiting for them to send your document back? What if you're still working on a chapter? How do you maintain which is the "master" copy, and which is for edits? Do you **copy-paste** your edits, like the beasts of the wild?

    If you feel shame, that's okay. It means the healing can begin. Giterary provides an intelligent system to manage change, and to do so immediately, letting you maintain a sane workflow for you, your potential co-authors, and your editors.

*  **Backups are nice. Backed up git repositories are nicer.**

    Giterary isn't a backup tool, but this is because other things solve this problem much more reliably and elegantly. Getting automated backups should be a concern of anyone who can't afford to lose files.

    However, assume bad things happen: your hard drive craters, your computer is stolen, or you accidentally deletes files. It's fine, because you backed up your files, right? *Right?* Right. But then the question becomes: when was your last backup? Did that have the latest version of X, Y, or Z? You can look at the timestamps on the files, but is that the time when the files were backed up, or when the files were last modified? What if you have two backups, both containing information you need?

    With a git repository, you can simply **ask**. And you can synchronize changes between one or more git repositories, even if changes were made at different points in time. And you can do it with freely available tools. Again, Giterary isn't a backup tool, but it *can* make putting the pieces back together much less daunting.

While storing metadata for changes to wording may seem like information overload, you can ignore it until you absolutely need it. The *nice thing* about Giterary is that it gives you a place to do these things while letting you focus on *just writing*.

## Why would I need to organize my information this way?

Sure, everyone has their own way to deal with their information. But there are a few *nice things* to the way Giterary deals.

*  **Text files abide by the law of least surprise.**

   Text is text is text. Files are files are files. Directories are, well, a bit more complicated, but you see what I mean. File formats change, features come and go, and registration keys get lost. Text files are the most basic way to store your information, and frankly, are hard to screw up. Most important: vast arrays of editing tools are available (far grander than Notepad), and are available *everywhere you will ever turn on a computing device*. You will never have to hunt down a copy of proprietary software that supports your file format (Word), nor worry that other programs won't support the features of the one you wrote in.

*  **You maybe should be dividing up your novel anyway...**

   *Just saying*. Why wouldn't you try to divide out the pieces of your work into separate files? Or organize your reference documents and glossaries and appendices in a structured way? 

   The reason I can think of for not doing so is because eventually you'll have to stitch them back together. This is easy for a computer to do, so why can't you work on your smaller pieces, then tell the computer to put them together when you're done? Giterary supports this, both in partitioning out documents into smaller files, as well as automatically creating "collection" files to stitch them back together. Plus, you can modify your collections to change the order of your documents *without changing the underlying documents*.

*  **A wiki lets you create information *and* an interface to it at the same time**

    This sounds strange, but you see it every time you visit Wikipedia. The content of a document is well and good, but it's the links within a document that lead you to the most interesting things. With a series of wikilinks, you can turn a normal document into a navigational structure. You can build chapter lists, character sheets, TODO lists, glossaries, indexes, whatever you want. And you can do it using the same syntax you use to write your story.

## But what about the Otaku-Neckbeard-Hacker crowd?

I'm not a writer, though I once had hopes and dreams. I am, instead, a programmer who likes words and how they go together. I know writers, though, and editors, too. Though my fervor for the nerdier portions of this project burns bright, their practicality and intolerance tempered Giterary into a finer steel.

That said, there are still some things in here for those who share my mindset and my apathetic facial hair.

*   **Minimal system requirements**

    The application requires PHP, a web server to serve the PHP scripts, and the git executable. Nothing else. Everything is self-contained. *Everybody fights, nobody quits*.

*   **A simple codebase. The kind you'd like to start with, but not have to build yourself.**

    Do you like learning an entirely new object model every time you need to modify a project? A grand new set of design decisions and limitations that were made without you, and never documented? Me neither. Use of OO in the PHP backend is limited, preferring a series of functions that wrap around git CLI functionality and display logic. Need complex, stateful logic for something? Great. I hope my functions help when you wrap them into your objects.

    There's a simplistic templating engine, a few performance tracking modules, and some file caching, but nothing special. Everything is PHP, Javascript, and CSS, with the necessary parsing and rendering libraries included (Markdown, Showdown, jQuery, Tablesorter, etc.).

*   **Edit how you want, git how you want.**

    Don't like the Giterary interface? Prefer Notepad++? TextMate? SublimeText? Vim? Emacs? Something with an even greater sense of vague technological elitism? Great! Edit with that. I won't be sad. Edit locally, and upload via your git client. That is, whichever of the dozens of beautiful, full-featured git clients roaming the wild. There is a git post-receive hook that will keep your web application up-to-date, but you can do with it as you like. Decide you hate Giterary and want to set up shop elsewhere? Certainly. It's git, and it's files. Go nuts.

## Okay, but can you do anything to sweeten the deal?

Um, sure? I wasn't going to mention, but since you asked...

* CSV file support, with client-side sorting, default sorting hints, and wikilink syntax, perfect for establishing that timeline of geological events in your world, cross-referenced and tagged according to the events in your novel.

* Being able to show which reversions, which author, and on which date every line of a file was last touched (called "blaming" in git-tongue). **Every line**. We don't skimp.

* Don't particularly care for your editor's last few edits? Well, reconcile your professional relationship by cherrypicking their edits ("canceling" or "restoring" edits, word by word). After you've corrected their obvious errors, drop the results into your editor to really show them the meaning of artistic license!

* Trying to balance dialog versus exposition? Giterary's dialog highlighting detects common "talk-y" looking quotes segments and can highlight them apart from normal text.

* Keep forgetting what's left to do? Insert a "TODO" line in your document, and then come back to it later from a specialized "TODO" search.

* Need to shove a bunch of things into one document? Don't feel like copy-pasting a bunch? Good! It's because you should **never do this to begin with**. Create a document collection, allowing you to render any Giterary files, in any order, and according to each file's specific rendering mechanism (Markdown, CSV, Text-only).

* Have a massive manuscript you need to split apart into workable segments? Document partitioning will allow you to load a document, select "partition boundaries," and even name the new partitions ("Chapter 1," "Chapter 2," etc.). It'll even create a new collection to provide a way to stitch your original document back together.

* Annotate documents and be able to "mouse over" the notes, but only when in "Decorations" mode. In fact, Giterary won't try to "decorate" your document in any way (past your specified formatting) unless you tell it specifically to "Decorate" a document. Nothing is more offensive than [somebody else's bad decorating choices](http://lh4.ggpht.com/_lkfp0Y8ol_U/TUnZ5E_G1RI/AAAAAAAAGVU/UedcGkystMQ/s400/DSC_0290.JPG).

* Annd... ah, well... you can search for things? Searching is fun. There is a search. And you can navigate your directory structures! It's an adventure all to itself.

*(Side note: It can also do things like show you dictionary word counts, auto-generating tables of contents, auto-saving drafts, etc., and other common word processing features, but those are pretty run-of-the-mill, so you can discover those for yourself.)*

## Whether by pity, morbid curiosity, genuine interest, or an intersection thereof, I feel compelled to give it a chance...

That strange compulsion is your keen technological and creative intuition talking. It means he or she is proud of you for getting this far. I, of course, never doubted you for a second.

From here you can:

* View the [[INSTALL|installation instructions]]. They're a little involved, but not insurmountable.
* Look at the [[HELP|help file]] to get a sense of how to use Giterary (it's not too bad).
* Kick around on the [playground](http://playground.giterary.com/) to see the application in its full glory.
* Check out the [[FAQ|Frequently Asked Questions]].

--

Thanks for reading. Happy writing!


[git]: http://git-scm.com/
[php]: http://php.net/
[markdown]: http://daringfireball.net/projects/markdown/
[jquery]: http://jquery.com
[showdown]: https://github.com/coreyti/showdown
[reddit]: http://www.reddit.com/r/writing/wiki/faq

~authorbias
