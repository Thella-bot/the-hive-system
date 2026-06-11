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

export function useNavigation() {
  const page = usePage();
  const {
    userRoles,
    isStudent,
    isAcademicStaff,
    isNonAcademicStaff,
    isStaff,
    isAdmin,
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

  const resourcesNav = () => [
    {
      name: 'Resources',
      icon: FolderIcon,
      children: [
        { name: 'Announcements', href: route('hive.announcements.index'), active: 'hive.announcements.*', roles: ['student', 'academic_staff', 'super-admin', 'school-admin'] },
        { name: 'Events', href: route('hive.events.index'), active: 'hive.events.*', roles: ['student', 'academic_staff', 'super-admin', 'school-admin'] },
        ...(isStaff.value || isAdmin.value ? [
          { name: 'Documents', href: route('hive.documents.index'), active: 'hive.documents.*', roles: ['academic_staff', 'non_academic_staff', 'super-admin', 'school-admin'] },
        ] : []),
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
        { name: 'Payslips', href: route('hive.payslips.index'), active: 'hive.payslips.index', roles: ['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff'] },
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
      ...resourcesNav(),
      ...admissionsNav(),
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