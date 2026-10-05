<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Models\WebsiteItem;
use App\Models\WebsiteHeroMedia;
use App\Models\WebsiteNews;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteCmsController extends Controller
{
    public function index()
    {
        $school       = SchoolSetting::first();
        $ws           = WebsiteSetting::all()->groupBy('section');
        $programs     = WebsiteItem::where('type','program')->orderBy('sort_order')->get();
        $features     = WebsiteItem::where('type','feature')->orderBy('sort_order')->get();
        $teachers     = WebsiteItem::where('type','teacher')->orderBy('sort_order')->get();
        $gallery      = WebsiteItem::where('type','gallery')->orderBy('category')->orderBy('sort_order')->get();
        $galleryCategories = $gallery->pluck('category')->filter()->unique()->values();
        $testimonials = WebsiteItem::where('type','testimonial')->orderBy('sort_order')->get();
        $faqs         = WebsiteItem::where('type','faq')->orderBy('sort_order')->get();
        $heroMedia    = WebsiteHeroMedia::orderBy('sort_order')->get();
        $news         = WebsiteNews::orderByDesc('published_at')->get();

        return view('admin.website.index', compact(
            'school','ws','programs','features','teachers',
            'gallery','galleryCategories','testimonials','faqs','heroMedia','news'
        ));
    }

    public function save(Request $request)
    {
        $skip = ['_token','_method','section'];
        foreach ($request->except($skip) as $key => $value) {
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store('website','public');
                WebsiteSetting::set($key, $path, 'image', $request->section ?? 'general');
            } elseif (is_string($value) || is_null($value)) {
                WebsiteSetting::set($key, $value, 'text', $request->section ?? 'general');
            }
        }
        Cache::forget('website_settings');
        return back()->with('success', 'Section saved.');
    }

    // ── Hero Media ────────────────────────────────────────────────
    public function storeHeroMedia(Request $request)
    {
        // MAX UPLOAD SIZE: 100 MB per file (102400 KB). Applies to images and videos.
        // Must stay below PHP upload_max_filesize (300M) and post_max_size (256M).
        // If raising this, check those PHP limits and any web server body-size limit.
        $request->validate(['files.*' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:102400']);
        foreach ($request->file('files') as $file) {
            $type = in_array($file->getClientOriginalExtension(), ['mp4','mov']) ? 'video' : 'image';
            $path = $file->store('website/hero','public');
            WebsiteHeroMedia::create(['file_path' => $path, 'type' => $type, 'sort_order' => 0]);
        }
        return back()->with('success', 'Hero media uploaded.');
    }

    public function destroyHeroMedia(WebsiteHeroMedia $media)
    {
        Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Media removed.');
    }

    // ── Items CRUD ────────────────────────────────────────────────
    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:teacher,gallery,testimonial,program,feature,faq',
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'icon'        => 'nullable|string|max:50',
            'badge'       => 'nullable|string|max:100',
            'link'        => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer',
        ]);

        // Multi-file for gallery
        if ($request->hasFile('images') && $data['type'] === 'gallery') {
            foreach ($request->file('images') as $file) {
                $d = $data;
                $d['image'] = $file->store('website/items','public');
                WebsiteItem::create($d);
            }
            return back()->with('success', 'Gallery images added.');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('website/items','public');
        }

        WebsiteItem::create($data);
        return back()->with('success', ucfirst($data['type']) . ' added.');
    }

    public function updateItem(Request $request, WebsiteItem $item)
    {
        $data = $request->except(['_token','_method']);
        if ($request->hasFile('image')) {
            if ($item->image) Storage::disk('public')->delete($item->image);
            $data['image'] = $request->file('image')->store('website/items','public');
        }
        $item->update($data);
        return back()->with('success', 'Item updated.');
    }

    public function destroyItem(WebsiteItem $item)
    {
        if ($item->image) Storage::disk('public')->delete($item->image);
        $item->delete();
        return back()->with('success', 'Item removed.');
    }

    // ── News CRUD ─────────────────────────────────────────────────
    public function storeNews(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'excerpt'      => 'nullable|string',
            'body'         => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'published'    => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);
        $data['published'] = $request->boolean('published', true);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('website/news','public');
        }
        WebsiteNews::create($data);
        return back()->with('success', 'News article added.');
    }

    public function updateNews(Request $request, WebsiteNews $news)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'excerpt'      => 'nullable|string',
            'body'         => 'nullable|string',
            'category'     => 'nullable|string|max:100',
            'published'    => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);
        $data['published'] = $request->boolean('published', true);
        if ($request->hasFile('image')) {
            if ($news->image) Storage::disk('public')->delete($news->image);
            $data['image'] = $request->file('image')->store('website/news','public');
        }
        $news->update($data);
        return back()->with('success', 'News updated.');
    }

    public function destroyNews(WebsiteNews $news)
    {
        if ($news->image) Storage::disk('public')->delete($news->image);
        $news->delete();
        return back()->with('success', 'News deleted.');
    }

    // ── Gallery Bulk Categorize ───────────────────────────────────
    public function bulkCategorize(Request $request)
    {
        $photos = $request->input('photos', []);
        foreach ($photos as $id => $data) {
            WebsiteItem::where('id', $id)->update([
                'category' => $data['category'] ?? null,
                'title'    => $data['title'] ?? null,
            ]);
        }
        return back()->with('success', 'Categories saved for all photos.');
    }

}
