<?php

namespace App\Http\Controllers;

use App\Support\Theme;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Role-scoped user guides. The guide a person gets is decided here from
 * their own role, never from the URL, so one role cannot open another's guide.
 * Content lives in resources/guides/{key}.md so each school can edit its copy.
 */
class GuideController extends Controller
{
    private const TITLES = [
        'admin'      => 'Administrator Guide',
        'accountant' => 'Accountant Guide',
        'teacher'    => 'Teacher Guide',
        'student'    => 'Student & Parent Guide',
    ];

    public function admin()      { return $this->page($this->adminKey(), 'guides.admin', 'admin.guide.pdf'); }
    public function adminPdf()   { return $this->pdf($this->adminKey()); }
    public function teacher()    { return $this->page('teacher', 'guides.teacher', 'teacher.guide.pdf'); }
    public function teacherPdf() { return $this->pdf('teacher'); }
    public function student()
    {
        $student = \App\Models\Student::findOrFail(session('student_id'));
        return $this->page('student', 'guides.student', 'student.guide.pdf', ['student' => $student]);
    }
    public function studentPdf() { return $this->pdf('student'); }

    private function adminKey(): string
    {
        $slug = auth()->user()?->role?->slug;
        return match ($slug) {
            'super_admin', 'admin' => 'admin',
            'accountant'           => 'accountant',
            default                => abort(403),
        };
    }

    private function page(string $key, string $view, string $pdfRoute, array $extra = [])
    {
        [$html, $toc] = $this->render($key);

        return view($view, $extra + [
            'school' => \App\Models\SchoolSetting::first(),
            'title'  => self::TITLES[$key],
            'html'   => $html,
            'toc'    => $toc,
            'pdfUrl' => route($pdfRoute),
        ]);
    }

    private function pdf(string $key)
    {
        [$html, $toc] = $this->render($key);

        // Drop the Markdown title (the PDF has its own header) and make task lists printable.
        $html = preg_replace('/<h1[^>]*>.*?<\/h1>/s', '', $html, 1);
        $html = preg_replace('/<input[^>]*checked[^>]*>/i', '&#9745;', $html);
        $html = preg_replace('/<input[^>]*type="checkbox"[^>]*>/i', '&#9744;', $html);

        $school = view()->shared('school');
        if (! $school) {
            $model = 'App\\Models\\SchoolSetting';
            if (class_exists($model)) {
                $school = $model::first();
            }
        }
        $schoolName = $school->school_name ?? config('app.name');

        $pdf = Pdf::loadView('guides.pdf', [
            'title'      => self::TITLES[$key],
            'html'       => $html,
            'toc'        => $toc,
            'schoolName' => $schoolName,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(Str::slug($schoolName . ' ' . self::TITLES[$key]) . '.pdf');
    }

    /** Markdown -> HTML with anchored headings and a list of top-level sections. */
    private function render(string $key): array
    {
        $path = resource_path('guides/' . $key . '.md');
        abort_unless(is_file($path), 404);

        $html = Str::markdown(file_get_contents($path), [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $toc  = [];
        $seen = [];
        $html = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function ($m) use (&$toc, &$seen) {
            $text = trim(html_entity_decode(strip_tags($m[2])));
            $id   = Str::slug($text) ?: 'section';
            $seen[$id] = ($seen[$id] ?? 0) + 1;
            if ($seen[$id] > 1) {
                $id .= '-' . $seen[$id];
            }
            if ($m[1] === '2') {
                $toc[] = ['id' => $id, 'text' => $text];
            }
            return '<h' . $m[1] . ' id="' . $id . '">' . $m[2] . '</h' . $m[1] . '>';
        }, $html);

        return [$html, $toc];
    }
}
