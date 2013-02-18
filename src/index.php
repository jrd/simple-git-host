<?php
require('include.inc.php');
$errorMsg = '';
if ($admin && isset($_POST['submit_repo'])) {
  $fRepo = $_POST['new-repo'];
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('create', $fRepo, $fDesc);
  if ($res === false) {
    $errorMsg = "Le dépôt n'a pas pu être ajouté.";
  }
  if (isset($_POST['new-export']) && $_POST['new-export'] == 'on') {
    gitrepoinfo('export', $fRepo, 'on');
  }
}
$pageTitle = $title;
require('header.inc.php');
?>
    <div id="repos">
      <div class="invite">Les dépôts Git :</div>
      <table>
        <tr>
          <th class="name">Nom</th>
          <th class="address">Adresse</th>
          <th class="member">Membre ?</th>
          <th class="actions">Actions</th>
        </tr>
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
    $users = gitrepoinfo('show-users', $proj);
    $membre = count($users) > 0 ? '<span title="Veuillez vous identifier"> ? </span>' : '<span title="Aucun utilisateur"> — </span>';
    if ($logged && count($users) > 0) {
      if (in_array($_SESSION['username'], $users)) {
        $membre = "Oui";
      } else {
        $membre = "Non";
      }
    }
    $actions = "<a href=\"repo-users.php?repo=$proj\">Utilisateurs</a>&nbsp;<a href=\"repo-histo.php?repo=$proj\">Historique</a>";
    if ($admin) {
      $actions .= "&nbsp;<a class=\"edit\" href=\"repo-edit.php?repo=$proj\">Éditer</a>";
      $actions .= "&nbsp;<a class=\"delete\" href=\"repo-del.php?repo=$proj\" onclick=\"return confirm('Êtes vous sûr de vouloir supprimer le dépôt \'$proj\' ?');\">Supprimer</a>";
    }
    $name = $proj;
    $exportok = file_exists("$gitdir/$file/git-daemon-export-ok");
    if (!empty($gitwebpath) && $exportok) {
      $name .= "&nbsp;<a href=\"$gitwebpath/?p=$file\">⇒</a>";
    }
    echo "        <tr><td class=\"name\" title=\"$desc\">$name</td><td class=\"address\">";
    echo "<div class=\"rw\">$gituser@$githost:$gitdir/$file</div>";
    if ($exportok) {
      echo "<div class=\"ro\">git://$githost/$file</div>";
    }
    echo "</td><td class=\"member\">$membre</td><td class=\"actions\">$actions</td></tr>\n";
  }
}
?>
      </table>
    </div>
<?php if ($admin) { ?>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="repo-add" action="" method="POST">
      <fieldset>
        <legend>Ajouter un dépôt</legend>
        <table>
          <tr>
            <td>
              <label for="new-repo">Nom du nouveau dépôt :</label>&nbsp;<input type="text" name="new-repo" id="new-repo" value=""/>
              <br/><label for="new-desc">Description :</label>&nbsp;<input type="text" name="new-desc" id="new-desc" value=""/>
              <?php if (!empty($gitwebpath)) { ?>
              <br/><label for="new-export">Anonymous read-only access :</label>&nbsp;<input type="checkbox" name="new-export" id="new-export" value="on"/>
              <?php } ?>
            </td>
            <td>
              <input type="submit" name="submit_repo" value="Ajouter le dépôt"/>
            </td>
          </tr>
        </table>
      </fieldset>
    </form>
    <a href="admin-users.php">Gestion des utilisateurs</a>
<?php } ?>
<?php require('footer.inc.php'); ?>
