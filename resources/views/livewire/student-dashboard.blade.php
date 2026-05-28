<div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">Upcoming Deadlines</h3>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($upcomingDeadlines as $assignment)
                            <li class="py-4">
                                <div class="flex space-x-3">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium">{{ $assignment->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $assignment->due_date->diffForHumans() }}</p>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $assignment->module->name }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No upcoming deadlines.
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
                <h3 class="text-lg font-medium text-gray-900">Upcoming Tests</h3>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($upcomingTests as $test)
                            <li class="py-4">
                                <div class="flex space-x-3">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium">{{ $test->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $test->due_date->diffForHumans() }}</p>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $test->module->name }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No upcoming tests.
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

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900">Recent Grades</h3>
                <div class="mt-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse($recentGrades as $submission)
                            <li class="py-4">
                                <div class="flex space-x-3">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium">{{ $submission->assignment->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $submission->grade }}%</p>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $submission->assignment->module->name }}</p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            No recent grades.
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
                <h3 class="text-lg font-medium text-gray-900">Recent Announcements</h3>
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