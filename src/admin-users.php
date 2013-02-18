<?php
require('include.inc.php');
if (!$admin) {
  header('Location: index.php');
  exit;
}
$errorMsg = '';
if (isset($_POST['submit_user_add'])) {
  $fUser = $_POST['username'];
  $fPwd = $_POST['password'];
  $res = gitrepoinfo('create-user', $fUser, md5($fPwd));
  if ($res === false) {
    $errorMsg = "L'utilisateur n'a pas pu être ajouté.";
  }
}
$pageTitle = "$title - Administration des utilisateurs";
require('header.inc.php');
?>
    <div id="users">
      <div class="invite">Les utilisateurs :</div>
      <table>
        <tr>
          <th class="name">Utilisateur</th>
          <th class="actions">Actions</th>
        </tr>
<?php
$users = gitrepoinfo('list-users');
foreach ($users as $user) {
  $actions = "<a class=\"delete\" href=\"user-del.php?user=$user\" onclick=\"return confirm('Êtes vous sûr de vouloir supprimer l\'utilisateur \'$user\' ?');\">Supprimer</a>";
  echo "        <tr><td class=\"name\">$user</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="add-user" action="" method="POST" autocomplete="off">
      <fieldset>
        <legend>Ajouter un utilisateur</legend>
        <label for="username">Login : </label><input type="text" name="username" id="username" value=""/>
        <label for="password">MdP : </label><input type="password" name="password" id="password" value=""/>
        <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
      </fieldset>
    </form>
<?php require('footer.inc.php'); ?>
