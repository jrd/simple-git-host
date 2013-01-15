<?php
require('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: index.php');
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
?>
<html>
  <head>
    <title><?php echo "$title - $repo"; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <h1><?php echo "$title - $repo"; ?></h1>
    <div id="nav"><a href="index.php">Index</a></div>
    <div id="users">
      <div class="invite">Les membres de <span><?php echo $repo; ?></span> :</div>
      <table>
        <tr>
          <th class="name">Utilisateur</th>
          <th class="actions">Actions</th>
        </tr>
<?php
$users = gitrepoinfo('show-users', $repo);
foreach ($users as $user) {
  $actions = ' — ';
  if ($admin) {
    $actions = "<a href=\"repo-user-del.php?repo=$repo&user=$user\">Retirer</a>";
  }
  echo "        <tr><td class=\"name\">$user</td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
<?php if ($admin) { ?>
    <hr/>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="repo-add-user" action="" method="POST">
      <label for="new-repo">Nouveau membre :</label>&nbsp;
      <select name="username">
<?php
$users = gitrepoinfo('list-users');
foreach ($users as $user) {
  $user = htmlspecialchars($user);
  echo "        <option value=\"$user\">$user</option>\n";
}
?>
      </select>
      <input type="submit" name="submit_user_add" value="Ajouter l'utilisateur au dépôt"/>
    </form>
<?php } ?>
  </body>
</html>
