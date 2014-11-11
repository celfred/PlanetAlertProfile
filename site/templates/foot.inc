      </div><!-- /#content -->
    </div><!-- /.row -->
  <div class="push"><!--//--!></div>
  </div><!-- /.container -->
</div><!--/wrap-->

  <footer class="text-center">
      <p>Powered by <a href='http://processwire.com'>ProcessWire</a> OpenSource CMS - &copy; <?php echo date("Y"); ?> F.L. 
        <a href="about/">[À propos]</a>
        <?php
          if($user->isLoggedin()) {
            echo "<a style='float: right; margin-right: 5px;' href='./?logout=1'>[Log out]</a>";
          } else {
            echo "<a style='float: right;' href='".$pages->get('/login')->url."?sender=".$page->id.'&team='.$input->urlSegment1."'>[Admin]</a>";
          }
        ?>
      </p>
	</footer>


	<?php 

  if ($user->isSuperuser()) {
    echo '<span class="backendLink">';
    echo '<a href="'.$config->urls->admin.'">[Backend]</a>';
    echo '<a href="./?logout=1">[Log out]</a>';
    echo '</span>';
  }
	// If the page is editable, then output a link that takes us straight to the page edit screen:
  /*
	if($page->editable()) {
		echo "<a class='nav backendLink' id='editpage' href='{$config->urls->admin}page/edit/?id={$page->id}'>Edit</a>"; 
	}
  */

	?>
  <script src="<?php echo $config->urls->templates?>bower_components/angular/angular.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/angular-route/angular-route.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/checklist-model/checklist-model.js"></script>
  <script src="<?php echo $config->urls->templates?>js/app.js"></script>
  <script src="<?php echo $config->urls->templates?>js/services.js"></script>
  <script src="<?php echo $config->urls->templates?>js/controllers.js"></script>
  <script src="<?php echo $config->urls->templates?>js/filters.js"></script>
  <script src="<?php echo $config->urls->templates?>js/directives.js"></script>
  <script src="<?php echo $config->urls->templates?>scripts/jquery-1.4.2.min.js"></script>
  <script src="<?php echo $config->urls->templates?>scripts/main.js"></script>
</body>
</html>
