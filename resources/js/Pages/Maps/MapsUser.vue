<script>
import { defineComponent, ref, onMounted } from "vue";
import { Marker } from "vue3-google-map";
import $ from "jquery";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";

export default defineComponent({
    emits: ["position_changed", "tilt_changed"],
    components: { Marker, Head, Link },
    props: { auth: Object },
    setup(props) {
        const center = ref({ lat: 0, lng: 0 });
        const markers = ref([]);
        const klikmarker = ref([]);
        const selectedMarker = ref(true);
        const mapInstance = ref(null);
        const address = ref("");
        const user = ref([]);

        // console.log(props.auth.user);

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
                            // markers.value.push({
                            //   position: userLocation,
                            //   label: "",
                            //   title: "Your Location",
                            // });
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

        const getReverseGeocoding = (lat, lng) => {
            return fetch(
                `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=AIzaSyD2dASx5Zo68GSyZuPjUs-4SBLYGsn4OPQ`
            )
                .then((response) => response.json())
                .then((data) => {
                    if (data.results && data.results.length > 0) {
                        const address = data.results[0].formatted_address;
                        return address; // Kembalikan alamat
                    } else {
                        return null; // Kembalikan null jika tidak ada hasil
                    }
                })
                .catch((error) => {
                    console.error(error);
                    return null; // Kembalikan null jika terjadi kesalahan
                });
        };

        const logout = () => {
            router.post("/logout");
        };

        const formInput = ref({
            // title: "",
            notes: "",
        });

        const mapWasMounted = (_map) => {
            mapInstance.value = _map;
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
                    showForm: true,
                };
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

        const fetchData = async () => {
            try {
                const response = await axios.get("/api/maps");
                const data = response.data;

                markers.value = data.map((map) => ({
                    position: {
                        lat: parseFloat(map.lat),
                        lng: parseFloat(map.lng),
                    },
                    id: map.id,
                    label: "",
                    notes: map.notes,
                    name: map.name,
                    date: map.date,
                    lokasi: map.lokasi,
                }));

                console.log("Data fetched successfully:", markers.value);
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        };

        const fetchUser = async () => {
            try {
                const response = await axios.get("/api/role");
                const data = response.data;
                user.value = data.map((user) => ({
                    role_names: user.role_names,
                }));

                // console.log(user.value);
            } catch (error) {
                console.error("Error fetching data:", error);
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
            fetchData();
            // Then call fetchData every 30 seconds
            // setInterval(fetchData, 60000);
            getCurrentLocation();
            getReverseGeocoding();
            fetchUser();
            // handleMarkerClick();
            // $("#showmarker").hide();
        });

        return {
            user,
            zoom,
            center,
            logout,
            markers,
            address,
            setPlace,
            formInput,
            closeModal,
            klikmarker,
            handleMarkerClick,
            closeShowMarker,
            selectedMarker,
            mapWasMounted,
            fetchData,
        };
    },
});
</script>

<template>
    <Head title="Maps" />
    <div class="mx-auto relative h-full">
        <div class="lg:hidden top-4 md:top-4 w-full px-4 md:px-8 pt-6 lg:pt-0">
            <div class="relative mb-6">
                <img
                    src="/images/icon/search.svg"
                    alt="search"
                    class="absolute left-5 top-1/2 transform -translate-y-1/2"
                />
                <GMapAutocomplete
                    placeholder="Cari Lokasi"
                    @place_changed="setPlace"
                    class="px-4 py-2 md:py-4 w-full md:w-[576px] xl:w-[800px] rounded-full focus:outline-none focus:ring focus:border-blue-300 lg:shadow-xl border pl-14 text-lg"
                >
                </GMapAutocomplete>
            </div>
        </div>
        <GMapMap
            api-key="AIzaSyD2dASx5Zo68GSyZuPjUs-4SBLYGsn4OPQ"
            id="google-map"
            :center="center"
            class="w-full h-[60vh] lg:h-screen"
            :zoom="zoom"
            :options="{ disableDefaultUI: true }"
            @load="mapWasMounted"
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
                            class="px-4 py-4 md:py-4 w-full md:w-[576px] xl:w-[800px] border rounded-full focus:outline-none focus:ring focus:border-blue-300 shadow-xl border-none pl-14 text-lg"
                        >
                        </GMapAutocomplete>
                    </div>
                </div>

                <div
                    class="pt-6 lg:pt-0 lg:absolute bottom-8 right-2 md:bottom-0 md:top-6 md:right-8 z-10"
                >
                    <div class="flex gap-4 justify-end mx-4">
                        <!-- Open the modal using ID.showModal() method -->
                        <button
                            class="bg-green-600 border-none text-white hover:bg-green-700 text-base pl-12 relative rounded-full btn shadow-xl"
                            v-on:click="fetchData"
                        >
                            <img
                                src="/images/icon/refresh.svg"
                                alt="User"
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 scale-50"
                            />
                            Refresh
                        </button>
                        <button
                            class="bg-red-600 border-none text-white hover:bg-red-700 text-base pl-10 relative rounded-full btn shadow-xl"
                            onclick="my_modal_2.showModal()"
                        >
                            <img
                                src="/images/icon/user.svg"
                                alt="User"
                                class="absolute left-3 top-1/2 transform -translate-y-1/2"
                            />
                            Akun
                        </button>
                    </div>

                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white">
                            <h1 class="text-xl font-semibold pb-2">
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
                                Login Dengan Email:
                                {{ auth.user.email }}
                            </p>
                            <div class="flex justify-center gap-4">
                                <!-- <Link href="/dashboard">
                            <button
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded z-40 mt-8"
                            >
                                Dashboard
                            </button>
                        </Link> -->
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
                v-if="selectedMarker"
                id="showmarker"
                :data-id="selectedMarker ? selectedMarker.id : ''"
                class="absolute z-10 inset-0 flex items-center justify-center"
                style="display: none"
            >
                <div
                    class="bg-white w-full max-w-md h-auto rounded-xl p-8 relative shadow-xl mx-4"
                >
                    <form @submit.prevent="editSaveFormData">
                        <h1 class="pb-4 w-[90%]">
                            Alamat : {{ selectedMarker.lokasi }}
                        </h1>

                        <label for="notes">Description:</label>

                        <textarea
                            id="notes"
                            class="w-full mb-2 p-2 border h-32 md:h-56"
                            disabled
                        >
                                {{ selectedMarker ? selectedMarker.notes : "" }}
                            </textarea
                        >

                        <div class="mt-8">
                            <h1>Dibuat oleh : {{ selectedMarker.name }}</h1>
                            <span>Dibuat pada : {{ selectedMarker.date }}</span>
                        </div>
                    </form>
                    <div class="absolute top-0 right-1">
                        <button
                            @click="closeShowMarker"
                            class="absolute top-2 right-2 bg-red-500 text-white cursor-pointer btn btn-circle"
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
