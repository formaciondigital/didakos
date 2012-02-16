<?php
//07/02/2012 - se cambia el requiere por un include para que no falle durante la instalacion

//require_once('phpmailer/class.phpmailer.php');
//require_once('../conf/mail.conf.php');
if(file_exists('phpmailer/class.phpmailer.php') && '../conf/mail.conf.php' )
{
include('phpmailer/class.phpmailer.php');
include('../conf/mail.conf.php');
}

 //regular expression to test for valid email address
 $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";

/**
 * Sends email using the phpmailer class
 * Sender name and email can be specified, if not specified
 * name and email of the platform admin are used
 *
 * @author Bert Vanderkimpen ICT&O UGent
 *
 * @param recipient_name   	name of recipient
 * @param recipient_email  	email of recipient
 * @param message           email body
 * @param subject           email subject
 * @return                  returns true if mail was sent
 * @see                     class.phpmailer.php
 */
function api_mail($recipient_name, $recipient_email, $subject, $message, $sender_name="", $sender_email="", $extra_headers="") {

   global $regexp;
   global $platform_email;

   $mail = new PHPMailer();
   $mail->IsSMTP();   
   $mail->Host  = $platform_email['SMTP_HOST'];  // specify main and backup server
   if($platform_email['SMTP_AUTH'])
   {
      $mail->SMTPAuth = $platform_email['SMTP_AUTH'];
      $mail->Username = $platform_email['SMTP_USER'];
      $mail->Password = $platform_email['SMTP_PASS'];
   }

   //$mail->Mailer  = $platform_email['SMTP_MAILER'];
   //$mail->Port    = $platform_email['SMTP_PORT'];
   //$mail->Priority = 3; // 5=low, 1=high
   //$mail->AddCustomHeader("Errors-To: ".$platform_email['SMTP_FROM_EMAIL']."");
   //$mail->SMTPKeepAlive = true;
   $mail->IsHTML(false);
   


   // attachments
   // $mail->AddAttachment($path);
   // $mail->AddAttachment($path,$filename);


   if ($sender_email!="")
   {
      $mail->From          = $sender_email;
      //$mail->ConfirmReadingTo = $sender_email; //Disposition-Notification
   }
   else
    {
      $mail->From = $platform_email['SMTP_FROM_EMAIL'];
      //$mail->ConfirmReadingTo = $platform_email['SMTP_FROM_EMAIL']; //Disposition-Notification
    }


   if ($sender_name!="")
   {
      $mail->FromName = $sender_name;
   }
   else
   {
      $mail->FromName =  get_setting('administratorName')." ".get_setting('administratorSurname'); ///$platform_email['SMTP_FROM_NAME'];
   }

      $mail->Subject = $subject;
      $mail->Body    = $message;
      //only valid address
      /*
if(eregi( $regexp, $recipient_email ))
      {
     	 $mail->AddAddress($recipient_email, $recipient_name);
      }

	if ($extra_headers != ""){
		$mail->AddCustomHeader($extra_headers);
	}
*/
      $mail->AddAddress($recipient_email);
   //send mail
   if (!$mail->Send())
   {
        //echo "ERROR: mail not sent to ".$recipient_name." (".$recipient_email.") because of ".$mail->ErrorInfo."<br>";
        return 0;
   }

   // Clear all addresses
   $mail->ClearAddresses();
   return 1;
 }

/**
 * Sends an HTML email using the phpmailer class (and multipart/alternative to downgrade gracefully)
 * Sender name and email can be specified, if not specified
 * name and email of the platform admin are used
 *
 * @author Bert Vanderkimpen ICT&O UGent
 * @author Yannick Warnier <yannick.warnier@dokeos.com>
 *
 * @param string		   	name of recipient
 * @param string		  	email of recipient
 * @param string            email subject
 * @param string			email body
 * @param string			sender name
 * @param string			sender e-mail
 * @param array				extra headers in form $headers = array($name => $value) to allow parsing
 * @return                  returns true if mail was sent
 * @see                     class.phpmailer.php
 */
function api_mail_html($recipient_name, $recipient_email, $subject, $message, $sender_name="", $sender_email="", $extra_headers=null) {



   global $regexp;
   global $platform_email;

   $mail = new PHPMailer();
   $mail->IsSMTP();   
   $mail->Host  = $platform_email['SMTP_HOST'];  // specify main and backup server
   if($platform_email['SMTP_AUTH'])
   {
      $mail->SMTPAuth = $platform_email['SMTP_AUTH'];
      $mail->Username = $platform_email['SMTP_USER'];
      $mail->Password = $platform_email['SMTP_PASS'];
   }

   //$mail->Mailer  = $platform_email['SMTP_MAILER'];
   //$mail->Port    = $platform_email['SMTP_PORT'];
   //$mail->Priority = 3; // 5=low, 1=high
   //$mail->AddCustomHeader("Errors-To: ".$platform_email['SMTP_FROM_EMAIL']."");
   //$mail->SMTPKeepAlive = true;
   $mail->IsHTML(true);
   


   // attachments
   // $mail->AddAttachment($path);
   // $mail->AddAttachment($path,$filename);


   if ($sender_email!="")
   {
      $mail->From          = $sender_email;
      //$mail->ConfirmReadingTo = $sender_email; //Disposition-Notification
   }
   else
    {
      $mail->From = $platform_email['SMTP_FROM_EMAIL'];
      //$mail->ConfirmReadingTo = $platform_email['SMTP_FROM_EMAIL']; //Disposition-Notification
    }


   if ($sender_name!="")
   {
      $mail->FromName = $sender_name;
   }
   else
   {
      $mail->FromName =  get_setting('administratorName')." ".get_setting('administratorSurname'); ///$platform_email['SMTP_FROM_NAME'];
   }

      $mail->Subject = $subject;
      $mail->AltBody    = strip_tags(str_replace('<br />',"\n",$message));
      $mail->Body    = '<html><body>'.str_replace("\n",'<br />',$message).'</body></html>';
      //only valid address
      /*if(is_array($recipient_email))
      {
      	$i = 0;
		foreach($recipient_email as $dest)
		{
	      if(eregi( $regexp, $dest ))
	      {
	     	 $mail->AddAddress($dest, ($i>1?'':$recipient_name));
	      }
	      $i++;
		}
      }
      else
      {
	      if(eregi( $regexp, $recipient_email ))
	      {
			//  	 $mail->AddAddress($recipient_email, $recipient_name);
	     }
      }*/
      
      $mail->AddAddress($recipient_email);
	 
	 

	if (is_array($extra_headers) && count($extra_headers)>0){
		foreach($extra_headers as $key=>$value)
		{
			switch(strtolower($key)){
				case 'encoding':
				case 'content-transfer-encoding':
					$mail->Encoding = $value;
					break;
				case 'charset':
					$mail->Charset = $value;
					break;
				case 'contenttype':
				case 'content-type':
					$mail->ContentType = $value;
					break;
				default:
					$mail->AddCustomHeader($key.':'.$value);
					break;
			}
		}
	}
   //send mail
   if (!$mail->Send())
   {
        //echo "ERROR: mail not sent to ".$recipient_name." (".$recipient_email.") because of ".$mail->ErrorInfo."<br>";
        return 0;
   }

   // Clear all addresses
   $mail->ClearAddresses();
   return 1;
 }

?>
