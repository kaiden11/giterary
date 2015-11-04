# Frequently Asked Questions

*   **I'm not technical enough to set up Giterary, but I like some of the features, and the price is right. What can I do?**

    I was once tasked at work with installing up a homebrewed Linux distribution. As part of its install process, a single-user mode prompt would drop me into an install script. That script began with a note from the author that said, "I never promised you a rose garden..." (quoting the [1970 Joe South/Lynn Anderson country song "Rose Garden"][rosegarden]) and finished by threatening to smash my hard drive platters if I didn't understand something. It succeeded at making me both feel incredibly alone and helpless in my task, as well as made me feel like I never wanted to ask the author for help. Which I think was the desired effect.

    I made this Giterary thing, and my only good measure of success is getting people to use it, or want to use it. I don't offer a lot of promises (and I'm a terrible gardener), but I do want to be available to help people set this up as well as get it so that more people can set it up. As I develop this further, I intend to try to make it easier to get Giterary deployed to any operating system, anywhere, with its full features enabled (git repository synchronization, etc.). However, configuration and installation are hard things, even if you have an extremely small list of dependencies.
 
    Right now it's easiest to install on Linux (as that is what I've been developing Giterary on). If you're installing on Linux, there is a *install.sh* script will take you through collecting your various bits of configuration information, setting up your directory structures, permissions, usernames, passwords, and other fun things. See the [[INSTALL|INSTALL documentation]] referencing the *install.sh* script for more information. It requires being a little bit tech savvy, and a familiarity with Linux-y types of things, but nothing insurmountable.

   *(Also, if you're curious, I did get the "No Rose Garden" Linux installed, and everything turned out easier than expected. And I learned a few things along the way. And so, too, will you, intrepid adventurers!)*

[rosegarden]: http://en.wikipedia.org/wiki/Rose_Garden_(Lynn_Anderson_song)

*   **Markdown is *okay*, but I like X better. Do you support that?**

    Probably? Is it text-based? Is it easy to edit from an HTML textarea tag? Well, there are certainly places to add such things, but there isn't anything particularly fancy. For the programmers out there, it's a PHP switch statement, that passes off handling to a function that is assumed to return valid HTML. However, a lot of work goes into making Markdown-specific editing possible (with the addition of annotations, the live Markdown preview, etc.)

*   **Editing with textareas is pretty lame. Why don't you use ckeditor? Or something more modern, or Javascript-y, or HTML5 friendly?**

    In the same way that text files are hard to screw up, so too are textareas. Every browser can render them, every person can use them. Not to disparage the writers of editors like ckeditor, or the users of Giterary, but sometimes it's nice to have something familiar, even if it is sort of lame.

    There's also that, in the event of complete Javascript failure (or you run NoScript), your browser should be able to render the textarea just fine, and **also** be able to preview and commit your changes without need for the client-side Javascript. I'm a fan of elegantly degrading web applications.

    That said, you have options:

    * Greasemonkey scripts let you do all sorts of interesting things to web sites. You could apply something like a [Vim emulator][vim-in-textarea] on top of the editing window, or any strange, awesome thing you can think of to make the textarea more useful to you.
    * Browser plugins provide similar modified editing and browsing experiences (though, cursory searches don't really bring up anything that improves upon the textarea experience).
    * Or even better, clone your Giterary repository locally, edit using your favorite text editors, and synchronize whenever you see fit. Some of my favorite text editors:
     * [Vim][vim] (or [Emacs][emacs], if you're into that sort of thing)
     * [Notepad++][notepadpp]
     * [TextMate][textmate]
     * [SublimeText][sublimetext] (haven't used it personally, but the things I hear is that I should).


[vim-in-textarea]: https://github.com/jakub-m/vim-in-textarea
[vim]: http://www.vim.org/
[emacs]: http://www.gnu.org/software/emacs/
[notepadpp]: http://notepad-plus-plus.org/
[textmate]: http://macromates.com
[sublimetext]: http://www.sublimetext.com/


*   **I don't understand why I would ever use partitioning. Why would I ever want to split up my document?**

    Valid question. It's more of a feature for people who, prior to writing in Giterary, or prior to sane document management practices, worked only in single, monolithic documents (see: one great big Word document, or something like that). A feature like that is useful because it lets you split up a larger work into smaller, more manageable chunks.

*   **Something broke, and my repository is messed up. Giterary isn't helping at all. I don't want to start over. Please help.**

    More often than not, an external git client, graphical or otherwise, will be able to answer your git repository's quandary better. At very worst, copying your working directory out, and cloning from your last known good repository state should be sufficient to not lose what is in your working directory.

*  **I put something in my repository that is, um, "sensitive." As in, it needs to be gone. Really gone.**

    It happens sometimes. Usually, performing a "hard reset" to a commit before the sensitive information was in the system is sufficient.

        git reset --HARD COMMIT_BEFORE_BAD_STUFF_HAPPENED

    But sometimes there are things you want to keep that happened *after* the sensitive information got into the system. In that case, you have to use git to "rewrite history." For this, Github [has a decent article on how to do just this](https://help.github.com/articles/remove-sensitive-data).

*   **Is Giterary supported on X (X being my operating system or computing platform of choice)?**

    PHP, being an interpreted language, does not require compilation, and therefore, is supported on any platform a PHP interpreter is available (version 5.3). However, you also need git to be able to run git, and a web server capable of serving PHP scripts, and your operating system needs to support the type of piping used by the [*proc_open*][proc_open] function in PHP. But other than that, pretty platform agnostic, I'd think.

[proc_open]: http://php.net/manual/en/function.proc-open.php

*   **I have a healthy degree of paranoia, does Giterary support file encryption?**

    Not at the moment, but it's an interesting and precarious feature. For instance: you could implement it such that you would have to enter an extra password in order to "unlock" a file for editing. However, unless said paranoid individual isn't *also* paranoid about their network traffic, it's possible that the submission of that password could be intercepted.

    So: it's possible. An extra extension handler, plus perhaps a modified form element, and you'd be in business. But it certainly wouldn't be perfect, and would play hell with Giterary's diff mechanisms.

*   **What coding conventions were used in the application, if any, you talentless hack?**

    You can read about some of the [[CONVENTIONS|programming conventions]], if you really want, but it's mostly the technical grandstanding and pseudo-philosophical ravings of a madman.

*   **What license is Giterary released under?**

    Giterary is licensed under the GNU Public License, version 3. License text is (should be) included in your distribution of Giterary.

*   **There already exist industry standard word processing and novel writing tools, cheaply available, reliable, and better documented than this. Giterary is destined to fail.**

    Wow. No punches pulled. Also, I didn't see a questionmark. But alright, let's dance.

    Consider shovels: levered surfaces with varying shapes and sizes. Flat-headed shovels are good for loose material, pointed shovels are good for digging into soil or cutting through grass/sod. Snowshovels are specialized and great for moving snow, but not much else. You could even say that a post-hole digger ({which digs holes for posts}[firefly]) is a type of shovel for removing soil in a small, deep, cylindrical area (but probably not good for much else).

    The point is: nobody in the shovel industry, or any person whose profession requires moving soil, rocks, snow, or digging posts, will say that an industry standard exists against which no other shovel can compete.

    However, the way people use computer software is weird, in a fashion that the way people use shovels is not. People agree to use products like Word, or Excel, or PowerPoint, or Outlook, or Scrivener, or Photoshop, or Premiere, or AutoCad, or Windows, or iOS, etc., because they are marketed with the notion of *trust* and *support*. Perception of trust in software far outweighs any set of features, performance, or cost, as if people trust something, they'll pay *anything* for it. You may pay extra for a metal blade on a shovel or a nicer grip, but you'll never buy a shovel because you trust it the way you would Photoshop.

    *Trust* sells, because people don't inherently *trust* their ability to use computers. And software companies know this, and design and market their software such that that trust is maintained. Companies hold on to proprietary file formats for as long as their industry can tolerate it. Schools are sold discount copies of hardware and software so that teachers will teach their product, and children grow up thinking that a certain product is accepted or necessary for a computing task. Universities sell student copies of software cheaply to ensure career-bound students are most familiar with a given application, and are taught that they are the industry standard tools.

    That said, software is hard to make. Good software even harder. Millions of man-years have gone into the various versions of industry-accepted applications, and as such, they tend to be better products. But software should be treated as tools, not as emotional attachments, and should be evaluated on their effectiveness.

    Giterary tries to fill a niche I think is poorly addressed, or poorly architected in other tools. It is simply a mashup of already established software, freely available on the web, but I chose that software because it did its job, it did it right, and did it in exactly the way I wanted. Settling for a tool just because "everyone else uses it" is silly. It should be the right tool for the job, based on features, time saved, learning curve, and price point.

    Additionally, Giterary is unique in that it encourages users to put their work into files and formats that are **not** beholden to any software license or encoding scheme. Giterary *wants* you to use other, better tools, and let Giterary handle the stuff that those tools don't. It seems silly to argue against the doom of software obscurity by saying that I make it easier to *walk away* from Giterary, but that's not really the feature that brings users back. It's that you are *allowed to choose*, where otherwise you are not.

    My hope is that as people become more computer literate, and less tolerant of their "*industry standard*" tools, they'll realize that sometimes a post-hole digger isn't the best for moving snow.

        /soapbox

{firefly}: Obligatory Firefly reference, please forgive me.


*   **Does Giterary support X method of authentication? Y method of authorization? How can I give person A permissions to do this, but not person B? Can I shout the words "Active Directory" three times and be able to log in successfully?**

    There is only so much I can do to predict what people will use Giterary for, or who the will want to be able to use Giterary. If you or your organization require complex rules to determine who can do what and where and when in Giterary, you might be looking for a Content Management System (such as [Wordpress][wordpress], [Drupal][drupal], etc.), which solves this problem much more elegantly.

[wordpress]: http://wordpress.org/
[drupal]: http://drupal.org

    That said, Giterary's barrier to entry on creating new but only slightly different things for authentication and authorization is pretty low. If you can easily solve a problem with a single PHP class, then it's pretty easy to wire up. It is also fashioned such that you can easily have multiple "registered" things that can tell you if you're allowed/not allowed to do something, keeping from making your dependence on one and only one "thing" to answer all questions. It asks all of its registered things if you can do something until either if runs out of things, or gets an "affirmative" answer.

*   **What the heck is this gibberish you turned my passwords into? And why, uhm... why does it still work?**

    These are password "hashes," or, algorithmically generated values that are hard to guess their original value. These enable Giterary to answer "Does this person have the right password?" without ever having to store their password. Giterary does this by taking the same hashing algorithm as was used in your password, and performing the hashing on your submitted login password. If the resulting hash matches the stored hash, the passwords match, and you can successfully log in.

    [[INSTALL|The install documentation]] has an in-depth description of these, how to generate them, and how to enter them into your *passfile.csv*.

~authorbias
~fireflyreferences