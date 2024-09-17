<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storejenis_barangRequest;
use App\Http\Requests\Updatejenis_barangRequest;
use App\Models\md_jenis_barang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

class MdJenisBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maps = md_jenis_barang::all();
        return response()->json($maps);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storejenis_barangRequest $request)
    {
        $company = new md_jenis_barang();
        $company->jenis_barang_name = $request->jenis_barang_name;
        $company->save();

        // return Inertia::render('Components/Company');
        return Redirect::route('manage.masterdata');
    }

    /**
     * Display the specified resource.
     */
    public function show(md_jenis_barang $md_jenis_barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit_jenisbarang(Request $request, $id): Response
    {
        $id = md_jenis_barang::find($id);

        return Inertia::render('Components/JenisBarang/EditJenisBarang', [
            'data' => $id,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_jenisbarang(Updatejenis_barangRequest $request): RedirectResponse
    {
        $agent = md_jenis_barang::find($request->id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($agent) {
            $agent->jenis_barang_name = $request->name; // Change this line
            $agent->save();
        } else {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return Redirect::route('manage.masterdata');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy_jenisbarang($id): RedirectResponse
    {
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        $user = md_jenis_barang::find($id);

        $user->delete();

        // return Redirect::to('/manage/company');
        return Redirect::route('manage.masterdata');
    }
}
