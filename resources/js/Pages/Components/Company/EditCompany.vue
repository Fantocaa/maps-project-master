<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import UpdateCompanyProfile from "./Partials/UpdateCompanyProfile.vue";
import DeleteCompany from "./Partials/DeleteCompany.vue";

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <AuthenticatedLayout title="Edit Company">
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Edit Company
            </h2>
        </template>

        <div class="space-y-6 flex gap-6">
            <div
                class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg w-1/2"
            >
                <UpdateCompanyProfile
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div
                class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg w-1/2"
            >
                <DeleteCompany class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
