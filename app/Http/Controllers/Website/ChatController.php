<?php
namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Models\SchoolSetting;
use App\Models\WebsiteItem;
use App\Models\WebsiteSetting;
use App\Models\WebsiteNews;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function respond(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:500',
            'session_id' => 'nullable|string|max:100',
        ]);

        $sessionId = $request->session_id ?: (string) Str::uuid();
        $school    = SchoolSetting::current();
        $ws        = WebsiteSetting::all()->pluck('value', 'key');
        $year      = AcademicYear::where('is_current', true)->first();

        // Contact info: the WebsiteSetting contact_* keys are the real, publicly
        // displayed values on the Contact page. SchoolSetting's own address/phone
        // fields are for internal admin use and can be stale — never used for
        // anything shown to a public visitor.
        $address = $ws->get('contact_address');
        $phone   = $ws->get('contact_phone');
        $email   = $ws->get('contact_email');

        $context = "SCHOOL INFORMATION\n";
        $context .= "Name: {$school->school_name}\n";
        if ($address) $context .= "Address: {$address}\n";
        if ($phone)   $context .= "Phone: {$phone}\n";
        if ($email)   $context .= "Email: {$email}\n";
        if ($school->motto) $context .= "Motto: {$school->motto}\n";
        if ($year) $context .= "Current academic year: {$year->name} ({$year->start_date->format('d M Y')} to {$year->end_date->format('d M Y')})\n";
        $context .= "Admissions currently: " . (($ws->get('admissions_open', '1') === '1') ? 'Open' : 'Closed') . "\n";

        if ($ws->get('about_body') || $ws->get('about_mission') || $ws->get('about_vision') || $ws->get('about_values')) {
            $context .= "\nABOUT THE SCHOOL\n";
            if ($ws->get('about_body'))    $context .= $ws->get('about_body') . "\n";
            if ($ws->get('about_mission')) $context .= "Mission: " . $ws->get('about_mission') . "\n";
            if ($ws->get('about_vision'))  $context .= "Vision: " . $ws->get('about_vision') . "\n";
            if ($ws->get('about_values'))  $context .= "Values: " . $ws->get('about_values') . "\n";
        }

        $context .= "\nSCHOOL STATS\n";
        for ($i = 1; $i <= 4; $i++) {
            $num = $ws->get("about_stat{$i}_number");
            $label = $ws->get("about_stat{$i}_label");
            if ($num && $label) $context .= "- {$num} {$label}\n";
        }

        $programs = WebsiteItem::where('type', 'program')->where('is_active', true)->orderBy('sort_order')->get();
        if ($programs->count()) {
            $context .= "\nPROGRAMS OFFERED\n";
            foreach ($programs as $p) {
                $context .= "- {$p->title}" . ($p->badge ? " ({$p->badge})" : '') . ($p->description ? ": {$p->description}" : '') . "\n";
            }
        }

        $features = WebsiteItem::where('type', 'feature')->where('is_active', true)->orderBy('sort_order')->get();
        if ($features->count()) {
            $context .= "\nWHY CHOOSE US\n";
            foreach ($features as $f) {
                $context .= "- {$f->title}" . ($f->description ? ": {$f->description}" : '') . "\n";
            }
        }

        $teachers = WebsiteItem::where('type', 'teacher')->where('is_active', true)->orderBy('sort_order')->get();
        if ($teachers->count()) {
            $context .= "\nTEACHERS\n";
            foreach ($teachers as $t) {
                $context .= "- {$t->title}" . ($t->subtitle ? " ({$t->subtitle})" : '') . ($t->description ? ": {$t->description}" : '') . "\n";
            }
        }

        $testimonials = WebsiteItem::where('type', 'testimonial')->where('is_active', true)->orderBy('sort_order')->take(5)->get();
        if ($testimonials->count()) {
            $context .= "\nWHAT PARENTS SAY\n";
            foreach ($testimonials as $t) {
                $context .= "- \"{$t->description}\"" . ($t->title ? " — {$t->title}" : '') . "\n";
            }
        }

        $news = WebsiteNews::published()->orderByDesc('published_at')->take(3)->get();
        if ($news->count()) {
            $context .= "\nRECENT NEWS\n";
            foreach ($news as $n) {
                $context .= "- {$n->title}" . ($n->excerpt ? ": {$n->excerpt}" : '') . "\n";
            }
        }

        $faqs = WebsiteItem::where('type', 'faq')->where('is_active', true)->orderBy('sort_order')->get();
        if ($faqs->count()) {
            $context .= "\nFREQUENTLY ASKED QUESTIONS\n";
            foreach ($faqs as $f) {
                $context .= "Q: {$f->title}\nA: {$f->description}\n\n";
            }
        }

        $systemPrompt = "You are the website assistant for {$school->school_name}. "
            . "Answer ONLY using the information provided below. Be warm, brief, and helpful. "
            . "If the answer is not in the information provided, say you don't have that specific detail "
            . "and direct the visitor to contact the school directly"
            . ($phone ? " at {$phone}" : '')
            . ($email ? " or {$email}" : '')
            . ", or to use the admission enquiry form. "
            . "Never guess, estimate, or state a fee, date, address, or policy that isn't explicitly given to you below — "
            . "not even a plausible-sounding general answer. If in doubt, say you don't know rather than answer approximately.\n\n"
            . $context;

        // No API key configured: do not call the API (the widget is hidden in this case too)
        if (! config('services.anthropic.api_key')) {
            return response()->json([
                'session_id' => $sessionId,
                'reply' => "The chat assistant is not available. Please contact the school directly" . ($phone ? " at {$phone}" : '') . '.',
            ], 200);
        }

        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-sonnet-5',
            'max_tokens' => 500,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $request->message],
            ],
        ]);

        if (!$response->successful()) {
            ChatLog::create([
                'session_id'   => $sessionId,
                'ip_address'   => $request->ip(),
                'question'     => $request->message,
                'answer'       => null,
                'was_grounded' => false,
            ]);
            return response()->json([
                'session_id' => $sessionId,
                'reply' => "Sorry, I'm having trouble responding right now. Please contact the school directly"
                    . ($phone ? " at {$phone}" : '') . " or try again shortly.",
            ], 200);
        }

        $answer = $response->json('content.0.text') ?? "Sorry, I couldn't generate a response. Please contact the school directly.";

        ChatLog::create([
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'question'   => $request->message,
            'answer'     => $answer,
        ]);

        return response()->json(['session_id' => $sessionId, 'reply' => $answer]);
    }
}
