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
    
    public static function set_charset_encoding($value){
        self::$charset_encoding = $value;
    }
    
    public static function get_charset_encoding(){
        return self::$charset_encoding;
    }
    
    public static function set_smtp_server_address($value){
        self::$smtp_server_address = $value;
    }
    
    public static function get_smtp_server_address(){
        return self::$smtp_server_address;
    }
    
    public static function set_smtp_server_port($value){
        self::$smtp_server_port = $value;
    }
    
    public static function get_smtp_server_port(){
        return self::$smtp_server_port;
    }
    
    /**
     * Set to '' or 'tls' or 'ssl'
     * @param type $value
     */
    public static function set_use_secure_connection($value){
        self::$use_secure_connection = $value;
    }
    
    public static function get_use_secure_connection(){
        return self::$use_secure_connection;
    }
    
    public static function set_do_authenticate($value){
        self::$do_authenticate = $value;
    }
    
    public static function get_do_authenticate(){
        return self::$do_authenticate;
    }
    
    public static function set_username($value){
        self::$username = $value;
    }
    
    public static function get_username(){
        return self::$username;
    }
    
    public static function set_password($value){
        self::$password = $value;
    }
    
    public static function get_password(){
        return self::$password;
    }
    
    public static function set_debug_messaging_level($value){
        self::$debug_messaging_level = $value;
    }
    
    public static function get_debug_messaging_level(){
        return self::$debug_messaging_level;
    }
    
    public static function set_language_of_message($value){
        self::$language_of_message = $value;
    }
    
    public static function get_language_of_message(){
        return self::$language_of_message;
    }
    
    protected $mailer = null;
    
    /*function __construct( $mailer = null ){
        parent::__construct();
	$this->mailer = $mailer;
    }*/

    protected function instanciate(){
        if( null == $this->mailer ) {
            $this->mailer = new PHPMailer( true );
            $this->mailer->IsSMTP();
            $this->mailer->CharSet = self::get_charset_encoding();
            $this->mailer->Host = self::get_smtp_server_address();
            $this->mailer->Port = self::get_smtp_server_port();
            $this->mailer->SMTPSecure = self::get_use_secure_connection();
            $this->mailer->SMTPAuth = self::get_do_authenticate();
            if( $this->mailer->SMTPAuth ) {
                $this->mailer->Username = self::get_username();
                $this->mailer->Password = self::get_password();
            }
            $this->mailer->SMTPDebug = self::get_debug_messaging_level();
            $this->mailer->SetLanguage(self::get_language_of_message());
        }
    }	
	

    /* Overwriting Mailer's function */
    function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false){
        $this->instanciate();
        $this->mailer->IsHTML( false );
        $this->mailer->Body = $plainContent;
        $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, false );
    }


    /* Overwriting Mailer's function */
    function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false){
        $this->instanciate();
        $this->mailer->IsHTML( true );
        if( $inlineImages ) {
            $this->mailer->MsgHTML( $htmlContent, Director::baseFolder() );
        }
        else {
            $this->mailer->Body = $htmlContent;
            if( empty( $plainContent ) ) $plainContent = trim( Convert::html2raw( $htmlContent ) );
            $this->mailer->AltBody = $plainContent;
        }
        $this->sendMailViaSmtp( $to, $from, $subject, $attachedFiles, $customheaders, $inlineImages );        
    }
    
    
    protected function sendMailViaSmtp( $to, $from, $subject, $attachedFiles = false, $customheaders = false, $inlineImages = false ){
        if( $this->mailer->SMTPDebug > 0 ) echo "<em><strong>*** Debug mode is on</strong>, printing debug messages and not redirecting to the website:</em><br />";
        $msgForLog = "\n*** The sender was : $from\n*** The message was :\n{$this->mailer->AltBody}\n";

        try {
            $this->buildBasicMail( $to, $from, $subject );
            $this->addCustomHeaders( $customheaders );
            $this->attachFiles( $attachedFiles );
            $this->mailer->Send();

            if( $this->mailer->SMTPDebug > 0 ) {
                echo "<em><strong>*** E-mail to $to has been sent.</strong></em><br />";
                echo "<em><strong>*** The debug mode blocked the process</strong> to avoid the url redirection. So the CC e-mail is not sent.</em>";
                die();
            }

        } catch( phpmailerException $e ) {
            $this->handleError( $e->errorMessage(), $msgForLog );
        } catch( Exception $e ) {
            $this->handleError( $e->getMessage(), $msgForLog );
        }
    }
    
    
    function handleError( $e, $msgForLog ){
        $msg = $e . $msgForLog;
        echo( $msg );
        Debug::log( $msg );
        die();
    }
        
    protected function buildBasicMail( $to, $from, $subject ){
        if( preg_match('/(\'|")(.*?)\1[ ]+<[ ]*(.*?)[ ]*>/', $from, $from_splitted ) ) {
            // If $from countain a name, e.g. "My Name" <me@acme.com>
            $this->mailer->SetFrom( $from_splitted[3], $from_splitted[2] );
        }
        else {
            $this->mailer->SetFrom( $from );
        }

        $to = Injector::inst()->create('Mailer')->validEmailAddr( $to );
        $this->mailer->ClearAddresses();
        $this->mailer->AddAddress( $to, ucfirst( substr( $to, 0, strpos( $to, '@' ) ) ) ); // For the recipient's name, the string before the @ from the e-mail address is used
        $this->mailer->Subject = $subject;
    }
    
    
    protected function addCustomHeaders( $headers ){
    	if( null == $headers or !is_array( $headers ) ) $headers = array();
	    if( !isset( $headers["X-Mailer"] ) ) $headers["X-Mailer"] = X_MAILER;
	    if( !isset( $headers["X-Priority"] ) ) $headers["X-Priority"] = 3;
	
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