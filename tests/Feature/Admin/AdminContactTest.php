<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_admin()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_can_search_contacts_by_keyword()
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
            ->get('/admin?keyword=山田');

        $response->assertStatus(200);

        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }


    public function test_admin_can_view_contact_detail()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertSee($contact->email);
    }


    public function test_admin_can_delete_contact()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}