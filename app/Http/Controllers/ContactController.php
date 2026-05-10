<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;


class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index', [
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::findOrFail($validated['category_id']);

        $tags = Tag::whereIn(
            'id',
            $validated['tag_ids'] ?? []
        )->get();

        return view('contact.confirm', compact(
            'validated',
            'category',
            'tags'
        ));
    }

    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create(
            $request->validated()
        );

        if ($request->filled('tag_ids')) {
            $contact->tags()->attach($request->tag_ids);
        }

        return redirect('/thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $request)
    {
        $query = Contact::query()
            ->with(['category']);

        // keyword
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhereRaw(
                        "CONCAT(first_name, last_name) LIKE ?",
                        ["%{$keyword}%"]
                    )
                    ->orWhereRaw(
                        "CONCAT(last_name, first_name) LIKE ?",
                        ["%{$keyword}%"]
                    )
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // gender
        if ($request->filled('gender') && $request->gender !== '0') {
            $query->where('gender', $request->gender);
        }

        // category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $contacts = $query->latest()->get();

        $filename = 'contacts_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                '氏名',
                '性別',
                'メールアドレス',
                '電話番号',
                '住所',
                '建物名',
                '種類（カテゴリ）',
                'お問い合わせ内容',
                '作成日',
            ]);

            $genderLabels = [
                1 => '男性',
                2 => '女性',
                3 => 'その他',
            ];

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->first_name . ' ' . $contact->last_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content ?? '',
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}