<?php
?>
<ul class="tabList list-inline">
  <li class="<?php if ($page->name=='players') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'players/'.$input->urlSegment1; ?>">The team</a></li>
  <li class="<?php if ($page->name=='world') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'world/'.$input->urlSegment1; ?>">The world</a></li>
  <?php if ( $user->isSuperuser()) { // Admin front-end ?>
  <li class="<?php if ($page->name=='admintable') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'adminTable/'.$input->urlSegment1; ?>">Admin table</a></li>
  <li class="<?php if ($page->name=='shop') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'shop/'.$input->urlSegment1; ?>">The marketplace</a></li>
  <li class="<?php if ($page->name=='quiz') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'quiz/'.$input->urlSegment1; ?>">Monster invasion</a></li>
  <li><a href="<?php echo $reportLink.$input->urlSegment1; ?>/participation/10" target="_blank">Participation des 10 derniers cours</a></li>
  <!-- <li ng-class="{active: selected == 7}" ng-click="selected = 7">Reports</li> -->
  <?php } //Guest contact form ?>
</ul>
