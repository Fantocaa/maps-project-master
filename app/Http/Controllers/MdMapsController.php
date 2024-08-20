<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storemd_mapsRequest;
use App\Http\Requests\Updatemd_mapsRequest;
use App\Models\md_agent;
use App\Models\md_biaya;
use App\Models\md_biaya_name;
use App\Models\md_company;
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
            ->leftJoin('md_companies as customers', 'md_maps.id_customer', '=', 'customers.id')
            ->select(
                'md_maps.*',
                'md_agents.name_agent as name_agent',
                'md_companies.name_company as name_company',
                'customers.name_company as name_customer'
            )
            ->get();

        foreach ($maps as $map) {
            $satuans = md_satuan::whereHas('biaya', function ($query) use ($map) {
                $query->where('id_maps', $map->id);
            })->get();

            $map->satuan = $satuans->map(function ($satuan) use ($map) {
                $biayas = md_biaya::where('id_maps', $map->id)
                    ->where('id_satuan', $satuan->id)
                    ->join('md_biaya_names', 'md_biayas.name_biaya', '=', 'md_biaya_names.id')
                    ->select('md_biayas.*', 'md_biaya_names.biaya_name as biaya_name')
                    ->get();

                return [
                    'name_satuan' => $satuan->name_satuan,
                    'biaya' => $biayas->map(function ($biaya) {
                        return [
                            'name_biaya' => $biaya->biaya_name,
                            'harga' => $biaya->harga,
                            'harga_modal' => $biaya->harga_modal,
                        ];
                    }),
                ];
            });
        }

        // $maps = md_maps::all();
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
        dd($request);

        $company = md_company::firstWhere('name_company', $request->name_company);
        if (!$company) {
            // Handle the case where the agent is not found
            return;
        }

        $agent = md_agent::firstWhere('name_agent', $request->name_agent);
        if (!$agent) {
            // Handle the case where the agent is not found
            return;
        }

        $customer = md_company::firstWhere('name_company', $request->name_customer);
        if (!$customer) {
            // Handle the case where the agent is not found
            return;
        }

        $form = new md_maps();
        $form->notes = $request->notes;
        $form->lat = $request->lat;
        $form->lng = $request->lng;
        $form->lokasi = $request->lokasi;
        $form->name = $request->name;
        $form->date = Carbon::now()->toDateString();
        $form->id_perusahaan = $company->id;
        $form->id_agent = $agent->id;
        $form->id_customer = $customer->id;
        $form->name_penerima = $request->name_penerima;
        $form->save();

        foreach ($request->satuan as $satuanData) {
            $satuan = md_satuan::firstWhere('name_satuan', $satuanData['name_satuan']);
            if ($satuan) {
                foreach ($satuanData['biaya'] as $biayaData) {
                    $biaya = new md_biaya();
                    $biaya->id_maps = $form->id; // Mengatur id_maps ke id dari md_maps yang baru saja dibuat
                    $biaya->id_satuan = $satuan->id; // Mengatur id_satuan ke id dari md_satuan yang ditemukan

                    // Find the biaya_name by its name and set its id
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

    public function update_maps(Updatemd_mapsRequest $request, md_maps $md_maps)
    {
        // dd($request);
        $form = md_maps::find($request->id);

        // Tambahkan pengecekan untuk memastikan objek ditemukan sebelum memanipulasinya
        if (!$form) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        // Hapus semua data md_biaya yang terkait dengan id_maps
        md_biaya::where('id_maps', $form->id)->delete();

        // Tambahkan data satuan dan biaya yang baru
        if (!empty($request->satuan)) {
            foreach ($request->satuan as $satuanData) {
                if (isset($satuanData['name_satuan'])) {
                    $satuan = md_satuan::firstWhere('name_satuan', $satuanData['name_satuan']);
                    if ($satuan && isset($satuanData['biaya']) && is_array($satuanData['biaya'])) {
                        foreach ($satuanData['biaya'] as $biayaData) {
                            if (isset($biayaData['name_biaya'], $biayaData['harga'])) {
                                $biayaNameEntry = md_biaya_name::firstWhere('biaya_name', $biayaData['name_biaya']);

                                if ($biayaNameEntry) {
                                    // Buat entri baru di md_biaya
                                    $biaya = new md_biaya();
                                    $biaya->id_maps = $form->id;
                                    $biaya->id_satuan = $satuan->id;
                                    $biaya->name_biaya = $biayaNameEntry->id;
                                    $biaya->harga = $biayaData['harga'];
                                    $biaya->harga_modal = $biayaData['harga_modal'];
                                    $biaya->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        // Perbarui catatan
        $form->notes = $request->notes;
        $form->save();

        return response()->json(['success' => 'Data updated successfully']);
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
