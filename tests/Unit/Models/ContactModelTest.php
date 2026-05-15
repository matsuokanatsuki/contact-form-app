<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_filter_by_keyword()
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'hanako@example.com',
        ]);

        $contacts = Contact::filter([
            'keyword' => '山田',
        ])->get();

        $this->assertCount(1, $contacts);
    }

    public function test_scope_filter_by_gender()
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 2,
        ]);

        $contacts = Contact::filter([
            'gender' => 1,
        ])->get();

        $this->assertCount(1, $contacts);
    }

    public function test_scope_filter_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $contacts = Contact::filter([
            'category_id' => $category1->id,
        ])->get();

        $this->assertCount(1, $contacts);
    }

    public function test_scope_filter_by_date()
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);

        $contacts = Contact::filter([
            'date' => now()->format('Y-m-d'),
        ])->get();

        $this->assertCount(1, $contacts);
    }
}