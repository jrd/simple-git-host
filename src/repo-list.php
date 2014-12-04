<?php
require_once('include.inc.php');
if ($logged && isset($_POST['submit_repo'])) {
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
    if ($logged) {
      $right = 'no';
      foreach (gitrepoinfo('show-users', $proj) as $userinfo) {
        $info = explode(':', $userinfo);
        if ($info[0] == $_SESSION['username']) {
          $right = $info[1];
          break;
        }
      }
    } else {
      $right = null;
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
    $actions = "<a href=\"/{$gitwebroot}info/$proj\">Info</a>&nbsp;<a href=\"/{$gitwebroot}users/$proj\">Utilisateurs</a>&nbsp;<a href=\"/{$gitwebroot}histo/$proj\">Historique</a>";
    if ($admin || $right == 'admin') {
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
    echo "</td><td class=\"member\">$member</td><td class=\"actions\">$actions</td></tr>\n";
  }
}
?>
      </table>
    </div>
<?php if ($logged) { ?>
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
<?php } ?>
<?php if ($admin) { ?>
    <a href="/<?php echo $gitwebroot;?>manage_users">Gestion des utilisateurs</a>
<?php } ?>
<?php require('footer.inc.php'); ?>
