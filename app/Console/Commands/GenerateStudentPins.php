<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\StudentParent;
use App\Mail\StudentPinMail;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Mail;

class GenerateStudentPins extends Command
{
    protected $signature   = 'students:generate-pins {--force : Regenerate even if PIN exists}';
    protected $description = 'Generate login PINs for all active students';

    public function handle(): void
    {
        $students = Student::where('is_active', true)
            ->when(!$this->option('force'), fn($q) => $q->whereDoesntHave('pin'))
            ->get();

        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        MailConfigService::applyFromSettings();
        $emailed = 0;

        $parentEmails = StudentParent::whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('email')->get()->keyBy('student_id');

        foreach ($students as $student) {
            $plain  = $student->generatePin();
            $parent = $parentEmails->get($student->id);
            if ($parent) {
                try {
                    $student->load('schoolClass');
                    Mail::to($parent->email)->send(new StudentPinMail($student, $plain, $parent));
                    $emailed++;
                } catch (\Exception $e) {
                    $this->warn("Email failed for {$student->full_name}: " . $e->getMessage());
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("PINs generated for {$students->count()} student(s). {$emailed} email(s) sent.");
    }
}
