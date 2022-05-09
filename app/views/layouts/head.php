<?php

use app\core\Application;
?>

<!doctype html>
<html>
<head>
	<link href='https://fonts.googleapis.com/css?family=Lobster' rel='stylesheet'>
	<?php Css::loadAll(array('style', 'datatable')); ?>

    <script crossorigin="anonymous" src="https://kit.fontawesome.com/625a0aa3b5.js"></script>
	<script
		src="https://code.jquery.com/jquery-3.5.1.js"
		integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
		crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://kit.fontawesome.com/2495fab115.js" crossorigin="anonymous"></script>

	<title><?= SITENAME; ?></title>

	<!-- Load required Bootstrap and BootstrapVue CSS -->
	<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

	<!-- Load polyfills to support older browsers -->
	<script src="//polyfill.io/v3/polyfill.min.js?features=es2015%2CIntersectionObserver" crossorigin="anonymous"></script>
	
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
	
	<script src="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>

	<!-- Datatable -->
	<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>


</head>

<body>
	{{content}}  
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	<?php Javascript::loadAll(array('page', 'datatable', 'datatables.bootstrap')); ?>
</body>
</html>