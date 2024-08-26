<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storemd_mapsRequest;
use App\Http\Requests\Updatemd_mapsRequest;
use App\Models\md_agent;
use App\Models\md_biaya;
use App\Models\md_biaya_name;
use App\Models\md_company;
use App\Models\md_jenis_barang;
use App\Models\md_maps;
use App\Models\md_satuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MdMapsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maps = md_maps::join('md_agents', 'md_maps.id_agent', '=', 'md_agents.id')
            ->join('md_companies', 'md_maps.id_perusahaan', '=', 'md_companies.id')
            ->select(
                'md_maps.*',
                'md_agents.name_agent as name_agent',
                'md_companies.name_company as name_company'
            )
            ->get();

        foreach ($maps as $map) {
            // Temukan semua pelanggan yang terkait dengan map
            $customers = md_company::join('md_biayas', 'md_biayas.id_customer', '=', 'md_companies.id')
                ->whereNull('md_biayas.deleted_at') // Filter untuk tidak termasuk data yang soft deleted
                ->where('md_biayas.id_maps', $map->id)
                ->select('md_companies.id as customer_id', 'md_companies.name_company as name_customer')
                ->distinct()
                ->get();

            $map->customers = $customers->map(function ($customer) use ($map) {
                // Temukan semua satuan yang terkait dengan pelanggan dan map ini
                $satuans = md_satuan::whereHas('biaya', function ($query) use ($map, $customer) {
                    $query->where('id_maps', $map->id)
                        ->where('id_customer', $customer->customer_id);
                })->get();

                return [
                    'name_customer' => $customer->name_customer,
                    'satuan' => $satuans->map(function ($satuan) use ($map, $customer) {
                        $jenis_barang = md_biaya::where('id_maps', $map->id)
                            ->where('id_customer', $customer->customer_id)
                            ->where('id_satuan', $satuan->id)
                            ->join('md_jenis_barangs', 'md_biayas.id_jenis_barang', '=', 'md_jenis_barangs.id')
                            ->select('md_jenis_barangs.jenis_barang_name as jenis_barang_name')
                            ->first();

                        $biayas = md_biaya::where('id_maps', $map->id)
                            ->where('id_customer', $customer->customer_id)
                            ->where('id_satuan', $satuan->id)
                            ->join('md_biaya_names', 'md_biayas.name_biaya', '=', 'md_biaya_names.id')
                            ->select('md_biayas.*', 'md_biaya_names.biaya_name as biaya_name')
                            ->get();

                        return [
                            'name_satuan' => $satuan->name_satuan,
                            'jenis_barang_name' => $jenis_barang ? $jenis_barang->jenis_barang_name : null,
                            'biaya' => $biayas->map(function ($biaya) {
                                return [
                                    'name_biaya' => $biaya->biaya_name,
                                    'harga' => $biaya->harga,
                                    'harga_modal' => $biaya->harga_modal,
                                ];
                            }),
                        ];
                    }),
                ];
            });
        }

        return response()->json($maps);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function has_role()
    {
        $users = User::all();

        $users = $users->map(function ($user) {
            // Sembunyikan relasi 'viewCustomers' sebelum mengubah user menjadi array
            $user->makeHidden(['viewCustomers']);

            $viewCustomers = $user->viewCustomers->map(function ($viewCustomer) {
                return $viewCustomer->company ? $viewCustomer->company->name_company : 'No company';
            });

            return array_merge($user->toArray(), [
                'role' => $user->role_names,
                'company' => $user->companies->pluck('name_company'),
                'view_company' => $user->viewCompanies->pluck('company.name_company'),
                'view_customer' => $viewCustomers,
            ]);
        });

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storemd_mapsRequest $request)
    {
        // dd($request);

        $company = md_company::firstWhere('name_company', $request->name_company);
        $agent = md_agent::firstWhere('name_agent', $request->name_agent);

        $form = new md_maps();
        $form->notes = $request->notes;
        $form->lat = $request->lat;
        $form->lng = $request->lng;
        $form->lokasi = $request->lokasi;
        $form->name = $request->name;
        $form->date = Carbon::now()->toDateString();
        $form->id_perusahaan = $company->id;
        $form->id_agent = $agent->id;
        // $form->id_customer = $customer->id;
        $form->name_penerima = $request->name_penerima;
        $form->save();

        // Proses setiap customer yang diberikan dalam markerData
        foreach ($request->markerData as $markerData) {
            // Lihat data markerData yang sedang diproses
            // dd($markerData);

            // Temukan customer berdasarkan name_customer
            $customer = md_company::firstWhere('name_company', $markerData['name_customer']);
            if (!$customer) {
                return response()->json(['error' => 'Customer not found: ' . $markerData['name_customer']], 404);
            }

            // Proses setiap satuan dan biaya terkait customer
            foreach ($markerData['satuan'] as $satuanData) {
                // Temukan jenis barang berdasarkan jenis_barang_name di dalam satuan
                $jenisBarang = md_jenis_barang::firstWhere('jenis_barang_name', $satuanData['jenis_barang_name']);
                // if (!$jenisBarang) {
                //     return response()->json(['error' => 'Jenis Barang not found: ' . $satuanData['jenis_barang_name']], 404);
                // } else {
                //     // Tangani kasus di mana jenis_barang_name adalah null
                //     $jenisBarang = null; // Atau sesuaikan dengan logika aplikasi Anda
                // }

                $satuan = md_satuan::firstWhere('name_satuan', $satuanData['name_satuan']);
                if ($satuan) {
                    foreach ($satuanData['biaya'] as $biayaData) {
                        $biaya = new md_biaya();
                        $biaya->id_maps = $form->id; // Mengaitkan id_maps dengan id dari md_maps yang baru saja dibuat
                        $biaya->id_satuan = $satuan->id; // Mengaitkan id_satuan dengan id dari md_satuan yang ditemukan
                        $biaya->id_customer = $customer->id; // Menyimpan id_customer dalam tabel md_biaya
                        $biaya->id_jenis_barang = $jenisBarang ? $jenisBarang->id : null; // Menyimpan id_jenis_barang jika ada

                        // Cari dan kaitkan id dari md_biaya_name berdasarkan name_biaya
                        if (isset($biayaData['name_biaya'])) {
                            $biaya_name = md_biaya_name::firstWhere('biaya_name', $biayaData['name_biaya']);
                            if ($biaya_name) {
                                $biaya->name_biaya = $biaya_name->id;
                            }
                        }

                        $biaya->harga = $biayaData['harga'];
                        $biaya->harga_modal = $biayaData['harga_modal'];

                        $biaya->save();
                    }
                }
            }
        }

        return response()->json(['success' => 'Data saved successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(md_maps $md_maps) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(md_maps $md_maps)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatemd_mapsRequest $request, md_maps $md_maps)
    {
        //
    }

    public function update_maps(Updatemd_mapsRequest $request)
    {
        // dd($request);
        $form = md_maps::find($request->id);

        // Tambahkan pengecekan untuk memastikan objek ditemukan sebelum memanipulasinya
        if (!$form) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        // Hapus semua data md_biaya yang terkait dengan id_maps
        md_biaya::where('id_maps', $form->id)->delete();

        // Proses setiap customer yang diberikan dalam request
        if (!empty($request->customers)) {
            foreach ($request->customers as $customerData) {
                // Temukan customer berdasarkan name_customer
                $customer = md_company::firstWhere('name_company', $customerData['name_customer']);
                if (!$customer) {
                    return response()->json(['error' => 'Customer not found: ' . $customerData['name_customer']], 404);
                }

                // Proses setiap satuan dan biaya terkait customer
                foreach ($customerData['satuan'] as $satuanData) {
                    // Temukan jenis barang berdasarkan jenis_barang_name di dalam satuan
                    $jenisBarang = md_jenis_barang::firstWhere('jenis_barang_name', $satuanData['jenis_barang_name']);
                    // if (!$jenisBarang) {
                    //     return response()->json(['error' => 'Jenis Barang not found: ' . $satuanData['jenis_barang_name']], 404);
                    // }

                    $satuan = md_satuan::firstWhere('name_satuan', $satuanData['name_satuan']);
                    if ($satuan) {
                        foreach ($satuanData['biaya'] as $biayaData) {
                            $biaya = new md_biaya();
                            $biaya->id_maps = $form->id; // Mengaitkan id_maps dengan id dari md_maps yang baru saja dibuat
                            $biaya->id_satuan = $satuan->id; // Mengaitkan id_satuan dengan id dari md_satuan yang ditemukan
                            $biaya->id_customer = $customer->id; // Menyimpan id_customer dalam tabel md_biaya
                            $biaya->id_jenis_barang = $jenisBarang ? $jenisBarang->id : null; // Menyimpan id_jenis_barang jika ada

                            // Cari dan kaitkan id dari md_biaya_name berdasarkan name_biaya
                            if (isset($biayaData['name_biaya'])) {
                                $biaya_name = md_biaya_name::firstWhere('biaya_name', $biayaData['name_biaya']);
                                if ($biaya_name) {
                                    $biaya->name_biaya = $biaya_name->id;
                                }
                            }

                            $biaya->harga = $biayaData['harga'];
                            $biaya->harga_modal = $biayaData['harga_modal'];

                            $biaya->save();
                        }
                    }
                }
            }
        }

        // Perbarui catatan
        $form->notes = $request->notes;
        $form->save();

        return response()->json(['success' => 'Data updated successfully']);


        // Tambahkan data satuan dan biaya yang baru
        // if (!empty($request->satuan)) {
        //     foreach ($request->satuan as $satuanData) {
        //         if (isset($satuanData['name_satuan'])) {
        //             $satuan = md_satuan::firstWhere('name_satuan', $satuanData['name_satuan']);
        //             if ($satuan && isset($satuanData['biaya']) && is_array($satuanData['biaya'])) {
        //                 foreach ($satuanData['biaya'] as $biayaData) {
        //                     if (isset($biayaData['name_biaya'], $biayaData['harga'])) {
        //                         $biayaNameEntry = md_biaya_name::firstWhere('biaya_name', $biayaData['name_biaya']);

        //                         if ($biayaNameEntry) {
        //                             // Buat entri baru di md_biaya
        //                             $biaya = new md_biaya();
        //                             $biaya->id_maps = $form->id;
        //                             $biaya->id_satuan = $satuan->id;
        //                             $biaya->name_biaya = $biayaNameEntry->id;
        //                             $biaya->harga = $biayaData['harga'];
        //                             $biaya->harga_modal = $biayaData['harga_modal'];
        //                             $biaya->save();
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
    }


    public function delete_maps($id)
    {
        try {
            $maps = md_maps::find($id);

            if ($maps) {
                $maps->delete();

                return response()->json(['message' => 'Data deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Data not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function delete_user($id)
    {
        try {
            $maps = User::find($id);

            if ($maps) {
                $maps->delete();

                return response()->json(['message' => 'Data deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Data not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function geocode(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=', [
            'latlng' => "{$lat},{$lng}",
            'key' => env('GOOGLE_MAPS_API_KEY'),
        ]);

        $data = $response->json();

        if (!empty($data['results'])) {
            $address = $data['results'][0]['formatted_address'];
            // Hapus Plus Codes atau OLC dari alamat
            $plusCodeIndex = strpos($address, '+');
            if ($plusCodeIndex !== false) {
                $endOfPlusCode = strpos($address, ' ', $plusCodeIndex);
                $address = substr($address, $endOfPlusCode);
            }

            return response()->json(['address' => $address]);
        }

        return response()->json(['address' => null], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(md_maps $md_maps)
    {
        //
    }
}
