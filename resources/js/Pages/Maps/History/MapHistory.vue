<script setup>
import { defineProps, defineEmits, computed } from "vue";

// Define props
const props = defineProps({
    showHistory: Boolean,
    historyData: Array,
});

const emit = defineEmits(["back"]);

const goBack = () => {
    emit("back");
};

// Function to format date and time using toLocaleString
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: false,
    });
};

// const groupByExactDateTimeCustomerAndJenis = (data) => {
//     // Sort the data in descending order by created_at
//     const sortedData = data.sort(
//         (a, b) => new Date(b.created_at) - new Date(a.created_at)
//     );

//     return sortedData.reduce((acc, curr) => {
//         const formattedDateTime = formatDateTime(curr.created_at);
//         const customer = curr.customer?.name_company || "Unknown Customer";
//         const jenisBarang =
//             curr.jenisbarang?.jenis_barang_name || "Unknown Jenis Barang";
//         const key = ${formattedDateTime}-${customer}-${jenisBarang};

//         if (!acc[key]) {
//             acc[key] = {
//                 date: formattedDateTime,
//                 customer,
//                 jenisBarang,
//                 items: [],
//             };
//         }

//         // Add the item to the group's items array
//         acc[key].items.push({
//             id: curr.id,
//             satuan: curr.satuan?.name_satuan || "Tidak ada Satuan",
//             harga: curr.harga || "Tidak ada Harga",
//             harga_modal: curr.harga_modal || "Tidak ada Harga Modal",
//         });

//         return acc;
//     }, {});

// Function to group data by exact datetime and then by customer and jenis barang

const groupByExactDateTimeAndCustomerAndJenis = (data) => {
    // Sort the data in descending order by created_at
    const sortedData = data.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    return sortedData.reduce((acc, curr) => {
        const formattedDateTime = formatDateTime(curr.created_at);
        const customer = curr.customer?.name_company || "Unknown Customer";
        const jenisBarang =
            curr.jenisbarang?.jenis_barang_name || "Unknown Jenis Barang";
        const dateKey = formattedDateTime; // Use exact datetime as key

        // Create a key combining datetime and customer
        const key = `${dateKey}-${customer}-${jenisBarang}`;

        if (!acc[dateKey]) {
            acc[dateKey] = {
                date: dateKey,
                items: [],
            };
        }

        // Add or update item in the group
        acc[dateKey].items.push({
            customer,
            jenisBarang,
            satuan: curr.satuan?.name_satuan || "Tidak ada Satuan",
            harga: curr.harga || "Tidak ada Harga",
            harga_modal: curr.harga_modal || "Tidak ada Harga Modal",
        });

        return acc;
    }, {});
};

// Compute the grouped data using the historyData prop
const groupedData = computed(() =>
    groupByExactDateTimeAndCustomerAndJenis(props.historyData)
);
</script>

<template>
    <section>
        <div class="overflow-y-scroll max-h-72">
            <!-- Display grouped history data here -->
            <div v-if="Object.keys(groupedData).length">
                <!-- Iterate over each group -->
                <div
                    v-for="(group, dateKey) in groupedData"
                    :key="dateKey"
                    class="border rounded-2xl mb-4"
                >
                    <h3
                        class="text-base bg-blue-500 p-4 text-white rounded-t-xl"
                    >
                        Tanggal: {{ group.date }}
                    </h3>

                    <div class="px-4 pt-4 pb-2">
                        <!-- Iterate over each item in the group -->
                        <div
                            v-for="item in group.items"
                            :key="item.customer + item.jenisBarang"
                            class="mb-4"
                        >
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="customer" class="font-medium"
                                        >Customer</label
                                    >
                                    <input
                                        id="customer"
                                        :value="item.customer"
                                        class="cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                                <div>
                                    <label for="jenis" class="font-medium"
                                        >Jenis Barang</label
                                    >
                                    <input
                                        id="jenis"
                                        type="text"
                                        :value="item.jenisBarang"
                                        class="cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 mt-4 rounded-lg">
                                <div>
                                    <label for="satuan" class="font-medium"
                                        >Satuan</label
                                    >
                                    <input
                                        id="satuan"
                                        :value="item.satuan"
                                        class="cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                                <div>
                                    <label for="harga" class="font-medium"
                                        >Harga Jual</label
                                    >
                                    <input
                                        id="harga"
                                        type="text"
                                        :value="item.harga"
                                        class="cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                                <div>
                                    <label for="harga_modal" class="font-medium"
                                        >Harga Modal</label
                                    >
                                    <input
                                        id="harga_modal"
                                        type="text"
                                        :value="item.harga_modal"
                                        class="cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else>No history data available.</div>
        </div>

        <div class="mt-4">
            <button
                type="button"
                class="bg-violet-500 text-white py-3 px-4 rounded-md w-full"
                @click="goBack"
            >
                Kembali
            </button>
        </div>
    </section>
</template>
