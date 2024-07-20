<script setup>
import UnautenticatedLayout from "@/Layouts/UnAuthenticated.vue";
import Button from "@/Components/Button.vue";
import { GithubIcon } from "@/Components/Icons/brands";
</script>

<template>
    <UnautenticatedLayout title="Errors">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <h2 class="text-xl font-semibold leading-tight">Errors</h2>
            </div>
        </template>

        <div
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
        </div>
    </UnautenticatedLayout>
</template>
