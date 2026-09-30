import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useUser() {
  const page = usePage();

  const currentUser = computed(() => page.props.auth?.user);
  const userRoles = computed(() => currentUser.value?.roles || []);

  // New 21-role structure helpers
  const isStudent = computed(() => userRoles.value.includes('student'));
  const isParentGuardian = computed(() => userRoles.value.includes('parent-guardian'));
  const isAlumni = computed(() => userRoles.value.includes('alumni'));
  const isFaculty = computed(() => userRoles.value.some((role) =>
    ['chef-instructor', 'pastry-instructor', 'sous-chef', 'academic-director'].includes(role)
  ));
  const isAdmin = computed(() => userRoles.value.some((role) =>
    ['super-admin', 'it-support'].includes(role)
  ));
  // Staff = anyone who is not student, parent-guardian, or alumni
  const isStaff = computed(() => !isStudent.value && !isParentGuardian.value && !isAlumni.value);
  const canAccessFinance = computed(() => userRoles.value.some((role) =>
    ['super-admin', 'finance', 'hr-manager'].includes(role)
  ));
  const isSuperAdmin = computed(() => userRoles.value.includes('super-admin'));
  const isInstructor = computed(() => isFaculty.value);

  // Roles that own the student register. Mirrors User::canManageStudents().
  const canManageStudents = computed(() => userRoles.value.some((role) =>
    ['super-admin', 'academic-director', 'program-coordinator', 'admissions-officer', 'registrar', 'examination-cell'].includes(role)
  ));

  // The register export carries contact details and national ID numbers.
  // Mirrors User::canExportStudents().
  const canExportStudents = computed(() =>
    canManageStudents.value || userRoles.value.some((role) => ['it-support', 'finance'].includes(role))
  );

  // Academic management roles that are not admins and not teaching staff.
  // academic-director is deliberately excluded: it already renders the
  // instructor dashboard, and the dashboard blocks are mutually exclusive.
  // Mirrors RoleService::isAcademicManagement().
  const isAcademicStaff = computed(() =>
    !isAdmin.value && userRoles.value.some((role) =>
      ['program-coordinator', 'registrar', 'examination-cell'].includes(role)
    )
  );

  // Everyone else on staff, e.g. admissions, finance, HR, library.
  // Mirrors RoleService::NON_ACADEMIC_STAFF_ROLES.
  const isNonAcademicStaff = computed(() =>
    isStaff.value && !isAdmin.value && !isFaculty.value && !isAcademicStaff.value
  );

  const hasNoDashboard = computed(() =>
    !isAdmin.value && !isFaculty.value && !isAcademicStaff.value &&
    !isNonAcademicStaff.value && !isStudent.value &&
    !isParentGuardian.value && !isAlumni.value
  );

  const needsRegistration = computed(() => currentUser.value?.needs_registration ?? false);
  const isRegisteredStudent = computed(() => isStudent.value && !needsRegistration.value);

  const displayRole = computed(() => {
    const primaryRole = userRoles.value[0] || 'User';
    return primaryRole.replaceAll('-', ' ').replaceAll('_', ' ');
  });

  const canAccess = (requiredRoles) => {
    if (!requiredRoles || requiredRoles.length === 0) {
      return true;
    }
    return requiredRoles.some((role) => userRoles.value.includes(role));
  };

  return {
    currentUser,
    userRoles,
    isStudent,
    isParentGuardian,
    isAlumni,
    isFaculty,
    isAdmin,
    isStaff,
    canAccessFinance,
    canManageStudents,
    canExportStudents,
    isAcademicStaff,
    isNonAcademicStaff,
    hasNoDashboard,
    isSuperAdmin,
    isInstructor,
    needsRegistration,
    isRegisteredStudent,
    displayRole,
    canAccess,
  };
}
