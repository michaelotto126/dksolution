<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Redirecting...</title>

<?php echo $product->head_code; ?>
</head>
<body onload="load()">

<p>Redirecting....</p>

<?php echo $product->body_code; ?>
<script>
function load()
{
	window.top.location = '<?php echo $url; ?>';
}
</script>
</body>
</html>