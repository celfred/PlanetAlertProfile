<?php
  $monthName = strtolower(date("F"));
  $monthId = $pages->get("name=$monthName")->id;
  $currentPeriod = $pages->get("name=admin-actions")->periods;
?>
<ul class="tabList list-inline">
  <li class="<?php if ($page->template=='main-office') { echo 'active'; } ?>"><a href="<?php echo $team->child("template=main-office")->url; ?>">Main Office</a></li>
  <li class="<?php if ($page->name=='players') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'players/'.$input->urlSegment1; ?>">The team</a></li>
  <li class="<?php if ($page->name=='world') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'world/'.$input->urlSegment1; ?>">The world</a></li>
  <?php if ($user->isLoggedin() && $user->isSuperuser() === false) { ?>
  <li class="<?php if ($page->name=='shop') { echo 'active'; } ?>"><a class="" href="<?php echo $pages->get('/shop_generator')->url.$pages->get("template=player, login=$user->name"); ?>">The marketplace</a></li>
  <?php } ?>
  <?php if ( $user->isSuperuser()) { // Admin front-end ?>
    <li class="<?php if ($page->name=='admintable') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'adminTable/'.$input->urlSegment1; ?>">Admin table</a></li>
    <li class="<?php if ($page->name=='shop') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'shop/'.$input->urlSegment1; ?>">The marketplace</a></li>
    <li class="<?php if ($page->name=='quiz') { echo 'active'; } ?>"><a href="<?php echo $homepage->url.'quiz/'.$input->urlSegment1; ?>">Monster invasions</a></li>
    <li><a target="_blank" href="<?php echo $homepage->url.'report_generator/participation/'.$input->urlSegment1.'/'.$currentPeriod->id.'?sort=title'; ?>">Current period participation</a></li>
  <?php } ?>
</ul>
