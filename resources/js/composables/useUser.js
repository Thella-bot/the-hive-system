import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useUser() {
  const page = usePage();

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
    isAcademicStaff,
    isNonAcademicStaff,
    isStaff,
    isAdmin,
    needsRegistration,
    isRegisteredStudent,
    displayRole,
    canAccess,
  };
}
