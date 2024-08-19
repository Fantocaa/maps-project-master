<script>
import { defineComponent, ref, onMounted } from "vue";
import { Marker } from "vue3-google-map";
import $ from "jquery";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { reactive } from "vue";

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
    },
    props: { auth: Object },
    setup(props) {
        const center = ref({ lat: 0, lng: 0 });
        const markers = ref([]);
        const klikmarker = ref([]);
        const selectedMarker = ref(false);
        const mapInstance = ref(null);
        const address = ref("");
        const user = ref([]);
        const agent = ref([]);
        const customer = ref([]);
        const satuan = ref([]);
        const biaya = ref([]);
        const matchingUser = ref(null);

        const getCurrentLocation = () => {
            if (markers.value.length > 0) {
                // Set the center to the first marker's position
                center.value = markers.value[0].position;
            } else {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const userLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };
                            center.value = userLocation;
                        },
                        (error) => {
                            console.error("Error getting location:", error);
                        }
                    );
                } else {
                    console.error(
                        "Geolocation is not supported by this browser."
                    );
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
                biaya: [{ nama: null, harga: "", harga_modal: "" }],
                // items: [
                //     {
                //         name_satuan: null,
                //         biaya: [{ nama: "", harga: "", harga_modal: "" }],
                //     },
                // ],
            },
        ]);

        const handleMapClick = (event) => {
            const clickedPosition = {
                lat: event.latLng.lat(),
                lng: event.latLng.lng(),
            };

            // Panggil fungsi reverse geocoding dengan latitude dan longitude
            getReverseGeocoding(clickedPosition.lat, clickedPosition.lng)
                .then((receivedAddress) => {
                    if (receivedAddress) {
                        address.value = receivedAddress; // Update the address variable with the received address
                    }
                })
                .catch((error) => console.error(error));

            markers.value.push({
                position: clickedPosition,
                label: "",
                title: "New Marker",
                showForm: true,
            });

            center.value = clickedPosition;
            klikmarker.value = [];
            $("#showmarker").hide();

            saveMapPosition(); // Save map position after map click
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
            const index = markers.value.findIndex(
                (marker) =>
                    marker.position.lat === clickedMarker.position.lat &&
                    marker.position.lng === clickedMarker.position.lng
            );

            // Check if the clicked marker has an ID
            if (!markers.value[index].id) {
                // Marker does not have an ID, remove it directly
                markers.value.splice(index, 1);
            } else {
                selectedMarker.value = {
                    id: markers.value[index].id,
                    notes: clickedMarker.notes,
                    name: clickedMarker.name,
                    date: clickedMarker.date,
                    lokasi: clickedMarker.lokasi,
                    name_company: clickedMarker.name_company,
                    name_penerima: clickedMarker.name_penerima,
                    name_agent: clickedMarker.name_agent,
                    name_customer: clickedMarker.name_customer,
                    // satuan: clickedMarker.satuan,
                    satuan: clickedMarker.satuan.map((satuan) => ({
                        name_satuan: satuan.name_satuan,
                        biaya: satuan.biaya.map((biaya) => ({
                            name_biaya: biaya.name_biaya,
                            harga: biaya.harga,
                            harga_modal: biaya.harga_modal,
                        })),
                    })),
                    showForm: true,
                };
                // console.log(selectedMarker.value);
                $("#showmarker").show();
            }

            // zoom.value = 16;
            center.value = clickedMarker.position;

            // Manually update the map
            if (mapInstance.value) {
                mapInstance.value.setZoom(zoom.value);
                mapInstance.value.setCenter(center.value);
            }

            // Call getReverseGeocoding with the clicked marker's position
            getReverseGeocoding(
                clickedMarker.position.lat,
                clickedMarker.position.lng
            )
                .then((addr) => {
                    if (addr) {
                        address.value = addr; // Update the address variable with the received address
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
                        matchingUser.value.company.includes(map.name_customer)
                );

                // console.log(data);

                markers.value = data.map((map) => ({
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
                    name_customer: map.name_customer,
                    name_company: map.name_company,
                    name_penerima: map.name_penerima,
                    satuan: map.satuan.map((satuan) => ({
                        name_satuan: satuan.name_satuan,
                        biaya: satuan.biaya.map((biaya) => ({
                            name_biaya: biaya.name_biaya,
                            harga: biaya.harga,
                            harga_modal: biaya.harga_modal,
                        })),
                    })),
                    showForm: false,
                }));

                // Simpan data ke localStorage
                // localStorage.setItem("markers", JSON.stringify(markers.value));
                // console.log("Fetched data from server and saved to cache.");
            } catch (error) {
                console.error("Error fetching data:", error);
            }
            // }
        };

        const fetchCustomer = async () => {
            try {
                const response = await axios.get("/company");
                const data = response.data;
                customer.value = data.map((customer) => customer.name_company);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
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

        const apiData = reactive({
            biaya: [],
            // Anda bisa menambahkan ref lain di sini jika perlu
        });

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

        //add new marker

        const tambahCustomer = () => {
            formInput.value.push({
                name_customer: null,
                name_satuan: null,
                biaya: [{ nama: "", harga: "", harga_modal: "" }],
            });
        };

        const kurangiCustomer = () => {
            if (formInput.value.length > 1) {
                formInput.value.pop();
            }
        };

        const tambahItem = () => {
            formInput.value.push({
                name_satuan: null,
                biaya: [{ nama: "", harga: "", harga_modal: "" }],
            });
        };

        const kurangiItem = () => {
            if (formInput.value.length > 1) {
                formInput.value.pop();
            }
        };

        const tambahBiaya = (index) => {
            formInput.value[index].biaya.push({
                nama: "",
                harga: "",
                harga_modal: "",
            });
        };

        const kurangiBiaya = (index) => {
            if (formInput.value[index].biaya.length > 1) {
                formInput.value[index].biaya.pop();
            }
        };

        // Tambah Customer
        // const tambahCustomer = () => {
        //     formInput.value.push({
        //         name_customer: null,
        //         items: [
        //             {
        //                 name_satuan: null,
        //                 biaya: [{ nama: "", harga: "", harga_modal: "" }],
        //             },
        //         ],
        //     });
        // };

        // // Kurangi Customer
        // const kurangiCustomer = () => {
        //     if (formInput.value.length > 1) {
        //         formInput.value.pop();
        //     }
        // };

        // // Tambah Item
        // const tambahItem = (customerIndex) => {
        //     formInput.value[customerIndex].items.push({
        //         name_satuan: null,
        //         biaya: [{ nama: "", harga: "", harga_modal: "" }],
        //     });
        // };

        // // Kurangi Item
        // const kurangiItem = (customerIndex) => {
        //     if (formInput.value[customerIndex].items.length > 1) {
        //         formInput.value[customerIndex].items.pop();
        //     }
        // };

        // // Tambah Biaya
        // const tambahBiaya = (customerIndex, itemIndex) => {
        //     formInput.value[customerIndex].items[itemIndex].biaya.push({
        //         nama: "",
        //         harga: "",
        //         harga_modal: "",
        //     });
        // };

        // // Kurangi Biaya
        // const kurangiBiaya = (customerIndex, itemIndex) => {
        //     if (
        //         formInput.value[customerIndex].items[itemIndex].biaya.length > 1
        //     ) {
        //         formInput.value[customerIndex].items[itemIndex].biaya.pop();
        //     }
        // };

        //showmarker

        const tambahItemBiaya = () => {
            selectedMarker.value.satuan.push({
                name_satuan: null,
                biaya: [{ name_biaya: "", harga: "", harga_modal: "" }],
            });
        };

        const kurangiItemBiaya = () => {
            if (selectedMarker.value.satuan.length > 1) {
                selectedMarker.value.satuan.pop();
            }
        };

        const tambahBiayaBiaya = (index) => {
            selectedMarker.value.satuan[index].biaya.push({
                name_biaya: "",
                harga: "",
                harga_modal: "",
            });
        };

        const kurangiBiayaBiaya = (index, biayaIndex) => {
            if (selectedMarker.value.satuan[index].biaya.length > 1) {
                selectedMarker.value.satuan[index].biaya.splice(biayaIndex, 1);
            }
        };

        const saveFormData = () => {
            if (markers.value.length > 0) {
                const lastMarker = markers.value[markers.value.length - 1];

                if (
                    lastMarker.position &&
                    lastMarker.position.lat &&
                    lastMarker.position.lng
                ) {
                    const formData = {
                        notes: formInput.value.notes,
                        lat: lastMarker.position.lat,
                        lng: lastMarker.position.lng,
                        name: props.auth.user.name,
                        lokasi: address.value,
                        name_penerima: formInput.value.name_penerima,
                        name_company: matchingUser.value
                            ? matchingUser.value.company.join(", ")
                            : "Loading...",
                        name_agent: formInput.value.name_agent,
                        name_customer: formInput.value.name_customer,
                        satuan: formInput.value.map((item) => {
                            return {
                                name_satuan: item.name_satuan,
                                biaya: item.biaya.map((biaya) => {
                                    return {
                                        name_biaya: biaya.nama,
                                        harga: biaya.harga,
                                        harga_modal: biaya.harga_modal,
                                    };
                                }),
                            };
                        }),
                        // customer: formInput.value.map((item) => {
                        //     return {
                        //         name_customer: item.name_customer,
                        //         satuan: item.satuan.map((satuan) => {
                        //             return {
                        //                 name_satuan: satuan.name_satuan,
                        //                 biaya: satuan.biaya.map((biaya) => {
                        //                     return {
                        //                         name_biaya: biaya.nama,
                        //                         harga: biaya.harga,
                        //                         harga_modal: biaya.harga_modal,
                        //                     };
                        //                 }),
                        //             };
                        //         }),
                        //     };
                        // }),
                    };

                    // Menggunakan Ajax jQuery untuk mengirim data
                    $.ajax({
                        type: "POST",
                        contentType: "application/json",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        url: "/maps/store",
                        data: JSON.stringify(formData),
                        success: function (data) {
                            alert("Data saved : Success", data);

                            markers.value[
                                markers.value.length - 1
                            ].showForm = false;

                            // Reset formInput.value to its initial state
                            formInput.value = [
                                {
                                    notes: "",
                                    name_penerima: "",
                                    name_agent: null,
                                    name_customer: null,
                                    name_satuan: null,
                                    biaya: [
                                        {
                                            nama: "",
                                            harga: "",
                                            harga_modal: "",
                                        },
                                    ],
                                    // customer: [
                                    //     {
                                    //         name_customer: null,
                                    //         satuan: [
                                    //             {
                                    //                 name_satuan: null,
                                    //                 biaya: [
                                    //                     {
                                    //                         nama: "",
                                    //                         harga: "",
                                    //                         harga_modal: "",
                                    //                     },
                                    //                 ],
                                    //             },
                                    //         ],
                                    //     },
                                    // ],
                                },
                            ];
                            fetchData();
                            fetchUser();
                            fetchAgent();
                            fetchCustomer();
                            fetchUnit();
                            fetchBiaya();
                        },
                        error: function (error) {
                            console.error("Error saving data:", error);
                        },
                    });
                } else {
                    console.error("Error: Marker position data is incomplete");
                }
            } else {
                console.error("Error: No markers available to save");
            }
        };

        const editSaveFormData = () => {
            if (selectedMarker.value && selectedMarker.value.id) {
                const formData = {
                    notes: selectedMarker.value.notes,
                    satuan: selectedMarker.value.satuan,
                };

                $.ajax({
                    url: `/maps/edit/${selectedMarker.value.id}`,
                    type: "POST",
                    contentType: "application/json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    data: JSON.stringify(formData),
                    success: function (data) {
                        alert("Data saved : Success", data);
                        // Update the notes of the selectedMarker directly
                        selectedMarker.value.notes = formInput.notes;
                        selectedMarker.value.satuan = formInput.satuan;
                        $("#showmarker").hide();
                        fetchData();
                        fetchUser();
                        fetchAgent();
                        fetchCustomer();
                        fetchUnit();
                        fetchBiaya();
                    },
                    error: function (error) {
                        console.error("Error saving data:", error);
                    },
                });
            } else {
                console.error("Error: No marker selected for editing");
            }
        };

        const deleteSaveFormData = () => {
            if (selectedMarker.value && selectedMarker.value.id) {
                $.ajax({
                    url: `/maps/delete/${selectedMarker.value.id}`,
                    type: "DELETE",
                    contentType: "application/json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (data) {
                        alert("Data deleted : Success", data);
                        const index = markers.value.findIndex(
                            (marker) => marker.id === selectedMarker.value.id
                        );
                        markers.value.splice(index, 1);
                        $("#showmarker").hide();
                        fetchData();
                    },
                    error: function (error) {
                        console.error("Error deleting data:", error);
                    },
                });
            } else {
                console.error("Error: No marker selected for deletion");
            }
        };

        const zoom = ref(7); // Atur level zoom awal

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
                    // Set showForm to false for the new last marker
                    if (markers.value.length > 0) {
                        markers.value[
                            markers.value.length - 1
                        ].showForm = false;
                    }
                }
            }
            $("#showmarker").hide();
        };

        const closeModal = () => {
            const dialog = document.getElementById("logout_button");
            if (dialog) {
                dialog.close();
            }
        };

        onMounted(async () => {
            // fetchData();
            fetchAgent();
            fetchCustomer();
            fetchUser();
            fetchUnit();
            fetchBiaya();
            loadMapPosition();
            handleMapIdle();
            // Then call fetchData every 30 seconds
            // setInterval(fetchData, 60000);
            getCurrentLocation();
            // getReverseGeocoding();
            // handleMarkerClick();
            // $("#showmarker").hide();
        });

        return {
            user,
            zoom,
            agent,
            customer,
            center,
            satuan,
            biaya,
            apiData,
            logout,
            markers,
            address,
            setPlace,
            matchingUser,
            kurangiBiaya,
            formInput,
            tambahBiaya,
            tambahCustomer,
            kurangiCustomer,
            tambahItem,
            kurangiItem,
            closeModal,
            klikmarker,
            defineSelectedMarkerValueSatuan,
            handleMapClick,
            handleMarkerClick,
            deleteSaveFormData,
            editSaveFormData,
            closeShowMarker,
            selectedMarker,
            mapWasMounted,
            saveFormData,
            tambahItemBiaya,
            kurangiItemBiaya,
            tambahBiayaBiaya,
            kurangiBiayaBiaya,
            options,
            handleMapIdle,
            saveMapPosition,
            loadMapPosition,
        };
    },
});
</script>

<template>
    <Head title="Maps" />
    <div class="mx-auto relative h-full">
        <div
            class="lg:hidden top-4 md:top-4 w-full px-4 md:px-8 pt-6 lg:pt-0 text-sm"
        >
            <div class="relative mb-6">
                <img
                    src="/images/icon/search.svg"
                    alt="search"
                    class="absolute left-5 top-1/2 transform -translate-y-1/2"
                />
                <GMapAutocomplete
                    placeholder="Cari Lokasi"
                    @place_changed="setPlace"
                    class="px-4 py-2 md:py-[10px] w-64 md:w-[576px] xl:w-[800px] rounded-full focus:outline-none focus:ring focus:border-blue-300 lg:shadow-xl border pl-14 text:sm lg:text-lg"
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
            @click="handleMapClick"
            gestureHandling="greedy"
        >
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
                    class="hidden lg:block lg:absolute top-4 md:top-4 w-full px-2 md:px-8"
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
                            class="px-4 py-4 md:py-2 2xl:py-4 w-full md:w-[576px] xl:w-[800px] border rounded-full focus:outline-none focus:ring focus:border-blue-300 shadow-xl border-none pl-14 text-lg"
                        >
                        </GMapAutocomplete>
                    </div>
                </div>

                <div
                    class="pt-6 md:pt-0 absolute right-2 top-0 md:top-6 md:right-8 z-10 flex justify-end md:px-0"
                >
                    <!-- Open the modal using ID.showModal() method -->
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

            <!-- Add new Marker -->
            <div
                v-show="markers.length && markers[markers.length - 1].showForm"
                class="absolute z-10 inset-0 flex items-center justify-center 2xl:pl-[40%] text-xs pt-[86px] md:pt-24 lg:pt-8"
            >
                <div
                    class="bg-white w-96 md:w-[1024px] lg:w-[512px] xl:w-[660px] h-auto rounded-xl p-8 relative shadow-xl mx-4 md:mx-24"
                >
                    <form @submit.prevent="saveFormData">
                        <h1 class="pb-4 w-[90%]">Alamat : {{ address }}</h1>
                        <div class="overflow-y-scroll max-h-96 pr-4">
                            <div class="pb-2">
                                <div class="w-full pb-2">
                                    <label for="name_agent" class="pb-2"
                                        >Nama Agent:</label
                                    >
                                    <v-select
                                        id="name_agent"
                                        :options="agent"
                                        v-model="formInput.name_agent"
                                        class="w-full"
                                    />
                                    <p
                                        v-if="!formInput.name_agent"
                                        class="text-red-500"
                                    >
                                        Agent tidak boleh kosong
                                    </p>
                                </div>
                                <div class="pb-4">
                                    <label for="name_penerima" class="pb-2"
                                        >Nama Penerima:</label
                                    >
                                    <input
                                        v-model="formInput.name_penerima"
                                        id="name_penerima"
                                        class="w-full mb-2 p-2 border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                        placeholder="isi nama penerima"
                                    />
                                    <p
                                        v-if="!formInput.name_penerima"
                                        class="text-red-500"
                                    >
                                        Nama Penerima tidak boleh kosong
                                    </p>
                                </div>
                            </div>
                            <!-- <div
                                v-for="(customer, customerIndex) in formInput"
                                :key="customerIndex"
                                class="p-4 border rounded mb-4"
                            >
                                <div class="w-full flex gap-4">
                                    <div class="w-full pb-2">
                                        <label for="name_customer"
                                            >Nama Customer:</label
                                        >
                                        <v-select
                                            v-model="customer.name_customer"
                                            :options="customer"
                                            class="w-full"
                                        />
                                        <p
                                            v-if="!customer.name_customer"
                                            class="text-red-500"
                                        >
                                            Customer tidak boleh kosong
                                        </p>
                                    </div>
                                    <div class="flex pt-2 gap-2">
                                        <button
                                            type="button"
                                            class="btn bg-green-500 text-white hover:bg-green-700"
                                            @click="tambahCustomer"
                                        >
                                            +
                                        </button>
                                        <button
                                            type="button"
                                            class="btn bg-red-500 text-white hover:bg-red-700"
                                            @click="kurangiCustomer"
                                        >
                                            -
                                        </button>
                                    </div>
                                </div>

                                <div
                                    v-for="(item, itemIndex) in customer.items"
                                    :key="itemIndex"
                                >
                                    <div class="flex gap-4">
                                        <div class="w-full">
                                            <label for="name_satuan"
                                                >Satuan:</label
                                            >
                                            <v-select
                                                v-model="item.name_satuan"
                                                :options="satuanOptions"
                                                class="w-full"
                                            />
                                            <p
                                                v-if="!item.name_satuan"
                                                class="text-red-500"
                                            >
                                                Satuan tidak boleh kosong
                                            </p>
                                        </div>
                                        <div class="flex pt-2 gap-2">
                                            <button
                                                type="button"
                                                class="btn bg-green-500 text-white hover:bg-green-700"
                                                @click="
                                                    tambahItem(customerIndex)
                                                "
                                            >
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                class="btn bg-red-500 text-white hover:bg-red-700"
                                                @click="
                                                    kurangiItem(customerIndex)
                                                "
                                            >
                                                -
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        v-for="(
                                            biaya, biayaIndex
                                        ) in item.biaya"
                                        :key="biayaIndex"
                                        class="w-full flex gap-2"
                                    >
                                        <input
                                            v-model="biaya.nama"
                                            placeholder="Nama Biaya"
                                            class="w-full"
                                        />
                                        <input
                                            v-model="biaya.harga"
                                            placeholder="Harga Jual"
                                            class="w-full"
                                        />
                                        <input
                                            v-model="biaya.harga_modal"
                                            placeholder="Harga Modal"
                                            class="w-full"
                                        />

                                        <div class="flex gap-2 pt-2">
                                            <button
                                                type="button"
                                                class="btn bg-green-500 text-white hover:bg-green-700"
                                                @click="
                                                    tambahBiaya(
                                                        customerIndex,
                                                        itemIndex
                                                    )
                                                "
                                            >
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                class="btn bg-red-500 text-white hover:bg-red-700"
                                                @click="
                                                    kurangiBiaya(
                                                        customerIndex,
                                                        itemIndex
                                                    )
                                                "
                                            >
                                                -
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <div class="p-4 border rounded mb-4">
                                <div class="w-full flex gap-4">
                                    <div class="w-full pb-2">
                                        <label for="name_customer" class="pb-2"
                                            >Nama Customer:</label
                                        >
                                        <v-select
                                            id="name_customer"
                                            :options="customer"
                                            v-model="formInput.name_customer"
                                            class="w-full"
                                        />
                                        <p
                                            v-if="!formInput.name_customer"
                                            class="text-red-500"
                                        >
                                            Customer tidak boleh kosong
                                        </p>
                                    </div>
                                    <div class="flex pt-2 gap-2">
                                        <button
                                            type="button"
                                            class="btn bg-green-500 text-white hover:bg-green-700"
                                            @click="tambahCustomer"
                                        >
                                            +
                                        </button>
                                        <button
                                            type="button"
                                            class="btn bg-red-500 text-white hover:bg-red-700"
                                            @click="kurangiCustomer"
                                        >
                                            -
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="pb-2"
                                        v-for="(item, index) in formInput"
                                        :key="index"
                                    >
                                        <div>
                                            <div
                                                class="flex gap-2 md:gap-4 pb-2"
                                            >
                                                <div class="w-full">
                                                    <label
                                                        :for="
                                                            'name_satuan' +
                                                            index
                                                        "
                                                        class="pb-2"
                                                        >Satuan:</label
                                                    >
                                                    <v-select
                                                        :id="
                                                            'name_satuan' +
                                                            index
                                                        "
                                                        :options="satuan"
                                                        v-model="
                                                            item.name_satuan
                                                        "
                                                        class="w-full"
                                                    />
                                                    <p
                                                        v-if="!item.name_satuan"
                                                        class="text-red-500"
                                                    >
                                                        Satuan tidak boleh
                                                        kosong
                                                    </p>
                                                </div>
                                                <div class="flex pt-2 gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn bg-green-500 text-white hover:bg-green-700"
                                                        @click="tambahItem"
                                                    >
                                                        +
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn bg-red-500 text-white hover:bg-red-700"
                                                        @click="kurangiItem"
                                                    >
                                                        -
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex gap-4">
                                                <div
                                                    class="flex flex-col w-full"
                                                >
                                                    <div
                                                        v-for="(
                                                            biaya, biayaIndex
                                                        ) in formInput[index]
                                                            .biaya"
                                                        :key="biayaIndex"
                                                        class="w-full flex gap-2"
                                                    >
                                                        <div
                                                            class="lg:grid grid-cols-2 xl:grid-cols-3 gap-4"
                                                        >
                                                            <div class="pb-2">
                                                                <label
                                                                    :for="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    >Nama Biaya
                                                                    {{
                                                                        biayaIndex +
                                                                        1
                                                                    }}:</label
                                                                >
                                                                <v-select
                                                                    :id="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.nama
                                                                    "
                                                                    :options="
                                                                        apiData.biaya
                                                                    "
                                                                    class="w-full rounded-lg text-xs"
                                                                />
                                                                <p
                                                                    v-if="
                                                                        !biaya.nama
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
                                                                        'biaya' +
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
                                                                    :id="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.harga
                                                                    "
                                                                    class="w-full rounded-lg text-xs"
                                                                    placeholder="isi Nama Harga"
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

                                                            <div class="pb-2">
                                                                <label
                                                                    :for="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    >Harga Modal
                                                                    {{
                                                                        biayaIndex +
                                                                        1
                                                                    }}:</label
                                                                >
                                                                <input
                                                                    :id="
                                                                        'biaya' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.harga_modal
                                                                    "
                                                                    class="w-full rounded-lg text-xs"
                                                                    placeholder="isi Nama Harga"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex gap-2 pt-2 lg:hidden"
                                                        >
                                                            <button
                                                                type="button"
                                                                class="btn bg-green-500 text-white hover:bg-green-700"
                                                                @click="
                                                                    tambahBiaya(
                                                                        index
                                                                    )
                                                                "
                                                            >
                                                                +
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn bg-red-500 text-white hover:bg-red-700"
                                                                @click="
                                                                    kurangiBiaya(
                                                                        index
                                                                    )
                                                                "
                                                            >
                                                                -
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="gap-2 pt-2 hidden lg:flex"
                                                >
                                                    <button
                                                        type="button"
                                                        class="btn bg-green-500 text-white hover:bg-green-700"
                                                        @click="
                                                            tambahBiaya(index)
                                                        "
                                                    >
                                                        +
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn bg-red-500 text-white hover:bg-red-700"
                                                        @click="
                                                            kurangiBiaya(index)
                                                        "
                                                    >
                                                        -
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <label for="notes" class="pb-2">Catatan:</label>
                                <textarea
                                    v-model="formInput.notes"
                                    id="notes"
                                    class="w-full mb-2 p-2 border focus:outline-none focus:ring focus:border-blue-300 h-16 md:h-16 rounded-lg text-sm"
                                />
                            </div>
                        </div>

                        <div class="flex gap-4 justify-center">
                            <button
                                type="submit"
                                class="bg-blue-500 text-white py-2 px-4 rounded-md w-full"
                            >
                                Save
                            </button>
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

            <!-- Show Marker -->
            <div
                v-show="selectedMarker"
                id="showmarker"
                :data-id="
                    selectedMarker.value && selectedMarker.value.satuan
                        ? selectedMarker.value.satuan
                        : ''
                "
                class="absolute z-10 inset-0 flex items-center justify-center 2xl:pl-[40%] text-xs pt-[86px] md:pt-24 lg:pt-0"
                style="display: none"
            >
                <div
                    class="bg-white w-full lg:w-[512px] xl:w-[660px] max-h-[1024px] rounded-xl p-8 relative shadow-xl mx-4 md:mx-24"
                >
                    <form @submit.prevent="editSaveFormData">
                        <!-- <div class="overflow-y-scroll max-h-[448px]"> -->
                        <h1 class="pb-4 w-[90%]">
                            Alamat :
                            {{ selectedMarker.lokasi }}
                        </h1>
                        <div
                            class="max-h-[448px] xl:max-h-[384px] 2xl:max-h-[448px] overflow-auto"
                        >
                            <h1 class="pb-4 w-[90%]">
                                Nama Penerima :
                                {{ selectedMarker.name_penerima }}
                            </h1>
                            <h1 class="pb-4 w-[90%]">
                                Nama Agent :
                                {{ selectedMarker.name_agent }}
                            </h1>
                            <h1 class="pb-4 w-[90%]">
                                Nama Customer :
                                {{ selectedMarker.name_customer }}
                            </h1>
                            <div>
                                <div
                                    class="pb-2"
                                    v-for="(
                                        satuanItem, index
                                    ) in selectedMarker.satuan"
                                    :key="index"
                                >
                                    <div>
                                        <div class="flex gap-2 md:gap-4 pb-2">
                                            <div class="w-full">
                                                <label
                                                    :for="'name_satuan' + index"
                                                    class="pb-2"
                                                    >Satuan:</label
                                                >
                                                <v-select
                                                    :disabled="
                                                        !(
                                                            matchingUser &&
                                                            matchingUser.company.includes(
                                                                selectedMarker.name_company
                                                            )
                                                        )
                                                    "
                                                    :id="'name_satuan' + index"
                                                    :options="satuan"
                                                    v-model="
                                                        satuanItem.name_satuan
                                                    "
                                                    class="w-full"
                                                />
                                                <p
                                                    v-if="
                                                        !satuanItem.name_satuan
                                                    "
                                                    class="text-red-500"
                                                >
                                                    Satuan tidak boleh kosong
                                                </p>
                                            </div>
                                            <div class="flex pt-2 gap-2">
                                                <button
                                                    v-if="
                                                        matchingUser &&
                                                        matchingUser.company.includes(
                                                            selectedMarker.name_company
                                                        )
                                                    "
                                                    type="button"
                                                    class="btn bg-green-500 text-white hover:bg-green-700"
                                                    @click="tambahItemBiaya"
                                                >
                                                    +
                                                </button>
                                                <button
                                                    v-if="
                                                        matchingUser &&
                                                        matchingUser.company.includes(
                                                            selectedMarker.name_company
                                                        )
                                                    "
                                                    type="button"
                                                    class="btn bg-red-500 text-white hover:bg-red-700"
                                                    @click="kurangiItemBiaya"
                                                >
                                                    -
                                                </button>
                                            </div>
                                        </div>

                                        <div class="flex gap-4">
                                            <div class="flex flex-col w-full">
                                                <div
                                                    v-for="(
                                                        biayaItem, biayaIndex
                                                    ) in satuanItem.biaya"
                                                    :key="biayaIndex"
                                                    class="w-full flex gap-2"
                                                >
                                                    <div
                                                        :class="[
                                                            'w-full lg:grid gap-4',
                                                            {
                                                                'grid-cols-2':
                                                                    !matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    ),
                                                                'xl:grid-cols-3':
                                                                    matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    ) &&
                                                                    biayaItem.harga_modal,
                                                            },
                                                        ]"
                                                    >
                                                        <div class="pb-2">
                                                            <label
                                                                :for="
                                                                    'biaya' +
                                                                    index +
                                                                    '-' +
                                                                    biayaIndex
                                                                "
                                                                class=""
                                                                >Nama Biaya
                                                                {{
                                                                    biayaIndex +
                                                                    1
                                                                }}:</label
                                                            >
                                                            <v-select
                                                                :disabled="
                                                                    !(
                                                                        matchingUser &&
                                                                        matchingUser.company.includes(
                                                                            selectedMarker.name_company
                                                                        )
                                                                    )
                                                                "
                                                                :id="
                                                                    'biaya' +
                                                                    index +
                                                                    '-' +
                                                                    biayaIndex
                                                                "
                                                                v-model="
                                                                    biayaItem.name_biaya
                                                                "
                                                                :options="
                                                                    apiData.biaya
                                                                "
                                                                class="w-full rounded-lg text-xs"
                                                            />
                                                            <p
                                                                v-if="
                                                                    !biayaItem.name_biaya
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                Nama Biaya tidak
                                                                boleh kosong
                                                            </p>
                                                        </div>
                                                        <div class="pb-2">
                                                            <label
                                                                :for="
                                                                    'biaya' +
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
                                                                    'biaya' +
                                                                    index +
                                                                    '-' +
                                                                    biayaIndex
                                                                "
                                                                v-model="
                                                                    biayaItem.harga
                                                                "
                                                                class="w-full rounded-lg text-xs"
                                                                placeholder="isi Nama Harga"
                                                                type="number"
                                                            />
                                                            <p
                                                                v-if="
                                                                    !biayaItem.harga
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                Harga Jual tidak
                                                                boleh kosong
                                                            </p>
                                                        </div>
                                                        <div
                                                            :class="{
                                                                hidden: !matchingUser.company.includes(
                                                                    selectedMarker.name_company
                                                                ),
                                                            }"
                                                            class="pb-2"
                                                        >
                                                            <label
                                                                :for="
                                                                    'biaya' +
                                                                    index +
                                                                    '-' +
                                                                    biayaIndex
                                                                "
                                                                >Harga Modal
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
                                                                    'biaya' +
                                                                    index +
                                                                    '-' +
                                                                    biayaIndex
                                                                "
                                                                v-model="
                                                                    biayaItem.harga_modal
                                                                "
                                                                class="w-full rounded-lg text-xs"
                                                                placeholder="isi Nama Harga"
                                                                type="number"
                                                            />
                                                            <!-- <p
                                                                v-if="
                                                                    !biayaItem.harga_modal
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                Harga Modal
                                                                tidak boleh
                                                                kosong
                                                            </p> -->
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex gap-2 pt-2 lg:hidden"
                                                    >
                                                        <button
                                                            v-if="
                                                                matchingUser &&
                                                                matchingUser.company.includes(
                                                                    selectedMarker.name_company
                                                                )
                                                            "
                                                            type="button"
                                                            class="btn bg-green-500 text-white hover:bg-green-700"
                                                            @click="
                                                                tambahBiayaBiaya(
                                                                    index
                                                                )
                                                            "
                                                        >
                                                            +
                                                        </button>
                                                        <button
                                                            v-if="
                                                                matchingUser &&
                                                                matchingUser.company.includes(
                                                                    selectedMarker.name_company
                                                                )
                                                            "
                                                            type="button"
                                                            class="btn bg-red-500 text-white hover:bg-red-700"
                                                            @click="
                                                                kurangiBiayaBiaya(
                                                                    index
                                                                )
                                                            "
                                                        >
                                                            -
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="gap-2 pt-2 hidden lg:flex"
                                            >
                                                <button
                                                    v-if="
                                                        matchingUser &&
                                                        matchingUser.company.includes(
                                                            selectedMarker.name_company
                                                        )
                                                    "
                                                    type="button"
                                                    class="btn bg-green-500 text-white hover:bg-green-700"
                                                    @click="
                                                        tambahBiayaBiaya(index)
                                                    "
                                                >
                                                    +
                                                </button>
                                                <button
                                                    v-if="
                                                        matchingUser &&
                                                        matchingUser.company.includes(
                                                            selectedMarker.name_company
                                                        )
                                                    "
                                                    type="button"
                                                    class="btn bg-red-500 text-white hover:bg-red-700"
                                                    @click="
                                                        kurangiBiayaBiaya(index)
                                                    "
                                                >
                                                    -
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <label for="notes">Catatan:</label>
                            <textarea
                                id="notes"
                                class="w-full mb-2 p-2 border h-32 md:h-16"
                                :disabled="
                                    !(
                                        matchingUser &&
                                        matchingUser.company.includes(
                                            selectedMarker.name_company
                                        )
                                    )
                                "
                                >{{
                                    selectedMarker ? selectedMarker.notes : ""
                                }}</textarea
                            >

                            <div class="flex lg:flex-row gap-2 justify-center">
                                <button
                                    v-if="
                                        matchingUser &&
                                        matchingUser.company.includes(
                                            selectedMarker.name_company
                                        )
                                    "
                                    type="submit"
                                    class="bg-blue-500 text-white py-3 px-4 rounded-md w-full"
                                >
                                    Save
                                </button>
                                <button
                                    v-if="
                                        matchingUser &&
                                        matchingUser.company.includes(
                                            selectedMarker.name_company
                                        )
                                    "
                                    @click="deleteSaveFormData"
                                    type="button"
                                    class="bg-red-500 text-white py-3 px-4 rounded-md w-full"
                                >
                                    Delete
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
