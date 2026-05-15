<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_contact_list()
    {
        $category = Category::factory()->create();

        Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();

        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    public function test_can_get_contact_detail()
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $contact->id,
        ]);
    }

    public function test_returns_404_for_missing_contact()
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertStatus(404);

        $response->assertJson([
            'message' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    public function test_can_create_contact()
    {
        $category = Category::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'detail' => 'APIからのお問い合わせ',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
        ]);
    }

    public function test_validation_error_returns_422()
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);
    }

    public function test_can_update_contact()
    {
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'category_id' => $newCategory->id,
            'tag_ids' => [],
            'first_name' => '次郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'jiro@example.com',
            'tel' => '09087654321',
            'address' => '東京都港区',
            'building' => '更新ビル',
            'detail' => '更新しました',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'jiro@example.com',
        ]);
    }

    public function test_can_delete_contact()
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/contacts/{$contact->id}"
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_pagination_respects_per_page()
    {
        $category = Category::factory()->create();

        Contact::factory()->count(25)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?per_page=10'
        );

        $response->assertOk();

        $response->assertJsonPath(
            'meta.per_page',
            10
        );
    }
}