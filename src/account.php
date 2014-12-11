<?php
require_once('include.inc.php');
if (!$logged) {
  redirect('/');
}
$redirect = false;
if (isset($_POST['submit_key'])) {
  $fKey = $_POST['new-key'];
  $res = gitrepoinfo('add-key', $username, $fKey);
  if ($res === false) {
    $errorMsg = "La clé n'a pas pu être ajoutée.";
  } else {
    $redirect = true;
  }
}
if (isset($_POST['submit_pwd'])) {
  $fPwd = $_POST['new-pwd'];
  $res = gitrepoinfo('change-user', $username, md5($fPwd));
  if ($res === false) {
    $errorMsg = "Le mot de passe n'a pas pu être changé.";
  } else {
    $redirect = true;
  }
}
if ($redirect) {
  redirecturl($_SERVER['REQUEST_URI']);
}
$pageTitle = "$title - $username";
$cat = 'account';
require('header.inc.php');
?>
    <div id="keys">
      <div class="invite">Les clés SSH de <span><?php echo $username; ?> </span>:</div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Clé</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
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
  $actions = '<a class="delete" href="' . url('user-del-key', 'pos', $i) . '" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette clé ?\');">Supprimer</a>';
  echo "        <tr><td>$name</td><td><textarea readonly=\"readonly\">$key</textarea></td><td class=\"actions\">$actions</td></tr>\n";
}
?>
          </tbody>
        </table>
      </div>
    </div>
    <form id="add-key" action="" method="POST">
      <fieldset>
        <legend>Ajouter une clé SSH</legend>
        <label for="new-key">Nouvelle clé SSH :</label><br/>
        <textarea name="new-key" id="new-key"></textarea><br/>
        <input type="submit" name="submit_key" value="Ajouter"/>
      </fieldset>
    </form>
    <form id="change-pwd" action="" method="POST" autocomplete="off">
      <fieldset>
        <legend>Changer le mot de passe</legend>
        <label for="new-pwd">Nouveau mot de passe :</label><br/>
        <input type="password" name="new-pwd" id="new-pwd" value=""/><br/>
        <input type="submit" name="submit_pwd" value="Changer le mot de passe"/>
      </fieldset>
    </form>
<?php require('footer.inc.php'); ?>
