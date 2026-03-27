import { usePage } from '@inertiajs/vue3';

export function userHasPermissionTo(permission) {
    const permissions = usePage().props.auth.user.permissions;
    return permissions.includes(permission);
}

export function hasRole(role) {
    const roles = usePage().props.auth.user.roles;
    return roles.includes(role);
}
