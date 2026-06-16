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
  )));
  // Staff = anyone who is not student, parent-guardian, or alumni
  const isStaff = computed(() => !isStudent.value && !isParentGuardian.value && !isAlumni.value));
  const canAccessFinance = computed(() => userRoles.value.some((role) =>
    ['super-admin', 'finance', 'hr-manager'].includes(role)
  )));
  const isSuperAdmin = computed(() => userRoles.value.includes('super-admin'));
  const isInstructor = computed(() => isFaculty.value);
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
    isSuperAdmin,
    isInstructor,
    needsRegistration,
    isRegisteredStudent,
    displayRole,
    canAccess,
  };
}
