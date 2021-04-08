<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Common\Models\IPhoneCode;
use Common\Components\Log;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail extends Component {

    public $mail;

    static public function init(){        

        $settings = settings();
        
        $i = new self;
        $i->mail = new PHPMailer(true); 
        $i->mail->CharSet='UTF-8';
        // $i->mail->Encoding = "base64"; 
        //Server settings
        $i->mail->SMTPDebug = 0;                                 // Enable verbose debug output
        $i->mail->isSMTP();                                      // Set mailer to use SMTP
        $i->mail->Host = $settings['mail_smtp_server'];          // Specify main and backup SMTP servers
        $i->mail->SMTPAuth = true;                               // Enable SMTP authentication
        $i->mail->Username = $settings['mail_account'];          // SMTP username
        $i->mail->Password = $settings['mail_password'];         // SMTP password
        $i->mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
        $i->mail->Port = $settings['mail_smtp_port'];            //587 // TCP port to connect to

        return $i;
    }

    public function sendCode($email,$code){
        $settings = settings();
        try {
            //Recipients
            $this->mail->setFrom($settings['mail_account'], $settings['app_name']);
            $this->mail->addAddress($email);               // Name is optional

            $conf = conf();
          //  $body = '您正在'.$settings['app_name'].'使用此邮箱地址注册账号，邮箱验证码为：[code]。如果您并未进行该操作，请忽略此邮件。';
       $body =    'Hola.
Bienvenido la pagina on-line de Chisnbal.
Estás registrando una cuenta con esta dirección de correo electrónico. El código de verificación de correo electrónico es [code].
Si no lo es, ignore este mensaje.

Un saludo
Gracias.';
        //   $body = '您正在'.$settings['app_name'].'使用此邮箱地址注册账号，邮箱验证码为：[code]。如果您并未进行该操作，请忽略此邮件。';
            if(!empty($conf['email_code_body'])){
                $body = $conf['email_code_body'];
            }

            $subject = '您的注册验证码';
            if(!empty($conf['email_code_subject'])){
                $subject = $conf['email_code_subject'];
            }

            $body = str_replace('[code]',$code,$body);

            //Content
            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            // $this->mail->AltBody = '您正在Olmart APP使用此邮箱地址注册账号，邮箱验证码为：'.$code.'。如果您并未进行该操作，请忽略此邮件。';
        
            $this->mail->send();
            return true;
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
        }
        return false;
    }

    public function sendVerifCode($email,$code){
        $settings = settings();
        try {
            //Recipients
            $this->mail->setFrom($settings['mail_account'], $settings['app_name']);
            $this->mail->addAddress($email);               // Name is optional

            $conf = conf();
            $body = '您正在'.$settings['app_name'].'使用此邮箱地址修改账号密码，邮箱验证码为：[code]。如果您并未进行该操作，请忽略此邮件。';
            if(!empty($conf['email_code_body'])){
                $body = $conf['email_code_body'];
            }

            $subject = '您的验证码';
            if(!empty($conf['email_code_subject'])){
                $subject = $conf['email_code_subject'];
            }

            $body = str_replace('[code]',$code,$body);

            //Content
            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            // $this->mail->AltBody = '您正在Olmart APP使用此邮箱地址注册账号，邮箱验证码为：'.$code.'。如果您并未进行该操作，请忽略此邮件。';

            $this->mail->send();
            return true;
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
        }
        return false;
    }

    public function sendContent($email,$subject,$content){
        $settings = settings();
        try {
            //Recipients
            // echo $settings['mail_account'];exit;
            $this->mail->setFrom($settings['mail_account'], $settings['app_name']);
            $this->mail->addAddress($email);               // Name is optional

            $body = $content;

            //Content
            // $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
        
            $res = $this->mail->send();
            // var_dump($res);
            // echo 'Message has been sent';
            return true;
            
        } catch (Exception $e) {
            // echo $settings['mail_account'].PHP_EOL;
            // echo $subject.PHP_EOL;
            // echo $content.PHP_EOL;
            // echo 'Message could not be sent. Mailer Error: '. $this->mail->ErrorInfo;
        }
        return false;
    }

	
}
