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
const companyForm = useForm({
    name_company: "",
});

const agentForm = useForm({
    name_agent: "",
});

const unitForm = useForm({
    name_satuan: "",
});

const biayaForm = useForm({
    biaya_name: "",
});

const jenisbarangForm = useForm({
    jenis_barang_name: "",
});

const companyData = ref(null);
const agentData = ref(null);
const unitData = ref(null);
const biayaData = ref(null);
const JenisBarangData = ref(null);

const fetchDataCompany = async () => {
    const response = await axios.get("/company");
    companyData.value = response.data;
};

const fetchDataAgent = async () => {
    const response = await axios.get("/agent");
    agentData.value = response.data;
};

const fetchDataUnit = async () => {
    const response = await axios.get("/unit");
    unitData.value = response.data;
};

const fetchDataBiaya = async () => {
    const response = await axios.get("/biaya_name");
    biayaData.value = response.data;
};

const fetchDataJenisBarang = async () => {
    const response = await axios.get("/jenis_barang");
    JenisBarangData.value = response.data;
};

const submitCompanyForm = async () => {
    try {
        await companyForm.post("/manage/company/new");
        alert("Data Saving Success");
        closeModal("company");
        await fetchDataCompany();
    } catch (error) {
        alert("Data Saving Failed. Try Again!");
    }
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
const submitUnitForm = async () => {
    try {
        await unitForm.post("/manage/unit/new");
        alert("Data Saving Success");
        closeModal("unit");
        await fetchDataUnit();
    } catch (error) {
        alert("Data Saving Failed. Try Again!");
    }
};
const submitBiayaForm = async () => {
    try {
        await biayaForm.post("/manage/biaya/new");
        alert("Data Saving Success");
        closeModal("biaya");
        await fetchDataBiaya();
    } catch (error) {
        alert("Data Saving Failed. Try Again!");
    }
};
const submitJenisBarangForm = async () => {
    try {
        await jenisbarangForm.post("/manage/jenisbarang/new");
        alert("Data Saving Success");
        closeModal("jenis");
        await fetchDataJenisBarang();
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
    fetchDataCompany();
    fetchDataAgent();
    fetchDataUnit();
    fetchDataBiaya();
    fetchDataJenisBarang();
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
        data: "agents", // Menampilkan nama agen
        title: "Agents",
        render: function (data, type, row) {
            if (data && data.length > 0) {
                return data.map((agent) => agent.name_agent).join(", ");
            }
            return "No agents"; // Jika tidak ada agen
        },
    },
    {
        data: null,
        title: "Actions",
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
        render: function (data, type, row) {
            return `<a href="/manage/biaya/${data.id}/edit">
                <button type="btn" class="btn btn-primary">
            Edit</button></a>`;
        },
    },
];

const columnsJenisBarang = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "jenis_barang_name", title: "Name" },
    {
        data: null,
        title: "Actions",
        render: function (data, type, row) {
            return `<a href="/manage/jenisbarang/${data.id}/edit">
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
    <AuthenticatedLayout title="Manage Master Data">
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4"
            >
                <dialog id="company" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form @submit.prevent="submitCompanyForm">
                            <label
                                for="name_company"
                                class="block text-sm font-medium"
                                >Company Name</label
                            >
                            <input
                                type="text"
                                id="name_company"
                                name="name_company"
                                v-model="companyForm.name_company"
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
                <dialog id="unit" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form @submit.prevent="submitUnitForm">
                            <label
                                for="name_satuan"
                                class="block text-sm font-medium"
                                >Unit Name</label
                            >
                            <input
                                type="text"
                                id="name_satuan"
                                name="name_satuan"
                                v-model="unitForm.name_satuan"
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
                        <form @submit.prevent="submitBiayaForm">
                            <label
                                for="biaya_name"
                                class="block text-sm font-medium"
                                >Biaya Name</label
                            >
                            <input
                                type="text"
                                id="biaya_name"
                                name="biaya_name"
                                v-model="biayaForm.biaya_name"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm my-4 text-dark-eval-0"
                            />
                            <button type="submit" class="mt-2 btn btn-primary">
                                Submit
                            </button>
                        </form>
                    </div>
                </dialog>
                <dialog id="jenis" class="modal">
                    <div class="modal-box bg-dark-eval-0">
                        <form method="dialog">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                            >
                                ✕
                            </button>
                        </form>
                        <form @submit.prevent="submitJenisBarangForm">
                            <label
                                for="jenis_barang_name"
                                class="block text-sm font-medium"
                                >Jenis Barang Name</label
                            >
                            <input
                                type="text"
                                id="jenis_barang_name"
                                name="jenis_barang_name"
                                v-model="jenisbarangForm.jenis_barang_name"
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
                <div
                    class="border-dark-eval-2 border p-4 rounded-xl col-span-2 w-full"
                >
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
                        class="table table-hover table-striped"
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
                        <h1 class="font-semibold text-xl">Satuan Unit List</h1>
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
                        <h1 class="font-semibold text-xl">Nama Biaya List</h1>
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

                <div class="border-dark-eval-2 border p-4 rounded-xl">
                    <div class="flex justify-between mb-8 items-center">
                        <h1 class="font-semibold text-xl">Jenis Barang List</h1>
                        <button
                            class="btn border border-dark-eval-2"
                            onclick="jenis.showModal()"
                        >
                            Add New Jenis Barang
                        </button>
                    </div>
                    <DataTable
                        :data="JenisBarangData"
                        :columns="columnsJenisBarang"
                        class="table table-hover table-striped"
                        width="100%"
                    />
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
