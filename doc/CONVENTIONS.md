# Programming Conventions

Description of file structures, naming conventions, and coding conventions used through the Giterary codebase.

### Sure, it's MVC... ish.

I suppose, technically, Giteary's codebase follows an MVC-type architecture. That is, if you say that:

* git is the "model"
* "views" are the *render* and *layout* templating mechanism
* "controllers" are a series of functions to route requests based on user parameters

That said, it's certainly not pretty, and violates quite a few things that other codebases implement much better. I try my best to separate display logic from data logic, but honestly, PHP itself is sort of a templating language that got out of hand.

### File Structure

    /*.php                        Files for accepting parameters, routing to functions,
                                  and providing the right bootstrapping "require" 
                                  statements to handle requests.

    /css/                         CSS

    /js/                          Javascript

    /include/*.php                Function definitions, named according to their site function.
    /include/config.php           Configuration bootstrapping
    /include/util.php             Utility methods used application-wide

    /include/git.php              Functions for interacting directly with git
    /include/git_html.php         Functions for translating git output to HTML
    /include/display.php          Functions for rendering and translating documents from Markdown, CSV,
                                  text, or any other formats.

    /include/config/*.php         Configuration PHP and config "libraries"

    /theme/                       Storage for "theme" renderables for the templating mechanism
    /theme/default/               Storage for default theme
    /theme/*/renderable/          Storage for "renderable" PHP pages for the templating mechanism
    /theme/*/renderable/layout    Storage for "renderable" PHP pages for the templating mechanism

    
### Function naming

*   **git_* functions**

    Functions that interact directly with git. Usually the suffix will correspond directly to the git "verb" being used.

*   **gen_* functions**

    Functions that "generate" HTML output, but also have the possibility to serve as "shim" functions to separate functionality like "Am I allowed to perform this function?" from the actual execution of the function.

    Additionally, serves to provide a "staging area" for handling default variables or configured variables before passing off to the "executing" functions.

*   **\_gen_* functions**

    Functions prefixed with *_gen* tend to be the "executing" functions, counterparts to their parameterizing and authenticating *gen* functions. These tend to have more or less options than their *gen* counterparts, being the "advanced" interface to a particular feature, or being a function that serves more than one set of *gen* functional areas.

### Keep Things Simple Where They Should Be Simple

If at all possible, it is recommended that you keep "logic" code out of the root \*.php files, instead delegating display logic to underlying functions. For instance, the PHP file to display *search.php* is 


    require_once( dirname( __FILE__ ) . '/include/header.php');
    require_once( dirname( __FILE__ ) . '/include/footer.php');
    require_once( dirname( __FILE__ ) . '/include/util.php');
    require_once( dirname( __FILE__ ) . '/include/git_html.php');
    require_once( dirname( __FILE__ ) . '/include/edit.php');


    $term = substr( $_GET['term'], 0, 100 );

    echo layout(
        array(
            'header'            => gen_header( "Search" ),
            'content'           => gen_search( $term )
        )
    );

Its only concern is accepting the request, search terms, and passing them to the *gen_search()* function. It does not call any of the *git_* functions, instead relying on the *gen_* HTML generation to indicate display, success, or errors.


### Architectural Concerns

*   **Why don't you use library X to do Y? Wouldn't that be easier?**

    Or: *Why did you build your own templating system, git interface, user permissions, and user auth systems?*

    At least I used git, doesn't that count? :)

    It's hard to strike a good distance between a piece of code doing exactly what you want and the amount of work it takes to get you there. Some problems are better left to those with doctorates in computer science or mathematics, or those that get paid handsomely to solve such problems for their employers and contribute their efforts back to the open source community.

    That being said, it's rare that I find a programming library that solves a particular set of problems in ways that:

    * Fit with my level of paranoia
    * Fit with my level of vague technological elitism
    * "Get out of my way" if I want to do something that might be considered *unwise*.

    I don't pretend to be a great programmer, and I'm usually willing to defer to the expertise of others. However, sometimes you have to do *bad things* © for good reasons. Using libraries built on the design decisions of others means that eventually your requirements draw outside the lines of the intended use of a library. I appreciate the risks. But I don't like to be constrained by them.

    Also, package management. There are great systems out there that manage packages and their dependencies. I like to build things that are simple enough that they don't require package management, or, can exist without the need for package management.