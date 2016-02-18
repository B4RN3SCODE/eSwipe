<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
ini_set("log_errors", 1);
ini_set("ignore_repeated_errors", 0);
ini_set("track_errors", 1);
ini_set("html_errors", 1);


include_once("include/swipe/core/eSwipeImap.php");
echo "<html><body><pre>";

$o = new eSwipeImap();
if($o->Open()) {

	$output = '';
	$emails = tst($o->_mailBox);

	foreach($emails as $mail) {

		$headerInfo = imap_headerinfo($o->_mailBox,$mail);
		$output .= $headerInfo->subject.'<br/>';
		$output .= $headerInfo->toaddress.'<br/>';
		$output .= $headerInfo->date.'<br/>';
		$output .= $headerInfo->fromaddress.'<br/>';
		$output .= $headerInfo->reply_toaddress.'<br/>';
		$emailStructure = imap_fetchstructure($o->_mailBox,$mail);

		//$f = fopen('asdf.mp4', 'w');
		//fwrite($f, base64_decode(imap_fetchbody($inbox, $mail, '2', FT_PEEK)));
		//fclose($f);

		echo htmlentities(imap_fetchbody($o->_mailBox, $mail, '', FT_PEEK));


		//if(!isset($emailStructure->parts)) {
			//$output .= imap_body($inbox, $mail);
		//} else {
			//echo "no parts";
		//}
		//echo $output;
		//$output = '';
	}

}



function tst($in) {
	if(!is_resource($in)) { echo 'no'; exit; }
	// search and get unseen emails, function will return email ids
	return imap_search($in,'ALL UNSEEN');
}

// colse the connection
imap_expunge($inbox);
imap_close($inbox);

echo "</pre></body></html>";
?>
