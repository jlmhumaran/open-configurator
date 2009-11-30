<?
require_once 'config.php';
require_once 'utilities.php';

global $session_duration;

session_set_cookie_params( $session_duration );
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>South Padre Computers</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
    function displayDescription( drop_down )
    {
        document.getElementById( 'description_frame' ).src = "http://oc.ericneill.com/get_description.php?id=" + drop_down.options[drop_down.selectedIndex].value;
    }
</script>
</head>
<body>
<div id="main1">
<div id="content">
<div id="back">
<!-- header begins -->
<div id="header"> 
	 <div id="menu">
		<ul>
			<? //CHANGE THIS LINK FOR REAL SETUP!!!! ?>
			<li><a href="/index.php" title="">Home</a></li>
			<li><a href="configuration_system.php" title="">Shop</a></li>
			<li><a href="cart.php" title="">Item Cart</a></li>
			<li><a href="admin/" title="">Admin</a></li>
                        <li><a href="history.php" title="">History</a></li>
			</ul>
	</div>
	</div>
 <div id="main">	
	<div id="logo">
		<h1>South Padre Computers</h1>
		<h2>Your Computer, Your Way</h2>
	</div>	