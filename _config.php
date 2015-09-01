<?php

Injector::inst()->registerService(new SmtpMailer(), 'Mailer');

/////////////////////////////////////////////////////
// SMTP Mailer
/////////////////////////////////////////////////////
Config::inst()->update('SmtpMailer', 'charset_encoding', SMTPMAILER_CHARSET_ENCODING); # E-mails characters encoding, e.g. : 'utf-8' or 'iso-8859-1'
Config::inst()->update('SmtpMailer', 'smtp_server_address', SMTPMAILER_SERVER_ADDRESS); # SMTP server address
Config::inst()->update('SmtpMailer', 'smtp_server_port', SMTPMAILER_SERVER_PORT); # SMTP server port. Set to 25 if no encryption or tls. Set to 465 if ssl
Config::inst()->update('SmtpMailer', 'use_secure_connection', SMTPMAILER_SECURE_CONNECTION); # SMTP encryption method : Set to '' or 'tls' or 'ssl'
Config::inst()->update('SmtpMailer', 'do_authenticate', SMTPMAILER_DO_AUTHENTICATE); # Turn on SMTP server authentication. Set to false for an anonymous connection
Config::inst()->update('SmtpMailer', 'username', SMTPMAILER_USERNAME); # SMTP server username, if SMTPAUTH == true
Config::inst()->update('SmtpMailer', 'password', SMTPMAILER_PASSWORD); # SMTP server password, if SMTPAUTH == true
Config::inst()->update('SmtpMailer', 'debug_messaging_level', SMTPMAILER_DEBUG_LEVEL); # Print debugging informations. 0 = no debuging, 1 = print errors, 2 = print errors and messages, 4 = print full activity
Config::inst()->update('SmtpMailer', 'language_of_message', SMTPMAILER_LANGUAGE); # Language for messages. Look into code/vendor/language for available languages

if(defined('LOG_EMAIL')) SS_Log::add_writer(new SMTP_LogEmailWriter(LOG_EMAIL), SS_Log::NOTICE, '<=');