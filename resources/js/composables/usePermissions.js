import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const page = usePage();

  const can = (permission) => {
    const perms = page.props.auth?.user?.permissions;
    return Array.isArray(perms) ? perms.includes(permission) : false;
  };

  return { can };
}
