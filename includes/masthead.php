
<div class="row"><div class="col-md-8 col-md-offset-2" id="login">
		<?php if(isset($_SESSION['username'])) { echo "<h5>" . $_SESSION['username'] . " / <a href='../index.php'>logout</a></h5>";} ?>
</div></div>

<div class="row">
	<div id='masthead' class="col-md-8 col-md-offset-2">
		<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		</button>
		</div>
	<div id="brand">
		<a href="home.php"><p><img src="../img/los_masthead.png" alt="land of sunshine logo" /></a>
	</div>
	<div class="navbar-collapse collapse">
		<ul class="nav nav-justified">
			<li class="active"><a href="http://www.landofsunshine.org/blog/">home</a></li>
			<li><a href="http://localhost:8888/los/views/data-table.php">data</a></li>
			<li><a href="http://localhost:8888/los/views/visualization.php">visualization</a></li>

			<?php $tag = (isset($_SESSION['username'])) ? "<li><a href='http://localhost:8888/los/views/reviewer.php'>edit reviews</a></li>" :
					"<li><a href='http://localhost:8888/los/views/login.php'>login</a></li>";
			echo $tag ?>
		</ul>
	</div>
	</div>
</div>