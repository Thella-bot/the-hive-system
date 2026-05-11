<template>
  <div class="min-h-screen flex">
    <div class="w-64 bg-gray-800 text-white flex flex-col">
      <div class="p-4 text-xl font-bold">HBCI Intranet</div>
      <nav class="flex-1">
        <Link :href="route('intranet.dashboard')" class="block py-2 px-4 hover:bg-gray-700">Dashboard</Link>
        <Link v-if="$page.props.user.roles.some(r=>['admin','instructor'].includes(r.name))" :href="route('intranet.assignments.index')" class="block py-2 px-4 hover:bg-gray-700">Assignments</Link>
        <Link :href="route('grades.index')" class="block py-2 px-4 hover:bg-gray-700">Grades</Link>
        <Link v-if="$page.props.user.roles.some(r=>r.name==='admin')" :href="route('admin.modules.index')" class="block py-2 px-4 hover:bg-gray-700">Modules & Programmes</Link>
        <Link v-if="$page.props.user.roles.some(r=>r.name==='admin')" :href="route('admin.import-users')" class="block py-2 px-4 hover:bg-gray-700">Import Users</Link>
        <Link v-if="$page.props.user.roles.some(r=>r.name==='admin')" :href="route('admin.approve-users')" class="block py-2 px-4 hover:bg-gray-700">Approve Users</Link>
        <Link :href="route('intranet.documents.index')" class="block py-2 px-4 hover:bg-gray-700">Documents</Link>
        <Link :href="route('intranet.announcements.index')" class="block py-2 px-4 hover:bg-gray-700">Announcements</Link>
        <Link :href="route('intranet.profile.edit')" class="block py-2 px-4 hover:bg-gray-700">My Profile</Link>
        <Link :href="route('intranet.leaves.index')" class="block py-2 px-4 hover:bg-gray-700">Leave Requests</Link>
        <Link :href="route('intranet.payslips.index')" class="block py-2 px-4 hover:bg-gray-700">Payslips</Link>
      </nav>
      <div class="p-4">
        <span>{{ $page.props.user.name }}</span>
        <Link :href="route('logout')" method="post" as="button" class="text-sm ml-2">Logout</Link>
      </div>
    </div>
    <main class="flex-1 p-6 bg-gray-50">
      <slot />
    </main>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3';
</script>