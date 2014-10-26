<body>
$for(include-before)$
$include-before$
$endfor$
$if(title)$
<header>
<h1 class="title">$title$</h1>
$for(author)$
<h2 class="author">$author$</h2>
$endfor$
$if(date)$
<h3 class="date">$date$</h3>
$endif$
</header>
$endif$
$if(toc)$
<nav id="$idprefix$TOC">
$toc$
</nav>
$endif$
<section class="documentation">
$body$
</section>
$for(include-after)$
$include-after$
$endfor$
