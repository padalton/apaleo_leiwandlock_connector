<?php

require_once('vendor/autoload.php');

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require('vendor/phpmailer/phpmailer/src/Exception.php');
require('vendor/phpmailer/phpmailer/src/PHPMailer.php');
require('vendor/phpmailer/phpmailer/src/SMTP.php');

require_once('leiwandlock/LeiwandLockConnector.php');

/**
 * Class LeiwandLockManager
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
class LeiwandLockManager
{

    var $connector;
    var $apaleo_data;
    var $setup;

    function __construct($data)
    {
        if (empty($data)) {
            $this->log('apaleo_data empty!');
            exit();
        }
        $this->apaleo_data = $data;
        global $db;
        $res = $db->query("SELECT * FROM leiwand_setup WHERE account_code = '".$_SESSION['account_code']."' AND property_id = '" . $this->apaleo_data->property->id . "' AND active = '1'");
        $this->setup = $res->fetch();
        if(empty($this->setup['ll_token']) || empty($this->setup['host'])) {
            $this->log("LeiwandLock Server config missing in connector database");
            die("LeiwandLock Server config missing in connector database");
        }
        $this->connector = new LeiwandLockConnector($this->setup['host'], $this->setup['ll_token']);
        $check_api_response = $this->connector->callMethod("", "HEAD");
        if ($check_api_response->code !== "200") {
            $this->log("LeiwandLock API responded with " . $check_api_response->code);
        }
        $this->connector->close();
        $this->connector = new LeiwandLockConnector($this->setup['host'], $this->setup['ll_token']);
    }

    function log($message, $loglevel = "ERROR")
    {
        $GLOBALS['log']->fatal('[APALEO API] (' . get_class($this) . ') ' . $loglevel . ': ' . $message);
        //later maybe add email functionality ? Spam the Developer if anything goes wrong ?
    }

    function create()
    {
        global $db;
        $post_array['description'] = $this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email;
        $post_array['code'] = md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email);
        $post_array['valid_from'] = $this->apaleo_data->arrival;
        $to = new DateTime($this->apaleo_data->departure);
        $to->add(new DateInterval("PT30M"));
        $post_array['valid_until'] = $to->format('c');
        $post_array['active'] = true;
        $post_array['tag'] = $this->apaleo_data->unit->id;
        $this->connector->callMethod("codes/", "POST", array(), $post_array);
        $db->exec("INSERT INTO reservations (id,account_id,property_id,reservation_id,qrcode_generated) VALUES (UUID(),'".$_SESSION['account_code']."','" . $this->apaleo_data->property->id . "','" . $this->apaleo_data->id . "',1)");

        $qrdata = md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email);
        $code = $this->generateQRCode($qrdata);
        $this->senEmail($qrdata, $code);
    }

    function generateQRCode($code)
    {
        $data = $code;

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel' => QRCode::ECC_H,
        ]);

// invoke a fresh QRCode instance
        $qrcode = new QRCode($options);

// ...with additional cache file
        return $qrcode->render($data, 'cache/' . md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email) . '.jpg');
    }

    function senEmail($qrdata, $code)
    {
        global $db;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $stmt = $db->prepare("SELECT * FROM email_setup WHERE account_id = :code ");
        $stmt->bindParam(':code', $_SESSION['account_code']);
        $stmt->execute();
        $setup = $stmt->fetch();
        $mail->SMTPDebug = 0; //
        $mail->SMTPSecure = $setup['SMTPSecure'];
        $mail->SMTPAuth = ($setup['SMTPAuth'] == '1');
        $mail->Host = $setup['Host'];
        $mail->Port = $setup['Port'];
        $mail->Username = $setup['Username'];
        $mail->Password = $setup['Password'];

        $mail->setFrom($setup['FromAddress']);

        $mail->Subject = "Your personal digital Roomkey";
        $mail->isHTML();
        $email_tpl = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
</head>
<body>
<p>Welcome to the DEVrupt Hotel!</p>
<p>Thank you for your booking. We are happy to see that you already checked-in.</p>
<p>Here is your personal QR-Code which you can use to get into your room until your check-out.</p>
<p>{{QRCODE}}</p>
<p>powered by <a href="https://www.leiwand-lock.at">Leiwand Lock</a><br></p>
<p>Next to your room door is a QR-Code scanner placed. In order to unlock your door, simply hold your QR-Code on your phone or printed out in front of the QR-Code Scanner with a distance from 5-15cm.</p>
<p>This QR-Code functions as your room key, thereby, do not give it to others than persons who are with you in the room.</p>
<p><br />We wish you a pleasent stay!</p>
<p>Your DEVrupt Hotel Team.</p>
<p><br />DEVrupt Hotel<br /> Your smart key is powered by <a href="https://www.leiwand-lock.at">Leiwand Lock</a>!<br />info@leiwand-lock.at</p>
</body>
</html>
EOD;
        $mail->Body = str_replace("{{QRCODE}}", '<img alt="Image attached" src="cid:' . $qrdata . '">', $email_tpl);
        $mail->AddEmbeddedImage('cache/' . $qrdata . '.jpg', $qrdata, $qrdata . '.jpg');
        $mail->AddAttachment('cache/' . $qrdata . '.jpg', 'YourQR-Code.jpg');

        if (!empty($this->apaleo_data->primaryGuest->email)) {
            $mail->addAddress($this->apaleo_data->primaryGuest->email);
        } else {
            $mail->addAddress($setup['FromAddress']);
            $mail->Subject .= "- Reservation " . $this->apaleo_data->id . " has no email-address!";
        }

        try {
            $mail->send();
        } catch (phpmailerException $e) {
            $this->log($e->errorMessage());
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
        $db->exec("UPDATE reservations SET email_sent = NOW() WHERE reservation_id = '" . $this->apaleo_data->id . "' AND property_id = '" . $this->apaleo_data->property->id . "' AND account_id = '".$_SESSION['account_code']."'");

    }

    function update()
    {
        global $db;
        $ll_res = $this->connector->callMethod("codes/", "GET", array("code" => md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email), "active" => "true", "format" => "json"));
        $this->connector->close();
        $this->connector = new LeiwandLockConnector($this->setup['host'], $this->setup['ll_token']);
        if (!empty($ll_res[0]->id)) { //we need to update cause we have found a code for this reservation
            $post_array['description'] = $this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email;
            $post_array['code'] = md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email);
            $post_array['valid_from'] = $this->apaleo_data->arrival;
            $to = new DateTime($this->apaleo_data->departure);
            $to->add(new DateInterval("PT30M")); //add 30 minutes after checkout time, just to be nice
            $post_array['valid_until'] = $to->format('c');
            $post_array['tag'] = $this->apaleo_data->unit->id;
            if ($this->apaleo_data->status == "Canceled" || $this->apaleo_data->status == 'NoShow') {
                $post_array['active'] = "false"; //we update all reservation informations on qr code system but deactivate it
            } else {
                $post_array['active'] = "true";
            }
            $this->connector->callMethod("codes/" . $ll_res[0]->id . "/", "PUT", array(), $post_array);
        } else { //we have not generated a qr code yet
            $post_array['description'] = $this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email;
            $post_array['code'] = md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email);
            $post_array['valid_from'] = $this->apaleo_data->arrival;
            $to = new DateTime($this->apaleo_data->departure);
            $to->add(new DateInterval("PT30M")); //add 30 minutes after checkout time, just to be nice
            $post_array['valid_until'] = $to->format('c');
            if ($this->apaleo_data->status == 'Canceled' || $this->apaleo_data->status == 'NoShow') {
                $post_array['active'] = false;
                //if noshow or cancel and we haven't generated a qr in leiwandlock, we dont have to generate one now
                exit;
            } else {
                $post_array['active'] = true;
            }
            $post_array['tag'] = $this->apaleo_data->unit->id;
            $this->connector->callMethod("codes/", "POST", array(), $post_array);
            $db->exec("INSERT INTO reservations (id,account_id,property_id,reservation_id,qrcode_generated) VALUES (UUID(),'".$_SESSION['account_code']."','" . $this->apaleo_data->property->id . "','" . $this->apaleo_data->id . "',1)");

            $qrdata = md5($this->apaleo_data->id . "/" . $this->apaleo_data->primaryGuest->email);
            $code = $this->generateQRCode($qrdata);
            $this->senEmail($qrdata, $code);
        }
    }

}