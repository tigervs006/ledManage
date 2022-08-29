<?php

namespace core\utils;

use think\facade\Config;
use PHPMailer\PHPMailer\SMTP;
use core\exceptions\ApiException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use core\utils\StringHandler as Str;

class MailHandler
{
    /**
     * 端口
     * @var int
     */
    private int $port;

    /**
     * 地址
     * @var string
     */
    private string $host;

    /**
     * 实例
     * @var PHPMailer
     */
    private PHPMailer $mail;

    /**
     * 用户
     * @var string
     */
    private string $userName;

    /**
     * 密码
     * @var string
     */
    private string $password;

    /**
     * 收件人
     * @var array
     */
    private array $receiver;

    /**
     * 发件人
     * @var string
     */
    private string $sendFrom;

    /**
     * 邮件主题
     * @var string
     */
    private string $mailSubject;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->port = Config::get('index.mail_send_port');
        $this->host = Config::get('index.mail_smtp_host');
        $this->sendFrom = Config::get('index.mail_send_from');
        $this->userName = Config::get('index.mail_user_name');
        $this->password = Config::get('index.mail_user_password');
        $this->mailSubject = Config::get('index.mail_send_subject');
        $this->receiver = explode(',', Config::get('index.mail_send_receiver'));
    }

    /**
     * @return void
     * @param string $mailBody 主体内容
     */
    public function sendMail(string $mailBody): void
    {
        try {
            // Server options
            $this->mail->SMTPDebug  = SMTP::DEBUG_OFF;                          // Enable verbose debug output
            $this->mail->isSMTP();                                              // Send using SMTP
            $this->mail->CharSet    = 'UTF-8';                                  // Mail CharSet
            $this->mail->Host       = $this->host;                              // Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                     // Enable SMTP authentication
            $this->mail->Username   = $this->userName;                          // SMTP username
            $this->mail->Password   = $this->password;                          // SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $this->mail->Port       = $this->port;                              // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            // Recipients
            $this->mail->setFrom($this->sendFrom, Str::strNeedleExtract($this->sendFrom, '@')); // set a From
            foreach ($this->receiver as $val) {
                $this->mail->addAddress($val, Str::strNeedleExtract($val, '@'));                // Add some recipient
            }

            // Mial Content
            $this->mail->isHTML();                                              // Set email format to HTML
            $this->mail->Subject = $this->mailSubject;                          // Mail subject
            $this->mail->Body    = $mailBody;                                   // Mail Body

            $this->mail->setLanguage('zh_cn');                         // Localization

            // sendMail
            $this->mail->send();
        } catch (Exception) {
            Throw new ApiException("邮件发送失败，错误信息：{$this->mail->ErrorInfo}");
        }
    }
}
