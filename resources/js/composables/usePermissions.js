import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const page = usePage();

  const permissions = computed(() => {
    const perms = page.props.auth?.user?.permissions;
    return Array.isArray(perms) ? perms : [];
  });

  /**
   * True when the shared props carry no permission data at all. This happens in
   * environments where RolePermissionSeeder has not been run; nav must fall
   * back to role checks there rather than collapsing to an empty sidebar.
   */
  const isUnseeded = computed(() => permissions.value.length === 0);

  const can = (permission) => permissions.value.includes(permission);

  // Any-of semantics: a nav item may be satisfied by several permissions.
  const canAny = (...names) => names.flat().some((name) => can(name));

  const canAll = (...names) => names.flat().every((name) => can(name));

  return { can, canAny, canAll, permissions, isUnseeded };
}
