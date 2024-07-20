<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function edit_user(Request $request, $id): Response
    {
        // Get the user from the database
        $user = User::find($id);

        // Get the user's roles
        $roles = $user->roles->pluck('name');
        $company = $user->companies->pluck('name_company');
        // $view_company = $user->viewCompanies->pluck('name_company');
        $view_company = $user->viewCompanies->map(function ($viewCompany) {
            return $viewCompany->company ? $viewCompany->company->name_company : null;
        })->filter();

        // Get the user's view customers
        $view_customer = $user->viewCustomers->map(function ($viewCustomer) {
            return $viewCustomer->company ? $viewCustomer->company->name_company : null;
        })->filter();

        // dd($view_customer);

        return Inertia::render('Components/Edit', [
            'user' => $user,
            'roles' => $roles, // Add this line
            'company_id' => $company, // Add this line
            'id_view_name_company' => $view_company, // Add this line
            'id_view_name_customer' => $view_customer,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update_user(UpdateUserRequest $request): RedirectResponse
    {
        // dd($request);
        $userId = $request->id; // get the user id from the request

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', Rule::in(['user', 'superuser', 'admin', 'superadmin'])],
            'company_id' => ['required', 'array'],
            'company_id.id' => ['exists:md_companies,id'],
            'id_view_name_company' => ['required', 'array'],
            'id_view_name_company.id' => ['exists:md_companies,id'],
            'id_view_name_customer' => ['required', 'array'],
            'id_view_name_customer.id' => ['exists:md_companies,id'],
        ]);

        $user = User::find($userId);
        $user->name = $request->name;
        $user->email = $request->email;

        $user->save();

        $user->roles()->detach();
        $user->assignRole($request->role);

        $companyIds = array_map(function ($company) {
            return $company['id'];
        }, $request->company_id);

        $user->companies()->sync($companyIds);

        $viewCompanyIds = array_map(function ($view_company) {
            if (is_array($view_company) && isset($view_company['id'])) {
                return $view_company['id'];
            }

            // Jika $view_company adalah string, cari perusahaan berdasarkan nama
            if (is_string($view_company)) {
                $company = Company::where('name_company', $view_company)->first();

                // Jika perusahaan ditemukan, kembalikan ID-nya
                if ($company) {
                    return $company->id;
                }
            }

            return null;
        }, $request->id_view_name_company);

        // Hapus null dari $viewCompanyIds
        $viewCompanyIds = array_filter($viewCompanyIds);

        // Hapus semua ViewCompany yang terkait dengan pengguna
        $user->viewCompanies()->delete();

        // Buat ViewCompany baru untuk setiap ID perusahaan dalam $viewCompanyIds
        foreach ($viewCompanyIds as $viewCompanyId) {
            $user->viewCompanies()->create(['company_id' => $viewCompanyId]);
        }

        $viewCustomerIds = array_map(function ($view_customer) {
            if (is_array($view_customer) && isset($view_customer['id'])) {
                return $view_customer['id'];
            }

            if (is_string($view_customer)) {
                $company = Company::where('name_company', $view_customer)->first();

                // Jika perusahaan ditemukan, kembalikan ID-nya
                if ($company) {
                    return $company->id;
                }
            }

            return null;
        }, $request->id_view_name_customer);

        $viewCustomerIds = array_filter($viewCustomerIds);

        $user->viewCustomers()->delete();

        foreach ($viewCustomerIds as $viewCompanyId) {
            $user->viewCustomers()->create(['customer_id' => $viewCompanyId]);
        }

        return Redirect::route('manage.user');
    }

    public function destroy_user($id): RedirectResponse
    {
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        $user = User::find($id);

        $user->delete();

        return Redirect::to('/manage/user');
    }

    public function update_password($id, Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::find($id);

        if (! $user) {
            return back()->withErrors(['message' => 'User not found']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return Redirect::to('/manage/user');
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
