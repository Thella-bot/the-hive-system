<?php

namespace Tests\Feature\Hive;

use App\Models\BookCategory;
use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LibraryControllerTest extends HiveTestCase
{
    public function test_library_dashboard_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('library.dashboard'));

        $response->assertOk();
    }

    public function test_books_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('library.books.index'));

        $response->assertOk();
    }

    public function test_books_create_returns_success_for_librarian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $this->actingAs($user);

        $response = $this->get(route('library.books.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Library/Books/Create'));
    }

    public function test_books_store_creates_book_for_librarian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $this->actingAs($user);

        BookCategory::factory()->create();

        $response = $this->post(route('library.books.store'), [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '978-0-123456-78-9',
            'publisher' => 'Test Publisher',
            'publish_year' => 2025,
            'category_id' => BookCategory::first()->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('library_books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
        ]);
    }

    public function test_books_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $book = LibraryBook::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('library.books.show', $book));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Library/Books/Show'));
    }

    public function test_categories_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('library.categories.index'));

        $response->assertForbidden();
    }

    public function test_categories_index_returns_success_for_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('library.categories.index'));

        $response->assertOk();
    }

    public function test_loans_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('library.loans.index'));

        $response->assertForbidden();
    }

    public function test_loans_index_returns_success_for_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('library.loans.index'));

        $response->assertOk();
    }

    public function test_reservations_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('library.reservations.index'));

        $response->assertOk();
    }
}