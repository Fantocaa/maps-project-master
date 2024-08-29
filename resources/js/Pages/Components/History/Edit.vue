<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import Input from "@/Components/Input.vue";
import { Head } from "@inertiajs/vue3";
import Label from "@/Components/Label.vue";
import InputError from "@/Components/InputError.vue";
import Button from "@/Components/Button.vue";

const { props } = usePage();
const mapsData = props.mapsData;

console.log(mapsData[0].id);

const lat = mapsData.length > 0 ? mapsData[0].lat : null;
const lng = mapsData.length > 0 ? mapsData[0].lng : null;

// Fungsi untuk membuat URL Google Static Maps
const getStaticMapUrl = (lat, lng) => {
    const apiKey = "AIzaSyD2dASx5Zo68GSyZuPjUs-4SBLYGsn4OPQ"; // Ganti dengan kunci API Anda
    const zoom = 10; // Zoom level, bisa diubah sesuai kebutuhan
    const size = "1280x200"; // Ukuran gambar peta
    const marker = `&markers=color:red%7C${lat},${lng}`; // Marker pada peta

    return `https://maps.googleapis.com/maps/api/staticmap?center=${lat},${lng}&zoom=${zoom}&maptype=roadmap&size=${size}&scale=2${marker}&key=${apiKey}`;
};

// Fungsi untuk memformat tanggal dan waktu menggunakan toLocaleString
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("id-ID", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: false, // Menggunakan format waktu 24 jam
    });
};

// Fungsi untuk mengelompokkan data berdasarkan tanggal, customer, dan jenis barang
const groupByDateTimeAndCustomerAndJenis = (data) => {
    return data.reduce((acc, curr) => {
        const formattedDateTime = formatDateTime(curr.created_at);
        const date = formattedDateTime.split(",")[0];
        const customer = curr.customer?.name_company || "Unknown Customer";
        const jenisBarang =
            curr.jenisbarang?.jenis_barang_name || "Unknown Jenis Barang";
        const key = `${date}-${customer}-${jenisBarang}`;

        if (!acc[key]) {
            acc[key] = { date, customer, jenisBarang, items: [] };
        }

        // Tambahkan atau perbarui item dalam kelompok
        const existingItem = acc[key].items.find((item) => item.id === curr.id);
        if (existingItem) {
            // Perbarui informasi yang ada
            Object.assign(existingItem, {
                satuan: curr.satuan?.name_satuan || "Unknown Satuan",
                harga: curr.harga || "Unknown Harga",
                harga_modal: curr.harga_modal || "Unknown Harga Modal",
            });
        } else {
            // Tambahkan item baru
            acc[key].items.push({
                id: curr.id,
                satuan: curr.satuan?.name_satuan || "Unknown Satuan",
                harga: curr.harga || "Unknown Harga",
                harga_modal: curr.harga_modal || "Unknown Harga Modal",
            });
        }

        return acc;
    }, {});
};

const groupedData = groupByDateTimeAndCustomerAndJenis(mapsData);
</script>

<template>
    <Head title="View Marker History" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    View Marker History
                </h2>
                <!-- <a :href="`/maps/export/${mapsData[0].id}`">
                    <Button
                        class="items-center gap-2 max-w-xs bg-green-500 hover:bg-green-600"
                    >
                        <span>Export Excel</span>
                    </Button>
                </a> -->
            </div>
        </template>

        <img
            :src="getStaticMapUrl(lat, lng)"
            alt="Static Map"
            class="w-full h-full rounded-lg"
        />

        <div class="mt-8">
            <div class="w-full grid grid-cols-2 gap-4">
                <div>
                    <Label for="name" value="Nama User" />
                    <Input
                        id="name"
                        type="text"
                        v-model="mapsData[0].name"
                        disabled
                        class="w-full cursor-not-allowed"
                    />
                </div>
                <div>
                    <Label for="name_company" value="Perusahaan User" />
                    <Input
                        id="name_company"
                        type="text"
                        v-model="mapsData[0].perusahaan.name_company"
                        disabled
                        class="w-full cursor-not-allowed"
                    />
                </div>
                <div>
                    <Label for="lokasi" value="Lokasi" />
                    <textarea
                        id="lokasi"
                        type="text"
                        v-model="mapsData[0].lokasi"
                        disabled
                        :class="[
                            'py-2 border-gray-400 rounded-md w-full h-full cursor-not-allowed',
                            'focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white',
                            'dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1',
                        ]"
                    />
                </div>
                <div>
                    <Label for="name_agent" value="Nama Agent" />
                    <Input
                        id="name_agent"
                        type="text"
                        v-model="mapsData[0].agent.name_agent"
                        disabled
                        class="w-full cursor-not-allowed"
                    />
                </div>
            </div>
        </div>

        <div class="mt-24">
            <h1
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4"
            >
                History Input
            </h1>
            <!-- Iterasi kelompok data berdasarkan kombinasi tanggal, customer, dan jenis barang -->
            <div v-for="(group, key) in groupedData" :key="key" class="mb-8">
                <h3 class="text-lg font-semibold mb-2">
                    Tanggal: {{ group.date }}
                </h3>

                <div class="bg-dark-eval-1 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="customer" value="Customer" />
                            <Input
                                id="customer"
                                v-model="group.customer"
                                class="w-full cursor-not-allowed"
                                disabled
                            />
                        </div>
                        <div>
                            <Label for="jenis" value="Jenis Barang" />
                            <Input
                                id="jenis"
                                type="text"
                                v-model="group.jenisBarang"
                                class="w-full cursor-not-allowed"
                                disabled
                            />
                        </div>
                    </div>
                    <!-- Iterasi untuk setiap item dalam kelompok -->
                    <div
                        v-for="item in group.items"
                        :key="item.id"
                        class="mb-4"
                    >
                        <div
                            class="grid grid-cols-3 gap-4 mt-4 bg-dark-eval-3 p-4 rounded-lg"
                        >
                            <div>
                                <Label for="satuan" value="Satuan" />
                                <Input
                                    id="satuan"
                                    v-model="item.satuan"
                                    class="w-full cursor-not-allowed"
                                    disabled
                                />
                            </div>
                            <div>
                                <Label for="harga" value="Harga Jual" />
                                <Input
                                    id="harga"
                                    type="text"
                                    v-model="item.harga"
                                    class="w-full cursor-not-allowed"
                                    disabled
                                />
                            </div>
                            <div>
                                <Label for="harga_modal" value="Harga Modal" />
                                <Input
                                    id="harga_modal"
                                    type="text"
                                    v-model="item.harga_modal"
                                    class="w-full cursor-not-allowed"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
