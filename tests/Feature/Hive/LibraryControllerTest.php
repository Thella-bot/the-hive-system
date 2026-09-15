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

        $response = $this->get(route('hive.library.dashboard'));

        $response->assertOk();
    }

    public function test_books_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.books.index'));

        $response->assertOk();
    }

    public function test_books_create_returns_success_for_librarian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.books.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Library/Books/Create'));
    }

    public function test_books_store_creates_book_for_librarian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $this->actingAs($user);

        BookCategory::factory()->create();

        $response = $this->post(route('hive.library.books.store'), [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '978-0-123456-78-9',
            'publisher' => 'Test Publisher',
            'publish_year' => 2025,
            'total_copies' => 5,
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

        $response = $this->get(route('hive.library.books.show', $book));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Library/Books/Show'));
    }

    public function test_categories_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.categories.index'));

        $response->assertRedirect();
    }

    public function test_categories_index_returns_success_for_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.categories.index'));

        $response->assertOk();
    }

    public function test_loans_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.loans.index'));

        $response->assertRedirect();
    }

    public function test_loans_index_returns_success_for_finance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.loans.index'));

        $response->assertOk();
    }

    public function test_reservations_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.library.reservations.index'));

        $response->assertOk();
    }

    public function test_loan_store_creates_loan_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $book = LibraryBook::factory()->create(['available_copies' => 5]);
        $borrower = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.library.loans.store'), [
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('book_loans', [
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('library_books', [
            'id' => $book->id,
            'available_copies' => 4,
        ]);
    }

    public function test_loan_store_rejects_unavailable_book(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $book = LibraryBook::factory()->create(['available_copies' => 0, 'is_available' => false]);
        $borrower = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.library.loans.store'), [
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('book_loans', [
            'book_id' => $book->id,
        ]);
    }

    public function test_loan_store_denies_student(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $book = \App\Models\LibraryBook::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($student);

        $response = $this->post(route('hive.library.loans.store'), [
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
    }

    public function test_loan_return_restores_available_copies(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $book = LibraryBook::factory()->create(['available_copies' => 5]);
        $borrower = User::factory()->create();

        $loan = \App\Models\BookLoan::create([
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'active',
        ]);
        $book->decrement('available_copies');

        $this->actingAs($user);

        $response = $this->patch(route('hive.library.loans.return', $loan));

        $response->assertRedirect();
        $this->assertDatabaseHas('book_loans', [
            'id' => $loan->id,
            'status' => 'returned',
        ]);
        $this->assertDatabaseHas('library_books', [
            'id' => $book->id,
            'available_copies' => 5,
        ]);
    }

    public function test_loan_renew_succeeds_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $book = LibraryBook::factory()->create();
        $borrower = User::factory()->create();

        $loan = \App\Models\BookLoan::create([
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'active',
            'renewal_count' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.library.loans.renew', $loan));

        $response->assertRedirect();
        $this->assertDatabaseHas('book_loans', [
            'id' => $loan->id,
            'renewal_count' => 1,
        ]);
    }

    public function test_reservation_store_creates_for_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $book = LibraryBook::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.library.reservations.store'), [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('book_reservations', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'pending',
        ]);
    }

    public function test_reservation_store_blocks_duplicate_pending(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $book = LibraryBook::factory()->create();

        \App\Models\BookReservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reserved_at' => now()->toDateString(),
            'expires_at' => now()->addDays(3)->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.library.reservations.store'), [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('book_reservations', 1);
    }

    public function test_reservation_fulfill_creates_loan_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $book = LibraryBook::factory()->create(['available_copies' => 5]);
        $borrower = User::factory()->create();

        $reservation = \App\Models\BookReservation::create([
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'reserved_at' => now()->toDateString(),
            'expires_at' => now()->addDays(3)->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.library.reservations.fulfill', $reservation));

        $response->assertRedirect();
        $this->assertDatabaseHas('book_reservations', [
            'id' => $reservation->id,
            'status' => 'fulfilled',
        ]);
        $this->assertDatabaseHas('book_loans', [
            'user_id' => $borrower->id,
            'book_id' => $book->id,
            'status' => 'active',
        ]);
    }

    public function test_reservation_cancel_by_owner(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $book = LibraryBook::factory()->create();

        $reservation = \App\Models\BookReservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reserved_at' => now()->toDateString(),
            'expires_at' => now()->addDays(3)->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.library.reservations.cancel', $reservation));

        $response->assertRedirect();
        $this->assertDatabaseHas('book_reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }
}