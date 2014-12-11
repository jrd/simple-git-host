<?php
require_once('include.inc.php');
$queryString = $_SERVER['QUERY_STRING'];
$request = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
$resource = implode('/', array_slice($request, 1 + substr_count($gitwebroot, '/')));
if ("/$gitwebroot$resource" == $_SERVER['SCRIPT_NAME']) {
  $resource = '';
}
require_once('controller.config.php');
if (empty($resource)) {
  $resource = $defaultResource;
}
$vars = array();
foreach ($controllers as $c) {
  $regex = $c[0];
  $file = $c[1];
  if (preg_match($regex, $resource, $matches)) {
    $vars = array();
    foreach ($matches as $k => $v) {
      if (!is_int($k)) {
        $vars[$k] = $v;
      }
    }
    if (include_once($file)) {
      exit;
    } else {
      header('HTTP/1.0 404 Not Found');
      die("No such file: $file");
    }
  }
}
header('HTTP/1.0 404 Not Found');
echo "404 Not found ($resource).";
?>
