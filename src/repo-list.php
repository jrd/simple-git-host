<?php
require_once('include.inc.php');
$tab_active = 'list';
if ($logged && isset($_POST['submit_repo'])) {
  $tab_active = 'add';
  $fRepo = $_POST['new-repo'];
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('create', $fRepo, $fDesc);
  if ($res === false) {
    $errorMsg = "Le dépôt n'a pas pu être ajouté.";
  } else {
    if (isset($_POST['new-export']) && $_POST['new-export'] == 'on') {
      gitrepoinfo('export', $fRepo, 'on');
    }
    # add current user as admin to the newly created repo.
    gitrepoinfo('add-user', $fRepo, $_SESSION['username'], 'admin');
    $tab_active = 'list';
  }
}
$pageTitle = $title;
$cat = 'repos';
$extrajs = array("/${gitwebroot}js/repo-list.js");
require('header.inc.php');
?>
    <ul class="nav nav-pills" id="tabs">
      <li role="presentation" class="<?php if ($tab_active == 'list') { echo 'active'; }?>"><a href="javascript:" data-div="#repo-list">Dépôts</a></li>
      <?php if ($logged) { ?>
      <li role="presentation" class="<?php if ($tab_active == 'add') { echo 'active'; }?>"><a href="javascript:" data-div="#repo-add"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>&nbsp;&nbsp;Ajouter</a></li>
      <?php } ?>
    </ul>
    <div id="repo-list">
      <h3>Les dépôts Git :</h3>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Adresses</th>
              <th>Membre ?</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php
$files = scandir($gitdir);
foreach ($files as $file) {
  if ($file[0] == '.') continue;
  if (is_dir("$gitdir/$file") && preg_match('/\.git$/', $file)) {
    $proj = preg_replace('/\.git$/', '', $file);
    $desc = htmlspecialchars(file_get_contents("$gitdir/$file/description"));
    if (empty($desc) || preg_match('/^Unnamed repository;/', $desc)) {
      $desc = $proj;
    }
    if ($logged) {
      $right = gitrepoinfo('user-right', $proj, $_SESSION['username']);
      if ($right !== false) {
        $right = implode('', $right);
      }
      if (!$right || empty($right)) {
        $right = 'no';
      }
    } else {
      $right = null;
    }
    $exportok = file_exists("$gitdir/$file/git-daemon-export-ok");
    if (!$admin && !$exportok && ($right == null || $right == 'no')) {
      continue;
    }
    switch ($right) {
      case 'admin':
        $member = 'Admin';
        break;
      case 'user':
        $member = 'Oui';
        break;
      case 'readonly':
        $member = 'Readonly';
        break;
      case 'no':
        $member = 'Non';
        break;
      default:
        $member = '<span title="Veuillez vous identifier"> ? </span>';
        break;
    }
    $actions = "<a href=\"".url('repo-info', 'repo', $proj)."\"><span class=\"glyphicon glyphicon-info-sign\" aria-hidden=\"true\"></span>&nbsp;Info</a>";
    $actions .= "&nbsp;<a href=\"".url('repo-histo', 'repo', $proj)."\"><span class=\"glyphicon glyphicon-list\" aria-hidden=\"true\"></span>&nbsp;Historique</a>";
    if ($exportok) {
      $actions .= "&nbsp;<a href=\"/$gitwebroot$gitwebpath/?p=$file\" target=\"gitweb\"><span class=\"glyphicon glyphicon-hand-right\" aria-hidden=\"true\"></span>&nbsp;Gitweb</a>";
    }
    $actions .= "&nbsp;<a href=\"".url('repo-users', 'repo', $proj)."\"><span class=\"glyphicon glyphicon-user\" aria-hidden=\"true\"></span>&nbsp;Utilisateurs</a>";
    if ($admin || $right == 'admin') {
      $actions .= "&nbsp;<a class=\"edit\" href=\"".url('repo-edit', 'repo', $proj)."\"><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>&nbsp;Éditer</a>";
      $actions .= "&nbsp;<a class=\"delete\" href=\"".url('repo-del', 'repo', $proj)."\" onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer le dépôt \'$proj\' ?');\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span>&nbsp;Supprimer</a>";
    }
    $name = $proj;
    echo "        <tr><td title=\"$desc\"><a href=\"".url('repo-info', 'repo', $proj)."\">$name</a></td><td class=\"address\">";
    echo "<div class=\"uri uri-ssh\">$gituser@$githost:$file</div>";
    if ($exportok) {
      echo "<div class=\"uri uri-git\">git://$githost/$file</div>";
      $httpurl = sprintf("%s://%s/{$gitwebroot}readonly/%s", isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['HTTP_HOST'], $file);
      echo "<div class=\"uri uri-http\">$httpurl</div>";
    }
    echo "</td><td class=\"member\">$member</td><td class=\"actions\">$actions</td></tr>\n";
  }
}
?>
            </tbody>
        </table>
      </div>
    </div>
<?php if ($logged) { ?>
    <div id="repo-add">
      <form id="repo-add" action="" method="POST">
        <fieldset>
          <legend>Ajouter un dépôt</legend>
          <label for="new-repo">Nom du nouveau dépôt :</label>&nbsp;<input type="text" name="new-repo" id="new-repo" value=""/>
          <br/><label for="new-desc">Description :</label>&nbsp;<input type="text" name="new-desc" id="new-desc" value=""/>
          <?php if (!empty($gitwebpath)) { ?>
          <br/><label for="new-export">Anonymous read-only access :</label>&nbsp;<input type="checkbox" name="new-export" id="new-export" value="on"/>
          <?php } ?>
          <br/><input type="submit" name="submit_repo" value="Ajouter le dépôt"/>
        </fieldset>
      </form>
    </div>
<?php } ?>
<?php require('footer.inc.php'); ?>
