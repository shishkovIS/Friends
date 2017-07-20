<?php 

function sendMailWithAttach($currentListName,$params)
{
	$mail = new PHPMailer();
	
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true; // authentication enabled
	$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
	$mail->Host = $params["mail_host"];
	$mail->Port = $params["mail_port"]; // or 587
	$mail->IsHTML(true);
	$mail->Username = $params["mail_username"];
	$mail->Password = $params["mail_password"];
	$mail->SetFrom($params["mail_set_from"]);
	$mail->Subject = $params["mail_subject"];
	$mail->Body = $params["mail_body"];
	$mail->AddAttachment($params["mail_fullpath"].$currentListName,$currentListName);
	$mail->AddAddress($params["mail_receiver"]);

	if(!$mail->Send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		echo "Message has been sent";
	}
}
