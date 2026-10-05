<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $school  = SchoolSetting::first();
        $notices = Notice::with('poster')->latest()->get();
        return view('admin.notices.index', compact('school','notices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'audience'   => 'required|in:all,students,teachers,parents',
            'expires_at' => 'nullable|date|after:today',
        ]);

        Notice::create([
            'posted_by'    => auth()->id(),
            'title'        => $request->title,
            'body'         => $request->body,
            'audience'     => $request->audience,
            'is_published' => true,
            'expires_at'   => $request->expires_at,
        ]);

        return back()->with('success', 'Notice published.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return back()->with('success', 'Notice deleted.');
    }
}
