<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net";
import "datatables.net-select";
import "datatables.net-responsive";
import axios from "axios";
import { ref, onMounted } from "vue";
import Button from "@/Components/Button.vue";

DataTable.use(DataTablesCore);

// Get CSRF token from meta tag
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

// Set default headers for axios
axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

// Initialize forms

const agentForm = useForm({
    name_agent: "",
});

const agentData = ref(null);

const fetchDataAgent = async () => {
    const response = await axios.get("/agent");
    agentData.value = response.data;
};

const submitAgentForm = async () => {
    try {
        await agentForm.post("/manage/agent/new");
        alert("Data Saving Success");
        closeModal("agent");
        await fetchDataAgent();
    } catch (error) {
        alert("Data Saving Failed. Try Again!");
    }
};

const closeModal = (modalId) => {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.close();
    }
};

onMounted(async () => {
    fetchDataAgent();
});

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
        render: function (data, type, row) {
            return `<a href="/manage/agent/${data.id}/edit">
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
    <AuthenticatedLayout title="Manage Agent">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4"
            >
                <dialog id="agent" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form @submit.prevent="submitAgentForm">
                            <label
                                for="name_agent"
                                class="block text-sm font-medium"
                                >Agent Name</label
                            >
                            <input
                                type="text"
                                id="name_agent"
                                name="name_agent"
                                v-model="agentForm.name_agent"
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
            <div class="w-full mb-16">
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
            </div>
        </template>
    </AuthenticatedLayout>
</template>
