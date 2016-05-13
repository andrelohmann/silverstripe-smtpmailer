<?php

require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'class.phpmailer.php' );

class SmtpMailer extends Mailer {

    private static $charset_encoding = "utf-8";
    private static $smtp_server_address = "localhost";
    private static $smtp_server_port = 25;
    private static $use_secure_connection = '';
    private static $do_authenticate = false;
    private static $username = "username";
    private static $password = "password";
    private static $debug_messaging_level = 0;
    private static $language_of_message = 'en';

    protected $mailer = null;

    /*function __construct( $mailer = null ){
        parent::__construct();
	$this->mailer = $mailer;
    }*/

    protected function instanciate(){
        // create an emailer from Log Mail credentials
        if(self::config()->credentials != 'default' && isset(self::config()->settings['credentials'][self::config()->credentials]) && is_array(self::config()->settings['credentials'][self::config()->credentials])){
            $creds = self::config()->settings['credentials'][self::config()->credentials];
        }else{
            $creds = self::config()->settings['credentials']['default'];
        }

        $this->mailer = new PHPMailer( true );
        $this->mailer->IsSMTP();
        $this->mailer->CharSet = self::config()->settings['charset_encoding'];
        $this->mailer->SMTPDebug = (isset(self::config()->settings['debug_level']))?self::config()->settings['debug_level']:0;
        $this->mailer->SMTPDebugStop = (isset(self::config()->settings['debug_stop']))?self::config()->settings['debug_stop']:true;
        $this->mailer->Host = $creds['server_address'];
        $this->mailer->Port = $creds['server_port'];
        $this->mailer->SMTPSecure = $creds['secure_connection'];
        $this->mailer->SMTPAuth = $creds['do_authenticate'];
        if( $this->mailer->SMTPAuth ) {
            $this->mailer->Username = $creds['username'];
            $this->mailer->Password = $creds['password'];
        }
        // set fixed From Address
        if(isset($creds['from'])) $this->buildFrom($creds['from']);
    }


    /* Overwriting Mailer's function */
    function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false){
        $this->instanciate();
        // set $from to selected Mailsettings From Address
        $from = $this->mailer->From;
        $this->mailer->IsHTML( false );
        $this->mailer->Body = $plainContent;
        return $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, false );
    }


    /* Overwriting Mailer's function */
    function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false){
        $this->instanciate();
        // set $from to selected Mailsettings From Address
        $from = $this->mailer->From;
        $this->mailer->IsHTML( true );
        if( $inlineImages ) {
            $this->mailer->MsgHTML( $htmlContent, Director::baseFolder() );
        }
        else {
            $this->mailer->Body = $htmlContent;
            if( empty( $plainContent ) ) $plainContent = trim( Convert::html2raw( $htmlContent ) );
            $this->mailer->AltBody = $plainContent;
        }
        return $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, $inlineImages );
    }


    protected function sendMailViaSmtp( $to, $from, $subject, $attachedFiles = false, $customheaders = false, $inlineImages = false ){
        if( $this->mailer->SMTPDebug > 0 && $this->mailer->SMTPDebugStop) {
            echo "<em><strong>*** Debug mode is on</strong>, printing debug messages and not redirecting to the website:</em><br />";
        }
        $msgForLog = "\n*** The sender was : $from\n*** The message was :\n{$this->mailer->AltBody}\n";


        $Success = false;
        try {
            $this->buildBasicMail( $to, $from, $subject );
            $this->addCustomHeaders( $customheaders );
            $this->attachFiles( $attachedFiles );
            $Success = $this->mailer->Send();

            if( $this->mailer->SMTPDebug > 0 && $this->mailer->SMTPDebugStop) {
                echo "<em><strong>*** E-mail to $to has been sent.</strong></em><br />";
                echo "<em><strong>*** The debug mode blocked the process</strong> to avoid the url redirection. So the CC e-mail is not sent.</em>";
                die();
            }

        } catch( phpmailerException $e ) {
            $this->handleError( $e->errorMessage(), $msgForLog );
        } catch( Exception $e ) {
            $this->handleError( $e->getMessage(), $msgForLog );
        }
        Config::inst()->update('SmtpMailer', 'credentials', 'default');

        return $Success;
    }


    function handleError( $e, $msgForLog ){
        if( $this->mailer->SMTPDebug > 0 ){
            $msg = $e . $msgForLog;
            echo( $msg );
            Debug::log( $msg );
            if($this->mailer->SMTPDebugStop) die();
        }else{
            user_error("SmtpMailer Error", E_USER_WARNING);
        }
    }

    protected function buildBasicMail( $to, $from, $subject ){
        if(!$this->mailer->From) $this->buildFrom ($from);

        $to = Injector::inst()->create('Mailer')->validEmailAddress( $to );
        $this->mailer->ClearAddresses();
        $this->mailer->AddAddress( $to, ucfirst( substr( $to, 0, strpos( $to, '@' ) ) ) ); // For the recipient's name, the string before the @ from the e-mail address is used
        $this->mailer->Subject = $subject;
    }

    protected function buildFrom($from){
        if( preg_match('/(\'|")(.*?)\1[ ]+<[ ]*(.*?)[ ]*>/', $from, $from_splitted ) ) {
            // If $from countain a name, e.g. "My Name" <me@acme.com>
            $this->mailer->SetFrom( $from_splitted[3], $from_splitted[2] );
        }
        else {
            $this->mailer->SetFrom( $from );
        }
    }


    protected function addCustomHeaders( $headers ){
        if( null == $headers or !is_array( $headers ) ) $headers = array();
        if( !isset( $headers["X-Mailer"] ) ) $headers["X-Mailer"] = X_MAILER;
        if( !isset( $headers["X-Priority"] ) ) $headers["X-Priority"] = 3;
        if(isset($headers["Cc"])){
            $this->mailer->AddCC($headers["Cc"]);
            unset($headers["Cc"]);
        }
        if(isset($headers["Bcc"])){
            $this->mailer->AddBCC($headers["Bcc"]);
            unset($headers["Bcc"]);
        }

        $this->mailer->ClearCustomHeaders();
        foreach( $headers as $header_name => $header_value ) {
            $this->mailer->AddCustomHeader( $header_name.':'.$header_value );
        }
    }


    protected function attachFiles( $attachedFiles ){
        if( !empty( $attachedFiles ) and is_array( $attachedFiles ) ) {
            foreach( $attachedFiles as $attachedFile ) {
                $this->mailer->AddAttachment( Director::baseFolder().DIRECTORY_SEPARATOR.$attachedFile['filename'] );
            }
        }
    }
}
