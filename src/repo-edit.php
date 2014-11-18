<?php
require_once('include.inc.php');
redirectifnotadmin();
if (empty($_GET['repo'])) {
  header('Location: /');
  exit;
} else {
  $repo = $_GET['repo'];
}
$errorMsgDesc = '';
$errorMsgConfig = '';
$errorMsgExport = '';
$errorMsgSyncTo = '';
$errorMsgSyncFrom = '';
if (isset($_POST['submit_repo_desc'])) {
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('set', $repo, 'description', $fDesc);
  if ($res === false) {
    $errorMsgDesc = "La description n'a pas pu être appliquée.";
  }
} else if (isset($_POST['submit_repo_config'])) {
  $fOption = $_POST['new-option'];
  $fValue = $_POST['new-value'];
  $res = gitrepoinfo('set', $repo, $fOption, $fValue);
  if ($res === false) {
    $errorMsgConfig = "L'option n'a pas pu être appliquée.";
  }
} else if (isset($_POST['submit_repo_export'])) {
  $fExport = $_POST['new-export'] == 'on' ? 'on' : 'off';
  $res = gitrepoinfo('export', $repo, $fExport);
  if ($res === false) {
    $errorMsgExport = "L'export n'a pas pu être appliqué.";
  }
} else if (isset($_POST['submit_repo_syncto_add'])) {
  $fUrl = $_POST['new-syncto-url'];
  $res = gitrepoinfo('sync', $repo, 'to', $fUrl);
  if ($res === false) {
    $errorMsgSyncTo = "L'url n'a pas pu être ajoutée.";
  }
} else if (isset($_POST['submit_repo_syncto_deploy'])) {
  $res = gitrepoinfo('deploy-key', $repo);
  if ($res === false) {
    $errorMsgSyncTo = "La clé de déploiement n'a pas pu être ajoutée.";
  }
} else if (isset($_GET['delete']) && $_GET['delete'] == 'syncto') {
  $fUrl = $_GET['url'];
  $res = gitrepoinfo('unsync', $repo, 'to', $fUrl);
  header("Location: /edit/$repo");
} else if (isset($_POST['submit_repo_syncfrom_add'])) {
  $fUrl = $_POST['new-syncfrom-url'];
  $res = gitrepoinfo('sync', $repo, 'from', $fUrl);
  if ($res === false) {
    $errorMsgSyncFrom = "L'url n'a pas pu être ajoutée.";
  }
} else if (isset($_GET['delete']) && $_GET['delete'] == 'syncfrom') {
  $fUrl = $_GET['url'];
  $res = gitrepoinfo('unsync', $repo, 'from', $fUrl);
  header("Location: /edit/$repo");
}
$pageTitle = "$title - Configuration de $repo";
require('header.inc.php');
?>
    <div id="desc">
      <div class="error"><?php echo $errorMsgDesc; ?></div>
      <form id="repo-edit-desc" action="" method="POST">
        <fieldset>
          <legend>Description</legend>
          <label for="new-desc">Description :</label>&nbsp;<input type="text" name="new-desc" id="new-desc" value="<?php echo htmlspecialchars(file_get_contents("$gitdir/$repo.git/description")); ?>"/>
          <input type="submit" name="submit_repo_desc" value="Mettre à jour la description"/>
        </fieldset>
      </form>
    </div>
    <div id="config">
      <div class="invite">Configuration de <span><?php echo $repo; ?></span> :</div>
      <pre>
<?php
  $cfg = file_get_contents("$gitdir/$repo.git/config");
  echo preg_replace('/[ \t][ \t]*/', '  ', $cfg);
?>
      </pre>
      <div class="error"><?php echo $errorMsgConfig; ?></div>
      <form id="repo-edit-config" action="" method="POST">
        <fieldset>
          <legend>Changer/Ajouter une option de config</legend>
          <label for="new-option">Config option :</label>&nbsp;<input type="text" name="new-option" id="new-option" value=""/>
          <label for="new-value">Config value :</label>&nbsp;<input type="text" name="new-value" id="new-value" value=""/>
          <input type="submit" name="submit_repo_config" value="Mettre à jour l'option de configuration"/>
        </fieldset>
      </form>
    </div>
    <?php if (!empty($gitwebpath)) { ?>
    <?php $isExport = file_exists("$gitdir/$repo.git/git-daemon-export-ok") ? 'checked="checked"' : ''; ?>
    <div id="export">
      <div class="error"><?php echo $errorMsgExport; ?></div>
      <form id="repo-edit-export" action="" method="POST">
        <fieldset>
          <legend>Gitweb</legend>
          <label for="new-export">Anonymous read-only access :</label>&nbsp;<input type="checkbox" name="new-export" id="new-export" value="on" <?php echo $isExport; ?>/>
          <input type="submit" name="submit_repo_export" value="Mettre à jour l'option d'export lecture-seule"/>
        </fieldset>
      </form>
    </div>
    <?php } ?>
    <div id="syncto">
      <div class="error"><?php echo $errorMsgSyncTo; ?></div>
      <div class="invite">This repo is synchronized to :</div>
      <table>
        <tr>
          <th class="name">Url</th>
          <th class="actions">Actions</th>
        </tr>
<?php
  $urls = gitrepoinfo('listsync', $repo, 'to');
  foreach ($urls as $url) {
    echo "        <tr>\n";
    echo "          <td class=\"name\">$url</td>\n";
    echo "          <td class=\"actions\"><a class=\"delete\" href=\"?delete=syncto&url=$url\" onclick=\"return confirm('Êtes vous sûr de vouloir supprimer cette url ?');\">Supprimer</a></td>\n";
    echo "        </tr>\n";
  }
?>
      </table>
      <form id="repo-syncto-add" action="" method="POST">
        <fieldset>
          <legend>Ajouter une URL vers laquelle se synchroniser</legend>
          <label for="new-syncto-url">URL :</label>&nbsp;<input type="text" name="new-syncto-url" id="new-syncto-url" value=""/>
          <input type="submit" name="submit_repo_syncto_add" value="Ajouter l'URL"/>
        </fieldset>
      </form>
      <div class="invite">Clé de déploiement :</div>
<?php
  if (file_exists("$gitdir/$repo.git/id_rsa.pub")) {
    $deploy = file_get_contents("$gitdir/$repo.git/id_rsa.pub");
    echo "<div><em>$deploy</em></div>\n";
  } else {
    echo "<form id=\"repo-syncto-deploy\" action=\"\" method=\"POST\"><input type=\"submit\" name=\"submit_repo_syncto_deploy\" value=\"Générer une clé de déploiement\"/></form>\n";
  }
?>
    </div>
    <div id="syncfrom">
      <div class="error"><?php echo $errorMsgSyncFrom; ?></div>
      <div class="invite">This repo is synchronized from :</div>
      <table>
        <tr>
          <th class="name">Url</th>
          <th class="actions">Actions</th>
        </tr>
<?php
  $urls = gitrepoinfo('listsync', $repo, 'from');
  foreach ($urls as $url) {
    echo "        <tr>\n";
    echo "          <td class=\"name\">$url</td>\n";
    echo "          <td class=\"actions\"><a class=\"delete\" href=\"?delete=syncfrom&url=$url\" onclick=\"return confirm('Êtes vous sûr de vouloir supprimer cette url ?');\">Supprimer</a></td>\n";
    echo "        </tr>\n";
  }
?>
      </table>
      <p>Url to trigger synchronization: <a class="text" href="/post-update.php">post-update.php</a></p>
      <form id="repo-syncfrom-add" action="" method="POST">
        <fieldset>
          <legend>Ajouter une URL depuis laquelle se synchroniser</legend>
          <label for="new-syncfrom-url">URL :</label>&nbsp;<input type="text" name="new-syncfrom-url" id="new-syncfrom-url" value=""/>
          <input type="submit" name="submit_repo_syncfrom_add" value="Ajouter l'URL"/>
        </fieldset>
      </form>
    </div>
<?php require('footer.inc.php'); ?>
