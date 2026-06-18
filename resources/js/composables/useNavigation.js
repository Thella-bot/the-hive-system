import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUser } from '@/composables/useUser';
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
  CurrencyDollarIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  MegaphoneIcon,
  RectangleStackIcon,
  UserCircleIcon,
  UserGroupIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

export function useNavigation() {
  const page = usePage();
  const {
    userRoles,
    isStudent,
    isFaculty,
    isStaff,
    isAdmin,
    canAccessFinance,
    needsRegistration,
    isRegisteredStudent,
  } = useUser();

  const sidebarOpen = ref(false);
  const expandedCategories = ref([]);

  const isActive = (pattern) => (pattern ? route().current(pattern) : false);

  const isGradableTypeActive = (type) => {
    if (!route().current('hive.gradables.module-select')) return false;
    return route().params()?.type === type;
  };

  const toggleCategory = (categoryName) => {
    expandedCategories.value = expandedCategories.value.includes(categoryName)
      ? expandedCategories.value.filter((name) => name !== categoryName)
      : [...expandedCategories.value, categoryName];
  };

  // ─── Navigation helper ────────────────────────────────────────────────────────

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

  const academicResourcesNav = () => [
    {
      name: 'Academic Resources',
      icon: BookOpenIcon,
      children: [
        // Library
        { name: 'Library', href: route('library.dashboard'), active: 'library.*', roles: ['student', 'super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'] },
        // Documents (unified from old Documents module)
        { name: 'Documents', href: route('hive.documents.index'), active: 'hive.documents.*', roles: ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager', 'student'] },
        // Announcements
        { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', roles: ['student', 'super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'] },
        // Events
        { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', roles: ['student', 'super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'] },
      ],
    },
  ];

  const admissionsNav = () => {
    const authRoles = userRoles.value;
    const isAdmissionsStaff = authRoles.some(r => ['super-admin', 'it-support', 'admissions-officer', 'registrar', 'program-coordinator'].includes(r));
    if (!isAdmissionsStaff) return [];

    const children = [
      { name: 'Applications', href: route('hive.applications.index'), active: 'hive.applications.*', roles: ['super-admin', 'it-support', 'admissions-officer', 'registrar', 'program-coordinator'] },
      { name: 'Registrations', href: route('hive.registration.index'), active: 'hive.registration.*', roles: ['super-admin', 'it-support', 'admissions-officer', 'registrar', 'program-coordinator'] },
    ];

    if (isAdmin.value) {
      children.push(
        { name: 'User Approvals', href: route('hive.admin.approve-users'), active: 'hive.admin.approve-users', roles: ['super-admin', 'it-support'] },
        { name: 'Import Users', href: route('hive.admin.import-users'), active: 'hive.admin.import-users', roles: ['super-admin', 'it-support'] },
      );
    }

    return [{ name: 'Admissions', icon: DocumentTextIcon, children }];
  };

  const teachingNav = () => {
    if (!isFaculty.value && !isAdmin.value) return [];

    const facultyRoles = ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'];

    return [
      {
        name: 'Teaching',
        icon: BookOpenIcon,
        children: [
          { name: 'My Modules', href: route('hive.modules.index'), active: 'hive.modules.*', roles: facultyRoles },
          { name: 'Gradebook', href: route('hive.grades.index'), active: 'hive.grades.*', roles: facultyRoles },
          { name: 'Module Chat', href: route('hive.chat.index'), active: 'hive.chat.*', roles: facultyRoles },
          { name: 'QR Check-In', href: route('hive.attendance.scan'), active: 'hive.attendance.*', roles: facultyRoles },
        ],
      },
      {
        name: 'Assessments',
        icon: ClipboardDocumentCheckIcon,
        children: [
          { name: 'All Assessments', href: route('hive.gradables.index'), active: 'hive.gradables.index', roles: facultyRoles },
          { name: 'Quizzes', href: route('hive.gradables.module-select', { type: 'quiz' }), isActive: () => isGradableTypeActive('quiz'), roles: facultyRoles },
          { name: 'Tests', href: route('hive.gradables.module-select', { type: 'test' }), isActive: () => isGradableTypeActive('test'), roles: facultyRoles },
          { name: 'Assignments', href: route('hive.gradables.module-select', { type: 'assignment' }), isActive: () => isGradableTypeActive('assignment'), roles: facultyRoles },
          { name: 'Mid-Term Exams', href: route('hive.gradables.module-select', { type: 'mid-term_exam' }), isActive: () => isGradableTypeActive('mid-term_exam'), roles: facultyRoles },
          { name: 'Final Exams', href: route('hive.gradables.module-select', { type: 'final_exam' }), isActive: () => isGradableTypeActive('final_exam'), roles: facultyRoles },
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
        { name: 'Departments', href: route('hive.departments.index'), active: 'hive.departments.*', roles: ['super-admin', 'it-support', 'academic-director'] },
        { name: 'Programmes', href: route('hive.programmes.index'), active: 'hive.programmes.*', roles: ['super-admin', 'it-support', 'academic-director'] },
        { name: 'Modules', href: route('hive.modules.index'), active: 'hive.modules.*', roles: ['super-admin', 'it-support', 'academic-director', 'program-coordinator'] },
        { name: 'Cohorts', href: route('hive.cohorts.index'), active: 'hive.cohorts.*', roles: ['super-admin', 'it-support', 'academic-director', 'program-coordinator'] },
        { name: 'Academic Years', href: route('hive.academic-years.index'), active: 'hive.academic-years.*', roles: ['super-admin', 'it-support'] },
        { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', roles: ['super-admin', 'it-support', 'hr-manager'] },
        { name: 'Waitlist', href: route('hive.waitlist.index'), active: 'hive.waitlist.*', roles: ['super-admin', 'it-support', 'admissions-officer'] },
      ],
    }];
  };

  const peopleNav = () => {
    if (!isAdmin.value) return [];

    return [{
      name: 'People',
      icon: UserGroupIcon,
      children: [
        { name: 'All Users', href: route('hive.users.index'), active: 'hive.users.*', roles: ['super-admin', 'it-support'] },
        { name: 'Students', href: route('hive.students.index'), active: 'hive.students.*', roles: ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer', 'registrar'] },
        { name: 'Staff', href: route('hive.staff.index'), active: 'hive.staff.*', roles: ['super-admin', 'it-support', 'hr-manager'] },
        { name: 'Achievements', href: route('hive.achievements.index'), active: 'hive.achievements.*', roles: ['super-admin', 'it-support', 'program-coordinator', 'career-services'] },
      ],
    }];
  };

  const operationsNav = () => {
    const authRoles = userRoles.value;
    const isOpsStaff = authRoles.some(r =>
      ['super-admin', 'it-support', 'procurement-manager', 'storekeeper', 'events-pr-manager', 'cafeteria-manager', 'librarian'].includes(r)
    );
    if (!isOpsStaff && !isAdmin.value) return [];

    const opsRoles = ['super-admin', 'it-support', 'procurement-manager', 'storekeeper', 'events-pr-manager', 'cafeteria-manager', 'librarian'];

    return [{
      name: 'Operations',
      icon: CalendarDaysIcon,
      children: [
        { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', roles: opsRoles },
        { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*' },
        { name: 'Visitor Logs', href: route('hive.visitor-logs.index'), active: 'hive.visitor-logs.*', roles: opsRoles },
        { name: 'Suppliers', href: route('hive.suppliers.index'), active: 'hive.suppliers.*', roles: ['super-admin', 'it-support', 'procurement-manager', 'storekeeper'] },
        { name: 'Keys', href: route('hive.keys.index'), active: 'hive.keys.*', roles: opsRoles },
        { name: 'Upload Document', href: route('hive.documents.create'), active: 'hive.documents.create', roles: opsRoles },
      ],
    }];
  };

  const hrNav = () => {
    if (!isStaff.value && !isAdmin.value) return [];

    // All staff roles for HR access
    const staffRoles = ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'];

    return [{
      name: 'HR',
      icon: BriefcaseIcon,
      children: [
        { name: 'Leave Requests', href: route('hive.leaves.index'), active: 'hive.leaves.*', roles: staffRoles },
        { name: 'Payslips', href: route('hive.payslips.index'), active: 'hive.payslips.index', roles: staffRoles },
        { name: 'Polls & Surveys', href: route('hive.polls.index'), active: 'hive.polls.*', roles: staffRoles },
        { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', roles: staffRoles },
      ],
    }];
  };

  const bursarNav = () => {
    if (!canAccessFinance.value && !isAdmin.value) return [];

    const financeRoles = ['super-admin', 'finance', 'hr-manager'];

    return [{
      name: 'Finance',
      icon: CurrencyDollarIcon,
      children: [
        { name: 'Dashboard', href: route('bursar.reports.dashboard'), active: 'bursar.reports.dashboard', roles: financeRoles },
        { name: 'Invoices', href: route('bursar.invoices.index'), active: 'bursar.invoices.*', roles: financeRoles },
        { name: 'Payments', href: route('bursar.payments.index'), active: 'bursar.payments.*', roles: financeRoles },
        { name: 'Expenses', href: route('bursar.expenses.index'), active: 'bursar.expenses.*', roles: financeRoles },
        { name: 'Budgets', href: route('bursar.budgets.index'), active: 'bursar.budgets.*', roles: financeRoles },
      ],
    }];
  };

  const systemNav = () => {
    if (!userRoles.value.includes('super-admin')) return [];

    return [{
      name: 'System',
      icon: RectangleStackIcon,
      children: [
        { name: 'Academic Years', href: route('hive.academic-years.index'), active: 'hive.academic-years.*' },
        { name: 'System Logs', href: route('log-viewer'), target: '_blank' },
      ],
    }];
  };

  // ─── Final assembled nav (order = sidebar display order) ─────────────────────

  const navigation = computed(() =>
    buildNav([
      ...dashNav,
      ...studentNav(),
      ...academicResourcesNav(),
      ...admissionsNav(),
      ...administrationNav(),
      ...peopleNav(),
      ...operationsNav(),
            ...hrNav(),
      ...bursarNav(),
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

  // Auto-expand category on route change
  watch(
    () => page.url,
    () => {
      sidebarOpen.value = false;
      autoExpandActiveCategory();
    },
  );

  return {
    sidebarOpen,
    expandedCategories,
    toggleCategory,
    navigation,
    singleItems,
    categories,
    autoExpandActiveCategory,
  };
}