<?php
require_once('include.inc.php');
if (empty($vars['repo'])) {
  redirect('/');
} else {
  $repo = $vars['repo'];
}
redirectifnotrepoadmin($repo);
$redirect = false;
if (isset($_POST['submit_repo_desc'])) {
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('set', $repo, 'description', $fDesc);
  if ($res === false) {
    $errorMsg = "La description n'a pas pu être appliquée.";
  } else {
    $redirect = true;
  }
} else if (isset($_POST['submit_repo_config'])) {
  $fOption = $_POST['new-option'];
  $fValue = $_POST['new-value'];
  $res = gitrepoinfo('set', $repo, $fOption, $fValue);
  if ($res === false) {
    $errorMsg = "L'option n'a pas pu être appliquée.";
  } else {
    $redirect = true;
  }
} else if (isset($_POST['submit_repo_export'])) {
  $fExport = $_POST['new-export'] == 'on' ? 'on' : 'off';
  $res = gitrepoinfo('export', $repo, $fExport);
  if ($res === false) {
    $errorMsg = "L'export n'a pas pu être appliqué.";
  } else {
    $redirect = true;
  }
} else if (isset($_POST['submit_repo_syncto_add'])) {
  $fUrl = $_POST['new-syncto-url'];
  $res = gitrepoinfo('sync', $repo, 'to', $fUrl);
  if ($res === false) {
    $errorMsg = "L'url n'a pas pu être ajoutée.";
  } else {
    $redirect = true;
  }
} else if (isset($_POST['submit_repo_syncto_deploy'])) {
  $res = gitrepoinfo('deploy-key', $repo);
  if ($res === false) {
    $errorMsg = "La clé de déploiement n'a pas pu être ajoutée.";
  } else {
    $redirect = true;
  }
} else if (isset($_GET['delete']) && $_GET['delete'] == 'syncto') {
  $fUrl = $_GET['url'];
  $res = gitrepoinfo('unsync', $repo, 'to', $fUrl);
  $redirect = true;
} else if (isset($_POST['submit_repo_syncfrom_add'])) {
  $fUrl = $_POST['new-syncfrom-url'];
  $res = gitrepoinfo('sync', $repo, 'from', $fUrl);
  if ($res === false) {
    $errorMsg = "L'url n'a pas pu être ajoutée.";
  } else {
    $redirect = true;
  }
} else if (isset($_GET['delete']) && $_GET['delete'] == 'syncfrom') {
  $fUrl = $_GET['url'];
  $res = gitrepoinfo('unsync', $repo, 'from', $fUrl);
  $redirect = true;
}
if ($redirect) {
  redirecturl($_SERVER['REQUEST_URI']);
}
$pageTitle = "$title - Configuration de $repo";
require('header.inc.php');
$repo_tab_active = 'admin';
$exportok = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
$repoadmin = true;
require('repo-nav.inc.php');
?>
    <div class="panel panel-info">
      <div class="panel-heading">Description de <strong><?php echo $repo; ?></strong> :</div>
      <div class="panel-body">
        <form id="repo-edit-desc" action="" method="POST">
          <label for="new-desc">Description :</label>&nbsp;<input type="text" name="new-desc" id="new-desc" value="<?php echo htmlspecialchars(file_get_contents("$gitdir/$repo.git/description")); ?>"/>
          <input type="submit" name="submit_repo_desc" value="Mettre à jour la description"/>
        </form>
      </div>
    </div>
    <div class="panel panel-info">
      <div class="panel-heading">Configuration de <strong><?php echo $repo; ?></strong> :</div>
      <div class="panel-body">
        <pre>
<?php
  $cfg = file_get_contents("$gitdir/$repo.git/config");
  echo preg_replace('/[ \t][ \t]*/', '  ', $cfg);
?>
        </pre>
        <form id="repo-edit-config" action="" method="POST">
          <fieldset>
            <legend>Changer/Ajouter une option de config</legend>
            <label for="new-option">Config option :</label>&nbsp;<input type="text" name="new-option" id="new-option" value=""/>
            <label for="new-value">Config value :</label>&nbsp;<input type="text" name="new-value" id="new-value" value=""/>
            <input type="submit" name="submit_repo_config" value="Mettre à jour l'option de configuration"/>
          </fieldset>
        </form>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Visibilité de <strong><?php echo $repo; ?></strong> :</div>
      <div class="panel-body">
        <?php $isExport = file_exists("$gitdir/$repo.git/git-daemon-export-ok") ? 'checked="checked"' : ''; ?>
        <form id="repo-edit-export" action="" method="POST">
          <label for="new-export">Anonymous read-only access :</label>&nbsp;<input type="checkbox" name="new-export" id="new-export" value="on" <?php echo $isExport; ?>/>
          <input type="submit" name="submit_repo_export" value="Mettre à jour l'option d'export lecture-seule"/>
        </form>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading"><strong><?php echo $repo; ?></strong> is synchronized to:</div>
      <div class="panel-body">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Url</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php $urls = gitrepoinfo('listsync', $repo, 'to'); foreach ($urls as $url) { ?>
            <tr>
              <td><?php echo $url; ?></td>
              <td class="actions"><a href="?delete=syncto&url=<?php echo $url; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette url ?');"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;Supprimer</a></td>
            </tr>
<?php } ?>
          </tbody>
        </table>
        <form id="repo-syncto-add" action="" method="POST">
          <fieldset>
            <legend>Ajouter une URL vers laquelle se synchroniser</legend>
            <label for="new-syncto-url">URL :</label>&nbsp;<input type="text" name="new-syncto-url" id="new-syncto-url" value=""/>
            <input type="submit" name="submit_repo_syncto_add" value="Ajouter l'URL"/>
          </fieldset>
        </form>
        <h4>Clé de déploiement :</h4>
<?php
  if (file_exists("$gitdir/$repo.git/id_rsa.pub")) {
    $deploy = file_get_contents("$gitdir/$repo.git/id_rsa.pub");
    echo "<p class=\"well\" style=\"word-break: break-all;\">$deploy</p>";
  } else {
    echo '<form action="" method="POST"><input type="submit" name="submit_repo_syncto_deploy" value="Générer une clé de déploiement"/></form>';
  }
?>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading"><strong><?php echo $repo; ?></strong> is synchronized from:</div>
      <div class="panel-body">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Url</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php $urls = gitrepoinfo('listsync', $repo, 'from'); foreach ($urls as $url) { ?>
            <tr>
              <td><?php echo $url; ?></td>
              <td class="actions"><a href="?delete=syncfrom&url=<?php echo $url; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette url ?');"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;Supprimer</a></td>
            </tr>
<?php } ?>
          </tbody>
        </table>
        <p class="well">Url to trigger synchronization: <a class="text" href="/<?php echo $gitwebroot;?>post-update.php">post-update.php</a></p>
        <form id="repo-syncfrom-add" action="" method="POST">
          <fieldset>
            <legend>Ajouter une URL depuis laquelle se synchroniser</legend>
            <label for="new-syncfrom-url">URL :</label>&nbsp;<input type="text" name="new-syncfrom-url" id="new-syncfrom-url" value=""/>
            <input type="submit" name="submit_repo_syncfrom_add" value="Ajouter l'URL"/>
          </fieldset>
        </form>
      </div>
    </div>
<?php require('footer.inc.php'); ?>
