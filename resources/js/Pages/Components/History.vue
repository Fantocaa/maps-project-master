<script setup>
import { ref, onMounted } from "vue";
import DataTable from "datatables.net-vue3";
import DataTablesCore from "datatables.net";
import "datatables.net-select";
import "datatables.net-responsive";
import $ from "jquery";

DataTable.use(DataTablesCore);
let data = ref([]);
let dataTable = null; // Tambahkan variabel untuk menyimpan referensi ke instance DataTables

const fetchData = async () => {
    const response = await axios.get("/maps/index");
    data.value = response.data;
};

onMounted(async () => {
    await fetchData();
    dataTable = $("#myTable").DataTable({
        data: data.value,
        columns: columns,
        language: {
            search: "Filter records:",
        },
    });
});

const columns = [
    {
        data: "id",
        title: "No",
        render: function (data, type, row, meta) {
            return meta.row + 1;
        },
    },
    { data: "date", title: "Date" },
    { data: "name", title: "Name" },
    { data: "notes", title: "Notes" },
    { data: "lokasi", title: "Location" },
];
</script>

<style>
@import "datatables.net-dt";
@import "datatables.net-bs5";
/* @import "bootstrap"; */
</style>

<template>
    <DataTable
        :data="data"
        :columns="columns"
        class="table table-hover table-striped"
        width="100%"
    />
</template>
