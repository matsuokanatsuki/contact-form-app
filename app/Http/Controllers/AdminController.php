<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;


class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::query()
            ->with(['category', 'tags']);

        // 名前 + メール検索
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

        // 性別
        if ($request->filled('gender') && $request->gender !== '0') {
            $query->where('gender', $request->gender);
        }

        // カテゴリ
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 日付
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $contacts = $query
            ->latest()
            ->paginate(7)
            ->appends($request->query());

        return view('admin.index', [
            'contacts' => $contacts,
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin')
            ->with('success', 'お問い合わせを削除しました');
    }
}