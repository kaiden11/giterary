<?
require_once( dirname( __FILE__ ) . '/cache.lib.php' );

$registered_cache_handlers = array();

global $instance_name;

# Memcached server / post
define( 'CACHE_SERVER',   'localhost' );
define( 'CACHE_PORT',     11211       );

# Prefix to use for memcached server to distinguish this
# instance's keys from other potential instances
define( 'CACHE_PREFIX', "$instance_name.giterary" );


# Default cache expirations       
$tag_expirations = array(
    'file_head_commit'      =>  5*86400,
    'git_head_commit'       =>  5*86400,
    'git_file_exists'       =>  5*86400,
    'git_view'              =>  5*86400,
    'git_view_show_helper'  =>  5*86400,
    'git_todo_count'        =>  5*86400,
    'git_show'              =>  5*86400,
    'git_note'              =>  5*86400,
    'git_glob'              =>  5*86400,
    'git_ls_tree'           =>  5*86400,
    'git_commit_file_list'  =>  5*86400,
    'git_head_files'        =>  5*86400,
    'git_todos'             =>  5*86400,
    'git_annotations'       =>  5*86400,
    'git_tags'              =>  5*86400,
    'git_all_tags'          =>  5*86400,
    'git_all_meta'          =>  5*86400,
    'git_file_rev_list'     =>  5*86400,
    '_document_stats'       =>  5*86400,
    'lookup'                =>  5*86400,
    'git_history'           =>  2*86400,
    'git_pickaxe'           =>  2*86400
);


# Memcached-based cache handler
register_cache_handler( 
    new memcacheCache(
        $tag_expirations,
        CACHE_PREFIX,
        CACHE_SERVER,
        CACHE_PORT
    ),
    "encache",
    "decache",
    "clear_cache",
    "cache_keys",
    "clear_key"
);

/* 
# File-based cache handler
register_cache_handler( 
    new FileCache(
        $tag_expirations,
        CACHE_PREFIX,
        CACHE_DIR
    ),
    "encache",
    "decache",
    "clear_cache"
);
*/


?>
