<?php
require_once('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $repo = $_GET['repo'];
}
$errorMsg = '';
if ($admin && isset($_POST['submit_user_add'])) {
  $fUser = $_POST['username'];
  $res = gitrepoinfo('add-user', $repo, $fUser);
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
          <th class="actions">Actions</th>
        </tr>
<?php
$users = array_map(function($val) { return explode(':', $val)[0]; }, gitrepoinfo('show-users', $repo));
foreach ($users as $user) {
  $actions = ' — ';
  if ($admin) {
    $actions = "<a href=\"/{$gitwebroot}remove_user/$repo/$user\">Retirer</a>";
  }
  echo "        <tr><td class=\"name\">$user</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
<?php if ($admin) { ?>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="repo-add-user" action="" method="POST">
      <fieldset>
        <legend>Ajouter un utilisateur au dépôt</legend>
        <label for="username">Nouveau membre :</label>&nbsp;
        <select name="username">
<?php
$users = gitrepoinfo('list-users');
foreach ($users as $user) {
  $user = htmlspecialchars($user);
  echo "          <option value=\"$user\">$user</option>\n";
}
?>
        </select>
        <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
      </fieldset>
    </form>
<?php } ?>
<?php require('footer.inc.php'); ?>
