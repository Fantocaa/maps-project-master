<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Link } from "@inertiajs/vue3";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net";
// import $ from "jquery";
import axios from "axios";
import { ref, onMounted } from "vue";
import Button from "@/Components/Button.vue";
import "datatables.net-select";
import "datatables.net-responsive";
// import { GithubIcon } from "@/Components/Icons/brands";

DataTable.use(DataTablesCore);

let data = ref([]);

const fetchData = async () => {
    const response = await axios.get("/role");
    data.value = response.data;
    // console.log(response.data);
};

onMounted(async () => {
    fetchData();
});

const columns = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "name", title: "Name" },
    { data: "email", title: "Email" },
    // { data: "password", title: "Password" },
    { data: "role", title: "Role" },
    { data: "company", title: "Company" },
    { data: "view_company", title: "View Company" },
    // { data: "view_customer", title: "View Customer" },
    {
        data: null,
        title: "Actions",
        // orderable: false,
        render: function (data, type, row) {
            return `<a href="/manage/user/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];

// console.log(columns);
</script>

<style>
@import "datatables.net-dt";
@import "datatables.net-bs5";
/* @import "bootstrap"; */
</style>

<template>
    <AuthenticatedLayout title="Manage User">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8"
            >
                <h2 class="text-xl font-semibold leading-tight">Manage User</h2>
                <Button
                    class="items-center gap-2 max-w-xs"
                    v-slot="{ iconSizeClasses }"
                    href="/register"
                >
                    <!-- <GithubIcon aria-hidden="true" :class="iconSizeClasses" /> -->
                    <span>Add New User</span>
                </Button>
            </div>
        </template>
        <template #default>
            <DataTable
                :data="data"
                :columns="columns"
                class="table table-hover table-striped"
                width="100%"
            />
        </template>
    </AuthenticatedLayout>
</template>
