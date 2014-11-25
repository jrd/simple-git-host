<?php
require_once('include.inc.php');
$errorMsg = '';
if ($admin && isset($_POST['submit_repo'])) {
  $fRepo = $_POST['new-repo'];
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('create', $fRepo, $fDesc);
  if ($res === false) {
    $errorMsg = "Le dépôt n'a pas pu être ajouté.";
  } else {
    if (isset($_POST['new-export']) && $_POST['new-export'] == 'on') {
      gitrepoinfo('export', $fRepo, 'on');
    }
    gitrepoinfo('add-user', $fRepo, $_SESSION['username']); # add current user to the newly created repo.
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
    $users = array_map(function($val) { return explode(':', $val)[0]; }, gitrepoinfo('show-users', $proj));
    $membre = count($users) > 0 ? '<span title="Veuillez vous identifier"> ? </span>' : '<span title="Aucun utilisateur"> — </span>';
    if ($logged && count($users) > 0) {
      if (in_array($_SESSION['username'], $users)) {
        $membre = "Oui";
      } else {
        $membre = "Non";
      }
    }
    $actions = "<a href=\"/{$gitwebroot}info/$proj\">Info</a>&nbsp;<a href=\"/{$gitwebroot}users/$proj\">Utilisateurs</a>&nbsp;<a href=\"/{$gitwebroot}histo/$proj\">Historique</a>";
    if ($admin) {
      $actions .= "&nbsp;<a class=\"edit\" href=\"/{$gitwebroot}edit/$proj\">Éditer</a>";
      $actions .= "&nbsp;<a class=\"delete\" href=\"/{$gitwebroot}delete/$proj\" onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer le dépôt \'$proj\' ?');\">Supprimer</a>";
    }
    $name = $proj;
    $exportok = file_exists("$gitdir/$file/git-daemon-export-ok");
    if (!empty($gitwebpath) && $exportok) {
      $name .= "&nbsp;<a href=\"$gitwebpath/?p=$file\">⇒</a>";
    }
    echo "        <tr><td class=\"name\" title=\"$desc\">$name</td><td class=\"address\">";
    echo "<div class=\"rw\">$gituser@$githost:$file</div>";
    if ($exportok) {
      echo "<div class=\"ro-git\">git://$githost/$file</div>";
      $httpurl = sprintf("%s://%s/{$gitwebroot}readonly/%s", isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['HTTP_HOST'], $file);
      echo "<div class=\"ro-http\">$httpurl</div>";
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
    <a href="/<?php echo $gitwebroot;?>manage_users">Gestion des utilisateurs</a>
<?php } ?>
<?php require('footer.inc.php'); ?>
