<?
require_once("include/header.php");

// Unset all of the session variables.
$_SESSION = array();

$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
);

// Destroy current session
session_destroy();

// Redirect to the home page
header("Location: index.php");

?>
