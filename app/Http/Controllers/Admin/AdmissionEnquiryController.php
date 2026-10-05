<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdmissionEnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('admission_enquiries')->orderByDesc('created_at');
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $enquiries = $query->paginate(20)->withQueryString();
        $counts = DB::table('admission_enquiries')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        return view('admin.admissions.index', compact('enquiries', 'counts'));
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:new,contacted,enrolled,declined']);
        $enquiry = DB::table('admission_enquiries')->where('id', $id)->first();
        DB::table('admission_enquiries')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);
        ActivityLogger::log('enquiry.status', 'Set admission enquiry for ' . ($enquiry->child_name ?? "#$id") . ' to ' . $request->status . '.');
        return back()->with('success', 'Status updated.');
    }
    public function destroy($id)
    {
        $enquiry = DB::table('admission_enquiries')->where('id', $id)->first();
        DB::table('admission_enquiries')->where('id', $id)->delete();
        ActivityLogger::log('enquiry.delete', 'Deleted admission enquiry for ' . ($enquiry->child_name ?? "#$id") . '.');
        return back()->with('success', 'Enquiry deleted.');
    }
}
