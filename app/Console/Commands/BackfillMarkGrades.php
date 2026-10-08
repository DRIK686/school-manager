<?php

namespace App\Console\Commands;

use App\Models\StudentMark;
use Illuminate\Console\Command;

class BackfillMarkGrades extends Command
{
    protected $signature = 'marks:backfill-grades {--dry-run : Show what would change without saving}';
    protected $description = 'Fill missing per-subject grades/remarks on saved marks from the grade scale';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $checked = 0;
        $filled = 0;

        StudentMark::where(fn($q) => $q->whereNull('grade')->orWhere('grade', ''))
            ->where(fn($q) => $q->whereNull('is_absent')->orWhere('is_absent', false))
            ->whereNotNull('marks_obtained')
            ->chunkById(200, function ($marks) use (&$checked, &$filled, $dry) {
                foreach ($marks as $mark) {
                    $checked++;
                    StudentMark::applyGrade($mark);
                    if (! empty($mark->grade)) {
                        $filled++;
                        if (! $dry) {
                            $mark->saveQuietly();
                        }
                    }
                }
            });

        $this->info(($dry ? '[dry run] ' : '') . "checked $checked mark(s), " . ($dry ? 'would fill' : 'filled') . " $filled.");
        return self::SUCCESS;
    }
}
