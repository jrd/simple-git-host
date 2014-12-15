<?php
require_once('include.inc.php');
redirectifnotadmin();
if (isset($_POST['submit_user_add'])) {
  $fUser = $_POST['username'];
  $fPwd = $_POST['password'];
  $res = gitrepoinfo('create-user', $fUser, md5($fPwd));
  if ($res === false) {
    $errorMsg = "L'utilisateur <strong>$fUser</strong> n'a pas pu être ajouté.";
  } else {
    redirect('admin-users');
  }
}
$pageTitle = "$title - Administration des utilisateurs";
$cat = 'users';
require('header.inc.php');
?>
    <div id="users">
      <h3>Les utilisateurs :</h3>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php
$users = gitrepoinfo('list-users');
foreach ($users as $user) {
  $actions = "<a href=\"".url('user-del', 'user', $user)."\" onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur \'$user\' ?');\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span>&nbsp;Supprimer</a>";
  echo "        <tr><td>$user</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
          </tbody>
        </table>
      </div>
    </div>
    <form id="add-user" action="" method="POST" autocomplete="off">
      <fieldset>
        <legend>Ajouter un utilisateur</legend>
        <label for="username">Login : </label>&nbsp;<input type="text" name="username" id="username" value=""/>
        <label for="password">MdP : </label>&nbsp;<input type="password" name="password" id="password" value=""/>
        <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
      </fieldset>
    </form>
<?php require('footer.inc.php'); ?>
