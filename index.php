<?php
include_once("include/config/config.php");

echo "<html><body><pre>";
$inbox = imap_open(IMAP_CON_PATH, IMAP_ACC_USR, IMAP_ACC_PWD) or die(print_r(imap_errors()));

// search and get unseen emails, function will return email ids
$emails = imap_search($inbox,'UNSEEN');

$output = '';

foreach($emails as $mail) {

	$headerInfo = imap_headerinfo($inbox,$mail);
	$output .= $headerInfo->subject.'<br/>';
	$output .= $headerInfo->toaddress.'<br/>';
	$output .= $headerInfo->date.'<br/>';
	$output .= $headerInfo->fromaddress.'<br/>';
	$output .= $headerInfo->reply_toaddress.'<br/>';
	$emailStructure = imap_fetchstructure($inbox,$mail);

	//$f = fopen('asdf.mp4', 'w');
	//fwrite($f, base64_decode(imap_fetchbody($inbox, $mail, '2', FT_PEEK)));
	//fclose($f);

	echo htmlentities(imap_fetchbody($inbox, $mail, '', FT_PEEK));


	//if(!isset($emailStructure->parts)) {
		//$output .= imap_body($inbox, $mail);
	//} else {
		//echo "no parts";
	//}
	//echo $output;
	//$output = '';
}

// colse the connection
imap_expunge($inbox);
imap_close($inbox);

echo "</pre></body></html>";
?>
