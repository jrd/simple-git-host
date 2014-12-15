<?php
// $repo_tab_active
// $repo
// $repoadmin
// $exportok
?>
<ul class="nav nav-pills">
  <li role="presentation" <?php echo $repo_tab_active == 'info' ? 'class="active"' : ''; ?>><a href="<?php purl('repo-info', 'repo', $repo); ?>"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;Info</a></li>
  <li role="presentation" <?php echo $repo_tab_active == 'histo' ? 'class="active"' : ''; ?>><a href="<?php purl('repo-histo', 'repo', $repo); ?>"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;&nbsp;Historique</a></li>
  <?php if ($exportok) { ?>
  <li role="presentation"><a href="<?php echo "/$gitwebroot$gitwebpath/?p=$repo.git"; ?>" target="gitweb"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span>&nbsp;&nbsp;Gitweb</a></li>
  <?php } ?>
  <li class="dropdown <?php echo $repo_tab_active == 'admin' ? 'active' : '';?>">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span>&nbsp;&nbsp;Administrer&nbsp;<span class="caret"></span></a>
    <ul class="dropdown-menu" role="menu">
      <li role="presentation"><a href="<?php purl('repo-users', 'repo', $repo); ?>"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;Utilisateurs</a></li>
      <li role="presentation" <?php if (!$repoadmin) { echo 'class="disabled"'; } ?>><?php echo $repoadmin ? '<a href="'.url('repo-edit', 'repo', $repo).'">' : '<a href="javascript:">'; ?><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>&nbsp;&nbsp;Éditer</a></li>
      <li role="presentation" <?php if (!$repoadmin) { echo 'class="disabled"'; } ?>><?php echo $repoadmin ? '<a href="'.url('repo-del', 'repo', $repo).'" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer le dépôt '."\'$repo\'".' ?\');">' : '<a href="javascript:">'; ?><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;&nbsp;Supprimer</a></li>
    </ul>
  </li>
</ul>
