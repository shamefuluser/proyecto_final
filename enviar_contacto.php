<?php

define('GMAIL_USER', 'biteandbalance0@gmail.com');

define('GMAIL_PASSWORD', 'fieb gmpq ceqm mmma');

define('DESTINO_EMAIL', 'biteandbalance0@gmail.com');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

    if (empty($nombre) || empty($correo) || empty($mensaje)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El correo no es válido']);
        exit;
    }

    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
    $mensaje = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = GMAIL_USER;
    $mail->Password = GMAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(GMAIL_USER, 'Bite & Balance - Formulario');
    $mail->addAddress(DESTINO_EMAIL);
    $mail->addReplyTo($correo, $nombre);

    $mail->isHTML(true);
    $mail->Subject = 'Nuevo mensaje de contacto - Bite & Balance';
    
    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9f9f9;
            }
            .header {
                background: linear-gradient(135deg, #7a9d54 0%, #6a8e47 100%);
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .content {
                background: white;
                padding: 30px;
                border-radius: 0 0 10px 10px;
            }
            .info-row {
                margin: 15px 0;
                padding: 15px;
                background: #f5f5f5;
                border-left: 4px solid #7a9d54;
                border-radius: 5px;
            }
            .label {
                font-weight: bold;
                color: #5a6f47;
                display: block;
                margin-bottom: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                padding: 15px;
                color: #777;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2> Nuevo Mensaje de Contacto</h2>
                <p>Bite & Balance</p>
            </div>
            <div class='content'>
                <div class='info-row'>
                    <span class='label'> Nombre:</span>
                    {$nombre}
                </div>
                <div class='info-row'>
                    <span class='label'> Correo:</span>
                    <a href='mailto:{$correo}'>{$correo}</a>
                </div>
                <div class='info-row'>
                    <span class='label'> Mensaje:</span>
                    " . nl2br($mensaje) . "
                </div>
                <div class='info-row'>
                    <span class='label'> Fecha:</span>
                    " . date('d/m/Y H:i:s') . "
                </div>
            </div>
            <div class='footer'>
                <p>Este mensaje fue enviado desde el formulario de contacto de tu sitio web</p>
                <p><strong>Bite & Balance</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";


    $mail->AltBody = "Nuevo mensaje de contacto\n\n"
                   . "Nombre: {$nombre}\n"
                   . "Correo: {$correo}\n"
                   . "Mensaje: {$mensaje}\n"
                   . "Fecha: " . date('d/m/Y H:i:s');

   
    $mail->send();

   
    $mailConfirm = new PHPMailer(true);
    $mailConfirm->isSMTP();
    $mailConfirm->Host = 'smtp.gmail.com';
    $mailConfirm->SMTPAuth = true;
    $mailConfirm->Username = GMAIL_USER;
    $mailConfirm->Password = GMAIL_PASSWORD;
    $mailConfirm->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailConfirm->Port = 587;
    $mailConfirm->CharSet = 'UTF-8';

    $mailConfirm->setFrom(GMAIL_USER, 'Bite & Balance');
    $mailConfirm->addAddress($correo, $nombre);

    $mailConfirm->isHTML(true);
    $mailConfirm->Subject = 'Mensaje recibido - Bite & Balance';
    
    $mailConfirm->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { 
                background: linear-gradient(135deg, #7a9d54 0%, #6a8e47 100%); 
                color: white; 
                padding: 30px; 
                text-align: center; 
                border-radius: 10px; 
            }
            .content { 
                background: white; 
                padding: 30px; 
                margin-top: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 10px;
            }
            .message-box {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
                border-left: 4px solid #7a9d54;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>¡Gracias por contactarnos!</h2>
                <p> Bite & Balance</p>
            </div>
            <div class='content'>
                <p>Hola <strong>{$nombre}</strong>,</p>
                <p>Hemos recibido tu mensaje y te responderemos lo antes posible.</p>
                
                <p><strong>Tu mensaje fue:</strong></p>
                <div class='message-box'>
                    " . nl2br($mensaje) . "
                </div>
                
                <p>Si tienes alguna pregunta urgente, puedes responder directamente a este correo.</p>
                
                <p>Saludos cordiales,<br>
                <strong>Equipo Bite & Balance</strong> </p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mailConfirm->send();

    echo json_encode([
        'success' => true, 
        'message' => 'Mensaje enviado correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al enviar: ' . $mail->ErrorInfo
    ]);
}
?>