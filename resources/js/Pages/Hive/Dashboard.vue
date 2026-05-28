<template>
  <HiveLayout title="Dashboard" description="Welcome back! Here's what's happening today.">
    <!-- Admin Dashboard -->
    <div v-if="isAdmin">
      <!-- Stats Row 1 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link :href="route('hive.students.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <UsersIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ totalStudents }}</p>
              <p class="text-sm text-amber-600">Total Students</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.applications.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <UserPlusIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ pendingApplications }}</p>
              <p class="text-sm text-orange-600">Pending Applications</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.staff.index')" class="bg-gradient-to-br from-amber-50 to-amber-200 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-700 rounded-lg">
              <AcademicCapIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ totalInstructors }}</p>
              <p class="text-sm text-amber-600">Instructors</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.programmes.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <BookOpenIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ totalProgrammes }}</p>
              <p class="text-sm text-orange-600">Programmes</p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Stats Row 2 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link :href="route('hive.modules.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <RectangleStackIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ totalCourses }}</p>
              <p class="text-sm text-amber-600">Total Modules</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <CalendarDaysIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ pendingLeaveRequests }}</p>
              <p class="text-sm text-orange-600">Pending Leaves</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.documents.module-select')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ totalDocuments }}</p>
              <p class="text-sm text-amber-600">Documents</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.users.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <UsersIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ totalUsers }}</p>
              <p class="text-sm text-orange-600">Total Users</p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Quick Access -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
          <div class="grid grid-cols-2 gap-4">
            <Link :href="route('hive.users.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
              <UserPlusIcon class="h-5 w-5 text-amber-600 mr-3"/>
              <span class="text-sm font-medium">Add User</span>
            </Link>
            <Link :href="route('hive.announcements.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
              <MegaphoneIcon class="h-5 w-5 text-amber-600 mr-3"/>
              <span class="text-sm font-medium">Post Announcement</span>
            </Link>
            <Link :href="route('hive.documents.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
              <DocumentIcon class="h-5 w-5 text-amber-600 mr-3"/>
              <span class="text-sm font-medium">Upload Document</span>
            </Link>
            <Link :href="route('hive.gradables.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
              <BookOpenIcon class="h-5 w-5 text-amber-600 mr-3"/>
              <span class="text-sm font-medium">Create Assessment</span>
            </Link>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
          <div v-if="recentUsers && recentUsers.length" class="space-y-3">
            <div v-for="user in recentUsers.slice(0, 4)" :key="user.id" class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-amber-200 rounded-full flex items-center justify-center text-amber-800 font-semibold text-sm">
                  {{ user.name.charAt(0) }}
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-800">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">{{ user.email }}</p>
                </div>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(user.created_at) }}</span>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm">No recent activity</p>
        </div>
      </div>
    </div>

    <!-- Instructor Dashboard -->
    <div v-if="isInstructor" class="space-y-6">
      <!-- Stats Row -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <Link :href="route('hive.modules.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-2 bg-amber-600 rounded-lg">
              <BookOpenIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-amber-700">{{ myModulesCount }}</p>
              <p class="text-xs text-amber-600">My Modules</p>
            </div>
          </div>
        </Link>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-5 rounded-xl">
          <div class="flex items-center">
            <div class="p-2 bg-orange-600 rounded-lg">
              <UsersIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-orange-700">{{ totalStudents || 0 }}</p>
              <p class="text-xs text-orange-600">Students</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 p-5 rounded-xl">
          <div class="flex items-center">
            <div class="p-2 bg-red-600 rounded-lg">
              <ClipboardDocumentCheckIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-red-700">{{ pendingGrades || 0 }}</p>
              <p class="text-xs text-red-600">Pending Grades</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 rounded-xl">
          <div class="flex items-center">
            <div class="p-2 bg-amber-600 rounded-lg">
              <DocumentIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-amber-700">{{ totalAssessments || 0 }}</p>
              <p class="text-xs text-amber-600">Assessments</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl">
          <div class="flex items-center">
            <div class="p-2 bg-green-600 rounded-lg">
              <ChartBarIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-green-700">{{ classAverage ? classAverage.toFixed(1) : 'N/A' }}</p>
              <p class="text-xs text-green-600">Avg Grade</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Columns: Submissions + Upcoming -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Submissions to Grade -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pending Submissions</h3>
            <Link v-if="recentSubmissions?.length" :href="route('hive.grades.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
              View all →
            </Link>
          </div>
          <div v-if="recentSubmissions && recentSubmissions.length" class="space-y-2">
            <div v-for="s in recentSubmissions.slice(0, 6)" :key="s.id" class="flex items-center justify-between p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
              <div class="flex items-center">
                <div class="w-9 h-9 bg-amber-200 rounded-full flex items-center justify-center text-amber-800 font-semibold text-sm">
                  {{ s.student?.name?.charAt(0) || '?' }}
                </div>
                <div class="ml-3">
                  <p class="font-medium text-gray-800 text-sm">{{ s.student?.name || 'Unknown' }}</p>
                  <p class="text-xs text-gray-500">{{ s.gradable?.title }}</p>
                </div>
              </div>
              <Link :href="route('hive.grades.manage', s.gradable?.module_id)" class="px-3 py-1.5 bg-amber-600 text-white text-xs rounded-lg hover:bg-amber-700 transition">
                Grade
              </Link>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm py-6 text-center">No pending submissions</p>
        </div>

        <!-- Upcoming Assessments -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Upcoming Assessments</h3>
            <Link :href="route('hive.gradables.create')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
              + New
            </Link>
          </div>
          <div v-if="upcomingAssessments && upcomingAssessments.length" class="space-y-2">
            <div v-for="a in upcomingAssessments.slice(0, 6)" :key="a.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-amber-50 transition">
              <div>
                <p class="font-medium text-gray-800 text-sm">{{ a.title }}</p>
                <p class="text-xs text-gray-500">{{ a.module?.name }}</p>
              </div>
              <div class="text-right">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(a.type)">{{ formatType(a.type) }}</span>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(a.due_date) }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm py-6 text-center">No upcoming assessments</p>
        </div>
      </div>

      <!-- Bottom Row: Recently Graded & Documents -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recently Graded -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recently Graded</h3>
          <div v-if="recentlyGraded && recentlyGraded.length" class="space-y-2">
            <div v-for="s in recentlyGraded.slice(0, 4)" :key="s.id" class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-200 rounded-full flex items-center justify-center text-green-800 font-semibold text-sm">
                  {{ s.student?.name?.charAt(0) || '?' }}
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-800">{{ s.student?.name || 'Unknown' }}</p>
                  <p class="text-xs text-gray-500">{{ s.gradable?.title }}</p>
                </div>
              </div>
              <span class="text-sm font-bold text-green-700">{{ s.grade }}/{{ s.gradable?.max_marks || 100 }}</span>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm py-4 text-center">No recently graded submissions</p>
        </div>

        <!-- Recent Documents -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Materials</h3>
          <div v-if="recentDocuments && recentDocuments.length" class="space-y-2">
            <div v-for="doc in recentDocuments.slice(0, 4)" :key="doc.id" class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
              <div class="flex items-center">
                <DocumentIcon class="h-5 w-5 text-blue-600"/>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-800">{{ doc.title || 'Untitled' }}</p>
                  <p class="text-xs text-gray-500">{{ doc.module?.name }}</p>
                </div>
              </div>
              <Link v-if="doc.latestVersion?.file_path" :href="route('hive.documents.show', doc.id)" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                View
              </Link>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm py-4 text-center">No recent materials</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Link :href="route('hive.gradables.create')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
          <PlusIcon class="h-5 w-5 mr-2 text-amber-600"/>Create Assessment
        </Link>
        <Link :href="route('hive.documents.create')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
          <DocumentIcon class="h-5 w-5 mr-2 text-amber-600"/>Upload Material
        </Link>
        <Link :href="route('hive.chat.index')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
          <ChatBubbleLeftIcon class="h-5 w-5 mr-2 text-amber-600"/>Module Chat
        </Link>
        <Link :href="route('hive.grades.index')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
          <ChartBarIcon class="h-5 w-5 mr-2 text-amber-600"/>Full Gradebook
        </Link>
      </div>
    </div>

    <!-- Non-Academic Staff Dashboard -->
    <div v-if="isNonAcademicStaff" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Link :href="route('hive.applications.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <UserPlusIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ pendingApplications || 0 }}</p>
              <p class="text-sm text-amber-600">Pending Applications</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <CalendarDaysIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ staffLeaveBalance ?? 0 }}</p>
              <p class="text-sm text-orange-600">Leave Balance</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-amber-700 rounded-lg">
              <ClipboardDocumentCheckIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ staffPendingLeaveRequests || 0 }}</p>
              <p class="text-sm text-amber-600">Pending Leave</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.payslips.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-lg font-bold text-orange-700">Payslips</p>
              <p class="text-sm text-orange-600">{{ staffLatestPayslip ? formatDate(staffLatestPayslip.pay_period_end) : 'No payslip yet' }}</p>
            </div>
          </div>
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Admissions Queue</h3>
              <Link :href="route('hive.applications.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">View all</Link>
            </div>
            <div v-if="recentApplications && recentApplications.length" class="space-y-2">
              <div v-for="application in recentApplications" :key="application.id" class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                <div>
                  <p class="font-medium text-gray-800 text-sm">{{ application.name || application.user?.name || 'Applicant' }}</p>
                  <p class="text-xs text-gray-500">{{ application.programme?.name || 'Programme not selected' }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full capitalize" :class="getApplicationStatusClass(application.status)">
                  {{ application.status || 'pending' }}
                </span>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-6 text-center">No recent applications</p>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Recent Documents</h3>
              <Link :href="route('hive.documents.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">Repository</Link>
            </div>
            <div v-if="recentDocuments && recentDocuments.length" class="space-y-2">
              <div v-for="doc in recentDocuments" :key="doc.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center min-w-0">
                  <DocumentIcon class="h-5 w-5 text-amber-600 flex-shrink-0"/>
                  <div class="ml-3 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ doc.title || 'Untitled' }}</p>
                    <p class="text-xs text-gray-500">{{ doc.category || doc.module?.name || 'Document' }}</p>
                  </div>
                </div>
                <Link :href="route('hive.documents.show', doc.id)" class="text-amber-600 hover:text-amber-700 text-sm font-medium">View</Link>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-6 text-center">No documents available</p>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
            </div>
            <div class="grid gap-3">
              <Link :href="route('hive.announcements.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
                <MegaphoneIcon class="h-5 w-5 mr-3 text-amber-600"/>Post Announcement
              </Link>
              <Link :href="route('hive.events.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
                <CalendarDaysIcon class="h-5 w-5 mr-3 text-amber-600"/>Create Event
              </Link>
              <Link :href="route('hive.documents.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
                <DocumentIcon class="h-5 w-5 mr-3 text-amber-600"/>Upload Document
              </Link>
              <Link :href="route('hive.leaves.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700">
                <PlusIcon class="h-5 w-5 mr-3 text-amber-600"/>Request Leave
              </Link>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Events</h3>
            <div v-if="upcomingEvents && upcomingEvents.length" class="space-y-3">
              <div v-for="event in upcomingEvents" :key="event.id" class="p-3 bg-amber-50 rounded-lg">
                <p class="font-medium text-gray-800 text-sm">{{ event.title }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(event.start) }}</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm">No upcoming events</p>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Announcements</h3>
            <div v-if="announcements && announcements.length" class="space-y-4">
              <div v-for="announcement in announcements" :key="announcement.id" class="border-b border-amber-100 pb-3">
                <p class="font-medium text-gray-800 text-sm">{{ announcement.title }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ announcement.body?.substring(0, 80) }}...</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm">No announcements</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Dashboard -->
    <div v-if="isStudent" class="space-y-6">
      <!-- Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <BookOpenIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ totalModules || 0 }}</p>
              <p class="text-sm text-amber-600">Enrolled Modules</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700">{{ pendingSubmissions || 0 }}</p>
              <p class="text-sm text-orange-600">Pending</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <AcademicCapIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700">{{ averageGrade || '--' }}{{ averageGrade ? '%' : '' }}</p>
              <p class="text-sm text-amber-600">Avg Grade</p>
            </div>
          </div>
        </div>

        <Link :href="route('hive.transcript.download', { student: currentUser.id })" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-lg font-bold text-orange-700">Transcript</p>
              <p class="text-sm text-orange-600">Download PDF</p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <!-- Upcoming Assessments -->
          <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Assessments</h3>
            <div v-if="upcomingAssessments && upcomingAssessments.length" class="space-y-3">
              <div v-for="a in upcomingAssessments" :key="a.id" class="flex items-center justify-between p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition">
                <div>
                  <p class="font-medium text-gray-800">{{ a.title }}</p>
                  <p class="text-sm text-gray-500">{{ a.module?.name || 'Unknown Module' }}</p>
                </div>
                <div class="text-right">
                  <span class="px-3 py-1 text-sm rounded-full" :class="getTypeClass(a.type)">{{ formatType(a.type) }}</span>
                  <p class="text-xs text-gray-500 mt-1">Due {{ formatDate(a.due_date) }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-4 text-center">No upcoming assessments</p>
          </div>

          <!-- Recent Grades -->
          <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Grades</h3>
            <div v-if="recentGrades && recentGrades.length" class="space-y-3">
              <div v-for="s in recentGrades" :key="s.id" class="flex items-center justify-between p-4 bg-amber-50 rounded-lg">
                <div>
                  <p class="font-medium text-gray-800">{{ s.gradable?.title || 'Unknown' }}</p>
                  <p class="text-sm text-gray-500">{{ s.gradable?.module?.name || '' }}</p>
                </div>
                <span class="text-2xl font-bold" :class="s.grade >= 50 ? 'text-amber-600' : 'text-orange-600'">
                  {{ s.grade }}%
                </span>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-4 text-center">No grades yet</p>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Announcements -->
          <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Announcements</h3>
            <div v-if="announcements && announcements.length" class="space-y-4">
              <div v-for="a in announcements" :key="a.id" class="border-b border-amber-100 pb-3">
                <p class="font-medium text-gray-800 text-sm">{{ a.title }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ a.body?.substring(0, 80) }}...</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm">No announcements</p>
          </div>

          <!-- Upcoming Events -->
          <div v-if="upcomingEvents && upcomingEvents.length" class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Events</h3>
            <div class="space-y-3">
              <div v-for="e in upcomingEvents.slice(0, 3)" :key="e.id" class="p-3 bg-amber-50 rounded-lg">
                <p class="font-medium text-gray-800 text-sm">{{ e.title }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(e.start_date) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import {
    UsersIcon,
    UserPlusIcon,
    BookOpenIcon,
    AcademicCapIcon,
    MegaphoneIcon,
    DocumentIcon,
    CalendarDaysIcon,
    RectangleStackIcon,
    ClipboardDocumentCheckIcon,
    ChartBarIcon,
    PlusIcon,
    ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';

dayjs.extend(relativeTime);

const props = defineProps({
    // Admin
    totalStudents: Number,
    totalInstructors: Number,
    totalStaff: Number,
    pendingApprovals: Number,
    totalProgrammes: Number,
    totalCourses: Number,
    totalCohorts: Number,
    totalAnnouncements: Number,
    totalDocuments: Number,
    totalGradables: Number,
    pendingLeaveRequests: Number,
    pendingApplications: Number,
    totalUsers: Number,
    newStudentsThisMonth: Number,
    pendingGrades: Number,
    recentUsers: Array,
    recentSubmissions: Array,

    // Instructor
    myModulesCount: Number,
    totalStudents: Number,
    totalAssessments: Number,
    upcomingAssessments: {
        type: Array,
        default: () => [],
    },
    recentlyGraded: Array,
    recentDocuments: Array,
    classAverage: Number,

    // Non-academic staff
    staffLeaveBalance: Number,
    staffPendingLeaveRequests: Number,
    staffApprovedLeaveRequests: Number,
    staffLatestPayslip: Object,
    recentApplications: {
        type: Array,
        default: () => [],
    },

    // Student
    totalModules: Number,
    totalSubmissions: Number,
    pendingSubmissions: Number,
    averageGrade: Number,
    completedModules: Number,
    upcomingEvents: Array,
    pendingSubmissionsList: Array,

    // Common
    recentGrades: {
        type: Array,
        default: () => [],
    },
    announcements: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (date) => dayjs(date).fromNow();
const currentUser = computed(() => usePage().props.auth?.user);

const isAdmin = computed(() => usePage().props.auth?.user?.roles?.some(r => r === 'super-admin' || r === 'school-admin'));
const isSuperAdmin = computed(() => usePage().props.auth?.user?.roles?.includes('super-admin'));
const isInstructor = computed(() => usePage().props.auth?.user?.roles?.some(r => r === 'academic_staff'));
const isNonAcademicStaff = computed(() => usePage().props.auth?.user?.roles?.some(r => r === 'non_academic_staff'));
const isStudent = computed(() => usePage().props.auth?.user?.roles?.some(r => r === 'student'));

const formatType = (type) => {
    const labels = {
        quiz: 'Quiz',
        test: 'Test',
        assignment: 'Assignment',
        mid_term_exam: 'Mid-Term',
        final_exam: 'Final',
    };
    return labels[type] || type;
};

const getTypeClass = (type) => {
    const classes = {
        quiz: 'bg-amber-100 text-amber-700',
        test: 'bg-orange-100 text-orange-700',
        assignment: 'bg-amber-200 text-amber-800',
        mid_term_exam: 'bg-orange-200 text-orange-800',
        final_exam: 'bg-amber-300 text-amber-900',
    };
    return classes[type] || 'bg-gray-100 text-gray-700';
};

const getApplicationStatusClass = (status) => {
    const classes = {
        pending: 'bg-amber-100 text-amber-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};
</script>
