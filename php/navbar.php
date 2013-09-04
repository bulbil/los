<div class="navbar">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button> 
<a class="navbar-brand" href="home.php">THE LAND <img src="../img/los_logo.png" alt="land of sunshine logo" />OF SUNSHINE</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav nav-justified">
<li class="active"><a href="http://www.landofsunshine.org/blog/">home</a></li>
<li><a href="http://localhost:8888/los/php/data-table.php">data</a></li>
<li><a href="http://localhost:8888/los/php/visualization.php">visualization</a></li>

<?php $tag = (isset($_SESSION['username'])) ? "<li><a href='http://localhost:8888/los/php/reviews.php'>edit reviews</a></li>" :
		"<li><a href='http://localhost:8888/los/php/login.php'>login</a></li>";
echo $tag ?>

</ul>
</div>
</div>