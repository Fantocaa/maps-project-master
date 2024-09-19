<script setup>
import UnautenticatedLayout from "@/Layouts/UnAuthenticated.vue";
import Button from "@/Components/Button.vue";
import { GithubIcon } from "@/Components/Icons/brands";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="h-screen">
        <UnautenticatedLayout title="Errors">
            <!-- <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <h2 class="text-xl font-semibold leading-tight">Error</h2>
            </div>
        </template> -->

            <div class="flex flex-col items-center justify-center text-center">
                <img src="/images/icon/404.svg" class="2xl:w-full max-w-2xl" />
                <Link href="/login">
                    <Button
                        external
                        variant="primary"
                        class="items-center gap-2 max-w-xs text-center"
                        v-slot="{ iconSizeClasses }"
                    >
                        <span>Kembali ke Laman</span>
                    </Button>
                </Link>
            </div>
            <!-- <div
            class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 flex justify-between"
        >
            <h1>Tidak menemukan Halaman yang kamu cari</h1>

            <Button
                external
                variant="black"
                class="items-center gap-2 max-w-xs"
                v-slot="{ iconSizeClasses }"
                href="/login"
            >
                <span>Kembali ke Laman</span>
            </Button>
        </div> -->
        </UnautenticatedLayout>
    </div>
</template>
