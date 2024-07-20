<?php

namespace App\Http\Controllers;

use App\Models\md_company;
use App\Http\Requests\Storemd_companyRequest;
use App\Http\Requests\Updatemd_companyRequest;
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
        $maps = md_company::all();
        return response()->json($maps);
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

        $id = md_company::find($id);
        // $name_company = $id->name_company;

        return Inertia::render('Components/Company/EditCompany', [
            'data' => $id,
            // 'name_company' => $name_company,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
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
        $company = md_company::find($request->id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($company) {
            $company->name_company = $request->name; // Change this line
            $company->save();
        } else {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return Redirect::route('manage.company');
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
