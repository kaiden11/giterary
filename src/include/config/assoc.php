<?

# Do we maintain file associations?
define( "ASSOC_ENABLE", 1 );

# Under which directory do we maintain file associations?
# Note that this will be dirified
define( "ASSOC_DIR", "Associations" );

# Relationship types beginning  with the following prefixes
# will have those relationships "flagged" at the top of document
# views.
$notable_relationships = array( "needs_", "need_", "want_", "wants_", "status_", "attn_" );


?>
