<?php 

// TODO : Use download area
$logo = $pages->get('/')->photo->eq(0)->getCrop('thumbnail');

$out = '';

$out .= '<img src="'.$logo->url.'" width="100" height="100" /> ';
$out .= '<h1>'.$page->title.'</h1>';
$out .= '<div>';
$out .= $page->body;
$out .= '</div>';

echo $out;
?>
