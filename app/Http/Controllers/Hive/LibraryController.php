<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\BookCategory;
use App\Models\BookLoan;
use App\Models\BookReservation;
use App\Models\LibraryBook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    use HasFilters;

    // Book Management
    public function booksIndex(Request $request): Response
    {
        $query = LibraryBook::with(['category'])
            ->active();

        $this->applyFilters($query, $request, [
            'search' => true,
            'searchColumns' => ['title', 'author', 'isbn', 'call_number'],
            'category_id' => true,
            'categoryColumn' => 'category_id',
        ]);

        if ($request->has('available') && $request->available) {
            $query->available();
        }

        return Inertia::render('Library/Books/Index', [
            'books' => $query->orderBy('title')->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['search', 'category_id', 'available']),
            'categories' => BookCategory::active()->orderBy('name')->get(),
        ]);
    }

    public function booksCreate(): Response
    {
        $categories = BookCategory::active()->orderBy('name')->get();

        return Inertia::render('Library/Books/Create', [
            'categories' => $categories,
        ]);
    }

    public function booksStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:20|unique:library_books,isbn',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer|min:1000|max:2100',
            'edition' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:book_categories,id',
            'total_copies' => 'required|integer|min:1',
            'location' => 'nullable|string|max:100',
            'call_number' => 'nullable|string|max:50',
            'cover_image' => 'nullable|string',
        ]);

        $data['available_copies'] = $data['total_copies'];

        LibraryBook::create($data);

        return to_route('library.books.index')->with('success', 'Book added to library.');
    }

    public function booksShow(LibraryBook $book): Response
    {
        $book->load(['category', 'loans.user', 'reservations.user']);

        return Inertia::render('Library/Books/Show', [
            'book' => $book,
        ]);
    }

    public function booksEdit(LibraryBook $book): Response
    {
        $categories = BookCategory::active()->orderBy('name')->get();

        return Inertia::render('Library/Books/Edit', [
            'book' => $book,
            'categories' => $categories,
        ]);
    }

    public function booksUpdate(Request $request, LibraryBook $book): RedirectResponse
    {
        $data = $request->validate([
            'isbn' => 'nullable|string|max:20|unique:library_books,isbn,' . $book->id,
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer|min:1000|max:2100',
            'edition' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:book_categories,id',
            'total_copies' => 'sometimes|integer|min:1',
            'location' => 'nullable|string|max:100',
            'call_number' => 'nullable|string|max:50',
            'cover_image' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        // Update available copies if total changed
        if (isset($data['total_copies'])) {
            $diff = $data['total_copies'] - $book->total_copies;
            $data['available_copies'] = $book->available_copies + $diff;
        }

        $book->update($data);

        return to_route('library.books.show', $book->id)->with('success', 'Book updated.');
    }

    public function booksDestroy(LibraryBook $book): RedirectResponse
    {
        if ($book->loans()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete book with active loans.');
        }

        $book->delete();

        return to_route('library.books.index')->with('success', 'Book deleted.');
    }

    // Book Categories
    public function categoriesIndex(): Response
    {
        return Inertia::render('Library/Categories/Index', [
            'categories' => BookCategory::withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function categoriesStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:book_categories,code',
            'description' => 'nullable|string|max:255',
        ]);

        BookCategory::create($data);

        return back()->with('success', 'Category created.');
    }

    public function categoriesUpdate(Request $request, BookCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'code' => 'sometimes|string|max:20|unique:book_categories,code,' . $category->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function categoriesDestroy(BookCategory $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return back()->with('error', 'Cannot delete category with books.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    // Loan Management
    public function loansIndex(Request $request): Response
    {
        $query = BookLoan::with(['user', 'book'])
            ->withCount('book');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return Inertia::render('Library/Loans/Index', [
            'loans' => $query->orderByDesc('loan_date')->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'user_id']),
        ]);
    }

    public function loansStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:library_books,id',
            'due_date' => 'required|date|after:loan_date',
        ]);

        $book = LibraryBook::findOrFail($data['book_id']);

        if (!$book->isAvailable()) {
            return back()->with('error', 'Book is not available.');
        }

        $data['loan_date'] = now()->toDateString();
        $data['status'] = BookLoan::STATUS_ACTIVE;

        $loan = BookLoan::create($data);

        // Update available copies
        $book->decrement('available_copies');

        return to_route('library.loans.index')->with('success', 'Book loaned successfully.');
    }

    public function loansReturn(Request $request, BookLoan $loan): RedirectResponse
    {
        if ($loan->status !== BookLoan::STATUS_ACTIVE) {
            return back()->with('error', 'Loan is not active.');
        }

        $loan->update([
            'return_date' => now()->toDateString(),
            'status' => BookLoan::STATUS_RETURNED,
        ]);

        // Restore available copies
        $loan->book->increment('available_copies');

        return back()->with('success', 'Book returned.');
    }

    public function loansRenew(Request $request, BookLoan $loan): RedirectResponse
    {
        if (!$loan->canRenew()) {
            return back()->with('error', 'Cannot renew this loan (max 2 renewals).');
        }

        $loan->update([
            'due_date' => now()->addDays(14),
            'renewal_count' => $loan->renewal_count + 1,
        ]);

        return back()->with('success', 'Loan renewed for 14 days.');
    }

    public function loansDestroy(BookLoan $loan): RedirectResponse
    {
        if ($loan->status === BookLoan::STATUS_ACTIVE) {
            $loan->book->increment('available_copies');
        }

        $loan->delete();

        return to_route('library.loans.index')->with('success', 'Loan record deleted.');
    }

    // Reservation Management
    public function reservationsIndex(Request $request): Response
    {
        $query = BookReservation::with(['user', 'book']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Library/Reservations/Index', [
            'reservations' => $query->orderByDesc('reserved_at')->paginate(20)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function reservationsStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:library_books,id',
        ]);

        $book = LibraryBook::findOrFail($data['book_id']);

        // Check existing pending reservation
        $existing = BookReservation::where('book_id', $data['book_id'])
            ->where('user_id', $data['user_id'])
            ->where('status', BookReservation::STATUS_PENDING)
            ->exists();

        if ($existing) {
            return back()->with('error', 'You already have a pending reservation for this book.');
        }

        BookReservation::create([
            'user_id' => $data['user_id'],
            'book_id' => $data['book_id'],
            'reserved_at' => now()->toDateString(),
            'expires_at' => now()->addDays(3)->toDateString(),
            'status' => BookReservation::STATUS_PENDING,
        ]);

        return back()->with('success', 'Book reserved.');
    }

    public function reservationsFulfill(BookReservation $reservation): RedirectResponse
    {
        if (!$reservation->canFulfill()) {
            return back()->with('error', 'Cannot fulfill this reservation.');
        }

        $book = $reservation->book;

        if (!$book->isAvailable()) {
            return back()->with('error', 'Book is no longer available.');
        }

        // Create loan
        BookLoan::create([
            'user_id' => $reservation->user_id,
            'book_id' => $reservation->book_id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => BookLoan::STATUS_ACTIVE,
        ]);

        $reservation->update([
            'fulfilled_at' => now()->toDateString(),
            'status' => BookReservation::STATUS_FULFILLED,
        ]);

        $book->decrement('available_copies');

        return back()->with('success', 'Reservation fulfilled - book loaned.');
    }

    public function reservationsCancel(BookReservation $reservation): RedirectResponse
    {
        if ($reservation->status !== BookReservation::STATUS_PENDING) {
            return back()->with('error', 'Cannot cancel this reservation.');
        }

        $reservation->update([
            'status' => BookReservation::STATUS_CANCELLED,
        ]);

        return back()->with('success', 'Reservation cancelled.');
    }

    // Dashboard/Statistics
    public function dashboard(): Response
    {
        $stats = [
            'total_books' => LibraryBook::active()->count(),
            'available_books' => LibraryBook::active()->available()->count(),
            'total_loans' => BookLoan::count(),
            'active_loans' => BookLoan::where('status', BookLoan::STATUS_ACTIVE)->count(),
            'overdue_loans' => BookLoan::where('status', BookLoan::STATUS_ACTIVE)
                ->where('due_date', '<', now()->toDateString())->count(),
            'pending_reservations' => BookReservation::where('status', BookReservation::STATUS_PENDING)->count(),
            'categories' => BookCategory::active()->count(),
        ];

        $recentLoans = BookLoan::with(['user', 'book'])
            ->orderByDesc('loan_date')
            ->limit(10)
            ->get();

        $popularBooks = LibraryBook::withCount('loans')
            ->orderByDesc('loans_count')
            ->limit(5)
            ->get();

        return Inertia::render('Library/Dashboard', [
            'stats' => $stats,
            'recentLoans' => $recentLoans,
            'popularBooks' => $popularBooks,
        ]);
    }
}