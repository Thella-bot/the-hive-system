<div>
    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Welcome back, {{ auth()->user()->name }}!</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">Pending Submissions</h3>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($pendingSubmissionsList as $submission)
                            <li class="py-4">
                                <a href="{{ route('hive.gradables.show', $submission->gradable) }}" class="block hover:bg-gray-50">
                                    <div class="flex space-x-3">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium">{{ $submission->gradable->title }}</h3>
                                                <p class="text-sm text-gray-500">Due: {{ $submission->gradable->due_date->format('M d, Y') }}</p>
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $submission->gradable->module->name }}</p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No pending submissions.
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Modules</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalModules }}</dd>
                </dl>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Submissions</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalSubmissions }}</dd>
                </dl>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Submissions</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $pendingSubmissions }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">My Programme</h3>
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-900">{{ $programme->name }}</p>
                    <p class="text-sm text-gray-500">{{ $programme->programme_code }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">My Progress</h3>
                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">Average Grade</p>
                        <p class="text-sm text-gray-500">{{ $averageGrade ?? 'N/A' }}%</p>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">Completed Modules</p>
                        <p class="text-sm text-gray-900">{{ $completedModules }} out of {{ $totalModules }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">Upcoming Assessments</h3>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($upcomingAssessments as $assessment)
                            <li class="py-4">
                                <a href="{{ route('hive.gradables.show', $assessment) }}" class="block hover:bg-gray-50">
                                    <div class="flex space-x-3">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium">{{ $assessment->title }}</h3>
                                                <div class="flex items-center">
                                                    @if($assessment->is_due_soon)
                                                        <span class="mr-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Due Soon
                                                        </span>
                                                    @endif
                                                    <p class="text-sm text-gray-500">{{ $assessment->due_date->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $assessment->module->name }}</p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No upcoming assessments.
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <a href="{{ route('hive.announcements.index') }}" class="block hover:bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">Recent Announcements</h3>
                </a>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($announcements as $announcement)
                            <li class="py-4">
                                <div class="flex space-x-3">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium">{{ $announcement->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $announcement->created_at->diffForHumans() }}</p>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $announcement->body }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No recent announcements.
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

