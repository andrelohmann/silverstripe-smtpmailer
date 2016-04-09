<?php

Injector::inst()->registerService(new SmtpMailer(), 'Mailer');

/////////////////////////////////////////////////////
// SMTP Mailer
/////////////////////////////////////////////////////
Config::inst()->update('SmtpMailer', 'settings', json_decode(SMTPMAILER, true)); // set settings
Config::inst()->update('SmtpMailer', 'credentials', 'default'); // selected credentials set


if(defined('LOG_EMAIL')){
    Config::inst()->update('SS_LogEmailWriter', 'log_email', LOG_EMAIL);
    SS_Log::add_writer(new SMTP_LogEmailWriter(LOG_EMAIL), SS_Log::NOTICE, '<=');
}
