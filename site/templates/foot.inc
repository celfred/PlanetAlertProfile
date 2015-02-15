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

	?>
  <script src="<?php echo $config->urls->templates?>bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/DataTables/media/js/jquery.dataTables.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/datatables-plugins-bootstrap3/dist/js/datatables-plugins-bootstrap3.min.js"></script>
  <script src="<?php echo $config->urls->templates?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="<?php echo $config->urls->templates?>scripts/svg-pan-zoom.min.js"></script>
  <script src="<?php echo $config->urls->templates?>scripts/main.js"></script>
</body>
</html>
