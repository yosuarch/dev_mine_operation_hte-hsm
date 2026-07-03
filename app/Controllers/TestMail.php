<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TestMail extends BaseController
{
    public function sendTestEmail()
    {
        // Load the email service
        $email = \Config\Services::email();

        // If you need to override the default "From" address dynamically
        $email->setFrom('yosuarch@gmail.com', 'Mine-Ops');

        $email->setTo('yosuarch@hotmail.com');
        $email->setSubject('SMTP Testing - CodeIgniter 4');
        $email->setMessage('<h1>It Works!</h1><p>This email was successfully sent via SMTP setup.</p>');

        if ($email->send()) {
            return "Email successfully sent!";
        } else {
            // This will display detailed debug logs if the connection fails
            $data = $email->printDebugger(['headers', 'subject', 'body']);
            return print_r($data, true);
        }
    }
}
