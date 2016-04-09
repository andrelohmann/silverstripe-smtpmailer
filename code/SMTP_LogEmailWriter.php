<?php
/**
 * Thanks to Dean Rather <dean@deanrather.com>
 *                       https://github.com/deanrather
 */
class SMTP_LogEmailWriter extends SS_LogEmailWriter {

	/**
	 * Send an email to the email address set in
	 * this writer.
	 */
	public function _write($event) {
		// If no formatter set up, use the default
		if(!$this->_formatter) {
			$formatter = new SS_LogErrorEmailFormatter();
			$this->setFormatter($formatter);
		}

		$formattedData = $this->_formatter->format($event);
		$subject = $formattedData['subject'];
		$data = $formattedData['data'];
		
		if(!isset($GLOBALS['LogMailSend'])){
			$email = new Email();
			$email->setTo($this->emailAddress);
			$email->setSubject($subject);
			$email->setBody($data);	
			$email->setFrom(Config::inst()->get('SS_LogEmailWriter', 'log_email'));
			Config::inst()->update('SmtpMailer', 'credentials', 'log'); // choose "log" credentials set
			$email->send();
			$GLOBALS['LogMailSend'] = true; // prevent resending logmail, if smtp is the source for the error
		}
	}
}
?>
