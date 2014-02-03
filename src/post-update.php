<?php
require_once('include.inc.php');
if (isset($_POST['payload'])) {
  // github post update
  $info = json_decode($_POST['payload'], true);
  $repoName = $info['repository']['name'];
  $repoUrl = $info['repository']['url'];
  gitrepoinfo('fetch', $repoName, $repoUrl);
} else if (isset($_GET['name']) && isset($_GET['url'])) {
  $repoName = $_GET['name'];
  $repoUrl = $_GET['url'];
  gitrepoinfo('fetch', $repoName, $repoUrl);
} else {
  echo "<pre>
You should provide a 'payload' variable in POST, with the corresponding format:
{repository: {name: 'local repository name', url: 'remote url'}}

You could also provide the 'name' and 'url' variable directly by GET method.
</pre>";
}
?>
