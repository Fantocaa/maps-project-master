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
                    const isUserInCompany = matchingUser.value.company.includes(
                        marker.name_company
                    );

                    return {
                        ...marker,
                        customers: isUserInCompany
                            ? marker.customers // Tampilkan semua customers jika pengguna berada di perusahaan yang sama
                            : marker.customers.filter((customer) =>
                                  matchingUser.value.view_company.includes(
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

        //add new marker

        const tambahCustomer = () => {
            formInput.value.push({
                name_customer: null,
                items: [
                    {
                        name_satuan: null,
                        jenis_barang: null,
                        biaya: [{ nama: "", harga: "", harga_modal: "" }],
                    },
                ],
            });
        };

        const kurangiCustomer = (index) => {
            if (formInput.value.length > 1) {
                formInput.value.splice(index, 1);
            }
        };

        const tambahItem = (customerIndex) => {
            formInput.value[customerIndex].items.push({
                name_satuan: null,
                jenis_barang: null,
                biaya: [{ nama: "", harga: "", harga_modal: "" }],
            });
        };

        const kurangiItem = (customerIndex, itemIndex) => {
            if (formInput.value[customerIndex].items.length > 1) {
                formInput.value[customerIndex].items.splice(itemIndex, 1);
            }
        };

        const tambahBiaya = (customerIndex, itemIndex) => {
            formInput.value[customerIndex].items[itemIndex].biaya.push({
                nama: "",
                harga: "",
                harga_modal: "",
            });
        };

        const kurangiBiaya = (customerIndex, itemIndex, biayaIndex) => {
            if (
                formInput.value[customerIndex].items[itemIndex].biaya.length > 1
            ) {
                formInput.value[customerIndex].items[itemIndex].biaya.splice(
                    biayaIndex,
                    1
                );
            }
        };

        //showmarker

        const tambahItemCustomer = () => {
            selectedMarker.value.customers.push({
                name_customer: "", // Nama customer baru bisa ditambahkan di sini
                satuan: [
                    {
                        name_satuan: "", // Nama satuan baru
                        jenis_barang_name: "", // Nama jenis barang baru
                        biaya: [
                            {
                                name_biaya: "", // Nama biaya baru
                                harga: "", // Harga baru
                                harga_modal: "", // Harga modal baru
                            },
                        ],
                    },
                ],
            });
        };

        const kurangiItemCustomer = (customerIndex) => {
            if (selectedMarker.value.customers.length > 1) {
                selectedMarker.value.customers.splice(customerIndex, 1);
            }
        };

        const tambahItemBiaya = (customerIndex) => {
            // Pastikan ada setidaknya satu customer
            if (selectedMarker.value.customers.length > 0) {
                // Tambahkan satuan baru ke customer pertama (index 0)
                selectedMarker.value.customers[customerIndex].satuan.push({
                    name_satuan: null,
                    jenis_barang_name: "",
                    biaya: [{ name_biaya: "", harga: "", harga_modal: "" }],
                    isSaved: false,
                });
            }
        };

        const kurangiItemBiaya = (customerIndex, index) => {
            if (
                selectedMarker.value.customers[customerIndex].satuan.length > 1
            ) {
                selectedMarker.value.customers[customerIndex].satuan.splice(
                    index,
                    1
                );
            }
        };

        const tambahBiayaBiaya = (customerIndex, index) => {
            const customer = selectedMarker.value.customers?.[customerIndex];
            if (customer) {
                if (customer.satuan?.[index]) {
                    customer.satuan[index].biaya.push({
                        name_biaya: "",
                        harga: "",
                        harga_modal: "",
                    });
                }
            }
        };

        const kurangiBiayaBiaya = (customerIndex, index, biayaIndex) => {
            const satuan =
                selectedMarker.value.customers?.[customerIndex]?.satuan?.[
                    index
                ];

            // Pastikan 'satuan' ada dan memiliki properti 'biaya'
            if (satuan && satuan.biaya?.length > 1) {
                // Hapus item biaya berdasarkan indeks
                satuan.biaya.splice(biayaIndex, 1);
            }
        };

        const saveFormData = async () => {
            if (markers.value.length > 0) {
                validationErrors.value = []; // Reset errors

                let isValid = true;

                // Validasi input utama
                if (!formInput.value.name_agent) {
                    validationErrors.value.name_agent =
                        "Nama Agent tidak boleh kosong";
                    isValid = false;
                }

                if (!formInput.value.name_penerima) {
                    validationErrors.value.name_penerima =
                        "Nama Penerima tidak boleh kosong";
                    isValid = false;
                }

                // Validasi input utama untuk setiap customer
                formInput.value.forEach((customer, customerIndex) => {
                    const customerErrors = {
                        name_customer: "",
                        items: [],
                    };

                    // Validasi name_customer
                    if (!customer.name_customer) {
                        customerErrors.name_customer =
                            "Customer tidak boleh kosong";
                        isValid = false;
                    }

                    // Validasi item dalam array
                    if (Array.isArray(customer.items)) {
                        customer.items.forEach((item, itemIndex) => {
                            const itemErrors = {
                                name_satuan: "",
                                // jenis_barang_name: "",
                                biaya: [],
                            };

                            if (!item.name_satuan) {
                                itemErrors.name_satuan =
                                    "Satuan tidak boleh kosong";
                                isValid = false;
                            }

                            // if (!item.jenis_barang_name) {
                            //     itemErrors.jenis_barang_name =
                            //         "Jenis Barang tidak boleh kosong";
                            //     isValid = false;
                            // }

                            // Validasi biaya dalam array
                            if (Array.isArray(item.biaya)) {
                                item.biaya.forEach((biaya, biayaIndex) => {
                                    const biayaErrors = { nama: "", harga: "" };

                                    if (!biaya.nama) {
                                        biayaErrors.nama =
                                            "Nama Biaya tidak boleh kosong";
                                        isValid = false;
                                    }

                                    if (!biaya.harga) {
                                        biayaErrors.harga =
                                            "Harga Jual tidak boleh kosong";
                                        isValid = false;
                                    }

                                    itemErrors.biaya.push(biayaErrors);
                                });
                            } else {
                                itemErrors.biaya = [];
                            }

                            customerErrors.items.push(itemErrors);
                        });
                    } else {
                        customer.items = [];
                    }

                    validationErrors.value.push(customerErrors);
                });

                if (!isValid) {
                    alert(
                        "Ada kesalahan dalam input form. Silakan periksa dan coba lagi."
                    );
                    return; // Hentikan eksekusi fungsi jika ada error
                }

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
                    };

                    // Format formData
                    const formattedData = formInput.value.reduce(
                        (acc, entry) => {
                            let customerIndex = acc.findIndex(
                                (item) =>
                                    item.name_customer === entry.name_customer
                            );

                            if (customerIndex === -1) {
                                acc.push({
                                    name_customer: entry.name_customer || null,
                                    satuan: (entry.items || []).map((item) => ({
                                        name_satuan: item.name_satuan || null,
                                        jenis_barang_name:
                                            item.jenis_barang_name || null,
                                        biaya: (item.biaya || []).map(
                                            (biaya) => ({
                                                name_biaya: biaya.nama || "",
                                                harga: biaya.harga || "",
                                                harga_modal:
                                                    biaya.harga_modal || "",
                                            })
                                        ),
                                    })),
                                });
                            } else {
                                acc[customerIndex].satuan.push(
                                    ...(entry.items || []).map((item) => ({
                                        name_satuan: item.name_satuan || null,
                                        biaya: (item.biaya || []).map(
                                            (biaya) => ({
                                                name_biaya: biaya.nama || "",
                                                harga: biaya.harga || "",
                                                harga_modal:
                                                    biaya.harga_modal || "",
                                            })
                                        ),
                                    }))
                                );
                            }

                            return acc;
                        },
                        []
                    );

                    // Gabungkan formData dan formattedData
                    const dataToSend = {
                        ...formData,
                        markerData: formattedData,
                    };

                    try {
                        const response = await fetch("/maps/store", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": $(
                                    'meta[name="csrf-token"]'
                                ).attr("content"),
                            },
                            body: JSON.stringify(dataToSend),
                        });

                        if (response.ok) {
                            const data = await response.json();
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
                                    items: [
                                        {
                                            name_satuan: null,
                                            biaya: [
                                                {
                                                    nama: "",
                                                    harga: "",
                                                    harga_modal: "",
                                                },
                                            ],
                                        },
                                    ],
                                },
                            ];

                            fetchData();
                            fetchUser();
                            fetchAgent();
                            fetchCustomer();
                            fetchUnit();
                            fetchBiaya();
                        } else {
                            console.error(
                                "Error saving data:",
                                response.statusText
                            );
                        }
                    } catch (error) {
                        console.error("Error saving data:", error);
                    }
                } else {
                    console.error("Error: Marker position data is incomplete");
                }
            } else {
                console.error("Error: No markers available to save");
            }
        };

        const editSaveFormData = async () => {
            if (selectedMarker.value && selectedMarker.value.id) {
                // Initialize validation errors and validity flag
                const validationErrors = {
                    customers: [],
                };
                let isValid = true;

                // Validate selectedMarker and its properties
                if (!selectedMarker.value) {
                    console.error("Error: No marker selected for editing");
                    alert("Error: No marker selected for editing");
                    return;
                }

                if (
                    !Array.isArray(selectedMarker.value.customers) ||
                    selectedMarker.value.customers.length === 0
                ) {
                    validationErrors.customers.push(
                        "At least one customer is required"
                    );
                    isValid = false;
                } else {
                    selectedMarker.value.customers.forEach(
                        (customer, customerIndex) => {
                            const customerErrors = {
                                name_customer: "",
                                satuan: [],
                            };

                            // Validate customer name
                            if (!customer.name_customer) {
                                customerErrors.name_customer =
                                    "Customer name cannot be empty";
                                isValid = false;
                            }

                            if (!Array.isArray(customer.satuan)) {
                                customerErrors.satuan.push(
                                    "Satuan must be an array"
                                );
                                isValid = false;
                            } else {
                                customer.satuan.forEach(
                                    (satuan, satuanIndex) => {
                                        const satuanErrors = {
                                            name_satuan: "",
                                            // jenis_barang_name: "",
                                            biaya: [],
                                        };

                                        if (!satuan.name_satuan) {
                                            satuanErrors.name_satuan =
                                                "Satuan name cannot be empty";
                                            isValid = false;
                                        }

                                        // if (!satuan.jenis_barang_name) {
                                        //     satuanErrors.jenis_barang_name =
                                        //         "Jenis Barang name cannot be empty";
                                        //     isValid = false;
                                        // }

                                        if (!Array.isArray(satuan.biaya)) {
                                            satuanErrors.biaya.push(
                                                "Biaya must be an array"
                                            );
                                            isValid = false;
                                        } else {
                                            satuan.biaya.forEach(
                                                (biaya, biayaIndex) => {
                                                    const biayaErrors = {
                                                        name_biaya: "",
                                                        harga: "",
                                                    };

                                                    if (!biaya.name_biaya) {
                                                        biayaErrors.name_biaya =
                                                            "Biaya name cannot be empty";
                                                        isValid = false;
                                                    }

                                                    if (!biaya.harga) {
                                                        biayaErrors.harga =
                                                            "Harga cannot be empty";
                                                        isValid = false;
                                                    }

                                                    satuanErrors.biaya.push(
                                                        biayaErrors
                                                    );
                                                }
                                            );
                                        }

                                        customerErrors.satuan.push(
                                            satuanErrors
                                        );
                                    }
                                );
                            }

                            validationErrors.customers.push(customerErrors);
                        }
                    );
                }

                if (!isValid) {
                    alert(
                        "Ada kesalahan dalam input form. Silakan periksa dan coba lagi."
                    );
                    return; // Stop execution if validation fails
                }

                const formData = {
                    notes: selectedMarker.value.notes,
                    satuan: selectedMarker.value.customers.flatMap(
                        (customer) => customer.satuan
                    ),
                    customers: selectedMarker.value.customers.map(
                        (customer) => ({
                            name_customer: customer.name_customer,
                            satuan: customer.satuan.map((satuan) => ({
                                name_satuan: satuan.name_satuan,
                                jenis_barang_name:
                                    satuan.jenis_barang_name || null,
                                biaya: satuan.biaya.map((biaya) => ({
                                    name_biaya: biaya.name_biaya,
                                    harga: biaya.harga,
                                    harga_modal: biaya.harga_modal,
                                })),
                            })),
                        })
                    ),
                };

                try {
                    const response = await fetch(
                        `/maps/edit/${selectedMarker.value.id}`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": $(
                                    'meta[name="csrf-token"]'
                                ).attr("content"),
                            },
                            body: JSON.stringify(formData),
                        }
                    );

                    if (response.ok) {
                        const data = await response.json();
                        alert("Data saved successfully.");

                        // Update data directly on selectedMarker
                        selectedMarker.value.notes = formData.notes;
                        selectedMarker.value.customers = formData.customers;

                        $("#showmarker").hide();

                        // Refresh data
                        fetchData();
                        fetchUser();
                        fetchAgent();
                        fetchCustomer();
                        fetchUnit();
                        fetchBiaya();
                    } else {
                        const errorData = await response.json();
                        console.error("Error saving data:", errorData);
                        alert("Error saving data: " + errorData.message);
                    }
                } catch (error) {
                    console.error("Error saving data:", error);
                    alert("Error saving data. Please try again.");
                }
            } else {
                console.error("Error: No marker selected for editing");
                alert("Error: No marker selected for editing");
            }
        };

        const deleteSaveFormData = async () => {
            if (selectedMarker.value && selectedMarker.value.id) {
                try {
                    const response = await fetch(
                        `/maps/delete/${selectedMarker.value.id}`,
                        {
                            method: "DELETE",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": $(
                                    'meta[name="csrf-token"]'
                                ).attr("content"),
                            },
                        }
                    );

                    if (response.ok) {
                        const data = await response.json();
                        alert("Data deleted : Success", data);

                        // Remove the deleted marker from the markers array
                        const index = markers.value.findIndex(
                            (marker) => marker.id === selectedMarker.value.id
                        );
                        if (index !== -1) {
                            markers.value.splice(index, 1);
                        }

                        $("#showmarker").hide();

                        // Fetch updated data
                        fetchData();
                    } else {
                        console.error(
                            "Error deleting data:",
                            response.statusText
                        );
                    }
                } catch (error) {
                    console.error("Error deleting data:", error);
                }
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
            fetchJenisBarang();
            loadMapPosition();
            handleMapIdle();
            // Then call fetchData every 30 seconds
            // setInterval(fetchData, 60000);
            getCurrentLocation();
            // getReverseGeocoding();
            // handleMarkerClick();
            // $("#showmarker").hide();
        });

        const handleClickRefresh = () => {
            // Panggil kembali fungsi yang diperlukan saat refresh
            fetchUser();
            fetchData();
            fetchAgent();
            fetchJenisBarang();
            fetchCustomer();
            fetchUnit();
            fetchBiaya();
            // loadMapPosition();
            // handleMapIdle();
            // getCurrentLocation();
        };

        return {
            user,
            zoom,
            agent,
            validationErrors,
            center,
            satuan,
            biaya,
            apiData,
            logout,
            markers,
            address,
            resetForm,
            customerOptions,
            handleClickRefresh,
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
            tambahItemCustomer,
            kurangiItemCustomer,
            tambahItemBiaya,
            kurangiItemBiaya,
            tambahBiayaBiaya,
            kurangiBiayaBiaya,
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
    <Head title="Maps Admin" />
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
                            class="md:w-[576px] xl:w-[800px] px-4 py-4 md:py-2 2xl:py-4 border rounded-full focus:outline-none focus:ring focus:border-blue-300 shadow-xl border-none pl-14 text-lg"
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
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5"
                        />
                        Akun
                    </button>
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white">
                            <h1 class="text-xl font-semibold">
                                Informasi Akun
                            </h1>
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
                    class="bg-white w-96 md:w-[1024px] lg:w-[512px] xl:w-[660px] h-auto rounded-xl p-6 relative shadow-xl mx-4 md:mx-24"
                >
                    <form @submit.prevent="saveFormData">
                        <h1 class="pb-4 w-[90%] px-2">
                            Alamat : {{ address }}
                        </h1>
                        <div class="overflow-y-scroll max-h-96 pr-4">
                            <div class="pb-2">
                                <div class="w-full pb-2 px-2">
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
                                <div class="pb-4 px-2">
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

                            <div class="px-2">
                                <div
                                    class="p-4 border rounded mb-4"
                                    v-for="(
                                        customer, customerIndex
                                    ) in formInput"
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
                                                >Nama Customer:</label
                                            >
                                            <v-select
                                                v-if="
                                                    customerOptions.length > 0
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
                                        <div class="flex pt-2 gap-2">
                                            <button
                                                type="button"
                                                class="btn bg-green-500 text-white hover:bg-green-700"
                                                @click="
                                                    tambahCustomer(
                                                        customerIndex
                                                    )
                                                "
                                            >
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                class="btn bg-red-500 text-white hover:bg-red-700"
                                                @click="
                                                    kurangiCustomer(
                                                        customerIndex
                                                    )
                                                "
                                            >
                                                -
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        v-for="(item, index) in customer.items"
                                        :key="index"
                                    >
                                        <div class="flex gap-2 md:gap-4 pb-2">
                                            <div class="w-full flex gap-4">
                                                <div class="w-full">
                                                    <label
                                                        :for="
                                                            'name_satuan' +
                                                            customerIndex +
                                                            '-' +
                                                            index
                                                        "
                                                        class="pb-2"
                                                        >Satuan:</label
                                                    >
                                                    <v-select
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
                                                        v-if="!item.name_satuan"
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
                                                        >Jenis Barang:</label
                                                    >
                                                    <v-select
                                                        :id="
                                                            'jenis_barang_name' +
                                                            customerIndex +
                                                            '-' +
                                                            index
                                                        "
                                                        :options="jenis_barang"
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
                                                        Jenis Barang tidak boleh
                                                        kosong
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex pt-2 gap-2">
                                                <button
                                                    type="button"
                                                    class="btn bg-green-500 text-white hover:bg-green-700"
                                                    @click="
                                                        tambahItem(
                                                            customerIndex
                                                        )
                                                    "
                                                >
                                                    +
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn bg-red-500 text-white hover:bg-red-700"
                                                    @click="
                                                        kurangiItem(
                                                            customerIndex,
                                                            index
                                                        )
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
                                            class="flex gap-4"
                                        >
                                            <div
                                                class="lg:grid grid-cols-2 xl:grid-cols-3 gap-4"
                                            >
                                                <div class="pb-2">
                                                    <label
                                                        :for="
                                                            'biaya' +
                                                            customerIndex +
                                                            '-' +
                                                            index +
                                                            '-' +
                                                            biayaIndex
                                                        "
                                                        >Nama Biaya
                                                        {{
                                                            biayaIndex + 1
                                                        }}:</label
                                                    >
                                                    <v-select
                                                        :id="
                                                            'biaya' +
                                                            customerIndex +
                                                            '-' +
                                                            index +
                                                            '-' +
                                                            biayaIndex
                                                        "
                                                        v-model="biaya.nama"
                                                        :options="apiData.biaya"
                                                        class="w-full rounded-lg text-xs"
                                                    />
                                                    <p
                                                        v-if="!biaya.nama"
                                                        class="text-red-500"
                                                    >
                                                        Nama Biaya tidak boleh
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
                                                            biayaIndex + 1
                                                        }}:</label
                                                    >
                                                    <input
                                                        :id="
                                                            'harga' +
                                                            customerIndex +
                                                            '-' +
                                                            index +
                                                            '-' +
                                                            biayaIndex
                                                        "
                                                        v-model="biaya.harga"
                                                        class="w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                                        placeholder="isi Total Harga"
                                                        type="number"
                                                    />
                                                    <p
                                                        v-if="!biaya.harga"
                                                        class="text-red-500"
                                                    >
                                                        Harga Jual tidak boleh
                                                        kosong
                                                    </p>
                                                </div>
                                                <div class="pb-2">
                                                    <label
                                                        :for="
                                                            'harga_modal' +
                                                            customerIndex +
                                                            '-' +
                                                            index +
                                                            '-' +
                                                            biayaIndex
                                                        "
                                                        >Harga Modal
                                                        {{
                                                            biayaIndex + 1
                                                        }}:</label
                                                    >
                                                    <input
                                                        :id="
                                                            'harga_modal' +
                                                            customerIndex +
                                                            '-' +
                                                            index +
                                                            '-' +
                                                            biayaIndex
                                                        "
                                                        v-model="
                                                            biaya.harga_modal
                                                        "
                                                        class="w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                                        placeholder="isi Nama Harga"
                                                        type="number"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex gap-2 pt-2">
                                                <button
                                                    type="button"
                                                    class="btn bg-green-500 text-white hover:bg-green-700"
                                                    @click="
                                                        tambahBiaya(
                                                            customerIndex,
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
                                                            customerIndex,
                                                            index,
                                                            biayaIndex
                                                        )
                                                    "
                                                >
                                                    -
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 px-2">
                                <label for="notes" class="pb-2">Catatan:</label>
                                <textarea
                                    v-model="formInput.notes"
                                    id="notes"
                                    class="w-full mb-2 p-2 border focus:outline-none focus:ring focus:border-blue-300 h-16 md:h-20 rounded-lg text-sm border-gray-950 border-opacity-25"
                                />
                            </div>
                        </div>

                        <div class="flex gap-4 justify-center pt-4">
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
                    class="bg-white w-full lg:w-[512px] xl:w-[660px] max-h-[1024px] rounded-xl p-6 relative shadow-xl mx-4 md:mx-24"
                >
                    <form @submit.prevent="editSaveFormData">
                        <!-- <div class="overflow-y-scroll max-h-[448px]"> -->
                        <h1 class="pb-4 w-[90%] px-2">
                            Alamat :
                            {{ selectedMarker.lokasi }}
                        </h1>
                        <div
                            class="max-h-[448px] xl:max-h-[384px] 2xl:max-h-[448px] overflow-auto"
                        >
                            <h1 class="pb-4 w-[90%] px-2">
                                Nama Penerima :
                                {{ selectedMarker.name_penerima }}
                            </h1>
                            <h1 class="pb-4 w-[90%] px-2">
                                Nama Agent :
                                {{ selectedMarker.name_agent }}
                            </h1>
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
                                                @click="
                                                    tambahItemCustomer(
                                                        customerIndex
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
                                                    kurangiItemCustomer(
                                                        customerIndex
                                                    )
                                                "
                                            >
                                                -
                                            </button>
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
                                                        <!-- :disabled="
                                                                item.isSaved ||
                                                                !(
                                                                    matchingUser &&
                                                                    matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    )
                                                                )
                                                            " -->
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
                                                        @click="
                                                            tambahItemBiaya(
                                                                customerIndex
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
                                                            kurangiItemBiaya(
                                                                customerIndex,
                                                                index
                                                            )
                                                        "
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
                                                        ) in item.biaya"
                                                        :key="biayaIndex"
                                                        class="w-full flex gap-2"
                                                    >
                                                        <div
                                                            :class="[
                                                                'w-full lg:grid gap-4 grid-cols-3',
                                                                {
                                                                    'lg:grid-cols-2':
                                                                        !matchingUser.company.includes(
                                                                            selectedMarker.name_company
                                                                        ) ||
                                                                        (matchingUser.view_company.includes(
                                                                            selectedMarker.name_company
                                                                        ) &&
                                                                            !biaya.harga_modal &&
                                                                            satuan.isSaved),
                                                                    'lg:grid-cols-3':
                                                                        matchingUser.company.includes(
                                                                            selectedMarker.name_company
                                                                        ) &&
                                                                        biaya.harga_modal &&
                                                                        !satuan.isSaved,
                                                                },
                                                            ]"
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
                                                            <div
                                                                :class="{
                                                                    hidden: !matchingUser.company.includes(
                                                                        selectedMarker.name_company
                                                                    ),
                                                                }"
                                                                class="pb-2"
                                                            >
                                                                <!-- <div
                                                                :class="{
                                                                    hidden:
                                                                        !matchingUser.company.includes(
                                                                            selectedMarker.name_company
                                                                        ) ||
                                                                        !biaya.harga_modal,
                                                                }"
                                                                class="pb-2"
                                                            > -->
                                                                <label
                                                                    :for="
                                                                        'harga_modal' +
                                                                        customerIndex +
                                                                        '-' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                >
                                                                    Harga Modal
                                                                    {{
                                                                        biayaIndex +
                                                                        1
                                                                    }}:
                                                                </label>
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
                                                                        'harga_modal' +
                                                                        customerIndex +
                                                                        '-' +
                                                                        index +
                                                                        '-' +
                                                                        biayaIndex
                                                                    "
                                                                    v-model="
                                                                        biaya.harga_modal
                                                                    "
                                                                    class="w-full border-gray-950 border-opacity-25 focus:outline-none focus:ring focus:border-blue-300 rounded text-xs"
                                                                    placeholder="isi Nama Harga"
                                                                    type="number"
                                                                />
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
                                                                        customerIndex,
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
                                                                        customerIndex,
                                                                        index,
                                                                        biayaIndex
                                                                    )
                                                                "
                                                            >
                                                                -
                                                            </button>
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
                                                                    tambahBiayaBiaya(
                                                                        customerIndex,
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
                                                                        customerIndex,
                                                                        index,
                                                                        biayaIndex
                                                                    )
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
                                </div>
                                <div class="">
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
