      </div><!-- /#content -->
    </div><!-- /.row -->
  <div class="push"><!--//--!></div>
  </div><!-- /.container -->
</div><!--/wrap-->

  <footer class="text-center">
      <p>Powered by <a href='http://processwire.com'>ProcessWire</a> OpenSource CMS - &copy; <?php echo date("Y"); ?> F.L. 
        <a href="about/">[About]</a>
        &nbsp;
        <a href="https://github.com/celfred/PlanetAlertProfile">[Github repository]</a>
      </p>
	</footer>


	<?php 

  if ($user->isSuperuser()) {
    echo '<span class="backendLink">';
    echo '<a href="'.$config->urls->admin.'">[Backend]</a>';
    echo '</span>';
  }

	?>
  <script src="<?php echo $config->urls->templates?>bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <?php if ($page->template != 'newsboard') { ?>
		<script src="<?php echo $config->urls->templates?>bower_components/DataTables/media/js/jquery.dataTables.min.js"></script>
		<script src="<?php echo $config->urls->templates?>bower_components/datatables-plugins-bootstrap3/dist/js/datatables-plugins-bootstrap3.min.js"></script>
		<script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/sweetalert/dist/sweetalert.min.js'></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $config->urls->templates?>bower_components/sweetalert/dist/sweetalert.css">
  <?php } ?>
  <?php if ($page->template == 'quiz') { ?>
		<script src="<?php echo $config->urls->templates?>scripts/svg-pan-zoom.min.js"></script>
  <?php } ?>
  <?php if ($page->template == 'exercise' || $page->name == 'underground-training') { ?>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular/angular.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-animate/angular-animate.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/angular-sanitize/angular-sanitize.min.js'></script>
    <script type='text/javascript' src='<?php echo $config->urls->templates?>bower_components/chance/dist/chance.min.js'></script>
    <!-- Set the game logic !-->
    <script type='text/javascript' src='<?php echo $config->urls->templates?>scripts/exercise.js'></script>
  <?php } ?>
  <script src="<?php echo $config->urls->templates?>scripts/main.js"></script>
</body>
</html>
