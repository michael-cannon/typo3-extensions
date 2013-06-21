<?php
	if ($_POST['method'] == 'update') {
		$fp = fopen($_POST['fileDir'] . $_POST['txtFile'],'r');
		$contents = fread($fp,filesize($_POST['txtFile']));
		$bkp = fopen('bkp/' . $_POST['txtFile'], 'w+');
		fwrite($bkp,$contents);
		fclose($bkp);
		fclose($fp);
		$contents = str_replace('?>', '', $contents);
		$contents = $contents . "\n" . $_POST['txtArray'] . "\n\n?>";
		$success = fwrite(fopen($_POST['fileDir'] . $_POST['txtFile'],'w+'), stripslashes($contents));
		if ($success) { echo "File Updated!"; }
		else echo "File Error. DEBUG: File: {$_POST['fileDir']}{$_POST['txtFile']} Permissions: " . fileperms($_POST['fileDir'] . $_POST['txtFile']);
	} elseif ($_POST['method'] == 'contents') {
		$contents = fread(fopen($_POST['fileDir'] . $_POST['txtFile'],'r'),filesize($_POST['fileDir'] . $_POST['txtFile']));
		$contents = str_replace("<", "&lt;", $contents);
		$contents = str_replace(">", "&gt;", $contents);
		echo "<pre>{$contents}</pre>";
	} elseif ($_POST['method'] == 'restore') {
		$bkp = fopen('bkp/' . $_POST['txtFile'], 'r');
		$fp = fopen($_POST['fileDir'] . $_POST['txtFile'], 'w+');
		if (fwrite($fp, fread($bkp, filesize('bkp/' . $_POST['txtFile'])))) echo "Backup Restored Successfully.";
		else echo "Backup Failed To Be Restored.";
	} else {
		echo file_get_contents($_POST['txtFile']);
	}
?>