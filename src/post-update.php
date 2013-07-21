<?php
require('include.inc.php');
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
  echo <<<'EOF'
<html><body><pre>
You must pass some variables to this script:
  - name, name of the repository to synchronize to.
  - url, full url of the repository to synchronize from.
You can pass it through POST or GET method.

For GET method, just supply these two variables.

For POST method, you should supply a json encoded 'payload' variable.
This variable should contains a array named 'repository' with 'name' and 'url' variable in it.
This method is the way Github works.
</pre></body></html>
EOF;
}
?>
