<?php
require_once('include.inc.php');
if (empty($vars['repo'])) {
  redirect('/');
} else {
  $repo = $vars['repo'];
}
$repoadmin = $admin || isrepoadmin($repo, $username);
if ($repoadmin && isset($_POST['submit_user_add'])) {
  $fUser = $_POST['username'];
  $fRight = $_POST['right'];
  $res = gitrepoinfo('add-user', $repo, $fUser, $fRight);
  if ($res === false) {
    $errorMsg = "L'utilisateur n'a pas pu être ajouté.";
  }
}
$pageTitle = "$title - Membres de $repo";
require('header.inc.php');
$repo_tab_active = 'admin';
$exportok = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
require('repo-nav.inc.php');
?>
    <div id="users">
      <h3>Les membres de <strong><?php echo $repo; ?></strong> :</h3>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Utilisateur</th>
            <th>Droit</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
<?php
$members = array();
foreach (gitrepoinfo('show-users', $repo) as $userinfo) {
  $info = explode(':', $userinfo);
  $members[$info[0]] = $info[1];
}
$isExport = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
foreach ($members as $user => $right) {
  $rightlabel = $right;
  $actions = ' — ';
  if ($repoadmin && ($admin || $user != $username)) {
    $rightlabel = '<div class="dropdown"><a class="dropdown-toggle" aria_expended="false" role="button" data-toggle="dropdown" href="#">' . $right . '&nbsp;<span class="caret"></a><ul class="dropdown-menu" role="menu">';
    $rightlabel .= '<li role="presentation"><a href="' . url('repo-user-right', 'repo', $repo, 'user', $user, 'right', 'admin') . '">admin</a></li>';
    $rightlabel .= '<li role="presentation"><a href="' . url('repo-user-right', 'repo', $repo, 'user', $user, 'right', 'user') . '">user</a></li>';
    $rightlabel .= '<li role="presentation"><a href="' . url('repo-user-right', 'repo', $repo, 'user', $user, 'right', 'readonly') . '">readonly</a></li>';
    $actions = '<a href="' . url('repo-user-del', 'repo', $repo, 'user', $user) . '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;Retirer</a>';
  }
  echo "        <tr><td>$user</td><td>$rightlabel</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
        </tbody>
      </table>
    </div>
<?php if ($repoadmin) { ?>
    <form id="repo-add-user" action="" method="POST">
      <fieldset>
        <legend>Ajouter un utilisateur au dépôt</legend>
        <label for="username">Nouveau membre :</label>&nbsp;
        <select name="username">
<?php
$users = gitrepoinfo('list-users');
foreach ($users as $user) {
  if (!array_key_exists($user, $members)) {
    $user = htmlspecialchars($user);
    echo "          <option value=\"$user\">$user</option>\n";
  }
}
?>
        </select>
        <select name="right"><option value="admin">admin</option><option value="user" selected="selected">user</option><option value="readonly">readonly</option></select>
        <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
      </fieldset>
    </form>
<?php } ?>
<?php require('footer.inc.php'); ?>
