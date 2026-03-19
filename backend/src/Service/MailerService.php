<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $params
    ) {}

    function generateMailBody($bodyMessage, $footerMessage = "")
    {
        return <<<HTML
               <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin:0; padding:0;
            }
            .container {
                width: 100%;
                padding: 20px 0;
                display: flex;
                justify-content: center;
            }
            .card {
                background: #ffffff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 500px;
                text-align: center;
            }
            h1 {
                color: #333;
            }
            p {
                color: #555;
                font-size: 16px;
            }
            .footer {
                margin-top: 20px;
                font-size: 12px;
                color: #aaa;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <img src="{$this->params->get('email_logo')}" alt="Logo" style="width: auto; max-height: 256px;">
                <p>$bodyMessage</p>
                <div class="footer">$footerMessage</div>
            </div>
        </div>
    </body>
    </html>
    HTML;
    }

    function sendEmailToUser($user_mail, $sujet, $bodyMessage, $footerMessage = "Ce mail a été envoyé automatiquement, merci de ne pas y répondre.")
    {

        try {
            $email = (new Email())
                ->from($this->params->get('noreply'))
                ->to($user_mail)
                ->subject($sujet)
                ->html($this->generateMailBody($bodyMessage, $footerMessage))
                ->text($bodyMessage);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            //! Mail part pas
        }
    }
}
