<?php namespace ProcessWire; ?>
<?php 
  if (!$config->ajax) {
          echo '</div>'; // #content
        echo '</div>'; // .row
      echo '<div class="push"><!--//--!></div>';
      echo '</div>'; // .container
    echo '</div>'; // wrap

    if ($user->isSuperuser() || $user->hasRole('teacher') || $user->name == 'test') {
      echo '<section class="navigate">';
        echo '<a href="#" id="scrollUp"><span class="glyphicon glyphicon-triangle-top"></span></a>';
        echo '<a href="#" id="scrollDown"><span class="glyphicon glyphicon-triangle-bottom"></span></a>';
      echo '</section>';
    }

    echo '<footer class="text-center">';
      echo '<p>';
      echo 'Planet Alert v1.1.0';
      echo ' &copy; '.date("Y").' FL';
      echo ' - ';
      echo 'Powered by <a href="http://processwire.com">ProcessWire</a> CMS (v'.$config->version.')';
      echo '&nbsp;';
      $about = $pages->get("name=about");
      echo '<a href="'.$about->url.'">['.$about->title.']</a>';
      echo ' <a href="https://framagit.org/celfred/planetAlert">'.__("[Framagit repository]").'</a>';
      echo '</p>';
    echo '</footer>';

    if ($user->isSuperuser()) {
      echo '<span class="backendLink">';
      echo '<a href="'.$config->urls->admin.'">[Backend]</a>';
      echo '</span>';
    } else if ($user->hasRole('teacher')) {
      echo '<span class="backendLink">'.__("Teacher").'</span>';
    }
  }

  $jsVersion = md5(filemtime($config->paths->templates.'foot.inc'));
?>
  <script src="<?php echo $config->urls->templates?>bower_components/jquery/dist/jquery.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.18/datatables.min.css"/>
  <script src="<?php echo $config->urls->templates?>bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.18/datatables.min.js"></script>
  <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/sweetalert2/dist/sweetalert2.min.js'></script>
  <link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/sweetalert2/dist/sweetalert2.css">
  <?php if ($page->is("template=hall-of-fame|visualizer")) { ?>
    <script src="<?php echo $config->urls->templates?>bower_components/masonry/dist/masonry.pkgd.min.js"></script>
  <?php } ?>
  <?php if ($page->is("template=quiz|main-office")) { ?>
    <script src="<?php echo $config->urls->templates?>bower_components/svg-pan-zoom/dist/svg-pan-zoom.min.js"></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/chance/dist/chance.min.js'></script>

  <?php } ?>
  <?php if ($page->is("template=exercise|speedQuiz")) { ?>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular/angular.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-animate/angular-animate.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-sanitize/angular-sanitize.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/chance/dist/chance.min.js'></script>
    <!-- Set the game logic !-->
    <script type='text/javascript' src='<?php echo $config->urls->templates?>scripts/exercise.js?v=<?php echo $jsVersion; ?>'></script>
  <?php }
    if ($user->language->name == 'french') {
      echo '<script src="'.$config->urls->templates.'scripts/dataTables.french.lang.js?v='.$jsVersion.'"></script>';
      echo '<script src="'.$config->urls->templates.'scripts/lang.fr.js?v='.$jsVersion.'"></script>';
    } else {
      echo '<script src="'.$config->urls->templates.'scripts/lang.en.js?v='.$jsVersion.'"></script>';
    }
    echo '<script src="'.$config->urls->templates.'scripts/main.js?v='.$jsVersion.'"></script>';

  if (!$config->ajax) {
    echo '</body>';
    echo '</html>';
  }
?>
