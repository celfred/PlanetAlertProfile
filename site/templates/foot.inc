<?php namespace ProcessWire; ?>
      </div><!-- /#content -->
    </div><!-- /.row -->
  <div class="push"><!--//--!></div>
  </div><!-- /.container -->
</div><!--/wrap-->

<?php if ($user->isSuperuser() || $user->hasRole('teacher')) { ?>
  <section class="navigate">
    <a href="#" id="scrollUp"><span class="glyphicon glyphicon-triangle-top"></span></a>
    <a href="#" id="scrollDown"><span class="glyphicon glyphicon-triangle-bottom"></span></a>
  </section>
<?php } ?>

<footer class="text-center">
  <p>Planet Alert v0.7.1
    &nbsp;&copy; <?php echo date("Y"); ?> FL 
    &nbsp;-&nbsp;
    Powered by <a href="http://processwire.com">ProcessWire</a> CMS (v<?php echo $config->version; ?>)
    &nbsp;
    <a href="about/">[<?php echo $pages->get("name=about")->title; ?>]</a>
    &nbsp;
    <a href="https://framagit.org/celfred/planetAlert"><?php echo __('[Framagit repository]'); ?></a>
   </p>
</footer>

<?php 
  if ($user->isSuperuser()) {
    echo '<span class="backendLink">';
    echo '<a href="'.$config->urls->admin.'">[Backend]</a>';
    echo '</span>';
} else if ($user->hasRole('teacher')) {
  echo '<span class="backendLink">'.__("Teacher").'</span>';
}

	?>
  <script src="<?php echo $config->urls->templates?>bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <?php if ($page->template != 'newsboard') { ?>
		<script src="<?php echo $config->urls->templates?>bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
		<script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/sweetalert2/dist/sweetalert2.min.js'></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/sweetalert2/dist/sweetalert2.css">
  <?php } ?>
  <?php if ($page->is("template=hall-of-fame|visualizer")) { ?>
		<script src="<?php echo $config->urls->templates?>bower_components/masonry/dist/masonry.pkgd.min.js"></script>
  <?php } ?>
  <?php if ($page->is("template=quiz|main-office")) { ?>
		<script src="<?php echo $config->urls->templates?>bower_components/svg-pan-zoom/dist/svg-pan-zoom.min.js"></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/chance/dist/chance.min.js'></script>

  <?php } ?>
  <?php if ($page->template == 'exercise') { ?>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular/angular.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-animate/angular-animate.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-sanitize/angular-sanitize.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/chance/dist/chance.min.js'></script>
    <!-- Set the game logic !-->
    <script type='text/javascript' src='<?php echo $config->urls->templates?>scripts/exercise.js'></script>
  <?php }
    if ($user->language->name == 'french') {
      echo '<script src="'.$config->urls->templates.'scripts/dataTables.french.lang.js"></script>';
      echo '<script src="'.$config->urls->templates.'scripts/lang.fr.js"></script>';
    } else {
      echo '<script src="'.$config->urls->templates.'scripts/lang.en.js"></script>';
    }
  ?>
  <script src="<?php echo $config->urls->templates?>scripts/main.js"></script>
</body>
</html>
