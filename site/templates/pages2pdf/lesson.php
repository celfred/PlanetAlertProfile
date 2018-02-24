<?php 

// TODO : Use download area
$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

$out = '';

$out .= '<img src="'.$logo->url.'" width="100" height="100" /> ';
// Add today's date
$out .= '<p style="text-decoration: underline;">'.\date('l, F dS').'</p>';
$out .= '<h1 style="text-decoration: underline;">'.$page->title.'</h1>';
$out .= '<h3 style="text-align: center;">'.$page->summary.'</h3>';
$out .= '<div class="copybook">';
$out .= $page->body;
$out .= '</div>';

echo $out;
?>
