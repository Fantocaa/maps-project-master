<?php

namespace App\Http\Controllers;

use App\Models\md_company;
use App\Http\Requests\Storemd_companyRequest;
use App\Http\Requests\Updatemd_companyRequest;
use App\Models\md_agent;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MdCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = md_company::with('agents:id,name_agent')->get(['id', 'name_company']);
        return response()->json($companies);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }



    public function edit_company(Request $request, $id): Response
    {

        $company = md_company::with('agents')->find($id);
        $agents = md_agent::all(); // Mendapatkan semua agent yang tersedia

        return Inertia::render('Components/Company/EditCompany', [
            'data' => $company,
            'agents' => $agents, // Kirim semua agents untuk pilihan
            // 'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storemd_companyRequest $request)
    {
        $company = new md_company();
        $company->name_company = $request->name_company;
        $company->save();

        // return Inertia::render('Components/Company');
        return Redirect::route('manage.company');
    }

    /**
     * Display the specified resource.
     */
    public function show(md_company $md_company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(md_company $md_company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_company(Updatemd_companyRequest $request): RedirectResponse
    {
        // dd($request);
        // Cari data company berdasarkan ID
        $company = md_company::find($request->id);

        // Validasi input name dan agent
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'agent' => ['required', 'array'], // Pastikan agent adalah array
        ]);

        // Jika company ditemukan
        if ($company) {
            // Update name_company
            $company->name_company = $request->name;
            $company->save();

            // Ambil ID agent dari array agent
            // $agentIds = array_map(function ($agent) {
            //     return $agent['id']; // Ambil hanya id dari masing-masing agent
            // }, $request->agent);

            // Mengambil ID agent langsung dari array
            $agentIds = $request->agent; // ID agen sudah ada dalam array

            // Sync agents ke tabel pivot (pivot_company_agents) menggunakan array agent_id
            $company->agents()->sync($agentIds);

            // Redirect ke halaman manage.company dengan pesan sukses
            return Redirect::route('manage.company')->with('status', 'Company updated successfully.');
        } else {
            // Jika company tidak ditemukan, return error 404
            return response()->json(['error' => 'Data not found'], 404);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(md_company $md_company)
    {
        //
    }

    public function destroy_company($id): RedirectResponse
    {
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        $user = md_company::find($id);

        $user->delete();

        return Redirect::to('/manage/company');
    }
}
