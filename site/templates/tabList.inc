<?php
  $monthName = strtolower(date("F"));
  $monthId = $pages->get("name=$monthName")->id;
?>
<ul class="tabList list-inline">
  <li class="<?php if ($page->name=='players') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'players/'.$input->urlSegment1; ?>">The team</a></li>
  <li class="<?php if ($page->name=='world') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'world/'.$input->urlSegment1; ?>">The world</a></li>
  <?php if ( $user->isSuperuser()) { // Admin front-end ?>
    <li class="<?php if ($page->name=='admintable') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'adminTable/'.$input->urlSegment1; ?>">Admin table</a></li>
    <li class="<?php if ($page->name=='shop') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'shop/'.$input->urlSegment1; ?>">The marketplace</a></li>
    <li class="<?php if ($page->name=='quiz') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'quiz/'.$input->urlSegment1; ?>">Monster invasion</a></li>
    <li class="<?php if ($page->name=='reports') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'reports/'.$input->urlSegment1; ?>">Reports</a></li>
    <?php if ($monthId !== 0) { ?>
      <li><a target="_blank" href="<?php echo $homepage->url.'report_generator/participation/'.$input->urlSegment1.'/'.$monthId.'?sort=title'; ?>">Current month participation</a></li>
    <?php } ?>
  <?php } ?>
</ul>
