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
?>
<html>
  <head>
  <title><?php echo "$title - Administration des utilisateurs"; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <h1><?php echo "$title - Administration des utilisateurs"; ?></h1>
    <div id="nav"><a href="index.php">Index</a></div>
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
  $actions = "<a href=\"user-del.php?user=$user\">Supprimer</a>";
  echo "        <tr><td class=\"name\">$user</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
    <hr/>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="repo-add-user" action="" method="POST" autocomplete="off">
      <label for="username">Login : </label><input type="text" name="username" id="username" value=""/>
      <label for="password">MdP : </label><input type="password" name="password" id="password" value=""/>
      <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
    </form>
  </body>
</html>
