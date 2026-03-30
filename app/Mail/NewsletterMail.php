<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class NewsletterMail extends Mailable
{
    public $subjectLine;
    public $content;

    public function __construct($subject, $content)
    {
        $this->subjectLine = $subject;
        $this->content = $content;
    }

    public function build()
    {
        $settings = Setting::getAll(); // 🔥 from your model

        return $this->subject($this->subjectLine)
            ->view('emails.newsletter')
            ->with([
                'content' => $this->content,
                'email' => $this->email ?? null,
                'settings' => $settings
            ]);
    }
}