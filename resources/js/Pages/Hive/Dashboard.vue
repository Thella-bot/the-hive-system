<template>
  <HiveLayout title="Dashboard" description="Welcome back! Here's what's happening today.">
    <!-- Admin Dashboard -->
    <div v-if="isAdmin">
      <!-- Stats Row 1 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link :href="route('hive.students.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <UsersIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ totalStudents }}</p>
              <p class="text-sm text-amber-600 dark:text-amber-400">Total Students</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.applications.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <UserPlusIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700 dark:text-orange-400">{{ pendingApplications }}</p>
              <p class="text-sm text-orange-600 dark:text-orange-400">Pending Applications</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.staff.index')" class="bg-gradient-to-br from-amber-50 to-amber-200 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/30 dark:to-amber-800/30">
          <div class="flex items-center">
            <div class="p-3 bg-amber-700 rounded-lg">
              <AcademicCapIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ totalInstructors }}</p>
              <p class="text-sm text-amber-600 dark:text-amber-400">Instructors</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.programmes.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <BookOpenIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700 dark:text-orange-400">{{ totalProgrammes }}</p>
              <p class="text-sm text-orange-600 dark:text-orange-400">Programmes</p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Stats Row 2 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link :href="route('hive.modules.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <RectangleStackIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ totalCourses }}</p>
              <p class="text-sm text-amber-600 dark:text-amber-400">Total Modules</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <CalendarDaysIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700 dark:text-orange-400">{{ pendingLeaveRequests }}</p>
              <p class="text-sm text-orange-600 dark:text-orange-400">Pending Leaves</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.documents.module-select')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-amber-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-amber-700 dark:text-amber-400">{{ totalDocuments }}</p>
              <p class="text-sm text-amber-600 dark:text-amber-400">Documents</p>
            </div>
          </div>
        </Link>

        <Link :href="route('hive.users.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-orange-600 rounded-lg">
              <UsersIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-3xl font-bold text-orange-700 dark:text-orange-400">{{ totalUsers }}</p>
              <p class="text-sm text-orange-600 dark:text-orange-400">Total Users</p>
            </div>
          </div>
        </Link>
      </div>

      <!-- Quick Access -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm dark:bg-gray-800">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Quick Actions</h3>
          <div class="grid grid-cols-2 gap-4">
            <Link :href="route('hive.users.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
              <UserPlusIcon class="h-5 w-5 text-amber-600 mr-3 dark:text-amber-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Add User</span>
            </Link>
            <Link :href="route('hive.announcements.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
              <MegaphoneIcon class="h-5 w-5 text-amber-600 mr-3 dark:text-amber-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Post Announcement</span>
            </Link>
            <Link :href="route('hive.documents.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
              <DocumentIcon class="h-5 w-5 text-amber-600 mr-3 dark:text-amber-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Upload Document</span>
            </Link>
            <Link :href="route('hive.gradables.create')" class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
              <BookOpenIcon class="h-5 w-5 text-amber-600 mr-3 dark:text-amber-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Create Assessment</span>
            </Link>
            <Link v-if="isSuperAdmin" :href="route('log-viewer')" target="_blank" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition dark:bg-orange-900/20 dark:hover:bg-orange-900/30">
              <RectangleStackIcon class="h-5 w-5 text-orange-600 mr-3 dark:text-orange-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">View System Logs</span>
            </Link>
            <Link v-if="isSuperAdmin" :href="route('hive.admin.approve-users')" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition dark:bg-orange-900/20 dark:hover:bg-orange-900/30">
              <UsersIcon class="h-5 w-5 text-orange-600 mr-3 dark:text-orange-400"/>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Pending Approvals</span>
            </Link>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm dark:bg-gray-800">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Recent Activity</h3>
          <div v-if="recentUsers && recentUsers.length" class="space-y-3">
            <div v-for="user in recentUsers.slice(0, 4)" :key="user.id" class="flex items-center justify-between p-3 bg-amber-50 rounded-lg dark:bg-amber-900/20">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-amber-200 rounded-full flex items-center justify-center text-amber-800 font-semibold text-sm dark:bg-amber-700 dark:text-amber-200">
                  {{ user.name.charAt(0) }}
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-800 dark:text-white">{{ user.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                </div>
              </div>
              <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(user.created_at) }}</span>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm dark:text-gray-400">No recent activity</p>
        </div>
      </div>

      <div class="mt-8 bg-white p-6 rounded-xl shadow-sm dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">User Distribution</h3>
        <UserDistributionChart
            :totalStudents="totalStudents"
            :totalInstructors="totalInstructors"
            :totalStaff="totalStaff"
        />
      </div>
    </div>

    <!-- Instructor Dashboard -->
    <div v-if="isInstructor" class="space-y-6">
      <!-- Stats Row -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <Link :href="route('hive.modules.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
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

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-5 rounded-xl dark:from-orange-900/20 dark:to-orange-800/20">
          <div class="flex items-center">
            <div class="p-2 bg-orange-600 rounded-lg">
              <UsersIcon class="h-5 w-5 text-white"/>
            </div>
            <div class="ml-3">
              <p class="text-2xl font-bold text-orange-700">{{ myStudents || 0 }}</p>
              <p class="text-xs text-orange-600">Students</p>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 p-5 rounded-xl dark:from-red-900/20 dark:to-red-800/20">
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

        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 rounded-xl dark:from-amber-900/20 dark:to-amber-800/20">
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

        <div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl dark:from-green-900/20 dark:to-green-800/20">
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

      <!-- Data Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upcoming Assessments -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Upcoming Assessments</h3>
            <Link :href="route('hive.gradables.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium dark:text-amber-400">View all</Link>
          </div>
          <div v-if="upcomingAssessments && upcomingAssessments.length" class="space-y-3">
            <div v-for="assessment in upcomingAssessments" :key="assessment.id" class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
              <p class="font-medium text-gray-800 text-sm dark:text-white">{{ assessment.title }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ assessment.module.name }} - Due: {{ formatDate(assessment.due_date) }}</p>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm text-center py-4 dark:text-gray-400">No upcoming assessments.</p>
        </div>

        <!-- Recent Grades -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Grades</h3>
            <Link :href="route('hive.grades.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium dark:text-amber-400">View all</Link>
          </div>
          <div v-if="recentGrades && recentGrades.length" class="space-y-3">
            <div v-for="submission in recentGrades" :key="submission.id" class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium text-gray-800 text-sm dark:text-white">{{ submission.gradable.title }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ submission.gradable.module.name }}</p>
                </div>
                <span class="font-bold text-gray-800 dark:text-white">{{ submission.grade }}%</span>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm text-center py-4 dark:text-gray-400">No recent grades.</p>
        </div>

        <!-- My Modules -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">My Modules</h3>
            <Link :href="route('hive.modules.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium dark:text-amber-400">View all</Link>
          </div>
          <div v-if="programme && programme.modules.length" class="space-y-3">
            <Link v-for="module in programme.modules" :key="module.id" :href="route('hive.modules.show', module.id)" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600">
              <p class="font-medium text-gray-800 text-sm dark:text-white">{{ module.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ module.code }}</p>
            </Link>
          </div>
          <p v-else class="text-gray-500 text-sm text-center py-4 dark:text-gray-400">You are not enrolled in any modules.</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Link :href="route('hive.gradables.create')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
          <PlusIcon class="h-5 w-5 mr-2 text-amber-600 dark:text-amber-400"/>Create Assessment
        </Link>
        <Link :href="route('hive.documents.create')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
          <DocumentIcon class="h-5 w-5 mr-2 text-amber-600 dark:text-amber-400"/>Upload Material
        </Link>
        <Link :href="route('hive.chat.index')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
          <ChatBubbleLeftIcon class="h-5 w-5 mr-2 text-amber-600 dark:text-amber-400"/>Module Chat
        </Link>
        <Link :href="route('hive.grades.index')" class="flex items-center justify-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
          <ChartBarIcon class="h-5 w-5 mr-2 text-amber-600 dark:text-amber-400"/>Full Gradebook
        </Link>
      </div>

      <!-- Class Averages Chart -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Class Averages</h3>
        <ClassAveragesChart :classAverages="classAverages" />
      </div>
    </div>

    <!-- Non-Academic Staff Dashboard -->
    <div v-if="isNonAcademicStaff" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Link :href="route('hive.applications.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
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

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
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

        <Link :href="route('hive.leaves.index')" class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-amber-900/20 dark:to-amber-800/20">
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

        <Link :href="route('hive.payslips.index')" class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-orange-900/20 dark:to-orange-800/20">
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

      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Leave Requests Overview</h3>
        <LeaveRequestsChart v-if="leaveRequestsByType" :leave-requests-by-type="leaveRequestsByType" />
        <p v-else class="text-gray-500 text-sm py-4 text-center dark:text-gray-400">No leave request data available.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Admissions Queue</h3>
              <Link :href="route('hive.applications.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium dark:text-amber-400">View all</Link>
            </div>
            <div v-if="recentApplications && recentApplications.length" class="space-y-2">
              <div v-for="application in recentApplications" :key="application.id" class="flex items-center justify-between p-3 bg-amber-50 rounded-lg dark:bg-amber-900/20">
                <div>
                  <p class="font-medium text-gray-800 text-sm dark:text-white">{{ application.name || application.user?.name || 'Applicant' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ application.programme?.name || 'Programme not selected' }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full capitalize" :class="getApplicationStatusClass(application.status)">
                  {{ application.status || 'pending' }}
                </span>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-6 text-center dark:text-gray-400">No recent applications</p>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Documents</h3>
              <Link :href="route('hive.documents.index')" class="text-sm text-amber-600 hover:text-amber-700 font-medium dark:text-amber-400">Repository</Link>
            </div>
            <div v-if="recentDocuments && recentDocuments.length" class="space-y-2">
              <div v-for="doc in recentDocuments" :key="doc.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                <div class="flex items-center min-w-0">
                  <DocumentIcon class="h-5 w-5 text-amber-600 flex-shrink-0 dark:text-amber-400"/>
                  <div class="ml-3 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate dark:text-white">{{ doc.title || 'Untitled' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.category || doc.module?.name || 'Document' }}</p>
                  </div>
                </div>
                <Link :href="route('hive.documents.show', doc.id)" class="text-amber-600 hover:text-amber-700 text-sm font-medium dark:text-amber-400">View</Link>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-6 text-center dark:text-gray-400">No documents available</p>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Quick Actions</h3>
            </div>
            <div class="grid gap-3">
              <Link :href="route('hive.announcements.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
                <MegaphoneIcon class="h-5 w-5 mr-3 text-amber-600 dark:text-amber-400"/>Post Announcement
              </Link>
              <Link :href="route('hive.events.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
                <CalendarDaysIcon class="h-5 w-5 mr-3 text-amber-600 dark:text-amber-400"/>Create Event
              </Link>
              <Link :href="route('hive.documents.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
                <DocumentIcon class="h-5 w-5 mr-3 text-amber-600 dark:text-amber-400"/>Upload Document
              </Link>
              <Link :href="route('hive.leaves.create')" class="flex items-center p-3 bg-amber-50 rounded-lg hover:bg-amber-100 transition text-sm font-medium text-gray-700 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 dark:text-gray-200">
                <PlusIcon class="h-5 w-5 mr-3 text-amber-600 dark:text-amber-400"/>Request Leave
              </Link>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Upcoming Events</h3>
            <div v-if="upcomingEvents && upcomingEvents.length" class="space-y-3">
              <div v-for="event in upcomingEvents" :key="event.id" class="p-3 bg-amber-50 rounded-lg dark:bg-amber-900/20">
                <p class="font-medium text-gray-800 text-sm dark:text-white">{{ event.title }}</p>
                <p class="text-xs text-gray-500 mt-1 dark:text-gray-400">{{ formatDate(event.start) }}</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm dark:text-gray-400">No upcoming events</p>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 dark:text-white">Announcements</h3>
            <div v-if="announcements && announcements.length" class="space-y-4">
              <div v-for="announcement in announcements" :key="announcement.id" class="border-b border-amber-100 pb-3 dark:border-gray-700">
                <p class="font-medium text-gray-800 text-sm dark:text-white">{{ announcement.title }}</p>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2 dark:text-gray-400">{{ announcement.body?.substring(0, 80) }}...</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm dark:text-gray-400">No announcements</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Dashboard -->
    <div v-if="isStudent" class="space-y-6">
      <!-- Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl dark:from-amber-900/20 dark:to-amber-800/20">
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

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl dark:from-orange-900/20 dark:to-orange-800/20">
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

        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl dark:from-amber-900/20 dark:to-amber-800/20">
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

        <a :href="route('hive.transcript.download', { student: currentUser.id })" download class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-green-900/20 dark:to-green-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-green-600 rounded-lg">
              <DocumentIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-lg font-bold text-green-700">Transcript</p>
              <p class="text-sm text-green-600">Download PDF</p>
            </div>
          </div>
        </a>

        <a :href="route('hive.registration.proof')" download class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl block hover:shadow-lg transition-shadow dark:from-green-900/20 dark:to-green-800/20">
          <div class="flex items-center">
            <div class="p-3 bg-green-600 rounded-lg">
              <ClipboardDocumentCheckIcon class="h-6 w-6 text-white"/>
            </div>
            <div class="ml-4">
              <p class="text-lg font-bold text-green-700">Registration</p>
              <p class="text-sm text-green-600">Proof of Reg.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Progress Tracker -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <ProgressTracker
            :completed-modules="completedModules || 0"
            :total-modules="totalModules || 0"
            :module-progress="moduleProgress || []"
            :average-grade="averageGrade"
            :pending-assessments="pendingSubmissions || 0"
          />
        </div>
        <div>
          <TodoWidget :initial-tasks="tasks || []" />
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <!-- Upcoming Assessments -->
          <div class="bg-white p-6 rounded-xl shadow-sm dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Upcoming Assessments</h3>
            <div v-if="upcomingAssessments && upcomingAssessments.length" class="space-y-3">
              <div v-for="a in upcomingAssessments" :key="a.id" class="flex items-center justify-between p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
                <div>
                  <p class="font-medium text-gray-800 dark:text-white">{{ a.title }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ a.module?.name || 'Unknown Module' }}</p>
                </div>
                <div class="text-right">
                  <span class="px-3 py-1 text-sm rounded-full" :class="getTypeClass(a.type)">{{ formatType(a.type) }}</span>
                  <p class="text-xs text-gray-500 mt-1 dark:text-gray-400">Due {{ formatDate(a.due_date) }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm py-4 text-center dark:text-gray-400">No upcoming assessments</p>
          </div>

          <!-- Grade Analytics -->
          <GradeAnalytics
            :grades="gradeHistory || []"
            :average-grade="averageGrade"
          />
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Announcements -->
          <div class="bg-white p-6 rounded-xl shadow-sm dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Announcements</h3>
            <div v-if="announcements && announcements.length" class="space-y-4">
              <div v-for="a in announcements" :key="a.id" class="border-b border-amber-100 dark:border-gray-700 pb-3">
                <p class="font-medium text-gray-800 dark:text-white text-sm">{{ a.title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ a.body?.substring(0, 80) }}...</p>
              </div>
            </div>
            <p v-else class="text-gray-500 text-sm dark:text-gray-400">No announcements</p>
          </div>

          <!-- Academic Calendar -->
          <AcademicCalendar
            :events="upcomingEvents || []"
            :deadlines="pendingSubmissionsList || []"
          />

          <!-- Bookmarks -->
          <Bookmarks :bookmarks="bookmarks || []" />
        </div>
      </div>

      <!-- Recent Activity -->
      <RecentActivity :activities="recentActivities || []" />
    </div>

    <!-- Quick Actions FAB -->
    <QuickActions :actions="quickActions" />
  </HiveLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import LeaveRequestsChart from './LeaveRequestsChart.vue';
import ClassAveragesChart from './ClassAveragesChart.vue';
import UserDistributionChart from './UserDistributionChart.vue';
import ProgressTracker from '@/Components/ProgressTracker.vue';
import GradeAnalytics from '@/Components/GradeAnalytics.vue';
import AcademicCalendar from '@/Components/AcademicCalendar.vue';
import TodoWidget from '@/Components/TodoWidget.vue';
import RecentActivity from '@/Components/RecentActivity.vue';
import Bookmarks from '@/Components/Bookmarks.vue';
import QuickActions from '@/Components/QuickActions.vue';
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
    myStudents: Number,
    totalAssessments: Number,
    upcomingAssessments: {
        type: Array,
        default: () => [],
    },
    recentlyGraded: Array,
    recentDocuments: Array,
    classAverage: Number,
    classAverages: Object,

    // Non-academic staff
    leaveRequestsByType: Object,
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
    moduleProgress: {
        type: Array,
        default: () => [],
    },
    gradeHistory: {
        type: Array,
        default: () => [],
    },
    tasks: {
        type: Array,
        default: () => [],
    },
    bookmarks: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array,
        default: () => [],
    },
    pendingSubmissionsList: {
        type: Array,
        default: () => [],
    },

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



const getApplicationStatusClass = (status) => {
    const classes = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};

const quickActions = computed(() => {
    if (isAdmin.value || isSuperAdmin.value) {
        return [
            { label: 'Add User', href: route('hive.users.create'), icon: 'UserPlusIcon' },
            { label: 'Post Announcement', href: route('hive.announcements.create'), icon: 'MegaphoneIcon' },
            { label: 'Upload Document', href: route('hive.documents.create'), icon: 'DocumentIcon' },
            { label: 'Create Assessment', href: route('hive.gradables.create'), icon: 'ClipboardDocumentCheckIcon' },
            { label: 'Create Event', href: route('hive.events.create'), icon: 'CalendarDaysIcon' },
        ];
    }
    if (isInstructor.value) {
        return [
            { label: 'Create Assessment', href: route('hive.gradables.create'), icon: 'ClipboardDocumentCheckIcon' },
            { label: 'Upload Material', href: route('hive.documents.create'), icon: 'DocumentIcon' },
            { label: 'Module Chat', href: route('hive.chat.index'), icon: 'ChatBubbleLeftIcon' },
            { label: 'Gradebook', href: route('hive.grades.index'), icon: 'ChartBarIcon' },
        ];
    }
    if (isNonAcademicStaff.value) {
        return [
            { label: 'Post Announcement', href: route('hive.announcements.create'), icon: 'MegaphoneIcon' },
            { label: 'Create Event', href: route('hive.events.create'), icon: 'CalendarDaysIcon' },
            { label: 'Upload Document', href: route('hive.documents.create'), icon: 'DocumentIcon' },
            { label: 'Request Leave', href: route('hive.leaves.create'), icon: 'CalendarDaysIcon' },
        ];
    }
    return [
        { label: 'My Modules', href: route('hive.modules.index'), icon: 'BookOpenIcon' },
        { label: 'My Grades', href: route('hive.grades.index'), icon: 'ChartBarIcon' },
        { label: 'Chat', href: route('hive.chat.index'), icon: 'ChatBubbleLeftIcon' },
    ];
});

const getTypeClass = (type) => {
    const classes = {
        quiz: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        test: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
        assignment: 'bg-amber-200 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
        mid_term_exam: 'bg-orange-200 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
        final_exam: 'bg-amber-300 text-amber-900 dark:bg-amber-900/60 dark:text-amber-100',
    };
    return classes[type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};
</script>