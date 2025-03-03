<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Config;

class EmailController extends Controller
{
    public function sendEmail()
    {
        $toEmail = "sunil.fcr@gmail.com";
        $message = "Hello, Welcome to our Website"; // Plain text message
        $subject = "Welcome to My Site";

        $user  = User::find(1); // Fetching the email from User model

        // Send the email
      //  Mail::to($toEmail)->send(new WelcomeEmail($message, $subject));

        $fromEmail = $user->email;  // This could be the 'from' email you're fetching
        $smtpUsername = $user->email;  // Or any other column in the `users` table
        $smtpPassword = $user->password;

        Config::set('mail.mailers.smtp.username', $smtpUsername);
        Config::set('mail.mailers.smtp.password', $smtpPassword);

      Mail::to($toEmail)
      ->send(new WelcomeEmail($message, $subject, $fromEmail)); 
        
        dd('Email sent successfully!');
    }
}
