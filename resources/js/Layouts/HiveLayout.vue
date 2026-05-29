<template>
  <div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Emergency Broadcast Modal -->
    <Transition name="fade">
      <div v-if="emergencyAlert" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90">
        <div class="mx-4 w-full max-w-lg rounded-2xl border-2 border-red-500 bg-white p-8 shadow-2xl">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-5xl">🚨</span>
            <div>
              <h2 class="text-2xl font-bold text-red-600 uppercase tracking-wide">Emergency Alert</h2>
              <p class="text-sm text-gray-500">{{ formatDateTime(emergencyAlert.created_at) }}</p>
            </div>
          </div>
          <h3 class="mb-3 text-xl font-bold text-gray-900">{{ emergencyAlert.title }}</h3>
          <p class="mb-6 whitespace-pre-line text-gray-700">{{ emergencyAlert.body }}</p>
          <button @click="emergencyAlert = null"
            class="w-full rounded-lg bg-red-600 px-4 py-3 font-semibold text-white hover:bg-red-700 transition">
            I Have Read This — Dismiss
          </button>
        </div>
      </div>
    </Transition>

    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-hbci-gray text-white transition-transform duration-200 lg:static lg:w-64 lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="px-6 pb-4 pt-6">
        <div class="flex items-start justify-between gap-3">
          <Link :href="route('hive.dashboard')" class="flex flex-1 flex-col items-center gap-3" @click="sidebarOpen = false">
            <img src="/images/hbci-logo-no-text.png" alt="The Hive" class="h-20 w-auto" />
            <div class="text-center">
              <div class="text-[10px] font-bold uppercase tracking-[0.25em] text-amber-400">Honey Bee Culinary Institute</div>
              <div class="mt-0.5 text-[11px] font-semibold tracking-wide text-gray-300">The Hive</div>
            </div>
          </Link>
          <button
            type="button"
            class="rounded-md p-1 text-gray-400 hover:bg-gray-800 hover:text-white lg:hidden"
            aria-label="Close navigation"
            @click="sidebarOpen = false"
          >
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div class="mx-6 border-t border-gray-700" />

      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <template v-for="item in singleItems" :key="item.name">
          <NavItem :href="item.href" :active="item.isActive ? item.isActive() : item.active" @click="sidebarOpen = false">
            <template #icon>
              <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
            </template>
            {{ item.name }}
          </NavItem>
        </template>

        <template v-for="category in categories" :key="category.name">
          <div>
            <button
              type="button"
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
              @click="toggleCategory(category.name)"
            >
              <span class="flex items-center gap-2">
                <component :is="category.icon" v-if="category.icon" class="h-4 w-4" />
                {{ category.name }}
              </span>
              <ChevronDownIcon
                class="h-4 w-4 transform transition-transform duration-200"
                :class="{ 'rotate-180': expandedCategories.includes(category.name) }"
              />
            </button>

            <div v-if="expandedCategories.includes(category.name)" class="mt-1 space-y-1">
              <NavItem
                v-for="child in category.children"
                :key="child.name"
                :href="child.href"
                :target="child.target"
                :active="child.isActive ? child.isActive() : child.active"
                class="pl-9"
                @click="sidebarOpen = false"
              >
                {{ child.name }}
              </NavItem>
            </div>
          </div>
        </template>
      </nav>

      <div class="border-t border-gray-700">
        <div v-if="currentUser" class="p-4">
          <div class="flex items-center gap-3">
            <img
              :src="currentUser.profile_photo_url"
              :alt="currentUser.name"
              class="h-8 w-8 flex-shrink-0 rounded-full object-cover"
            />
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium">{{ currentUser.name }}</p>
              <p class="truncate text-xs capitalize text-gray-400">{{ displayRole }}</p>
            </div>
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="rounded-md p-1 text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
              title="Logout"
            >
              <ArrowRightOnRectangleIcon class="h-5 w-5" />
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
      <header class="flex-shrink-0 border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex min-w-0 items-center gap-4">
            <button
              type="button"
              class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-50 lg:hidden"
              aria-label="Open navigation"
              @click="sidebarOpen = true"
            >
              <Bars3Icon class="h-5 w-5" />
            </button>
            <div class="min-w-0">
              <h1 class="truncate text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
              <p v-if="description" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form class="relative w-full sm:w-80" @submit.prevent="submitSearch">
              <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <input
                v-model="searchQuery"
                type="search"
                autocomplete="off"
                class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                placeholder="Search The Hive"
              />
            </form>

            <div class="flex items-center justify-end gap-2">
              <ThemeToggle />
              <Link
                :href="route('hive.notifications.index')"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                title="Notifications"
              >
                <BellIcon class="h-5 w-5" />
                <span
                  v-if="$page.props.unreadNotificationsCount > 0"
                  class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900"
                >
                  {{ $page.props.unreadNotificationsCount > 9 ? '9+' : $page.props.unreadNotificationsCount }}
                </span>
              </Link>
              <Link
                :href="route('hive.profile.edit')"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                title="Profile"
              >
                <UserCircleIcon class="h-5 w-5" />
              </Link>
              <slot name="header-actions" />
            </div>
          </div>
        </div>
      </header>

      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-4 pt-4 sm:px-6 lg:px-8">
        <div
          v-if="$page.props.flash?.success"
          class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-200"
        >
          <CheckCircleIcon class="h-5 w-5 flex-shrink-0 text-green-500" />
          {{ $page.props.flash.success }}
        </div>
        <div
          v-if="$page.props.flash?.error"
          class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
          {{ $page.props.flash.error }}
        </div>
      </div>

      <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8 dark:bg-gray-900">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import NavItem from '@/Components/NavItem.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import {
  AcademicCapIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  BellIcon,
  BookOpenIcon,
  BriefcaseIcon,
  CalendarDaysIcon,
  ChatBubbleLeftRightIcon,
  CheckCircleIcon,
  ChevronDownIcon,
  ClipboardDocumentCheckIcon,
  Cog6ToothIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  FolderIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  MegaphoneIcon,
  RectangleStackIcon,
  UserCircleIcon,
  UserGroupIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
});

const page = usePage();
const sidebarOpen = ref(false);
const expandedCategories = ref([]);
const searchQuery = ref('');
const emergencyAlert = ref(null);

const formatDateTime = (iso) => {
  return new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
};

onMounted(() => {
  autoExpandActiveCategory();

  if (window.Echo) {
    window.Echo.private('emergency')
      .listen('EmergencyAlert', (e) => {
        emergencyAlert.value = e;
      });
  }
});

const currentUser = computed(() => page.props.auth?.user);
const userRoles = computed(() => currentUser.value?.roles || []);
const isStudent = computed(() => userRoles.value.includes('student'));
const isAcademicStaff = computed(() => userRoles.value.includes('academic_staff'));
const isNonAcademicStaff = computed(() => userRoles.value.includes('non_academic_staff'));
const isStaff = computed(() => isAcademicStaff.value || isNonAcademicStaff.value);
const isAdmin = computed(() => userRoles.value.some((role) => ['super-admin', 'school-admin'].includes(role)));
const needsRegistration = computed(() => currentUser.value?.needs_registration ?? false);
const isRegisteredStudent = computed(() => isStudent.value && !needsRegistration.value);

const displayRole = computed(() => {
  const primaryRole = userRoles.value[0] || 'User';
  return primaryRole.replaceAll('-', ' ').replaceAll('_', ' ');
});

const isActive = (pattern) => (pattern ? route().current(pattern) : false);

// Helper to check if a specific gradable type (module-select) is active
const isGradableTypeActive = (type) => {
  if (!route().current('hive.gradables.module-select')) return false;
  return route().params()?.type === type;
};

const toggleCategory = (categoryName) => {
  expandedCategories.value = expandedCategories.value.includes(categoryName)
    ? expandedCategories.value.filter((name) => name !== categoryName)
    : [...expandedCategories.value, categoryName];
};

const canAccess = (requiredRoles) => {
  if (!requiredRoles || requiredRoles.length === 0) {
    return true;
  }

  return requiredRoles.some((role) => userRoles.value.includes(role));
};

const submitSearch = () => {
  const query = searchQuery.value.trim();

  if (!query) {
    return;
  }

  router.get(route('hive.search'), { query });
};

// ─── Navigation helper ────────────────────────────────────────────────────────

// Deduplicates children across the entire nav by href, keeping the first occurrence.
// Call once at the end after all categories are built.
const buildNav = (allItems) => {
  const seenHrefs = new Set();

  const dedupe = (children) =>
    children
      .filter((child) => {
        if (!child.href || child.target === '_blank') return true;
        if (seenHrefs.has(child.href)) return false;
        seenHrefs.add(child.href);
        return true;
      });

  return allItems
    .map((item) =>
      item.children
        ? { ...item, children: dedupe(item.children) }
        : item
    )
    .filter((item) => item.single || item.children?.length);
};

// ─── Per-role builders (each returns an array of category objects) ────────────

const dashNav = [
  {
    name: 'Dashboard',
    href: route('hive.dashboard'),
    active: 'hive.dashboard',
    icon: HomeIcon,
    single: true,
  },
];

const studentNav = () => {
  if (needsRegistration.value) {
    return [{
      name: 'Registration',
      href: route('hive.registration.index'),
      active: 'hive.registration.*',
      icon: ClipboardDocumentCheckIcon,
      single: true,
    }];
  }

  if (!isRegisteredStudent.value) return [];

  return [
    {
      name: 'My Learning',
      icon: AcademicCapIcon,
      children: [
        { name: 'My Modules', href: route('hive.modules.index'), active: 'hive.modules.*', roles: ['student'] },
        { name: 'My Grades', href: route('hive.grades.index'), active: 'hive.grades.*', roles: ['student'] },
        { name: 'My Transcript', href: route('hive.transcript.index'), active: 'hive.transcript.*', roles: ['student'] },
        { name: 'Enrollment', href: route('hive.enrollment.index'), active: 'hive.enrollment.*', roles: ['student'] },
        { name: 'Student ID Card', href: route('hive.student-id'), active: 'hive.student-id', roles: ['student'] },
      ],
    },
    {
      name: 'Assessments',
      icon: ClipboardDocumentCheckIcon,
      children: [
        { name: 'Quizzes', href: route('hive.gradables.module-select', { type: 'quiz' }), isActive: () => isGradableTypeActive('quiz'), roles: ['student'] },
        { name: 'Tests', href: route('hive.gradables.module-select', { type: 'test' }), isActive: () => isGradableTypeActive('test'), roles: ['student'] },
        { name: 'Assignments', href: route('hive.gradables.module-select', { type: 'assignment' }), isActive: () => isGradableTypeActive('assignment'), roles: ['student'] },
        { name: 'Mid-Term Exams', href: route('hive.gradables.module-select', { type: 'mid-term_exam' }), isActive: () => isGradableTypeActive('mid-term_exam'), roles: ['student'] },
        { name: 'Final Exams', href: route('hive.gradables.module-select', { type: 'final_exam' }), isActive: () => isGradableTypeActive('final_exam'), roles: ['student'] },
      ],
    },
    {
      name: 'Communication',
      icon: ChatBubbleLeftRightIcon,
      children: [
        { name: 'Chat', href: route('hive.chat.index'), active: 'hive.chat.*', roles: ['student'] },
        { name: 'Polls', href: route('hive.polls.index'), active: 'hive.polls.*', roles: ['student'] },
      ],
    },
  ];
};

const resourcesNav = () => [
  {
    name: 'Resources',
    icon: FolderIcon,
    children: [
      // Everyone gets announcements & events
      { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', roles: ['student', 'academic_staff', 'super-admin', 'school-admin'] },
      { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', roles: ['student', 'academic_staff', 'super-admin', 'school-admin'] },
      // Staff-only document categories
      ...(isStaff.value || isAdmin.value ? [
        { name: 'Documents', href: route('hive.documents.index'), active: 'hive.documents.index', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Browse by Module', href: route('hive.documents.module-select'), active: 'hive.documents.module-select', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Books', href: route('hive.documents.index', { category: 'Book' }), active: 'hive.documents.*', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Notes', href: route('hive.documents.index', { category: 'Notes' }), active: 'hive.documents.*', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Schedules', href: route('hive.documents.index', { category: 'Schedule' }), active: 'hive.documents.*', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Recipes', href: route('hive.documents.index', { category: 'Recipes' }), active: 'hive.documents.*', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
      ] : []),
      // Students get module-scoped docs only
      ...(!isStaff.value && !isAdmin.value ? [
        { name: 'Documents', href: route('hive.documents.module-select'), active: 'hive.documents.*', roles: ['student'] },
      ] : []),
    ],
  },
];

const admissionsNav = () => {
  if (!isAdmin.value && !isNonAcademicStaff.value) return [];

  const children = [
    { name: 'Applications', href: route('hive.applications.index'), active: 'hive.applications.*', roles: ['super-admin', 'school-admin', 'non_academic_staff'] },
    { name: 'Registrations', href: route('hive.registration.index'), active: 'hive.registration.*', roles: ['super-admin', 'school-admin', 'non_academic_staff'] },
  ];

  if (isAdmin.value) {
    children.push(
      { name: 'User Approvals', href: route('hive.admin.approve-users'), active: 'hive.admin.approve-users', roles: ['super-admin', 'school-admin'] },
      { name: 'Import Users', href: route('hive.admin.import-users'), active: 'hive.admin.import-users', roles: ['super-admin', 'school-admin'] },
    );
  }

  return [{ name: 'Admissions', icon: DocumentTextIcon, children }];
};

const teachingNav = () => {
  if (!isAcademicStaff.value && !isAdmin.value) return [];

  return [
    {
      name: 'Teaching',
      icon: BookOpenIcon,
      children: [
        { name: 'My Modules', href: route('hive.modules.index'), active: 'hive.modules.*', roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Gradebook', href: route('hive.grades.index'), active: 'hive.grades.*', roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Module Chat', href: route('hive.chat.index'), active: 'hive.chat.*', roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'QR Check-In', href: route('hive.attendance.scan'), active: 'hive.attendance.*', roles: ['academic_staff', 'super-admin', 'school-admin'] },
      ],
    },
    {
      name: 'Assessments',
      icon: ClipboardDocumentCheckIcon,
      children: [
        { name: 'All Assessments', href: route('hive.gradables.index'), active: 'hive.gradables.index', roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Quizzes', href: route('hive.gradables.module-select', { type: 'quiz' }), isActive: () => isGradableTypeActive('quiz'), roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Tests', href: route('hive.gradables.module-select', { type: 'test' }), isActive: () => isGradableTypeActive('test'), roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Assignments', href: route('hive.gradables.module-select', { type: 'assignment' }), isActive: () => isGradableTypeActive('assignment'), roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Mid-Term Exams', href: route('hive.gradables.module-select', { type: 'mid-term_exam' }), isActive: () => isGradableTypeActive('mid-term_exam'), roles: ['academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Final Exams', href: route('hive.gradables.module-select', { type: 'final_exam' }), isActive: () => isGradableTypeActive('final_exam'), roles: ['academic_staff', 'super-admin', 'school-admin'] },
      ],
    },
  ];
};

const administrationNav = () => {
  if (!isAdmin.value) return [];

  return [{
    name: 'Administration',
    icon: Cog6ToothIcon,
    children: [
      { name: 'Departments', href: route('hive.departments.index'), active: 'hive.departments.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Programmes', href: route('hive.programmes.index'), active: 'hive.programmes.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Modules', href: route('hive.modules.index'), active: 'hive.modules.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Cohorts', href: route('hive.cohorts.index'), active: 'hive.cohorts.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Academic Years', href: route('hive.academic-years.index'), active: 'hive.academic-years.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Waitlist', href: route('hive.waitlist.index'), active: 'hive.waitlist.*', roles: ['super-admin', 'school-admin'] },
    ],
  }];
};

const peopleNav = () => {
  if (!isAdmin.value) return [];

  return [{
    name: 'People',
    icon: UserGroupIcon,
    children: [
      { name: 'All Users', href: route('hive.users.index'), active: 'hive.users.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Students', href: route('hive.students.index'), active: 'hive.students.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Staff', href: route('hive.staff.index'), active: 'hive.staff.*', roles: ['super-admin', 'school-admin'] },
      { name: 'Achievements', href: route('hive.achievements.index'), active: 'hive.achievements.*', roles: ['super-admin', 'school-admin'] },
    ],
  }];
};

const operationsNav = () => {
  if (!isNonAcademicStaff.value && !isAdmin.value) return [];

  return [{
    name: 'Operations',
    icon: CalendarDaysIcon,
    children: [
      { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*' },
      { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*' },
      { name: 'Visitor Logs', href: route('hive.visitor-logs.index'), active: 'hive.visitor-logs.*', roles: ['super-admin', 'school-admin', 'non_academic_staff'] },
      { name: 'Suppliers', href: route('hive.suppliers.index'), active: 'hive.suppliers.*', roles: ['super-admin', 'school-admin', 'non_academic_staff'] },
      { name: 'Keys', href: route('hive.keys.index'), active: 'hive.keys.*' },
      { name: 'Upload Document', href: route('hive.documents.create'), active: 'hive.documents.create' },
    ],
  }];
};

const hrNav = () => {
  if (!isStaff.value && !isAdmin.value) return [];

  return [{
    name: 'HR',
    icon: BriefcaseIcon,
    children: [
      { name: 'Leave Requests', href: route('hive.leaves.index'), active: 'hive.leaves.*', roles: ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'] },
      { name: 'Payslips', href: route('hive.payslips.index'), active: 'hive.polls.index', roles: ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'] },
      { name: 'Polls & Surveys', href: route('hive.polls.index'), active: 'hive.polls.*', roles: ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'] },
      { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', roles: ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'] },
    ],
  }];
};

const systemNav = () => {
  if (!userRoles.value.includes('super-admin')) return [];

  return [{
    name: 'System',
    icon: RectangleStackIcon,
    children: [
      { name: 'System Logs', href: route('log-viewer'), target: '_blank' },
    ],
  }];
};

// ─── Final assembled nav (order = sidebar display order) ─────────────────────

const navigation = computed(() =>
  buildNav([
    ...dashNav,
    ...studentNav(),
    ...resourcesNav(),
    ...admissionsNav(),
    ...teachingNav(),
    ...administrationNav(),
    ...peopleNav(),
    ...operationsNav(),
    ...hrNav(),
    ...systemNav(),
  ])
);

const singleItems = computed(() => navigation.value.filter((item) => item.single));
const categories = computed(() => navigation.value.filter((item) => item.children));

const autoExpandActiveCategory = () => {
  categories.value.forEach((category) => {
    const hasActiveChild = category.children?.some((child) => isActive(child.active));
    if (hasActiveChild && !expandedCategories.value.includes(category.name)) {
      expandedCategories.value.push(category.name);
    }
  });
};

watch(
  () => page.url,
  () => {
    sidebarOpen.value = false;
    autoExpandActiveCategory();
  },
);
</script>
