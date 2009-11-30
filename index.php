<?
	/*$directory = scandir( "/home/oc/public_html/" );
	
	foreach( $directory as $file )
	{
		if( count( sscanf( $file, "%s.php" ) ) == 1 )
		{
			if( $file === "." || $file === ".." )
			{
			}
			else
			{
				echo "<a href='".$file."'>".$file."</a><br/>";
			}
		}
	}*/

require_once 'header.php';
?>

<!-- header ends -->
<!-- content begins -->

	<div id="right">
		<h2>Welcome To Our Website</h2><br />
			<h4><a href="#">South Padre Computers</a></h4><br />
			<p>This website is released under the Creative Commons Attribution 2.5 License.</p>
			    <p>Welcome to South Padre Computers, home of the Open Configurator project!  At South Padre Computers,
			    		you can customize your computer the way you like it, from the motherboard to the case!  We specialize
			    		in custom-building your computer the way you want it; any way you want it.
			    	<br />
  <br />
			      South Padre Computers : 2009 </p>
			<p class="date"><img src="images/timeicon.gif" alt="" />12:04pm</p>
	</div>
	<div id="left">		
			<h3>Headings</h3>
			<ul>
				<li class="m1"><ul>
					<li><a href="#">Heading A</a></li>
					<li><a href="#">Heading B</a></li>
					<li><a href="#">Heading C</a></li>
					<li><a href="#">Heading D</a></li>
					<li><a href="#">Heading E</a></li>
					<li><a href="#">Heading F</a></li>
					<li><a href="#">Heading G</a></li>
					<li><a href="#">Heading H</a></li>
					<li><a href="#">Heading I</a></li>
					<li><a href="#">Heading J</a></li>
					</ul>
			  </li>
			</ul><br />
			<h3>Company News</h3>
			<br />
			<ul>
			  <li>
				  <h4>Date</h4>
				  <p><a href="#">News goes here.</a></p></li>
				   <li>
				<div style="clear: both"></div>	
				</li>
			</ul>		
	</div>


<!--content ends -->

<!--footer begins -->

<?
require_once 'footer.php';
?>