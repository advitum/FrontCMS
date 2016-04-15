<!DOCTYPE html>

<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->

<head>
	<meta charset="UTF-8" />
	<title><fcms:title />FrontCMS</title>
	
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Roboto:400,700" />
	<link rel="stylesheet" type="text/css" href="{ROOT_URL}css/main.css">
	
	<meta name="keywords" content="{PAGE_OPTION.keywords}" />
	<meta name="description" content="{PAGE_OPTION.description}" />
	
	<fcms:head />
	
	<!--[if IE]>
		<script type="text/javascript" src="{ROOT_URL}js/html5shiv.js"></script>
	<![endif]-->
</head>
<fcms:body>
	<div id="root">
		<aside>
			<a href="https://github.com/advitum/FrontCMS">
				<img src="{ROOT_URL}img/logo-frontcms.png" alt="FrontCMS" />
			</a>
			<nav><fcms:navigation /></nav>
		</aside>
		<div id="content">