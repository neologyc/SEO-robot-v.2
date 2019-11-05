<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
  public $emailHost = '';
	public $emailPort = '';
	public $emailSMTPSecure = '';
	public $emailSMTPAuth = FALSE;
	public $emailSMTPUsername = '';
	public $emailSMTPPassword = '';
	public $emailAddressFrom = '';
	public $smtpDebug = TRUE;

  public $emailAddressTo = '';
  public $log = '';
  public $projectLog = '';
  public $parseErrorLog = '';
  public $errorLog = '';
  public $errorCount = 0;
  public $errorDebugData = array();

  public $projectId = '';



  function __construct( Project $projectData, $gs, $ps )
  {
    $this->emailHost = $gs->emailHost;
    $this->emailPort = $gs->emailPort;
    $this->emailSMTPSecure = $gs->emailSMTPSecure ? '' : PHPMailer::ENCRYPTION_STARTTLS;
    $this->emailSMTPAuth = $gs->emailSMTPAuth;
    $this->emailSMTPUsername = $gs->emailUsername;
    $this->emailSMTPPassword = $gs->emailPassword;
    $this->emailAddressFrom = $gs->emailFrom;
    $this->smtpDebug = $gs->smtpDebug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

    $this->emailAddressTo = $projectData->notificationEmail;
    $this->log = $projectData->log;
    $this->projectLog = $projectData->projectLog;
    $this->parseErrorLog = $projectData->parseErrorLog;
    $this->errorLog = $projectData->errorLog;
    $this->errorCount = $projectData->errorCount;
    $this->errorDebugData = $projectData->errorDebugData;
    $this->projectId = $projectData->id;
  }

  function send() {
    if($this->errorCount > 0 || !empty( $this->parseErrorLog) || !empty($this->errorLog) ) {
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = $this->smtpDebug;
        $mail->Host = $this->emailHost;
        $mail->Port = $this->emailPort;
        $mail->SMTPSecure = $this->emailSMTPSecure;
        $mail->SMTPAuth = $this->emailSMTPAuth;
        $mail->Username = $this->emailSMTPUsername;
        $mail->Password = $this->emailSMTPPassword;
        $mail->setFrom( $this->emailAddressFrom );
        $mail->addReplyTo($this->emailAddressFrom);
        // 2+ emails in emailAddressTo
        if( preg_match('/;/',$this->emailAddressTo) ) {
            $emails = explode(';', $this->emailAddressTo);
            foreach ($emails as $key => $email) {
              $mail->addAddress($email);
            }
        } else {
            $mail->addAddress($this->emailAddressTo);
        }

        $mail->Subject = '🛑☠️ '.$this->errorCount.' errors on test: '.$this->projectId. ' '. date('Y-m-d H:i:s') . ' | SEOrobot';
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML( $this->projectLog.
                        $this->parseErrorLog.
                        $this->errorLog.
                        $this->log.
                        $this->errorCount
                      );
        $mail->AltBody = 'This is a plain-text message body. Please use HTML to view test results.';
        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');
        //send the message, check for errors

        if( count($this->errorDebugData) >= 1 ) {
            foreach ($this->errorDebugData as $key=>$error) {
                 $mail->AddStringAttachment($error['response'], $key.' line-html-response.html', 'quoted-printable', 'text/html');
                 $mail->AddStringAttachment($error['headers'], $key.' line-html-headers.txt', 'quoted-printable', 'text/plain');
                 //print_r($key);
                 //print_r($value);
            }
        }

        if (!$mail->send()) {
            echo 'Mailer Error: '. $mail->ErrorInfo;
        } else {
            echo 'Message sent!';
        }
    }
  }

}
