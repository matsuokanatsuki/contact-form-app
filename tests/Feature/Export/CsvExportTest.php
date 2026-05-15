<?php

namespace Tests\Feature\Export;

use App\Models\User;
use App\Models\Category;
use App\Models\Contact;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_csv_export()
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_export_csv()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertOk();

        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );

        $response->assertHeader(
            'content-disposition'
        );
    }

    public function test_csv_export_respects_search_filter()
    {
        $user = User::factory()->create();

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

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?keyword=山田');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringNotContainsString('佐藤', $content);
    }
}