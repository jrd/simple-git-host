<?php
require_once('include.inc.php');
$right = $vars['right'] or '';
if (empty($vars['repo']) || empty($vars['user']) || ($right != 'admin' && $right != 'user' && $right != 'readonly')) {
  redirect('/');
} else {
  $repo = $vars['repo'];
  $user = $vars['user'];
}
$tmp = isrepoadmin($repo, $username);
redirectifnotrepoadmin($repo);
$res = gitrepoinfo('add-user', $repo, $user, $right);
redirect('repo-users', 'repo', $repo);
