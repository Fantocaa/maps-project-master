<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Link } from "@inertiajs/vue3";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net";
import "datatables.net-select";
import "datatables.net-responsive";
import $ from "jquery";
import axios from "axios";
import { reactive, ref, onMounted } from "vue";
import Button from "@/Components/Button.vue";
// import { GithubIcon } from "@/Components/Icons/brands";

DataTable.use(DataTablesCore);

const companyData = ref(null);
const agentData = ref(null);
const unitData = ref(null);
const biayaData = ref(null);

const fetchDataCompany = async () => {
    const response = await axios.get("/api/company");
    companyData.value = response.data;
    // console.log(response.data);
};

const fetchDataAgent = async () => {
    const response = await axios.get("/api/agent");
    agentData.value = response.data;
    // console.log(response.data);
};

const fetchDataUnit = async () => {
    const response = await axios.get("/api/unit");
    unitData.value = response.data;
    // console.log(response.data);
};

const fetchDataBiaya = async () => {
    const response = await axios.get("/api/biaya_name");
    biayaData.value = response.data;
    // console.log(response.data);
};

onMounted(async () => {
    fetchDataCompany();
    fetchDataAgent();
    fetchDataUnit();
    fetchDataBiaya();
});

const columnsCompany = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "name_company", title: "Name" },
    {
        data: null,
        title: "Actions",
        // orderable: false,
        render: function (data, type, row) {
            return `<a href="/manage/company/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];

const columnsAgent = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "name_agent", title: "Name" },
    {
        data: null,
        title: "Actions",
        // orderable: false,
        render: function (data, type, row) {
            return `<a href="/manage/agent/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];

const columnsUnit = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "name_satuan", title: "Name" },
    {
        data: null,
        title: "Actions",
        // orderable: false,
        render: function (data, type, row) {
            return `<a href="/manage/unit/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];

const columnsBiaya = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "biaya_name", title: "Name" },
    {
        data: null,
        title: "Actions",
        // orderable: false,
        render: function (data, type, row) {
            return `<a href="/manage/biaya/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];
</script>

<style>
@import "datatables.net-dt";
@import "datatables.net-bs5";
</style>

<template>
    <AuthenticatedLayout title="Manage Company">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4"
            >
                <!-- <h2 class="text-xl font-semibold leading-tight">
                    Manage Company
                </h2> -->

                <dialog id="company" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form action="/manage/company/new" method="post">
                            <label
                                for="name_company"
                                class="block text-sm font-medium"
                                >Company Name</label
                            >
                            <input
                                type="text"
                                id="name_company"
                                name="name_company"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm my-4 text-dark-eval-0"
                            />
                            <button type="submit" class="mt-2 btn btn-primary">
                                Submit
                            </button>
                        </form>
                    </div>
                </dialog>
                <dialog id="agent" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form action="/manage/agent/new" method="post">
                            <label
                                for="name_agent"
                                class="block text-sm font-medium"
                                >Agent Name</label
                            >
                            <input
                                type="text"
                                id="name_agent"
                                name="name_agent"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm my-4 text-dark-eval-0"
                            />
                            <button type="submit" class="mt-2 btn btn-primary">
                                Submit
                            </button>
                        </form>
                    </div>
                </dialog>
                <dialog id="unit" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form action="/manage/unit/new" method="post">
                            <label
                                for="name_satuan"
                                class="block text-sm font-medium"
                                >Unit Name</label
                            >
                            <input
                                type="text"
                                id="name_satuan"
                                name="name_satuan"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm my-4 text-dark-eval-0"
                            />
                            <button type="submit" class="mt-2 btn btn-primary">
                                Submit
                            </button>
                        </form>
                    </div>
                </dialog>
                <dialog id="biaya" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form action="/manage/biaya/new" method="post">
                            <label
                                for="biaya_name"
                                class="block text-sm font-medium"
                                >Biaya Name</label
                            >
                            <input
                                type="text"
                                id="biaya_name"
                                name="biaya_name"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm my-4 text-dark-eval-0"
                            />
                            <button type="submit" class="mt-2 btn btn-primary">
                                Submit
                            </button>
                        </form>
                    </div>
                </dialog>
            </div>
        </template>
        <template #default>
            <div class="grid grid-cols-2 gap-6 mb-16">
                <div class="border-dark-eval-2 border p-4 rounded-xl">
                    <div class="flex justify-between mb-8 items-center">
                        <h1 class="font-semibold text-xl">
                            Company & Customer List
                        </h1>
                        <button
                            class="btn border border-dark-eval-2"
                            onclick="company.showModal()"
                        >
                            Add New Company
                        </button>
                    </div>
                    <DataTable
                        :data="companyData"
                        :columns="columnsCompany"
                        class="table table-hover table-striped w-1/2"
                    />
                </div>

                <div class="border-dark-eval-2 border p-4 rounded-xl">
                    <div class="flex justify-between mb-8 items-center">
                        <h1 class="font-semibold text-xl">Agent List</h1>
                        <button
                            class="btn border border-dark-eval-2"
                            onclick="agent.showModal()"
                        >
                            Add New Agent
                        </button>
                    </div>
                    <DataTable
                        :data="agentData"
                        :columns="columnsAgent"
                        class="table table-hover table-striped"
                        width="100%"
                    />
                </div>

                <div class="border-dark-eval-2 border p-4 rounded-xl">
                    <div class="flex justify-between mb-8 items-center">
                        <h1 class="font-semibold text-xl">Unit List</h1>
                        <button
                            class="btn border border-dark-eval-2"
                            onclick="unit.showModal()"
                        >
                            Add New Unit
                        </button>
                    </div>
                    <DataTable
                        :data="unitData"
                        :columns="columnsUnit"
                        class="table table-hover table-striped"
                        width="100%"
                    />
                </div>

                <div class="border-dark-eval-2 border p-4 rounded-xl">
                    <div class="flex justify-between mb-8 items-center">
                        <h1 class="font-semibold text-xl">Biaya List</h1>
                        <button
                            class="btn border border-dark-eval-2"
                            onclick="biaya.showModal()"
                        >
                            Add New Biaya
                        </button>
                    </div>
                    <DataTable
                        :data="biayaData"
                        :columns="columnsBiaya"
                        class="table table-hover table-striped"
                        width="100%"
                    />
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
