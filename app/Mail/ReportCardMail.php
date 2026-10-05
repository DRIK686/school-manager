<?php
namespace App\Mail;

use App\Models\Student;
use App\Models\ExamType;
use App\Models\StudentParent;
use App\Models\SchoolSetting;
use App\Models\WebsiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ReportCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public Student $student;
    public ExamType $exam;
    public ?StudentParent $parent;
    public SchoolSetting $school;
    public string $pdfContent;
    public ?string $contactPhone;
    public ?string $contactEmail;

    public function __construct(Student $student, ExamType $exam, ?StudentParent $parent, string $pdfContent)
    {
        $this->student      = $student;
        $this->exam         = $exam;
        $this->parent       = $parent;
        $this->school       = SchoolSetting::first();
        $this->pdfContent   = $pdfContent;
        $ws = WebsiteSetting::all()->pluck('value', 'key');
        $this->contactPhone = $ws->get('contact_phone');
        $this->contactEmail = $ws->get('contact_email');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->school->school_name . ' — ' . $this->exam->name . ' Report Card',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report_card');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, str_replace('/', '-', "report-card-{$this->student->admission_no}-{$this->exam->name}.pdf"))
                ->withMime('application/pdf'),
        ];
    }
}
