<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>NY Pricrity Codes</title>
		<link href="css/site.css" ref="stylesheet" type="text/css" media="all" />
		<script src="js/mootools.js" language="text/javascript" type="text/javascript"></script>
		<script src="js/almtools.js" language="text/javascript" type="text/javascript"></script>
	</head>
	<body>
		<div class="container">
			<h1>Priority Code Editor</h1>
			<div class="content">
				<form id="myform" onSubmit="return false;" target=""alm"ajax.php">
					<input type="hidden" id="method" name="method" value="contents" />
					<p id="txtFileCSS">
						<input type="hidden" id="txtFile" name="txtFile" value="NY.priority-codes.php" />
					</p>
					<p id="txtArrayCSS">
						<label for="txtArray" />
						<textarea id="txtArray" name="txtArray" cols="65" rows="20"> </textarea><br>
						<input type="button" id="DoUpdate" name="DoUpdate" value="Update!">
						<script language="javascript">$('DoUpdate').addEvent('click',DoUpdate).addEvent('click',GetFile);</script>
					</p>
				</form>
				<div id="status">Waiting For Changes...</div>
				<div id="filecontents">...</div>
			</div>
		</div>
	</body>
</html>