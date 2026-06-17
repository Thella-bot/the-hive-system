<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-gray-500 mt-1">Here's what's happening with your studies today.</p>
        </div>
        <div class="text-right text-sm text-gray-500">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    {{-- Alert Banner --}}
    @if($remainingBalance > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
        <div class="flex-shrink-0 mt-0.5">
            <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l.818 1.47a1.5 1.5 0 001.823.136l.804-1.173c1.026-.748 2.291-.459 2.877.653l1.153 2.294c.586 1.171.052 2.735-.928 3.546l-1.533 1.266c-.663.547-.663 1.771 0 2.318l1.533 1.266c.98.811 1.514 2.375.928 3.546l-1.153 2.294c-.586 1.112-1.851 1.401-2.877.653l-.804-1.173a1.5 1.5 0 00-1.823.136l-.818 1.47c-.764 1.36-2.721 1.36-3.486 0l-.818-1.47a1.5 1.5 0 00-1.823-.136l-.804 1.173c-1.026.748-2.291.459-2.877-.653l-1.153-2.294c-.586-1.171-.052-2.735.928-3.546l1.533-1.266c.663-.547.663-1.771 0-2.318l-1.533-1.266c-.98-.811-1.514-2.375-.928-3.546l1.153-2.294c.586-1.112 1.851-1.401 2.877-.653l.804 1.173a1.5 1.5 0 001.823-.136l.818-1.47zM10 13a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm text-amber-800">
                You have <strong>{{ number_format($remainingBalance, 2) }}</strong> in outstanding fees.
                <a href="#fees" class="font-medium underline hover:text-amber-700">View invoices</a>
            </p>
        </div>
    </div>
    @endif

    {{-- Quick Stats --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Overview</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Total Modules</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalModules }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Total Submissions</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalSubmissions }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Pending</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingSubmissions }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Avg Grade</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $averageGrade ?? 'N/A' }}%</p>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pending Submissions --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Pending Submissions</h3>
            </div>
            <div class="p-5">
                <ul class="divide-y divide-gray-100">
                    @forelse($pendingSubmissionsList as $submission)
                        <li class="py-3 -mx-5 px-5 hover:bg-gray-50 transition-colors">
                            <a href="{{ route('hive.gradables.show', $submission->gradable) }}" class="block">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $submission->gradable->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $submission->gradable->module->name }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-gray-500">Due</p>
                                        <p class="text-sm text-gray-700">{{ $submission->gradable->due_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="py-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">No pending submissions</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Upcoming Assessments --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Upcoming Assessments</h3>
            </div>
            <div class="p-5">
                <ul class="divide-y divide-gray-100">
                    @forelse($upcomingAssessments as $assessment)
                        <li class="py-3 -mx-5 px-5 hover:bg-gray-50 transition-colors">
                            <a href="{{ route('hive.gradables.show', $assessment) }}" class="block">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $assessment->title }}</p>
                                            @if($assessment->is_due_soon)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Due Soon</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $assessment->module->name }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-gray-500">{{ $assessment->due_date->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="py-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">No upcoming assessments</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- My Programme --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">My Programme</h3>
            </div>
            <div class="p-5">
                @if($programme)
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-hbci-warm-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-hbci-warm-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-medium text-gray-900">{{ $programme->name }}</p>
                            <p class="text-sm text-gray-500">{{ $programme->programme_code }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Completed Modules</span>
                            <span class="font-medium text-gray-900">{{ $completedModules }} of {{ $totalModules }}</span>
                        </div>
                        <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-hbci-warm-500 rounded-full" style="width: {{ $totalModules > 0 ? ($completedModules / $totalModules * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No programme assigned.</p>
                @endif
            </div>
        </div>

        {{-- Recent Announcements --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Announcements</h3>
                <a href="{{ route('hive.announcements.index') }}" class="text-sm text-hbci-warm-600 hover:text-hbci-warm-700 font-medium">View all</a>
            </div>
            <div class="p-5">
                <ul class="divide-y divide-gray-100">
                    @forelse($announcements as $announcement)
                        <li class="py-3 -mx-5 px-5 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $announcement->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $announcement->body }}</p>
                                </div>
                                <p class="text-xs text-gray-400 flex-shrink-0">{{ $announcement->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24m0-2.744a6 6 0 11-12 0V5.882m12 0v13.24a6 6 0 0012 0V5.882" />
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">No recent announcements</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Fees Section --}}
    <div id="fees">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Fees Overview</h3>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Total Fees</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalFees, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Total Paid</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($totalPaid, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-sm text-gray-500 font-medium">Outstanding</p>
                <p class="text-2xl font-bold {{ $remainingBalance > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ number_format($remainingBalance, 2) }}</p>
            </div>
        </div>

        {{-- Invoices Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">My Invoices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $invoice['invoice_number'] }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($invoice['type']) }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $invoice['description'] ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($invoice['amount'], 2) }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-900 text-right">{{ number_format($invoice['total_paid'], 2) }}</td>
                                <td class="px-5 py-3.5 text-sm text-right {{ $invoice['balance'] > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($invoice['balance'], 2) }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">
                                    {{ $invoice['due_date'] ? $invoice['due_date']->format('M d, Y') : '-' }}
                                    @if($invoice['is_overdue'])
                                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($invoice['is_paid'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Paid</span>
                                    @elseif($invoice['is_overdue'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Overdue</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $invoice['status_label'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>