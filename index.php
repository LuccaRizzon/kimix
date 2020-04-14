<html>
	<head>
		<link rel="stylesheet" href="assets/css/styler.css">
		<link rel="stylesheet" href="assets/css/bootstrap.css">
	</head>
	<body>
		<div id="bod-hid" style="">
			<!--<div class="col-md-12">-->
			<p>Carregando</p>
			<div id="sw-load"><!--<img src="load.gif">--></div>
		</div>
	
		<div class="container">
			<div class="row">
				<div class="col-md-12">
			</div>
		</div>
		
	</body>
	<script src="assets/js/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			//alert("asd in");
			$(document).ajaxStart(function(){
				$("#bod-hid").fadeIn(200);
			});
			
			$(document).ajaxStart(function(){
				$("#bod-hid").fadeOut(200);
			});
			
			
			
		});
	</script>
</html>