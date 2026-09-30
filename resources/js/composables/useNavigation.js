import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUser } from '@/composables/useUser';
import { usePermissions } from '@/composables/usePermissions';
import {
  AcademicCapIcon,
  BookOpenIcon,
  BriefcaseIcon,
  CalendarDaysIcon,
  ChatBubbleLeftRightIcon,
  ClipboardDocumentCheckIcon,
  Cog6ToothIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  HomeIcon,
  IdentificationIcon,
  KeyIcon,
  MagnifyingGlassIcon,
  RectangleStackIcon,
  ShoppingCartIcon,
  UserGroupIcon,
  UsersIcon,
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
    isSuperAdmin,
    isParentGuardian,
    isAlumni,
    canAccess,
  } = useUser();

  const { canAny, isUnseeded } = usePermissions();

  const sidebarOpen = ref(false);
  const expandedCategories = ref([]);

  /**
   * Keep a nav item when the user holds any of its permissions.
   *
   * Items with no `permission` are always kept. When no permission data has
   * been seeded, role checks alone decide, so an unseeded environment does not
   * lose its sidebar.
   */
  const allowed = (item) => {
    if (isUnseeded.value) return true;
    if (!item.permission) return true;
    return canAny(item.permission);
  };

  const isActive = (pattern) => (pattern ? route().current(pattern) : false);

  /**
   * Enrollment exists as two distinct pages:
   *   - hive.enrollment.index      -> student self-enrollment (viewStudent gate)
   *   - hive.enrollment.admin.index -> admin manage-all (create gate)
   *
   * Staff who can enroll students must be sent to the admin page; pointing them
   * at hive.enrollment.index hits the student-only gate and 403s.
   */
  const ENROLLMENT_ADMIN_ROLES = [
    'super-admin',
    'it-support',
    'registrar',
    'program-coordinator',
    'academic-director',
  ];

  const enrollmentHref = () => {
    const isEnrollmentAdmin = userRoles.value.some((r) => ENROLLMENT_ADMIN_ROLES.includes(r));
    return isEnrollmentAdmin
      ? route('hive.enrollment.admin.index')
      : route('hive.enrollment.index');
  };

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
    const seenNames = new Set();

    const dedupe = (children) =>
      children
        .filter(allowed)
        .filter((child) => {
          if (!child.href || child.target === '_blank') return true;
          if (seenHrefs.has(child.href)) return false;
          seenHrefs.add(child.href);
          return true;
        });

    return allItems
      .filter((item) => {
        if (item.single) return true;
        if (!item.children) return false;
        if (!allowed(item)) return false;
        if (seenNames.has(item.name)) return false;
        seenNames.add(item.name);
        return true;
      })
      .map((item) =>
        item.children
          ? { ...item, children: dedupe(item.children) }
          : item
      )
      .filter((item) => item.single || item.children?.length);
  };

  // ─── Dashboard (always shown) ────────────────────────────────────────────────

  const dashNav = [
    {
      name: 'Dashboard',
      href: route('hive.dashboard'),
      active: 'hive.dashboard',
      icon: HomeIcon,
      single: true,
    },
  ];

  // ─── Student Navigation ──────────────────────────────────────────────────────

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
          { name: 'My Modules', href: route('hive.modules.index'), active: 'hive.modules.*', permission: 'view-modules' },
          { name: 'My Grades', href: route('hive.grades.index'), active: 'hive.grades.*', permission: 'view-grades' },
          { name: 'My Transcript', href: route('hive.transcript.index'), active: 'hive.transcript.*', permission: 'view-transcripts' },
          { name: 'My Attendance', href: route('hive.attendance.index'), active: 'hive.attendance.index' },
          { name: 'Student ID Card', href: route('hive.student-id'), active: 'hive.student-id' },
        ],
      },
      {
        name: 'Assessments',
        icon: ClipboardDocumentCheckIcon,
        permission: 'view-assessments',
        children: [
          { name: 'Quizzes', href: route('hive.gradables.module-select', { type: 'quiz' }), isActive: () => isGradableTypeActive('quiz') },
          { name: 'Tests', href: route('hive.gradables.module-select', { type: 'test' }), isActive: () => isGradableTypeActive('test') },
          { name: 'Assignments', href: route('hive.gradables.module-select', { type: 'assignment' }), isActive: () => isGradableTypeActive('assignment') },
          { name: 'Mid-Term Exams', href: route('hive.gradables.module-select', { type: 'mid-term_exam' }), isActive: () => isGradableTypeActive('mid-term_exam') },
          { name: 'Final Exams', href: route('hive.gradables.module-select', { type: 'final_exam' }), isActive: () => isGradableTypeActive('final_exam') },
        ],
      },
      {
        name: 'Communication',
        icon: ChatBubbleLeftRightIcon,
        children: [
          { name: 'Chat', href: route('hive.chat.index'), active: 'hive.chat.*', permission: 'view-messages' },
          { name: 'Polls', href: route('hive.polls.index'), active: 'hive.polls.*' },
          { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', permission: 'view-announcements' },
        ],
      },
      {
        name: 'Resources',
        icon: BookOpenIcon,
        children: [
          { name: 'Library', href: route('hive.library.dashboard'), active: 'hive.library.*', permission: 'view-library' },
          // The documents resource is restricted to staff by route middleware,
          // so this link is hidden for students rather than leading to a 403.
          { name: 'Documents', href: route('hive.documents.index'), active: 'hive.documents.*', permission: 'view-documents' },
          { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', permission: 'view-events' },
        ],
      },
    ];
  };

  // ─── Faculty / Teaching Navigation ────────────────────────────────────────────

  const facultyNav = () => {
    if (!isFaculty.value) return [];

    return [
      {
        name: 'Teaching',
        icon: BookOpenIcon,
        children: [
          { name: 'Modules', href: route('hive.modules.index'), active: 'hive.modules.*', permission: 'view-modules' },
          { name: 'Gradebook', href: route('hive.grades.index'), active: 'hive.grades.*', permission: ['view-grades', 'manage-grades'] },
          { name: 'Module Chat', href: route('hive.chat.index'), active: 'hive.chat.*', permission: 'view-messages' },
          { name: 'Attendance', href: route('hive.attendance.index'), active: 'hive.attendance.index', permission: ['view-student-attendance', 'manage-student-attendance'] },
          { name: 'QR Check-In', href: route('hive.attendance.scan'), active: 'hive.attendance.scan', permission: 'manage-student-attendance' },
          { name: 'Timetable', href: route('hive.timetable.index'), active: 'hive.timetable.*', permission: 'view-timetables' },
        ],
      },
      {
        name: 'Assessments',
        icon: ClipboardDocumentCheckIcon,
        permission: 'view-assessments',
        children: [
          { name: 'All Assessments', href: route('hive.gradables.index'), active: 'hive.gradables.index' },
          { name: 'Quizzes', href: route('hive.gradables.module-select', { type: 'quiz' }), isActive: () => isGradableTypeActive('quiz') },
          { name: 'Tests', href: route('hive.gradables.module-select', { type: 'test' }), isActive: () => isGradableTypeActive('test') },
          { name: 'Assignments', href: route('hive.gradables.module-select', { type: 'assignment' }), isActive: () => isGradableTypeActive('assignment') },
          { name: 'Mid-Term Exams', href: route('hive.gradables.module-select', { type: 'mid-term_exam' }), isActive: () => isGradableTypeActive('mid-term_exam') },
          { name: 'Final Exams', href: route('hive.gradables.module-select', { type: 'final_exam' }), isActive: () => isGradableTypeActive('final_exam') },
        ],
      },
      {
        name: 'Resources',
        icon: BookOpenIcon,
        children: [
          { name: 'Library', href: route('hive.library.dashboard'), active: 'hive.library.*', permission: 'view-library' },
          { name: 'Documents', href: route('hive.documents.index'), active: 'hive.documents.*', permission: 'view-documents' },
          { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', permission: 'view-announcements' },
        ],
      },
    ];
  };

  // ─── Admissions Staff Navigation ──────────────────────────────────────────────

  const admissionsNav = () => {
    const isAdmissionsStaff = userRoles.value.some(r => ['admissions-officer', 'registrar', 'program-coordinator'].includes(r));
    if (!isAdmissionsStaff) return [];

    return [{
      name: 'Admissions',
      icon: DocumentTextIcon,
      permission: 'view-applications',
      children: [
        { name: 'Applications', href: route('hive.applications.index'), active: 'hive.applications.*', permission: 'view-applications' },
        { name: 'Registrations', href: route('hive.registration.index'), active: 'hive.registration.*', permission: 'view-enrollments' },
        { name: 'Short Courses', href: route('hive.short-courses.index'), active: 'hive.short-courses.*', permission: 'view-applications' },
        { name: 'Waitlist', href: route('hive.waitlist.index'), active: 'hive.waitlist.*' },
      ],
    }];
  };

  // ─── Academic Administration Navigation ───────────────────────────────────────

  const academicAdminNav = () => {
    const isAcademicStaff = userRoles.value.some(r => ['academic-director', 'program-coordinator', 'registrar', 'examination-cell'].includes(r));
    if (!isAcademicStaff) return [];

    return [{
      name: 'Academic',
      icon: AcademicCapIcon,
      permission: 'view-programmes',
      children: [
        { name: 'Programmes', href: route('hive.programmes.index'), active: 'hive.programmes.*', permission: 'view-programmes' },
        { name: 'Modules', href: route('hive.modules.index'), active: 'hive.modules.*', permission: 'view-modules' },
        { name: 'Cohorts', href: route('hive.cohorts.index'), active: 'hive.cohorts.*', permission: 'view-cohorts' },
        { name: 'Enrollment', href: enrollmentHref(), active: 'hive.enrollment.*', permission: 'view-enrollments' },
        { name: 'Student Advancement', href: route('hive.advancement.index'), active: 'hive.advancement.*', permission: 'view-students' },
      ],
    }];
  };

  // ─── Finance Navigation ───────────────────────────────────────────────────────

  const financeNav = () => {
    if (!canAccessFinance.value) return [];

    return [{
      name: 'Finance',
      icon: CurrencyDollarIcon,
      permission: 'view-invoices',
      children: [
        { name: 'Dashboard', href: route('hive.finance.reports.dashboard'), active: 'hive.finance.reports.dashboard', permission: 'view-reports' },
        { name: 'Invoices', href: route('hive.finance.invoices.index'), active: 'hive.finance.invoices.*', permission: 'view-invoices' },
        { name: 'Payments', href: route('hive.finance.payments.index'), active: 'hive.finance.payments.*', permission: 'view-payments' },
        { name: 'Expenses', href: route('hive.finance.expenses.index'), active: 'hive.finance.expenses.*', permission: 'view-expenses' },
        { name: 'Budgets', href: route('hive.finance.budgets.index'), active: 'hive.finance.budgets.*', permission: 'view-budgets' },
        { name: 'Convectionary', href: route('hive.finance.convectionary.index'), active: 'hive.finance.convectionary.*', permission: 'manage-expenses' },
      ],
    }];
  };

  // ─── HR Navigation ────────────────────────────────────────────────────────────

  const hrNav = () => {
    const isHrStaff = userRoles.value.some(r => ['hr-manager'].includes(r));
    if (!isHrStaff) return [];

    return [{
      name: 'HR',
      icon: BriefcaseIcon,
      permission: 'view-leave-requests',
      children: [
        { name: 'Leave Requests', href: route('hive.leaves.index'), active: 'hive.leaves.*', permission: 'view-leave-requests' },
        { name: 'Payslips', href: route('hive.payslips.index'), active: 'hive.payslips.index', permission: 'view-payslips' },
        { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', permission: 'view-uniforms' },
        { name: 'Staff Directory', href: route('hive.staff.index'), active: 'hive.staff.*', permission: 'view-staff' },
      ],
    }];
  };

  // ─── Operations Navigation ────────────────────────────────────────────────────

  const operationsNav = () => {
    const isOpsStaff = userRoles.value.some(r =>
      ['procurement-manager', 'storekeeper', 'events-pr-manager', 'cafeteria-manager', 'librarian'].includes(r)
    );
    if (!isOpsStaff) return [];

    const children = [];

    if (userRoles.value.some(r => ['events-pr-manager', 'cafeteria-manager', 'librarian'].includes(r))) {
      children.push({ name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', permission: 'view-events' });
      children.push({ name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', permission: 'view-announcements' });
    }

    if (userRoles.value.some(r => ['procurement-manager', 'storekeeper'].includes(r))) {
      children.push({ name: 'Suppliers', href: route('hive.suppliers.index'), active: 'hive.suppliers.*', permission: 'view-suppliers' });
      children.push({ name: 'Keys', href: route('hive.keys.index'), active: 'hive.keys.*' });
    }

    if (userRoles.value.includes('librarian')) {
      children.push({ name: 'Library', href: route('hive.library.dashboard'), active: 'hive.library.*', permission: 'view-library' });
    }

    if (userRoles.value.some(r => ['events-pr-manager', 'procurement-manager', 'storekeeper', 'cafeteria-manager', 'librarian'].includes(r))) {
      children.push({ name: 'Visitor Logs', href: route('hive.visitor-logs.index'), active: 'hive.visitor-logs.*' });
      children.push({ name: 'Upload Document', href: route('hive.documents.create'), active: 'hive.documents.create', permission: 'view-documents' });
    }

    if (!children.length) return [];

    return [{ name: 'Operations', icon: CalendarDaysIcon, children }];
  };

  // ─── People Management (Admin & HR) ───────────────────────────────────────────

  const peopleNav = () => {
    const canManagePeople = userRoles.value.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer', 'registrar', 'hr-manager', 'career-services'].includes(r));
    if (!canManagePeople) return [];

    const children = [];

    if (userRoles.value.some(r => ['super-admin', 'it-support'].includes(r))) {
      children.push({ name: 'All Users', href: route('hive.users.index'), active: 'hive.users.*', permission: 'view-users' });
    }

    if (userRoles.value.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer', 'registrar'].includes(r))) {
      children.push({ name: 'Students', href: route('hive.students.index'), active: 'hive.students.*', permission: 'view-students' });
    }

    if (userRoles.value.some(r => ['super-admin', 'it-support', 'hr-manager'].includes(r))) {
      children.push({ name: 'Staff', href: route('hive.staff.index'), active: 'hive.staff.*', permission: 'view-staff' });
    }

    if (userRoles.value.some(r => ['super-admin', 'it-support', 'program-coordinator', 'career-services'].includes(r))) {
      children.push({ name: 'Achievements', href: route('hive.achievements.index'), active: 'hive.achievements.*' });
    }

    if (!children.length) return [];

    return [{ name: 'People', icon: UserGroupIcon, children }];
  };

  // ─── Administration (Admin only) ──────────────────────────────────────────────

  const administrationNav = () => {
    if (!isAdmin.value) return [];

    return [{
      name: 'Administration',
      icon: Cog6ToothIcon,
      children: [
        { name: 'Departments', href: route('hive.departments.index'), active: 'hive.departments.*', permission: 'manage-settings' },
        { name: 'Placements', href: route('hive.placements.index'), active: 'hive.placements.*', permission: 'view-placements' },
        { name: 'Disciplinary', href: route('hive.disciplinary.index'), active: 'hive.disciplinary.*', permission: 'view-disciplinary-records' },
        { name: 'Uniform Requests', href: route('hive.uniform-requests.index'), active: 'hive.uniform-requests.*', permission: 'view-uniforms' },
        { name: 'Academic Years', href: route('hive.academic-years.index'), active: 'hive.academic-years.*', permission: 'manage-settings' },
      ],
    }];
  };

  // ─── System (Super Admin only) ────────────────────────────────────────────────

  const systemNav = () => {
    if (!isSuperAdmin.value) return [];

    return [{
      name: 'System',
      icon: RectangleStackIcon,
      children: [
        { name: 'Settings', href: route('hive.settings.index'), active: 'hive.settings.*', permission: 'manage-settings' },
        { name: 'Roles & Permissions', href: route('hive.roles.index'), active: 'hive.roles.*', permission: 'manage-roles' },
        { name: 'Pending Approvals', href: route('hive.admin.approve-users'), active: 'hive.admin.approve-users', permission: 'manage-users' },
        { name: 'Import Users', href: route('hive.admin.import-users'), active: 'hive.admin.import-users', permission: 'manage-users' },
        { name: 'System Logs', href: route('log-viewer'), target: '_blank', permission: 'view-audit-logs' },
      ],
    }];
  };

  // ─── External accounts (parent/guardian, alumni) ──────────────────────────────

  const externalNav = () => {
    const children = [];

    if (isParentGuardian.value) {
      children.push({
        name: 'My Students',
        href: route('hive.dashboard'),
        active: 'hive.dashboard',
        permission: 'view-student-profile',
      });
      children.push({
        name: 'Announcements',
        href: route('hive.announcements.index'),
        active: 'hive.announcements.*',
        permission: 'view-announcements',
      });
      children.push({
        name: 'Events',
        href: route('hive.events.index'),
        active: 'hive.events.*',
        permission: 'view-events',
      });
    }

    if (isAlumni.value) {
      children.push({
        name: 'Placements',
        href: route('hive.placements.index'),
        active: 'hive.placements.*',
        permission: 'view-placements',
      });
      children.push({
        name: 'Announcements',
        href: route('hive.announcements.index'),
        active: 'hive.announcements.*',
        permission: 'view-announcements',
      });
      children.push({
        name: 'Events',
        href: route('hive.events.index'),
        active: 'hive.events.*',
        permission: 'view-events',
      });
    }

    if (!children.length) return [];

    return [{ name: 'Resources', icon: BookOpenIcon, children }];
  };

  // ─── Final assembled nav (order = sidebar display order) ─────────────────────

  const navigation = computed(() =>
    buildNav([
      ...dashNav,
      ...studentNav(),
      ...facultyNav(),
      ...admissionsNav(),
      ...academicAdminNav(),
      ...financeNav(),
      ...hrNav(),
      ...operationsNav(),
      ...peopleNav(),
      ...administrationNav(),
      ...externalNav(),
      ...systemNav(),
    ])
  );

  const singleItems = computed(() => navigation.value.filter((item) => item.single));
  const categories = computed(() => navigation.value.filter((item) => item.children));

  const autoExpandActiveCategory = () => {
    expandedCategories.value = [];
    categories.value.forEach((category) => {
      const hasActiveChild = category.children?.some((child) => isActive(child.active));
      if (hasActiveChild) {
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
