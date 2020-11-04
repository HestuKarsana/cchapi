<!DOCTYPE html>
<html>
<head>
	<title>Hello world</title>
</head>
<body>
	<p>Testing upload</p>
	<hr/>
	<form action="<?php echo base_url()?>test/postUpload" method="post" enctype="multipart/form-data">
			<input type="file" name="userfile">
		<button type="submit">Upload</button>
	</form>
</body>
</html>