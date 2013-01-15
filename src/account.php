<?php
require('include.inc.php');
if (!$logged) {
  header('Location: index.php');
  exit;
} else {
  $username = $_SESSION['username'];
}
$errorMsgKey = '';
$errorMsgPwd = '';
if (isset($_POST['submit_key'])) {
  $fKey = $_POST['new-key'];
  $res = gitrepoinfo('add-key', $username, $fKey);
  if ($res === false) {
    $errorMsgKey = "La clé n'a pas pu être ajoutée.";
  }
}
if (isset($_POST['submit_pwd'])) {
  $fPwd = $_POST['new-pwd'];
  $res = gitrepoinfo('change-user', $username, md5($fPwd));
  if ($res === false) {
    $errorMsgPwd = "Le mot de passe n'a pas pu être changé.";
  }
}
?>
<html>
  <head>
    <title><?php echo "$title - $username"; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <h1><?php echo "$title - $username"; ?></h1>
    <div id="nav"><a href="index.php">Index</a></div>
    <div id="keys">
      <div class="invite">Les clés SSH de <span><?php echo $username; ?> </span>:</div>
      <table>
        <tr>
          <th class="key">Clé</th>
          <th class="actions">Action</th>
        </tr>
<?php
$keys = gitrepoinfo('list-keys', $username);
$i = 0;
foreach ($keys as $key) {
  $i++;
  $actions = "<a href=\"user-del-key.php?pos=$i\">Supprimer</a>";
  echo "        <tr><td class=\"key\"><textarea readonly=\"readonly\">$key</textarea></td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
    <hr/>
    <div class="error"><?php echo $errorMsgKey; ?></div>
    <form id="add-key" action="" method="POST">
      <label for="new-key">Nouvelle clé SSH :</label><br/>
      <textarea name="new-key" id="new-key"></textarea><br/>
      <input type="submit" name="submit_key" value="Ajouter"/>
    </form>
    <hr/>
    <div class="error"><?php echo $errorMsgPwd; ?></div>
    <form id="change-pwd" action="" method="POST" autocomplete="off">
      <label for="new-pwd">Nouveau mot de passe :</label><br/>
      <input type="password" name="new-pwd" id="new-pwd" value=""/><br/>
      <input type="submit" name="submit_pwd" value="Changer le mot de passe"/>
    </form>
  </body>
</html>
