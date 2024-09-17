<?php

namespace App\Http\Controllers;

use App\Models\md_biaya_name;
use App\Http\Requests\Storemd_biaya_nameRequest;
use App\Http\Requests\Updatemd_biaya_nameRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MdBiayaNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maps = md_biaya_name::all();
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
    public function store(Storemd_biaya_nameRequest $request)
    {
        $company = new md_biaya_name();
        $company->biaya_name = $request->biaya_name;
        $company->save();

        // return Inertia::render('Components/Company');
        return Redirect::route('manage.masterdata');
    }

    /**
     * Display the specified resource.
     */
    public function show(md_biaya_name $md_biaya_name)
    {
        //
    }

    public function edit_biaya(Request $request, $id): Response
    {
        $id = md_biaya_name::find($id);

        return Inertia::render('Components/Biaya/EditBiaya', [
            'data' => $id,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update_biaya(Updatemd_biaya_nameRequest $request): RedirectResponse
    {
        $agent = md_biaya_name::find($request->id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($agent) {
            $agent->biaya_name = $request->name; // Change this line
            $agent->save();
        } else {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return Redirect::route('manage.masterdata');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(md_biaya_name $md_biaya_name)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatemd_biaya_nameRequest $request, md_biaya_name $md_biaya_name)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(md_biaya_name $md_biaya_name)
    {
        //
    }

    public function destroy_biaya($id): RedirectResponse
    {
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        $user = md_biaya_name::find($id);

        $user->delete();

        return Redirect::to('/manage/company');
    }
}
