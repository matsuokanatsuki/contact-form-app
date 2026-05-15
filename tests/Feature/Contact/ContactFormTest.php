<?php

namespace Tests\Feature\Contact;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_can_be_rendered()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_contact_confirm_success()
    {
        $category = Category::factory()->create();

        $response = $this->post('/contacts/confirm', [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
        ]);

        $response->assertStatus(200);
        $response->assertSee('確認');
    }

    public function test_contact_confirm_validation_error()
    {
        $response = $this->post('/contacts/confirm', []);

        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }

    public function test_contact_can_be_stored()
    {
        $category = Category::factory()->create();

        $response = $this->post('/contacts', [
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
        ]);

        $response->assertRedirect('/thanks');

        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
        ]);
    }

    public function test_thanks_page_can_be_rendered()
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
    }
}