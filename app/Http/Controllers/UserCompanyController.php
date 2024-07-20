<?php

namespace App\Http\Controllers;

use App\Models\user_company;
use App\Http\Requests\Storeuser_companyRequest;
use App\Http\Requests\Updateuser_companyRequest;

class UserCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = user_company::all();
        return response()->json($user);
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
    public function store(Storeuser_companyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(user_company $user_company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(user_company $user_company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateuser_companyRequest $request, user_company $user_company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(user_company $user_company)
    {
        //
    }
}
