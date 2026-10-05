<?php
namespace App\Mail;

use App\Models\Student;
use App\Models\StudentParent;
use App\Models\SchoolSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pin;
    public Student $student;
    public SchoolSetting $school;
    public ?StudentParent $parent;

    public function __construct(Student $student, string $pin, ?StudentParent $parent = null)
    {
        $this->student = $student;
        $this->pin     = $pin;
        $this->school  = SchoolSetting::first();
        $this->parent  = $parent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->school->school_name . ' — Student Portal Access PIN',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.student_pin');
    }
}
