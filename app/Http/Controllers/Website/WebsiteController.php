<?php
namespace App\Http\Controllers\Website;
use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Models\WebsiteItem;
use App\Models\WebsiteHeroMedia;
use App\Models\WebsiteNews;
use App\Models\Notice;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function home()
    {
        $ws           = WebsiteSetting::all()->pluck('value','key');
        $school       = SchoolSetting::first();
        $programs     = WebsiteItem::ofType('program')->get();
        $features     = WebsiteItem::ofType('feature')->get();
        $teachers     = WebsiteItem::ofType('teacher')->get();
        $gallery      = WebsiteItem::ofType('gallery')->orderBy('category')->get();
        $galleryCategories = $gallery->pluck('category')->filter()->unique()->values();
        $testimonials = WebsiteItem::ofType('testimonial')->get();
        $heroMedia    = WebsiteHeroMedia::orderBy('sort_order')->get();
        $news         = WebsiteNews::published()->orderByDesc('published_at')->take(6)->get();
        $notices      = Notice::active()
            ->where(fn($q) => $q->where('audience','all')->orWhere('audience','students'))
            ->latest()->take(3)->get();

        return view('website.home', compact(
            'ws','school','programs','features','teachers',
            'gallery','galleryCategories','testimonials',
            'heroMedia','news','notices'
        ));
    }

    public function admissions()
    {
        $ws     = WebsiteSetting::all()->pluck('value','key');
        $school = SchoolSetting::first();
        return view('website.admissions', compact('ws','school'));
    }

    public function faq()
    {
        $ws     = WebsiteSetting::all()->pluck('value','key');
        $school = SchoolSetting::first();
        $faqs   = WebsiteItem::ofType('faq')->get();
        return view('website.faq', compact('ws','school','faqs'));
    }

    public function submitAdmission(Request $request)
    {
        // Honeypot: real visitors never see or fill this field, bots often do.
        // Pretend success so the bot doesn't learn to avoid the trap.
        if ($request->filled('website_field')) {
            return back()->with('success', 'Thank you! We will contact you shortly.');
        }

        $request->validate([
            'child_name'   => 'required|string|max:200',
            'dob'          => 'required|date',
            'grade'        => 'required|string|max:100',
            'parent_name'  => 'required|string|max:200',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'nullable|email|max:200',
        ]);

        DB::table('admission_enquiries')->insert([
            'child_name'   => $request->child_name,
            'dob'          => $request->dob,
            'grade'        => $request->grade,
            'parent_name'  => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'parent_email' => $request->parent_email,
            'message'      => $request->message,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $school = SchoolSetting::current();
        if ($school && $school->email) {
            \App\Services\MailConfigService::applyFromSettings();
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "New admission enquiry received on the {$school->school_name} website:\n\n"
                    . "Child: {$request->child_name}\n"
                    . "Date of Birth: {$request->dob}\n"
                    . "Applying for: {$request->grade}\n"
                    . "Parent/Guardian: {$request->parent_name}\n"
                    . "Phone: {$request->parent_phone}\n"
                    . "Email: " . ($request->parent_email ?: '—') . "\n"
                    . "Message: " . ($request->message ?: '—') . "\n\n"
                    . "Log in to the admin portal to view and respond.",
                    fn($m) => $m->to($school->email)->subject('New Admission Enquiry — ' . $request->child_name)
                );
            } catch (\Exception $e) {
                // Don't block the visitor's submission if the email fails to send
            }
        }

        return back()->with('success', 'Thank you! We will contact you shortly.');
    }

    public function newsShow(string $slug)
    {
        $ws      = WebsiteSetting::all()->pluck('value','key');
        $school  = SchoolSetting::first();
        $article = WebsiteNews::published()->where('slug',$slug)->firstOrFail();
        $related = WebsiteNews::published()->where('id','!=',$article->id)->latest('published_at')->take(3)->get();
        return view('website.news-show', compact('ws','school','article','related'));
    }
}
