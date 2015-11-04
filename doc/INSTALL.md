# Installation Instructions

The following are general guidelines to getting Giterary running. These instructions are biased towards the Linux side of things, but there is no reason an enterprising individual couldn't get them running on Mac OS or Windows. They also assume you have a basic working knowledge of the components of the Internet, how to configure your systems to run "services" or "daemons," or a certain quantity of patience and a willingness to Google something if you don't understand it.

## Requirements

*  **A computer capable of running [PHP 5.3][php] or greater and [git 1.7.X.X or greater][git].**

   These are widely available from many different Linux distribution packaging mechanisms (Debian and Ubuntu's apt-get, Centos and Fedora's yum, etc.) Binary packages are also available from their respective sites for all operating systems.

*  **Web server software that supports running PHP**

   Numerous pieces of web server software support the use of PHP. Many LAMP setups are pre-configured simply for this ("Linux, Apache, MySQL, and PHP").

   * [Apache][apache] (Available on Linux, Mac OS, and Windows)
   * [Nginx][nginx] (Available on Linux, Windows, probably Mac OS)
   * [IIS (Windows)][iis] (Available on Windows)

    While installing these HTTP servers you should ensure that the steps taken to enable PHP have been taken before proceeding. Those steps are beyond the scope of this document, but are well documented elsewhere. There are also numerous "appliance" virutal machines available on the web, pre-configured to support your flavor of HTTP server. If you are installing to "the cloud," this may be an easier route than configuring your own HTTP server (though, we would invite you to learn about such things, as they are valuable things to be knowledgeable about, and makes you a blast at parties).

    * [TurnKey Linux][turnkey]

TODO: More places to get Linux LAMP

*  **OPTIONAL: An SSH server program (if you are doing git-over-SSH synchronization, which is recommended)**

   git has the ability to push and pull from multiple sources, and using multiple network protocols. The most reliable and conventionally accepted appears to be SSH, which provides encryption as well as authentication. If you plan on "synchronizing" another computer with your Giterary instance, you will want to have an SSH server or git-capable equivalent to provide access to your Giterary repository.

    * [OpenSSH][ssh] is widely supported on Linux, Mac OS, and on Windows.

*  **OPTIONAL: A git GUI client**

    You will need the command line git client installed. However, there are a number of GUI clients (graphical user interfaces) that are a lot more friendly to human eyes.

    It is highly recommended that you keep a git client handy, just in case you don't like how Giterary does something, or that you have to do something Giterary doesn't.  These tools let you interact with your Giterary repository in lots of nice ways. They also provide a more forgiving git learning tool than the command line, man pages, and frustrated Google searches. I recommend the following:

    * [TortoiseGit][tortoisegit] (for Windows)
    * [SourceTree][sourcetree] (for MacOS)

## Latest Version

Grab the latest version of Giterary from:

*   HTTP Download

    TODO: Hosting?

*   git clone

    TODO: Github? Bitbucket?

## Verify PHP versus Your Web Server

Every web server has a directory into which you can put files, and it will serve those files, for better or for worse.

Much of the configuration headache for web servers is getting the names, access rules, and pointers to the right directories into place. We leave it up to the reader to maintain their own security, but take heed when we say that the intent of a web server is to *put things on the Internet*. Be sure that you check to see if things are exposed that should not be (for instance, if you Giterary instance is exposed to whoever happens to be sitting next to your at the airport terminal).

Most important to establishing this "hosted directory" is ensuring that PHP is configured correctly. Within your "hosted" directory, place a file called *test.php*, which has only these contents.

    <? phpinfo(); ?>

Afterwards, using your web browser, browse to a URL referencing your host and the *test.php* file, for instance:

If you plan to be accessing Giterary from only your own machine:

    http://localhost/test.php

If you are installing to a separate server:

    http://myserver/test.php

If you are installing Giterary to a place other than the URL's "root" (referring to the "/giterary/" portion of the URL not being at the "root" of the path after the *myserver* host specification.)

    http://myserver/giterary/test.php

This page should contain an output from the *phpinfo* function that lists all of the compilation, configuration, and dependency information for your PHP installation. It will be purple.

If this is running successfully, then you have a working PHP configuration for your web server. *Good job!*

## The Easy Way, or The Hard Way (install.sh)

There is Linux install script that tries to walk you through your various configuration necessities (names, paths, usernames, passwords, etc.). It isn't particularly sophisticated, and isn't particularly platform agnostic (has only been ran on Ubuntu), but it's a lot easier than wading through all this mess.

It is located in the root directory of the source code, called *install.sh*.

If successful, it sets you up with a Giterary instance with:

*   A working git repository
*   Reasonable directory structure for git repository, cache, temp, draft, and session information:
    
        /var/lib/giterary
        /var/lib/giterary/repos/your_repo_name
        /var/lib/giterary/caches/your_repo_name
        /var/lib/giterary/temps/your_repo_name
        /var/lib/giterary/temps/your_repo_name/drafts
        /var/lib/giterary/temps/your_repo_name/sessions

*   Initializes your git repository, sets an anonymous user for the repository (required from operating from a web application like Giterary), and other small git repository configurations.    
*   A "passfile.csv" configuration, where a special CSV is added to Giterary that contains your username, git user information, and hashed password (pointed to with the *config/auth.php* config file). It is then configured as a "sensitive" file that only only be edited by the username your enter (with the *config/permissions.php* config file).
*   Adds your DEFAULT_FILE to the repository with a welcoming message.

This is a fairly straightforward and secure configuration out of the box.

## Verify Git Is Working

From your operating system's command line (any terminal in Linux, Terminal in MacOS, the *cmd* MS-DOS prompt in Windows), attempt to execute the following command.

    git --version

It should print out something like this:

    git version 1.7.4.1

Alternatively, if your environment paths are somehow incorrect, you may need to be explicit in the path to your git executable.

To find your explicit path on Linux and MacOSX, you can issue the following command:

    which git

...which should return the explicit path. In my case, it returns this:

    /usr/local/git/bin/git

On Windows you may have to search the filesystem for *git.exe* and use the explicit path it returns.

With your explicit path, run the following command (but with your explicit path):

    /usr/local/git/bin/git --version

This should give you version information for your installation of git.

Verify the security configuration on your system for your git client. **It needs to be executable by the user that is running your web server**. This is critical for Giterary to function.

Note this path value, as this is the path you will need to use later in the Giterary configuration.

## Create Giterary directories

Giterary needs a few directories to run:

1.  **The directory of the git repository.**

    This is a directory that will need to be readable/writable, and will store the git repository and its working directory. Recommended locations:

    Linux or MacOSX:

        /var/lib/giterary/repos/NAME_OF_YOUR_REPO

    On Windows:

        c:\programdata\giterary\repos\NAME_OF_YOUR_REPO

2.  **The directory for Giterary to store "cache" information to make the application speedier.**

    This is a directory that will need to be readable/writable, and will store the Giterary cache files to help make browsing via Giterary speedier. Recommended locations:

    Linux or MacOSX:

        /var/lib/giterary/caches/NAME_OF_YOUR_REPO

    On Windows:

        c:\programdata\giterary\caches\NAME_OF_YOUR_REPO

3.  **The directory for Giterary to store "session" information that allows you to be logged in to the application.**

    This is a directory that will need to be readable/writable, and will store the PHP session files that allow you to log in. Recommended locations:

    Linux or MacOSX:

        /var/lib/giterary/sessions/NAME_OF_YOUR_REPO

    On Windows:

        c:\programdata\giterary\sessions\NAME_OF_YOUR_REPO

4.  **The directory for Giterary to store "temp" data, like draft files and file comparison inputs.**

    This is a directory that will need to be readable/writable, and will store the PHP session files that allow you to log in. Recommended locations:

    Linux or MacOSX:

        /var/lib/giterary/temp/NAME_OF_YOUR_REPO

    On Windows:

        c:\programdata\giterary\temp\NAME_OF_YOUR_REPO

These locations are relatively arbitrary, and you could potentially combine the cache and session directories into one (but never the repository directory, that must remain apart from everything).

## Oh, the config files...

There are a series of configuration files necessary to getting Giterary up and running. They are all PHP files, and can be edited using any text editor. They are located within the *include/config/* directory of the Giterary source. They are situated such that one could have multiple instances of Giterary, but with different *include/config* directories, and the instances would be able to run simultaneously.

###### base.php

*  **base.php**

    *base.php* is the largest configuration file, as it contains references to the most basic portions of Giterary that are necessary to run ("Where is my repository?", "What is my name?", etc.). Below is a line-by-line description of the configuration values within *base.php*.

    The following are **required** fields.

    *SITE_NAME* and *SHORT_NAME* are display values for showing on the Giterary interface which instance of Giterary is running. This is where you put a name for the "title" of Giterary.

        define('SITE_NAME', "My New Story");
        define('SHORT_NAME', "New Story");

    The *BASE_URL* is the base HTTP URL that will be used to navigate to your Giterary instance. This is used in link generation.

        define('BASE_URL',"http://myserver/giterary/");

    The *DEFAULT_FILE* gives the name of the first file to be created within your Giterary instance, and the default file to be displayed when no file is specified (likely the first file that will be displayed with accessing your Giterary instance).

        define('DEFAULT_FILE', 'Home' );

    *SRC_DIR* is the filesystem location of the source files for Giterary.

        define('SRC_DIR',"/var/lib/wwwroot/giterary/");

    *GIT_PATH* is the filesystem path to the command line git client on your server.

        define('GIT_PATH',"/usr/local/bin/git");

    *GIT\_REPO\_DIR* is the path to the readable/writable directory that contains the git repository for this instance of Giterary.

        define('GIT_REPO_DIR',"/var/lib/giterary/repos/NAME_OF_YOUR_REPO/");

    *CACHE_DIR* is the readable/writable directory for maintaining caches of certain data within Giterary.

        define( 'CACHE_DIR','/var/lib/giterary/caches/NAME_OF_YOUR_REPO/' );

    *CACHE_ENABLE* is a value of 1 or 0, 1 being that use of the cache is enabled, 0 is that it is disabled.

        define( 'CACHE_ENABLE',   0 );

    *TMP_DIR* is the temporary directory for temporary files generated by Giterary. It can be under the same directory as the Giterary cache.

        define('TMP_DIR','/var/lib/giterary/temp/NAME_OF_YOUR_REPO/' );

    *DRAFT_DIR* is the directory for storing draft files within Giterary. It can be under the directory as the Giterary cache.

        define('DRAFT_DIR','/var/lib/giterary/temp/NAME_OF_YOUR_REPO/' );

    *SESS_PATH* is the directory for storing cookie information for interacting with your browser.

        define('SESS_PATH', '/var/lib/giterary/sessions/NAME_OF_YOUR_REPO/');

    The following are **optional** fields.
       
    *STYLESHEET* and *CSS_DIR* are relative URL references to the default CSS to be used by the Giterary application.

        define('STYLESHEET', "simpler.css");
        define('CSS_DIR', "css/");

    The following are variables for configuring cookie information for communicating login session information with your web browser.

        define('COOKIE_DOMAIN', 'www.YOUR_DOMAIN_HERE.com');
        define('COOKIE_PATH', '/');
        define('COOKIE_EXPR_TIME', 86400);
        define('SESS_NAME', 'GITERARYSESSION');

###### permissions.php

*  **permissions.php** and **permissions.lib.php**

    *permissions.php* is the configuration file for how users are permitted to perform certain actions within Giterary. *permissions.lib.php* is a set of common permission settings, defined as named classes, and usable as objects to be "registered" within *permissions.php*.

    A common, starting instance is "AllPlay", where all users can perform all functions without limit.

        <?
        require_once( dirname( __FILE__ ) . '/permissions.lib.php' );
        
        $registered_auth_calls = array();
        
        register_auth_call( new AllPlay(), "can" );

        ?>

    For a more "private" Giterary experience, the MustBeLoggedIn class defines that in order to view any portions of the site, you must have successfully logged in to the site.


        <?
        require_once( dirname( __FILE__ ) . '/permissions.lib.php' );
        
        $registered_auth_calls = array();
        
        register_auth_call( new MustBeLoggedIn(), "can" );
        
        ?>

    For a "mixed" Giterary experience, SensitiveFiles allows you to define certain files which are considered "sensitive," in that you must belong to a set of defined users otherwise you can neither read nor write to the files.

        <?
        require_once( dirname( __FILE__ ) . '/permissions.lib.php' );
        
        $registered_auth_calls = array();
        
        register_auth_call( 
            new SensitiveFiles( 
                array(
                    "passfile.csv"  => array( "jrhoades" )
                )
            ),
            "can"
        );

        ?>


###### auth.php

*  **auth.php** and **auth.lib.php**

    Similar to *permissions.php* and *permissions.lib.php*, *auth.php* and *auth.lib.php* provide the configuration and the common case library for defining *how people log in to Giterary*. Essentially, how the user list is determined, and how passwords will be stored.

    Currently there are two common configurations, *StaticUserList* and *PasswordFile*.

    In a *StaticUserList* configuration, a class is defined in *auth.lib.php* that describes the user list, their properties, and their passwords. Editing the StaticUserList class in *auth.lib.php* is necessary for this configuration. This method is not necessarily recommended, though, is common due to the ease of maintenance.

        <?

        require_once( dirname( __FILE__ ) . '/auth.lib.php' );
        
        $registered_login_calls = array();
        
        register_login_call( new StaticUserList(), "validate_login" );

        ?>

    In a *PasswordFile* configuration, a CSV is referenced that defines username, user git properties, and a [hashed](http://en.wikipedia.org/wiki/Hash_function#Hashing_with_cryptographic_hash_functions) password. This file can exist within your a repository (and be editable from the Giterary interface), or be outside of your repository and be edited manually, it is up to your security requirements. A *permissions.lib.php* class is defined specifically for this type of file called "SensitiveFiles," which requires specific user credentials to be able to edit a file.

        <?

        require_once( dirname( __FILE__ ) . '/auth.lib.php' );
        
        $registered_login_calls = array();
        
        register_login_call( 
            new PasswordFile( 
                '/var/lib/giterary/repos/my_repo/passfile.csv'
            ), 
            "validate_login" 
        );

        ?>

    A "passfile" is a CSV formatted file that contains names, emails, and password information for users to log in with. An example file might look like this:

        Username,GitUser,Password
        jimmy,"Jimmy <jimmy@jimmy.com>",6f1ed002ab5595859014ebf0951522d9

    The file is a CSV, and as such, can be edited using the Giterary interface (and will render as a table when viewing). However, you don't want just anyone on your Giterary instance viewing and/or editing your password file. In these cases, you want to use *permissions.php* to flag your *passfile.csv* as "Sensitive," and only viewable/editable by a small set of users.

    **WARNING:** Just because the Giterary interface restricts access to such a file doesn't mean that people with access to your git repository won't. Anyone who can read information from git can read your passfile. While efforts are taken to make sure your passwords are obfuscated, it's important to know that git itself doesn't do "security." If you want to use a passfile, but **not** have the file be located in your git repository, simple move your passfile to somewhere that isn't in your git repository. You will have to edit this file by hand.

    Finally, you might be asking yourself, "Who wants to enter in '6f1ed002ab5595859014ebf0951522d9' as a password?" The answer is: *absolutely nobody*. Nor do they. This is a "hash" of your password, a mathematical algorithm performed on your password to make it difficult to guess its original contents. When you log in to Giterary, the password you enter is ran through this same algorithm, and if the two "hashed" results are equal, you have successfully entered your password and you are allowed to log in. It's a clean and useful way of storing passwords without having to store them at all.

    However, the question becomes how to generate these password hashes. On Linux, the following commands are available:

        md5sum
        sha256sum
        sha512sum

    ...And others, which let you pipe in arbitrary text (ie, your password) and return a hashed result. For example, for the password "blah" (which is a terrible password which you should never use):

        echo -n "blah" | md5sum

    ...will return the following:

        6f1ed002ab5595859014ebf0951522d9  -

    **NOTE**: You **must** include the "-n" portion of the echo command, otherwise echo will emit a newline character, which will invalidate your hash value.

    The 6f1...22d9 value is what you paste into your Giterary *passfile.csv*. However, Giterary assumes that unless you specify your hashing algorithm, it will just use the MD5 algorithm. This is fine if you only ever generated hashes with MD5, but some people want a more cryptographically hardened hash value. If you want to do a SHA256 hash, you would have to put in the following:

        "sha256$8b7df143d91c716ecfa5fc1730022f6b421b05cedee8fd52b1fc65a96030ad52"

    Or, for SHA512:

        "sha512$39ca2b1f97...(long, long result )...1c2fa54b7"

    The important part being the "xxxx$" prefix. Anything before the "$" in the password fields is treated as a hashing *hint,* to suggest to Giterary which hashing algorithm is used. The "xxxx" hash hint can be anything that is supported by [PHP's hash() function][php.hash].

[php.hash]: http://php.net/manual/en/function.hash.php


###### dict.php

*   **dict.php**
    
    The *dict.php* file defines the path to the "dictionary" file, as well as sets of configurable groupings of words for calculating document metrics (the "Statistics" page).

    *DICTIONARY_PATH* is the path to a file that consists of 1 word per line. On Linux, this path is normally */usr/share/dict/words*.

        define( 'DICTIONARY_PATH', '/usr/share/dict/words' );

    The following are the lists that define the groupings for which words are to be considered conjunctions, "non-counted" words (common words you don't want counted as "words" for your stats), past tense verbs, and present tense verbs.
        
        $conjunctions = array(
            /* ...for, and, not, nor... */
        );
        
        # http://en.wikipedia.org/wiki/Most_common_words_in_English
        $non_counted_words = array(
            /* ...the, be, to, of... */
        );
        
        $past_tense_verbs = array(
            /* ...was, wasn't, did, didn't... */
        );
        
        $present_tense_verbs = array(
            /* ...is, isn't, am, are... */
        );

###### perf.php

*   **perf.php**
    
    A flag for whether to calculate performance stats and include a "performance report" in the HTML comments of every Giterary page. Enabled by default.

        <?
    
        define( 'PERF_STATS', 1 );

        ?>


*   **time.php**

    Defines the default timezone to be used when making changes from the Giterary interface.

        <?
    
        date_default_timezone_set ( 'America/Anchorage' );

        ?>

*   **conventions.php**

    The *conventions.php* file defines the basic "conventional" assumptions Giterary makes when defining things. Examples of conventions are:

    *   The suffix used for "dirified" directory names (*dir* by default).

            define( 'DIRIFY_SUFFIX', "dir" );
            
    *   Default character encoding for the Giterary instance (*UTF-8* by default).

            define( 'ENCODING', "UTF-8" );

    *   Regular expressions to determine the valid filename and file path patterns used on the site.
            
            $wikiname_pattern = '-_a-zA-Z0-9\.\s';
            
            $wikifile_pattern = "@^([$wikiname_pattern]+)(\\/[$wikiname_pattern]+)*$@";
            
            $wikilink_pattern = "@(\\\)?\[\[([$wikiname_pattern]+(\\/[$wikiname_pattern]+)*)(\|([\w\s\.\,\\/-]+))?\]\]@";
            
            $functionlink_pattern = "@(\\\)?\[\[([a-zA-Z]+):(([^\]|,]+=[^\]|,]+)(,[^\]|,]+=[^\]|,]+)*)?(\|([\w\s\.\,\"/-]+))\]\]@";

###### html.php

*   **html.php**

    *html.php* defines parameters for dealing with the HTML generation within Giterary.

    The *$allowed_tags* configuration variable defines which tags are allowed to be used for final rendering within Giterary.

        $allowed_tags = array(
            /* ...'<p>', '<h>', '<pre>', '<img>', '<table>'... */
        );

    The default list provided with Giterary is a good subset of HTML which provides a wide range of HTML dipslay elements while avoiding potential security risks by disallowing the inclusion of Javascript from a page. You, however, may want to change this at some point, which is entirely up to you and your security requirements. However, this is by no means recommended, and likely will be more of a pain to manage than the functionality gained by allowing such features to be embedded within documents. Proceed at your own risk.

###### themes.php

*   **themes.php**

    *themes.php* defines the names and paths responsible for rendering the various pieces of Giterary. This file is not necessary to edit in an initial configuration, but is important if you want to add your own elements as "overrides" to the default theme.
   
    *DEFAULT_THEME* is the name of the "default" theme to be used for your Giterary instance. This theme must be the name of a array key within the *$themes* configuration variable later on (and is by default).

        define('DEFAULT_THEME', 'default');
    
    The *$renderables* array is a list of name-to-path associations for 'well-known' renderable files, responsible for rendering anything from page layouts to reusable widgets or error messages withing Giterary.

        $renderables = array(
            'default_layout'        =>  'theme/default/renderable/layout/standard.php',
            'edit_layout'           =>  'theme/default/renderable/layout/edit.php',
            'show_layout'           =>  'theme/default/renderable/layout/show_layout.php',
            'note'                  =>  'theme/default/renderable/note.php',
            'gen_error'             =>  'theme/default/renderable/gen_error.php',
            'gen_header'            =>  'theme/default/renderable/gen_header.php',
            'gen_history'           =>  'theme/default/renderable/gen_history.php',
            /* ... */
            'not_logged_in'         =>  'theme/default/renderable/not_logged_in.php',
            'gen_csv'               =>  'theme/default/renderable/gen_csv.php'
        
        );

    The *$themes* array defines as the keys the names of available themes, and as the values of those keys, arrays that describe "overrides" of the *$renderables* definitions for the named renderables. This allows a theme author to make as much use of the default theme as possible, and only define their theme as "exceptions" to the default theme as necessary.

        $themes = array(
            'default'   =>  array(),
        );

    Note again that editing this file is not required, but we're just providing a description here for documentation purposes.

## Initialize your git repository

Your git repository needs to be "primed," so to speak, before being used by Giterary. First off, within your repository directory (perhaps */var/lib/giterary/repos/your_repo*), do the following command:

    git init 

This will initialize an empty directory for you to work with (and creates the "hidden" files that git uses alongside your own).

You also need to inform git "who you are," though, this isn't really your name, as the Giterary application provides your committing information for you. However, git still squawks when this is unavailable. To silence this, we set a temporary, "anonymous" user for the repository. The name doesn't particularly matter, nor does the email address have to be valid.

    git config user.name "Anonymous User"
    git config user.email "anonymous@anonymous.com"

*(Once the Giterary authors figure out how to get around this, we will likely be able to skip this step.)*

## That was horrific. Am I done?

Well, I think so. At least the Giterary part anyway. You should browse to your Giterary instance with your web browser, loading up *index.php* to see if it displays the editing screen for the file indicated by the file name configured with *DEFAULT_FILE* in *base.php*.

    http://localhost/giterary/index.php

If the page loads, and you're able to save your file successfully, then you've succeeded in installing and configuring Giterary.

## It didn't work. I have shamed myself and my family.

*No!* Not at all. We are human, we must err, or, do things which cause error messages. If you are getting errors, see if they are listed among the following common installation errors and try to rectify the problems.

TODO: Come up with a list of common errors with misconfiguration

## Optional Pieces

### Being able to push/pull from Giterary

There are a ton of sources out there to learn how to use Git.

* [gittutorial][gittutorial], git's own tutorial (admittedly dense).
* [git-scm's documentation and videos][git-scm], particularly their ["Distributed Git" section][dist]
* [Github's 15 minute tutorial][git-code-school]

Honestly, I learned git by way of Googling, man pages, and more furious Googling. The resources are out there. They may not be obvious, but there are some amazing things you can do with git.

[gittutorial]: http://www.kernel.org/pub/software/scm/git/docs/gittutorial.html
[git-scm]: http://git-scm.com/documentation
[git-code-school]: http://try.github.com/levels/1/challenges/1
[dist]: http://git-scm.com/book/en/Distributed-Git



### Post-receive git server hook

git gives you the ability to push and pull from whatever repository you like. A recommended setup with Giterary is to synchronize a copy of your Giterary repository to a separate computer (your daily driver laptop, etc.), and synchronize via SSH.

This creates a problem, however. Potentially, you can push to your Giterary server repository, and while your push may succeed, the files in the working directory on the server may be out of sync.

To solve this, we use a feature of git called "hooks." In particular, there is a hook called the *post-receive* hook, which allows you to perform arbitrary user action on the server after successfully receiving your commits.

Put the following into */path/to/your/repo/.git/hooks/post-receive* to enable this feature:

    #!/bin/bash

    echo "Refreshing server's working tree";

    /usr/local/bin/git --git-dir=/path/to/your/repo/.git --work-tree=/path/to/your/repo/ reset --hard;

    exit 0;

Additionally, git takes issue with pushing into non-bare repositories. While normally this would be for good reason, Giterary needs to be able to push into a non-bare repository in order to synchronize its files for display on the Giterary interface.

    git config receive.denyCurrentBranch ignore


----

[git]: http://git-scm.org/
[php]: http://php.net/
[apache]: http://httpd.apache.org/
[nginx]: http://nginx.org/
[iis]: http://www.iis.net/
[turnkey]: http://www.turnkeylinux.org/
[ssh]: http://openssh.com
[tortoisegit]: http://code.google.com/p/tortoisegit/wiki/Download
[sourcetree]: http://www.sourcetreeapp.com/