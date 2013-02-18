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
$pageTitle = "$title - $username";
require('header.inc.php');
?>
    <div id="keys">
      <div class="invite">Les clés SSH de <span><?php echo $username; ?> </span>:</div>
      <table>
        <tr>
          <th class="name">Nom</th>
          <th class="key">Clé</th>
          <th class="actions">Action</th>
        </tr>
<?php
$keys = gitrepoinfo('list-keys', $username);
$i = 0;
foreach ($keys as $key) {
  $i++;
  $a = preg_split('/[\s]/', $key);
  if (count($a) >= 3) {
    $name = htmlspecialchars($a[2]);
  } else {
    $name = ' — ';
  }
  $actions = "<a class=\"delete\" href=\"user-del-key.php?pos=$i\" onclick=\"return confirm('Êtes vous sûr de vouloir supprimer cette clé ?');\">Supprimer</a>";
  echo "        <tr><td class=\"keyname\">$name</td><td class=\"key\"><textarea readonly=\"readonly\">$key</textarea></td><td class=\"actions\">$actions</td></tr>\n";
}
?>
      </table>
    </div>
    <div class="error"><?php echo $errorMsgKey; ?></div>
    <form id="add-key" action="" method="POST">
      <fieldset>
        <legend>Ajouter une clé SSH</legend>
        <label for="new-key">Nouvelle clé SSH :</label><br/>
        <textarea name="new-key" id="new-key"></textarea><br/>
        <input type="submit" name="submit_key" value="Ajouter"/>
      </fieldset>
    </form>
    <div class="error"><?php echo $errorMsgPwd; ?></div>
    <form id="change-pwd" action="" method="POST" autocomplete="off">
      <fieldset>
        <legend>Changer le mot de passe</legend>
        <label for="new-pwd">Nouveau mot de passe :</label><br/>
        <input type="password" name="new-pwd" id="new-pwd" value=""/><br/>
        <input type="submit" name="submit_pwd" value="Changer le mot de passe"/>
      </fieldset>
    </form>
<?php require('footer.inc.php'); ?>
