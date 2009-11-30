<?
require_once '../config.php';

session_set_cookie_params( $session_duration );
session_start();

/*$myFile = "accesslog.txt";
$fh = fopen($myFile, 'a') or die("can't open file");
$stringData = date("D M d Y h:i:sa T")." ===> ".gethostbyaddr($_SERVER['REMOTE_ADDR'])." (".$_SERVER['REMOTE_ADDR'].")\n";
fwrite($fh, $stringData);
fclose($fh);*/


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>South Padre Computers</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="../styles.css" rel="stylesheet" type="text/css" media="screen" />
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
			<li><a href="../index.php" title="">Home</a></li>
			<li><a href="index.php" title="">Components</a></li>
			<li><a href="edit_account.php" title="">Accounts</a></li>
			<li><a href="edit_config.php" title="">Config</a></li>
                        <li><a href="edit_order.php" title="">Orders</a></li>
			</ul>
	</div>
	</div>
 <div id="main">	
	<div id="logo">
		<h1>South Padre Computers</h1>
		<h2>Your Computer, Your Way</h2>
	</div>	
