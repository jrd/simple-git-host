<?php
require_once('include.inc.php');
$queryString = $_SERVER['QUERY_STRING'];
$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
$request = explode('/', $uri);
$posoffset = substr_count($gitwebroot, '/');
$action = $request[1 + $posoffset];
if ($action == 'controller.php') {
  $action = '';
}
$actionVars = array_slice($request, 2 + $posoffset);
require_once('controller.config.php');
function call_action($name, $default, $vars) {
  if (empty($name)) {
    $name = $default;
  }
  if (function_exists("action_$name")) {
    return call_user_func("action_$name", $vars);
  } else {
    return false;
  }
}
if (!call_action($action, $defaultAction, $actionVars)) {
  header('HTTP/1.0 404 Not Found');
  echo "404 Not found ($action).";
}
?>
