<script>
import { defineComponent, ref, onMounted } from "vue";
import { Marker } from "vue3-google-map";
// import $ from "jquery";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { reactive } from "vue";
import MapHistory from "./History/MapHistory.vue";

export default defineComponent({
    data() {
        return {
            rawHarga: 0,
            biaya: {
                harga: "",
                harga_modal: "",
            },
        };
    },
    methods: {
        handleInput(event) {
            const input = event.target.value;
            const numericValue = parseInt(input.replace(/\D/g, ""), 10) || 0;
            this.biaya.harga = numericValue.toString();
            this.biaya.harga_modal = numericValue.toString();
            event.target.value = "Rp. " + numericValue.toLocaleString("id-ID");
        },
    },
    emits: ["position_changed", "tilt_changed"],
    components: {
        Marker,
        Head,
        Link,
        vSelect,
        MapHistory,
    },
    props: { auth: Object },
    setup(props) {
        const center = ref({ lat: -7.250445, lng: 112.768845 });
        const markers = ref([]);
        const klikmarker = ref([]);
        const selectedMarker = ref(false);
        const mapInstance = ref(null);
        const address = ref("");
        const user = ref([]);
        const agent = ref([]);
        // const customer = ref([]);
        const satuan = ref([]);
        const biaya = ref([]);
        const jenis_barang = ref([]);
        const matchingUser = ref(null);
        const customerOptions = reactive([]);
        const zoom = ref(7); // Atur level zoom awal
        const isLoading = ref(false); // Tambahkan state loading
        const showHistory = ref(false);
        const markerHistory = ref([]);

        const getCurrentLocation = () => {
            isLoading.value = true; // Mulai loading
            if (markers.value.length > 0) {
                // Set the center to the first marker's position
                center.value = markers.value[0].position;
                isLoading.value = false; // Selesai loading
            } else {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };
                            center.value = userLocation;
                            isLoading.value = false; // Selesai loading
                        },
                        (error) => {
                            console.error("Error getting location:", error);
                            isLoading.value = false; // Selesai loading
                        }
                    );
                } else {
                    console.error(
                        "Geolocation is not supported by this browser."
                    );
                    isLoading.value = false; // Selesai loading
                }
            }
        };

        const getReverseGeocoding = async (lat, lng) => {
            try {
                const response = await axios.get("/reverse-geocode", {
                    params: { lat, lng },
                });
                return response.data.address;
            } catch (error) {
                console.error(error);
                return null;
            }
        };

        const logout = () => {
            router.post("/logout");
        };

        const options = ref([]);

        const formInput = ref([
            {
                notes: "",
                name_penerima: "",
                name_agent: null,
                name_customer: null,
                name_satuan: null,
                jenis_barang_name: null,
                // biaya: [{ nama: null, harga: "", harga_modal: "" }],
                items: [
                    {
                        name_satuan: null,
                        biaya: [{ nama: "", harga: "", harga_modal: "" }],
                    },
                ],
            },
        ]);

        const validationErrors = ref({
            name_agent: "",
            name_penerima: "",
            items: [],
        });

        const resetForm = (index) => {
            // Reset data formulir untuk marker tertentu
            if (index >= 0 && index < formInput.value.length) {
                const input = formInput.value[index];
                input.notes = "";
                input.name_penerima = "";
                input.name_agent = null;
                input.name_customer = null;
                input.name_satuan = null;
                input.jenis_barang_name = null;
                input.items.forEach((item) => {
                    item.name_satuan = null;
                    item.biaya.forEach((biaya) => {
                        biaya.nama = "";
                        biaya.harga = "";
                        biaya.harga_modal = "";
                    });
                });
            }
        };

        const handleMapIdle = () => {
            saveMapPosition(); // Save map position on map idle
        };

        const saveMapPosition = () => {
            if (mapInstance.value) {
                const mapPosition = {
                    center: mapInstance.value.getCenter().toJSON(),
                    zoom: mapInstance.value.getZoom(),
                };
                localStorage.setItem(
                    "mapPosition",
                    JSON.stringify(mapPosition)
                );
                console.log("Map position has been cached:", mapPosition);
            }
        };

        const loadMapPosition = () => {
            const cachedPosition = localStorage.getItem("mapPosition");
            if (cachedPosition) {
                const { center: cachedCenter, zoom: cachedZoom } =
                    JSON.parse(cachedPosition);
                center.value = cachedCenter;
                zoom.value = cachedZoom;
                mapInstance.value.setCenter(cachedCenter);
                mapInstance.value.setZoom(cachedZoom);
                console.log("Loaded map position from cache:", {
                    center: cachedCenter,
                    zoom: cachedZoom,
                });
            }
        };

        const handleMarkerClick = (clickedMarker) => {
            // Temukan indeks marker yang sesuai
            const index = markers.value.findIndex(
                (marker) =>
                    marker.position.lat === clickedMarker.position.lat &&
                    marker.position.lng === clickedMarker.position.lng
            );

            if (index === -1) {
                console.error("Marker not found");
                return;
            }

            const clickedMarkerData = markers.value[index];

            if (!clickedMarkerData.id) {
                // Marker tidak memiliki ID, hapus langsung
                markers.value.splice(index, 1);
            } else {
                // Struktur data terbaru dengan multiple customers
                selectedMarker.value = {
                    id: clickedMarkerData.id,
                    notes: clickedMarker.notes,
                    name: clickedMarker.name,
                    date: clickedMarker.date,
                    lokasi: clickedMarker.lokasi,
                    name_company: clickedMarker.name_company,
                    name_penerima: clickedMarker.name_penerima,
                    name_agent: clickedMarker.name_agent,
                    // name_customer: clickedMarker.name_customer,
                    customers: clickedMarker.customers.map((customer) => ({
                        name_customer: customer.name_customer,
                        satuan: customer.satuan.map((satuan) => ({
                            name_satuan: satuan.name_satuan,
                            jenis_barang_name: satuan.jenis_barang_name || null,
                            biaya: satuan.biaya.map((biaya) => ({
                                name_biaya: biaya.name_biaya,
                                harga: biaya.harga,
                                harga_modal: biaya.harga_modal,
                                isSaved: true, // Menandakan bahwa data ini sudah disimpan di database
                            })),
                            isSaved: true, // Menandakan bahwa data ini sudah disimpan di database
                        })),
                        isSaved: true, // Menandakan bahwa data ini sudah disimpan di database
                    })),
                    showForm: true,
                };

                $("#showmarker").show();
            }

            // console.log(selectedMarker.value);

            // Update zoom dan center
            center.value = clickedMarker.position;

            if (mapInstance.value) {
                mapInstance.value.setZoom(zoom.value);
                mapInstance.value.setCenter(center.value);
            }

            // Panggil getReverseGeocoding dengan posisi marker yang diklik
            getReverseGeocoding(
                clickedMarker.position.lat,
                clickedMarker.position.lng
            )
                .then((addr) => {
                    if (addr) {
                        address.value = addr; // Update address dengan alamat yang diterima
                    }
                })
                .catch((error) => console.error(error));
        };

        const defineSelectedMarkerValueSatuan = (satuan) => {
            if (selectedMarker.value) {
                selectedMarker.value.satuan = satuan;
            }
        };

        const mapWasMounted = (_map) => {
            mapInstance.value = _map;

            // Tambahkan event listener untuk idle event
            _map.addListener("idle", handleMapIdle);
            console.log("Idle event listener added.");

            loadMapPosition();
        };

        const toggleHistory = async () => {
            if (!showHistory.value) {
                // Jika showHistory sebelumnya false, maka fetch data history
                await fetchHistory();
            }
            showHistory.value = !showHistory.value;
        };

        // Fungsi untuk fetch data history
        const fetchHistory = async () => {
            try {
                if (selectedMarker.value) {
                    const response = await fetch(
                        `/history/${selectedMarker.value.id}`
                    );
                    const data = await response.json();
                    markerHistory.value = data; // Simpan data history ke markerHistory

                    // console.log(data);
                }
            } catch (error) {
                console.error("Failed to fetch history data:", error);
            }
        };

        const fetchUser = async () => {
            try {
                const response = await axios.get("/role");
                const data = response.data;
                user.value = data.map((user) => ({
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    email_verified_at: user.email_verified_at,
                    created_at: user.created_at,
                    updated_at: user.updated_at,
                    deleted_at: user.deleted_at,
                    roles: user.role,
                    company: user.company,
                    view_company: user.view_company,
                    // view_customer: user.view_customer,
                }));

                // Mencari user yang cocok
                const foundUser = user.value.find(
                    (u) => u.id === props.auth.user.id
                );

                if (foundUser) {
                    // User ditemukan, simpan data tersebut ke matchingUser
                    matchingUser.value = foundUser;
                    // console.log("User ditemukan:", matchingUser.value);
                    fetchData();
                } else {
                    // User tidak ditemukan, lakukan sesuatu yang lain
                    // console.log("User tidak ditemukan");
                }
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchData = async () => {
            // const cachedMarkers = localStorage.getItem("markers");
            // if (cachedMarkers) {
            //     markers.value = JSON.parse(cachedMarkers);
            //     // console.log("Loaded markers from cache.");
            // } else {
            try {
                const response = await fetch("/maps/index");
                let data = await response.json();

                // Filter data berdasarkan name_company yang sama dengan name_company dari matchingUser

                data = data.filter(
                    (map) =>
                        matchingUser.value.view_company.includes(
                            map.name_company
                        ) ||
                        matchingUser.value.company.includes(map.name_company) ||
                        matchingUser.value.company.some((company) =>
                            map.customers.some(
                                (customer) => customer.name_customer === company
                            )
                        )
                );

                // console.log(data);

                markers.value = data.map((map) => {
                    return {
                        position: {
                            lat: parseFloat(map.lat),
                            lng: parseFloat(map.lng),
                        },
                        id: map.id,
                        notes: map.notes,
                        name: map.name,
                        date: map.date,
                        lokasi: map.lokasi,
                        name_agent: map.name_agent,
                        name_company: map.name_company,
                        name_penerima: map.name_penerima,
                        // Menggunakan seluruh data customers
                        customers: map.customers.map((customer) => ({
                            name_customer: customer.name_customer || null,
                            satuan: customer.satuan.map((satuan) => ({
                                name_satuan: satuan.name_satuan || null,
                                jenis_barang_name:
                                    satuan.jenis_barang_name || null,
                                biaya: satuan.biaya.map((biaya) => ({
                                    name_biaya: biaya.name_biaya || null,
                                    harga: biaya.harga || null,
                                    harga_modal: biaya.harga_modal || null,
                                })),
                            })),
                        })),
                        showForm: false,
                    };
                });

                // Filter tambahan untuk memastikan hanya customer yang relevan yang ditampilkan
                markers.value = markers.value.map((marker) => {
                    // Cek apakah pengguna berada di perusahaan yang sama dengan perusahaan pembuat pin
                    // const isUserInCompany = matchingUser.value.view_company.includes(
                    const isUserInCompany = matchingUser.value.company.includes(
                        marker.name_company
                    );

                    return {
                        ...marker,
                        customers: isUserInCompany
                            ? marker.customers // Tampilkan semua customers jika pengguna berada di perusahaan yang sama
                            : marker.customers.filter((customer) =>
                                  //   matchingUser.value.view_company.includes(
                                  matchingUser.value.company.includes(
                                      customer.name_customer
                                  )
                              ), // Filter customers jika pengguna tidak berada di perusahaan yang sama
                    };
                });

                // Simpan data ke localStorage
                // localStorage.setItem("markers", JSON.stringify(markers.value));
                // console.log("Fetched data from server and saved to cache.");
            } catch (error) {
                console.error("Error fetching data:", error);
            }
            // }
        };

        const fetchAgent = async () => {
            try {
                const response = await axios.get("/agent");
                const data = response.data;
                agent.value = data.map((agent) => agent.name_agent);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchJenisBarang = async () => {
            try {
                const response = await axios.get("/jenis_barang");
                const data = response.data;
                jenis_barang.value = data.map(
                    (jenis_barang) => jenis_barang.jenis_barang_name
                );
                // console.log(jenis_barang.value);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchCustomer = async () => {
            try {
                const response = await axios.get("/company");
                const data = response.data;
                // customer.value = data.map((customer) => customer.name_company);
                customerOptions.push(
                    ...data.map((customer) => customer.name_company)
                );
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchUnit = async () => {
            try {
                const response = await axios.get("/unit");
                const data = response.data;
                satuan.value = data.map((satuan) => satuan.name_satuan);
                // console.log(satuan.value);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchBiaya = async () => {
            try {
                const response = await axios.get("/biaya_name");
                const data = response.data;
                apiData.biaya = data.map((biaya) => biaya.biaya_name);
                // console.log(apiData.biaya);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const apiData = reactive({
            biaya: [],
            // Anda bisa menambahkan ref lain di sini jika perlu
        });

        const setPlace = (place) => {
            // Handle place changed event
            const selectedPosition = {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng(),
            };

            markers.value.push({
                position: selectedPosition,
                label: "",
                title: "New Marker",
                // showForm: true,
            });
            // console.log("Place set:", place);
            center.value = selectedPosition;
            zoom.value = 16; // Atur level zoom yang diinginkan
            // You can do something with the place data if needed
        };

        const closeShowMarker = () => {
            if (markers.value.length > 0) {
                const lastMarker = markers.value[markers.value.length - 1];

                if (lastMarker.id) {
                    // If the marker has an id, only hide the form
                    lastMarker.showForm = false;
                } else {
                    // If the marker doesn't have an id, remove it from the array
                    markers.value.pop();

                    // Set showForm to false for the new last marker, if any
                    if (markers.value.length > 0) {
                        markers.value[
                            markers.value.length - 1
                        ].showForm = false;
                    }

                    // Check if the last customer entry is empty
                    const lastForm =
                        formInput.value[formInput.value.length - 1];
                    const isLastFormEmpty =
                        !lastForm.notes &&
                        !lastForm.name_penerima &&
                        !lastForm.name_agent &&
                        !lastForm.name_customer &&
                        !lastForm.name_satuan &&
                        lastForm.items.every(
                            (item) =>
                                !item.name_satuan &&
                                !item.jenis_barang_name &&
                                item.biaya.every(
                                    (biaya) =>
                                        !biaya.nama &&
                                        !biaya.harga &&
                                        !biaya.harga_modal
                                )
                        );

                    if (isLastFormEmpty) {
                        // Remove the last customer entry if it is empty
                        kurangiCustomer(formInput.value.length - 1);
                    }
                }
            }
            $("#showmarker").hide();
            showHistory.value = false;
        };

        const closeModal = () => {
            const dialog = document.getElementById("logout_button");
            if (dialog) {
                dialog.close();
            }
        };

        const handleBack = () => {
            showHistory.value = false; // Set showHistory to false when the back button is clicked
        };

        onMounted(async () => {
            isLoading.value = true; // Mulai loading
            // fetchData();
            fetchAgent();
            fetchCustomer();
            fetchUser();
            fetchUnit();
            fetchBiaya();
            fetchJenisBarang();
            loadMapPosition();
            handleMapIdle();
            // Then call fetchData every 30 seconds
            // setInterval(fetchData, 60000);
            getCurrentLocation();
            // getReverseGeocoding();
            // handleMarkerClick();
            // $("#showmarker").hide();
            isLoading.value = false; // Selesai loading
        });

        const handleClickRefresh = () => {
            isLoading.value = true; // Mulai loading
            fetchUser();
            fetchData();
            fetchAgent();
            fetchJenisBarang();
            fetchCustomer();
            fetchUnit();
            fetchBiaya();
            // Anda bisa memanggil fungsi lainnya jika diperlukan
            isLoading.value = false; // Selesai loading
        };

        return {
            handleClickRefresh,
            user,
            handleBack,
            showHistory,
            toggleHistory,
            markerHistory,
            zoom,
            agent,
            validationErrors,
            center,
            satuan,
            biaya,
            resetForm,
            apiData,
            logout,
            markers,
            address,
            customerOptions,
            setPlace,
            isLoading,
            matchingUser,
            formInput,
            closeModal,
            klikmarker,
            defineSelectedMarkerValueSatuan,
            handleMarkerClick,
            closeShowMarker,
            selectedMarker,
            mapWasMounted,
            options,
            jenis_barang,
            handleMapIdle,
            saveMapPosition,
            loadMapPosition,
        };
    },
});
</script>

<template>
    <Head title="Maps User" />
    <div class="mx-auto relative h-full">
        <div
            v-if="isLoading"
            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50"
        >
            <div
                class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full text-blue-500"
            ></div>
        </div>
        <div class="hidden lg:block lg:absolute top-4 md:top-4 px-2 md:px-8">
            <div class="relative">
                <img
                    src="/images/icon/search.svg"
                    alt="search"
                    class="absolute left-5 top-1/2 transform -translate-y-1/2"
                />
                <GMapAutocomplete
                    placeholder="Cari Lokasi"
                    @place_changed="setPlace"
                    class="md:w-[576px] xl:w-[800px] px-4 py-4 md:py-2 2xl:py-4 border rounded-full focus:outline-none focus:ring focus:border-blue-300 shadow-xl border-none pl-14 text-lg"
                >
                </GMapAutocomplete>
            </div>
        </div>

        <GMapMap
            id="google-map"
            class="w-full h-[75vh] lg:h-screen"
            :center="center"
            :zoom="zoom"
            :options="{ disableDefaultUI: true }"
            @load="mapWasMounted"
            gestureHandling="greedy"
        >
            <!-- @click="handleMapClick" -->
            <GMapMarker
                :clickable="true"
                v-for="marker in markers"
                :key="marker.position.lat + marker.position.lng"
                :options="marker"
                onclick="showmarker.showModal()"
                @click="() => handleMarkerClick(marker)"
            />
            <!-- Menampilkan semua marker dalam array markers -->

            <div>
                <div
                    class="hidden lg:block lg:absolute top-4 md:top-4 px-2 md:px-8"
                >
                    <div class="relative">
                        <img
                            src="/images/icon/search.svg"
                            alt="search"
                            class="absolute left-5 top-1/2 transform -translate-y-1/2"
                        />
                        <GMapAutocomplete
                            placeholder="Cari Lokasi"
                            @place_changed="setPlace"
                            class="px-4 py-4 md:py-2 2xl:py-4 md:w-[576px] xl:w-[800px] border rounded-full focus:outline-none focus:ring focus:border-blue-300 shadow-xl border-none pl-14 text-lg"
                        >
                        </GMapAutocomplete>
                    </div>
                </div>

                <div
                    class="pt-6 md:pt-0 absolute right-2 top-0 md:top-6 md:right-8 z-10 flex justify-end md:px-0 gap-4"
                >
                    <!-- Open the modal using ID.showModal() method -->

                    <button
                        class="bg-green-600 border-none text-white hover:bg-green-700 text-base pl-10 relative rounded-full btn px-4 shadow-xl py-2"
                        @click="handleClickRefresh"
                    >
                        <img
                            src="/images/icon/refresh.svg"
                            alt="User"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5"
                        />
                        Refresh
                    </button>

                    <button
                        class="bg-red-600 border-none text-white hover:bg-red-700 text-base pl-10 relative rounded-full btn px-4 shadow-xl py-2"
                        onclick="my_modal_2.showModal()"
                    >
                        <img
                            src="/images/icon/user.svg"
                            alt="User"
                            class="absolute left-3 top-1/2 transform -translate-y-1/2"
                        />
                        Akun
                    </button>
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white">
                            <h1 class="text-xl font-semibold">
                                Informasi Akun
                            </h1>
                            <!-- <p>
                                {{ center.lat }} Latitude,
                                {{ center.lng }} Longitude
                            </p> -->
                            <br />
                            <p>
                                Login Sebagai :
                                {{ auth.user.name }}
                            </p>
                            <p>
                                Perusahaan:
                                {{
                                    matchingUser
                                        ? matchingUser.company.join(", ")
                                        : "Loading..."
                                }}
                            </p>
                            <p>
                                Login Dengan Email:
                                {{ auth.user.email }}
                            </p>
                            <div class="flex justify-center gap-4">
                                <button
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-8 rounded z-40 mt-8"
                                    onclick="logout_button.showModal()"
                                >
                                    Logout
                                </button>
                            </div>
                            <dialog id="logout_button" class="modal">
                                <div
                                    class="modal-box bg-white flex gap-4 flex-col justify-center mx-auto"
                                >
                                    <h1 class="text-xl font-semibold">
                                        Kamu yakin ingin Log Out?
                                    </h1>
                                    <div class="flex gap-4 justify-center mt-8">
                                        <button
                                            @click="logout"
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded z-40"
                                        >
                                            Ya
                                        </button>
                                        <button
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded z-40 modal-backdrop"
                                            @click="closeModal"
                                        >
                                            Tidak
                                        </button>
                                    </div>
                                </div>
                            </dialog>
                        </div>
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>
                </div>
            </div>

            <!-- Show Marker -->
            <div
                v-show="selectedMarker"
                id="showmarker"
                :data-id="
                    selectedMarker.value && selectedMarker.value.satuan
                        ? selectedMarker.value.satuan
                        : ''
                "
                class="absolute z-10 inset-0 flex items-center justify-center 2xl:pl-[40%] text-xs pt-[86px] md:pt-24 lg:pt-8 2xl:pt-0"
                style="display: none"
            >
                <div
                    class="bg-white w-full lg:w-[512px] xl:w-[800px] 2xl:w-[720px] lg:h-auto max-h-[1024px] rounded-xl p-6 relative shadow-xl mx-4 md:mx-24 2xl:ml-48"
                >
                    <form @submit.prevent="editSaveFormData">
                        <!-- <div class="overflow-y-scroll max-h-[448px]"> -->
                        <h1 class="pb-4 w-[90%] px-2">
                            Alamat :
                            {{ selectedMarker.lokasi }}
                        </h1>
                        <h1 class="pb-4 w-[90%] px-2">
                            Nama Penerima :
                            {{ selectedMarker.name_penerima }}
                        </h1>
                        <h1 class="pb-4 w-[90%] px-2">
                            Nama Agent :
                            {{ selectedMarker.name_agent }}
                        </h1>
                        <div class="px-2 h-auto" v-if="showHistory">
                            <MapHistory
                                :show-history="showHistory"
                                @back="handleBack"
                                :history-data="markerHistory"
                                :matching-user="matchingUser"
                            />
                        </div>
                        <div
                            class="max-h-[448px] xl:max-h-[384px] 2xl:max-h-[448px] overflow-auto"
                            v-else
                        >
                            <!-- <h1 class="pb-4 w-[90%]">
                                Nama Customer :
                                {{ selectedMarker.name_customer }}
                            </h1> -->
                            <div class="px-2">
                                <div
                                    class="p-4 border rounded mb-4"
                                    v-for="(
                                        customer, customerIndex
                                    ) in selectedMarker.customers"
                                    :key="customerIndex"
                                >
                                    <div class="w-full flex gap-4">
                                        <div class="w-full pb-2">
                                            <label
                                                :for="
                                                    'name_customer' +
                                                    customerIndex
                                                "
                                                class="pb-2"
                                                >Nama Customer
                                                {{ customerIndex + 1 }}:</label
                                            >
                                            <v-select
                                                v-if="
                                                    customerOptions.length > 0
                                                "
                                                :disabled="
                                                    customer.isSaved ||
                                                    !(
                                                        matchingUser &&
                                                        matchingUser.company.includes(
                                                            selectedMarker.name_company
                                                        )
                                                    )
                                                "
                                                :id="
                                                    'name_customer' +
                                                    customerIndex
                                                "
                                                :options="customerOptions"
                                                v-model="customer.name_customer"
                                                class="w-full"
                                            />

                                            <p
                                                v-if="!customer.name_customer"
                                                class="text-red-500"
                                            >
                                                Customer tidak boleh kosong
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="pb-2"
                                        v-for="(item, index) in customer.satuan"
                                        :key="index"
                                    >
                                        <div>
                                            <div
                                                class="flex gap-2 md:gap-4 pb-2"
                                            >
                                                <div class="w-full flex gap-4">
                                                    <div class="w-full">
                                                        <label
                                                            :for="
                                                                'name_satuan' +
                                                                customerIndex
                                                            "
                                                            class="pb-2"
                                                            >Satuan
                                                            {{
                                                                index + 1
                                                            }}:</label
                                                        >
                                                        <v-select
                                                            :disabled="
                                                                item.isSaved ||
                                                                !(
                                                                    matchingUser &&
                                                                    matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    )
                                                                )
                                                            "
                                                            :id="
                                                                'name_satuan' +
                                                                customerIndex +
                                                                '-' +
                                                                index
                                                            "
                                                            :options="satuan"
                                                            v-model="
                                                                item.name_satuan
                                                            "
                                                            class="w-full"
                                                        />
                                                        <p
                                                            v-if="
                                                                !item.name_satuan
                                                            "
                                                            class="text-red-500"
                                                        >
                                                            Satuan tidak boleh
                                                            kosong
                                                        </p>
                                                    </div>
                                                    <div class="w-full">
                                                        <label
                                                            :for="
                                                                'jenis_barang_name' +
                                                                customerIndex +
                                                                '-' +
                                                                index
                                                            "
                                                            class="pb-2"
                                                            >Jenis Barang
                                                            {{
                                                                index + 1
                                                            }}:</label
                                                        >
                                                        <v-select
                                                            :disabled="
                                                                item.isSaved ||
                                                                !(
                                                                    matchingUser &&
                                                                    matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    )
                                                                )
                                                            "
                                                            :id="
                                                                'jenis_barang_name' +
                                                                customerIndex +
                                                                '-' +
                                                                index
                                                            "
                                                            :options="
                                                                jenis_barang
                                                            "
                                                            v-model="
                                                                item.jenis_barang_name
                                                            "
                                                            class="w-full"
                                                        />
                                                        <p
                                                            v-if="
                                                                !item.jenis_barang_name
                                                            "
                                                            class="text-red-500"
                                                        >
                                                            Jenis Barang tidak
                                                            boleh kosong
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex gap-4">
                                                <div
                                                    class="flex flex-col w-full"
                                                >
                                                    <div
                                                        v-for="(
                                                            biaya, biayaIndex
                                                        ) in item.biaya"
                                                        :key="biayaIndex"
                                                        class="w-full flex gap-2"
                                                    >
                                                        <div
                                                            class="w-full lg:grid gap-4 grid-cols-2"
                                                        >
                                                            <div class="pb-2">
                                                                <label
                                                                    :for="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex +
                                                                        '-' +
                                                                        customerIndex
                                                                    "
                                                                    >Nama Biaya
                                                                    {{
                                                                        biayaIndex +
                                                                        1
                                                                    }}:</label
                                                                >
                                                                <v-select
                                                                    :disabled="
                                                                        biaya.isSaved ||
                                                                        !(
                                                                            matchingUser &&
                                                                            matchingUser.company.includes(
                                                                                selectedMarker.name_company
                                                                            )
                                                                        )
                                                                    "
                                                                    :id="
                                                                        'biaya' +
                                                                        customerIndex +
                                                                        '-' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.name_biaya
                                                                    "
                                                                    :options="
                                                                        apiData.biaya
                                                                    "
                                                                    class="w-full rounded-lg text-xs"
                                                                />
                                                                <p
                                                                    v-if="
                                                                        !biaya.name_biaya
                                                                    "
                                                                    class="text-red-500"
                                                                >
                                                                    Nama Biaya
                                                                    tidak boleh
                                                                    kosong
                                                                </p>
                                                            </div>
                                                            <div class="pb-2">
                                                                <label
                                                                    :for="
                                                                        'harga' +
                                                                        customerIndex +
                                                                        '-' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    >Harga Jual
                                                                    {{
                                                                        biayaIndex +
                                                                        1
                                                                    }}:</label
                                                                >
                                                                <input
                                                                    :disabled="
                                                                        !(
                                                                            matchingUser &&
                                                                            matchingUser.company.includes(
                                                                                selectedMarker.name_company
                                                                            )
                                                                        )
                                                                    "
                                                                    :id="
                                                                        'harga' +
                                                                        customerIndex +
                                                                        '-' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.harga
                                                                    "
                                                                    class="w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                                                    placeholder="isi Total Harga"
                                                                    type="number"
                                                                />
                                                                <p
                                                                    v-if="
                                                                        !biaya.harga
                                                                    "
                                                                    class="text-red-500"
                                                                >
                                                                    Harga Jual
                                                                    tidak boleh
                                                                    kosong
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="notes">Catatan:</label>
                                    <textarea
                                        id="notes"
                                        class="w-full mb-2 p-2 border h-32 md:h-20 focus:outline-none focus:ring focus:border-blue-300 rounded-lg text-sm border-gray-950 border-opacity-25"
                                        :disabled="
                                            !(
                                                matchingUser &&
                                                matchingUser.company.includes(
                                                    selectedMarker.name_company
                                                )
                                            )
                                        "
                                        >{{
                                            selectedMarker
                                                ? selectedMarker.notes
                                                : ""
                                        }}</textarea
                                    >
                                </div>
                                <div
                                    class="flex lg:flex-row gap-2 justify-center"
                                >
                                    <button
                                        type="button"
                                        class="bg-violet-500 hover:bg-violet-700 text-white py-3 px-4 rounded-md w-full"
                                        @click="toggleHistory"
                                    >
                                        History
                                    </button>
                                </div>

                                <div class="mt-8">
                                    <h1>
                                        Dibuat oleh :
                                        {{ selectedMarker.name }}
                                    </h1>
                                    <h1>
                                        Perusahaan :
                                        {{ selectedMarker.name_company }}
                                    </h1>
                                    <span
                                        >Dibuat pada :
                                        {{ selectedMarker.date }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="absolute top-0 right-1">
                        <button
                            @click="closeShowMarker"
                            class="absolute top-2 right-2 bg-red-500 text-white cursor-pointer btn btn-circle hover:bg-red-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentcolor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </GMapMap>
    </div>
</template>
