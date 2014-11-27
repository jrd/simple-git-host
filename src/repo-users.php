<?php
require_once('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $repo = $_GET['repo'];
}
$errorMsg = '';
$repoadmin = $admin || isrepoadmin($repo, $_SESSION['username']);
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
?>
    <div id="users">
      <div class="invite">Les membres de <span><?php echo $repo; ?></span> :</div>
      <table>
        <tr>
          <th class="name">Utilisateur</th>
          <th class="right">Droit</th>
          <th class="actions">Actions</th>
        </tr>
<?php
$members = array();
foreach (gitrepoinfo('show-users', $repo) as $userinfo) {
  $info = explode(':', $userinfo);
  $members[$info[0]] = $info[1];
}
$isExport = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
foreach ($members as $user => $right) {
  $actions = ' — ';
  if ($repoadmin) {
    $actions = "<a href=\"/{$gitwebroot}user_right/$repo/$user/admin\">→ admin right</a>";
    $actions .= "&nbsp;<a href=\"/{$gitwebroot}user_right/$repo/$user/user\">→ user right</a>";
    $actions .= "&nbsp;<a href=\"/{$gitwebroot}user_right/$repo/$user/readonly\">→ readonly right</a>";
    $actions .= "&nbsp;<a href=\"/{$gitwebroot}remove_user/$repo/$user\">Retirer</a>";
  }
  echo "        <tr><td class=\"name\">$user</td><td class=\"right\">$right</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
<?php if ($repoadmin) { ?>
    <div class="error"><?php echo $errorMsg; ?></div>
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
