<?php
require_once('include.inc.php');
if (!$logged || empty($vars['repo']) || empty($vars['user'])) {
  redirect('/');
} else {
  $repo = $vars['repo'];
  $user = $vars['user'];
}
redirectifnotrepoadmin($repo);
$res = gitrepoinfo('del-user', $repo, $user);
redirect('repo-users', 'repo', $repo);
