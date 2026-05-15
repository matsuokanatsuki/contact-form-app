<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_tag()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '重要',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '重要',
        ]);
    }

    public function test_tag_name_is_required()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_view_tag_edit_page()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($tag->name);
    }

    public function test_admin_can_update_tag()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '旧タグ',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => '新タグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '新タグ',
        ]);
    }

    public function test_admin_can_delete_tag()
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }
}