<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
// import Button from "@/Components/Button.vue";
// import { GithubIcon } from "@/Components/Icons/brands";
import { Link } from "@inertiajs/vue3";
import History from "./Components/History.vue";
import { ref, onMounted } from "vue";

// Define reactive variables
const startDate = ref("");
const endDate = ref("");
const today = ref("");

// Function to format date as YYYY-MM-DD
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}

// Set default date values on mount
onMounted(() => {
    const currentDate = new Date();
    today.value = formatDate(currentDate);

    // Set start date to one month ago
    const lastMonth = new Date(currentDate);
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    startDate.value = formatDate(lastMonth);

    // Set end date to today
    endDate.value = today.value;
});
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <h2 class="text-xl font-semibold leading-tight">Dashboard</h2>
                <!-- <Button
                    external
                    variant="black"
                    target="_blank"
                    class="items-center gap-2 max-w-xs"
                    v-slot="{ iconSizeClasses }"
                    href="https://github.com/kamona-wd/kui-laravel-breeze"
                >
                    <GithubIcon aria-hidden="true" :class="iconSizeClasses" />

                    <span>Star on Github</span>
                </Button> -->
            </div>
        </template>
        <div class="flex w-full gap-4 h-full items-end">
            <!-- <div
                class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 w-full"
            >
                You're logged in!
            </div> -->
            <div class="w-full">
                <label for="map" class="block font-semibold text-2xl"
                    >Access to Map</label
                >
                <div class="flex gap-4 w-full mt-4" id="map">
                    <Link href="/maps/user" class="w-full h-fit">
                        <button
                            class="w-full transition-colors font-medium select-none disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-dark-eval-2 bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500 rounded-md px-5 py-4 text-center whitespace-nowrap"
                        >
                            Maps User
                        </button>
                    </Link>
                    <Link href="/maps/superuser" class="w-full h-fit">
                        <button
                            class="w-full transition-colors font-medium select-none disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-dark-eval-2 bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500 rounded-md px-5 py-4 text-center whitespace-nowrap"
                        >
                            Maps SuperUser
                        </button>
                    </Link>
                    <Link href="/maps/admin" class="w-full h-fit">
                        <button
                            class="w-full transition-colors font-medium select-none disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-dark-eval-2 bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500 rounded-md px-5 py-4 text-center whitespace-nowrap"
                        >
                            Maps Admin
                        </button>
                    </Link>
                </div>
            </div>

            <div class="flex gap-4 w-full justify-end">
                <form
                    action="/maps/export"
                    method="GET"
                    class="flex flex-col gap-4"
                >
                    <div class="flex gap-4">
                        <div>
                            <label
                                for="start_date"
                                class="block text-sm font-medium"
                                >Start Date</label
                            >
                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                v-model="startDate"
                                class="mt-1 block w-full px-3 py-2 bg-dark-eval-1 border border-dark-eval-4 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                :max="today"
                                required
                            />
                        </div>
                        <div>
                            <label
                                for="end_date"
                                class="block text-sm font-medium"
                                >End Date</label
                            >
                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                v-model="endDate"
                                class="mt-1 block w-full px-3 py-2 bg-dark-eval-1 border border-dark-eval-4 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                :max="today"
                                required
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="flex items-center transition-colors font-medium select-none disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-dark-eval-2 px-5 py-2 bg-green-500 text-white hover:bg-green-600 focus:ring-green-500 rounded-md justify-center gap-2"
                    >
                        Export History Excel
                    </button>
                </form>
            </div>
        </div>
        <div class="mt-16">
            <h2 class="text-xl font-semibold leading-tight mb-8">History</h2>
            <History />
        </div>
    </AuthenticatedLayout>
</template>
