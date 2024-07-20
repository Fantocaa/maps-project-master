<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import UpdatePassword from "./Partials/UpdatePassword.vue";
import DeleteUser from "./Partials/DeleteUser.vue";
import UpdateUserProfile from "./Partials/UpdateUserProfile.vue";

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <AuthenticatedLayout title="Profile">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Profile User
            </h2>
        </template>

        <div class="space-y-6">
            <div
                class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
            >
                <UpdateUserProfile
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div
                class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
            >
                <UpdatePassword class="max-w-xl" />
            </div>

            <div
                class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
            >
                <DeleteUser class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
