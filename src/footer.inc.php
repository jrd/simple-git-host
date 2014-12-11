    </div>
    <div class="footer navbar-fixed-bottom navbar-inverse">
      <ul class="text-center list-inline">
        <li><a href="https://github.com/jrd/simple-git-host" target="github">Simple Git Host</a></li>
        <li> • </li>
        <li>version&nbsp;<strong><?php readfile('.version'); ?></strong></li>
        <li> • </li>
        <li>by&nbsp;<strong><?php readfile('.copyright'); ?></strong></li>
        <li> • </li>
        <li>license&nbsp;<strong><?php readfile('.license'); ?></strong></li>
      </ul>
    </div>
    <script type="text/javascript" src="/<?php echo $gitwebroot;?>js/jquery.min.js"></script>
    <script type="text/javascript" src="/<?php echo $gitwebroot;?>js/bootstrap.min.js"></script>
    <?php foreach ($extrajs as $js) { ?>
    <script type="text/javascript" src="<?php echo $js;?>"></script>
    <?php } ?>
  </body>
</html>

