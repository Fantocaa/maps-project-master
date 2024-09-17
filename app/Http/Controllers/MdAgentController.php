<?php

namespace App\Http\Controllers;

use App\Models\md_agent;
use App\Http\Requests\Storemd_agentRequest;
use App\Http\Requests\Updatemd_agentRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MdAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maps = md_agent::all();
        return response()->json($maps);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function edit_agent(Request $request, $id): Response
    {
        $id = md_agent::find($id);

        return Inertia::render('Components/Agent/EditAgent', [
            'data' => $id,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storemd_agentRequest $request)
    {

        // dd($request);
        $company = new md_agent();
        $company->name_agent = $request->name_agent;
        $company->save();

        // return Inertia::render('Components/Company');
        return Redirect::route('manage.masterdata');
    }

    /**
     * Display the specified resource.
     */
    public function show(md_agent $md_agent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(md_agent $md_agent)
    {
        //
    }

    public function update_agent(Updatemd_agentRequest $request): RedirectResponse
    {
        $agent = md_agent::find($request->id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($agent) {
            $agent->name_agent = $request->name;
            $agent->save();
        } else {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return Redirect::route('manage.masterdata');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatemd_agentRequest $request, md_agent $md_agent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(md_agent $md_agent)
    {
        //
    }

    public function destroy_agent($id): RedirectResponse
    {
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        $user = md_agent::find($id);

        $user->delete();

        return Redirect::to('/manage/company');
    }
}
