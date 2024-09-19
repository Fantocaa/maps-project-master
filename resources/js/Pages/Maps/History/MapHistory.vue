<script setup>
import { defineProps, defineEmits, computed } from "vue";

// Define props
const props = defineProps({
    showHistory: Boolean,
    historyData: Array,
    matchingUser: Object,
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

const getFirstEntry = (data) => {
    if (data.length > 0) {
        const firstEntry = data[0];
        return {
            customer: firstEntry.customer?.name_company || "Unknown Customer",
            jenisBarang:
                firstEntry.jenisbarang?.jenis_barang_name ||
                "Unknown Jenis Barang",
            satuan: firstEntry.satuan?.name_satuan || "Tidak ada Satuan",
            harga: firstEntry.harga || "Tidak ada Harga",
            harga_modal: firstEntry.harga_modal || "Tidak ada Harga Modal",
        };
    }
    return {};
};

const groupByExactDateTimeAndCustomerAndJenis = (data) => {
    if (!data || !Array.isArray(data)) {
        // Jika data tidak ada atau bukan array, kembalikan array kosong
        return {};
    }

    const sortedData = data.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at)
    );

    const firstEntry = getFirstEntry(sortedData);

    return sortedData.reduce((acc, curr) => {
        const formattedDateTime = formatDateTime(curr.created_at);
        const customer = curr.customer?.name_company || "Unknown Customer";
        const jenisBarang =
            curr.jenisbarang?.jenis_barang_name || "Unknown Jenis Barang";
        const dateKey = formattedDateTime;

        if (!acc[dateKey]) {
            acc[dateKey] = {
                date: dateKey,
                items: [],
            };
        }

        const isUpdated =
            curr.customer?.name_company !== firstEntry.customer ||
            curr.jenisbarang?.jenis_barang_name !== firstEntry.jenisBarang ||
            curr.satuan?.name_satuan !== firstEntry.satuan ||
            curr.harga !== firstEntry.harga ||
            curr.harga_modal !== firstEntry.harga_modal;

        acc[dateKey].items.push({
            customer,
            jenisBarang,
            satuan: curr.satuan?.name_satuan || "Tidak ada Satuan",
            harga: curr.harga || "Tidak ada Harga",
            harga_modal: curr.harga_modal || "Tidak ada Harga Modal",
            isUpdated,
        });

        return acc;
    }, {});
};

const groupedData = computed(() => {
    // Pastikan props.historyData tidak undefined atau null
    if (!props.historyData || !Array.isArray(props.historyData)) {
        return {}; // Kembalikan objek kosong jika tidak ada data
    }

    return groupByExactDateTimeAndCustomerAndJenis(props.historyData);
});
</script>

<template>
    <section>
        <div class="overflow-y-scroll xl:max-h-72 2xl:max-h-[392px]">
            <!-- Display grouped history data here -->
            <div v-if="Object.keys(groupedData).length">
                <!-- Iterate over each group -->
                <div
                    v-for="(group, dateKey) in groupedData"
                    :key="dateKey"
                    class="border rounded-2xl mb-4 mr-2"
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
                                        class="bg-gray-50 cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
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
                                        class="bg-gray-50 cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>
                            </div>
                            <div
                                :class="{
                                    'grid grid-cols-3 gap-4 mt-4 rounded-lg':
                                        (matchingUser &&
                                            matchingUser.roles.includes(
                                                'superadmin'
                                            )) ||
                                        (matchingUser &&
                                            matchingUser.roles.includes(
                                                'admin'
                                            )),
                                    'grid grid-cols-2 gap-4 mt-4 rounded-lg':
                                        !(
                                            matchingUser &&
                                            matchingUser.roles.includes(
                                                'superadmin'
                                            )
                                        ) &&
                                        !(
                                            matchingUser &&
                                            matchingUser.roles.includes('admin')
                                        ),
                                }"
                            >
                                <div>
                                    <label for="satuan" class="font-medium"
                                        >Satuan</label
                                    >
                                    <input
                                        id="satuan"
                                        :value="item.satuan"
                                        class="bg-gray-50 cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
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
                                        class="bg-gray-50 cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        disabled
                                    />
                                </div>

                                <div
                                    v-if="
                                        (matchingUser &&
                                            matchingUser.roles.includes(
                                                'superadmin'
                                            )) ||
                                        (matchingUser &&
                                            matchingUser.roles.includes(
                                                'admin'
                                            ))
                                    "
                                >
                                    <label for="harga_modal" class="font-medium"
                                        >Harga Modal</label
                                    >
                                    <input
                                        id="harga_modal"
                                        type="text"
                                        :value="item.harga_modal"
                                        class="bg-gray-50 cursor-not-allowed w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
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
