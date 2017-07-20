<?
header("Content-type: application/x-msdownload");
	//header("Content-Disposition: attachment; filename=expot.xls");
	//header("Pragma: no-cache");
	//header("Expires: 0");
	
/*   
	$mail = new PHPMailer();
	
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true; // authentication enabled
	$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 587; // or 587
	$mail->IsHTML(true);
	$mail->Username = "il.shishkov@gmail.com";
	$mail->Password = "Fermat_Laplace";
	$mail->SetFrom("il.shishkov@gmail.com");
	$mail->Subject = "Test";
	$mail->Body = "hello";
	$mail->AddAttachment("C:/Apache24/htdocs/example.xls","example.xls");
	$mail->AddAddress("ilyasshishkov@yandex.ru");

	if(!$mail->Send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		echo "Message has been sent";
	}*/