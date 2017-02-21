<?php

		$importFile = fopen($filename, "r") or die ("Unable to open " . $filename);
		$headers = fgets($importFile);
		$headers = explode("\t", $headers);
		foreach($headers as $h) {
			echo $h . "<br>";
		}
	
	
?>