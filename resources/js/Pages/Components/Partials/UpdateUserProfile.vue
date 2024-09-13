<script setup>
import axios from "axios";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

const { props } = usePage();
let companies = ref([]); // Add this line

const fetchDataCompany = async () => {
    const response = await axios.get("/company");
    companies.value = response.data;
};

let form = useForm({
    id: ref(props.user.id),
    name: ref(props.user.name),
    email: ref(props.user.email),
    role: ref(props.user.roles[0].name),
    company_id: ref(props.user.companies),
    // id_view_company: ref(props.user.companies[0].pivot.id_view_name_company),
    // view_company_name: ref(companies ? companies.name_company : null), // Jika perusahaan ditemukan, gunakan name_company. Jika tidak, gunakan null.
    id_view_name_company: ref(
        props.user.view_companies.map((vc) => vc.company.name_company)
    ),
    // id_view_name_customer: ref(
    //     props.user.view_customers.map((vc) => vc.company.name_company)
    // ),
});

// console.log(props.user);

onMounted(() => {
    fetchDataCompany();
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <!-- @submit.prevent="form.patch(route('user.update'))" -->
        <form
            @submit.prevent="
                form.patch(route('user.update', { id: props.user.id }), {
                    ...form,
                    company_id: form.company_id.value,
                })
            "
            class="mt-6 space-y-6"
        >
            <div>
                <Label for="name" value="Name" />

                <Input
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <Label for="email" value="Email" />

                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- <div
                v-if="props.mustVerifyEmail && user.email_verified_at === null"
            >
                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="props.status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div> -->

            <div>
                <Label for="role" value="Role" />

                <select
                    id="role"
                    v-model="form.role"
                    required
                    :class="[
                        'py-2 border-gray-400 rounded-md',
                        'focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white',
                        'dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full',
                    ]"
                >
                    <option value="user">User</option>
                    <option value="superuser">Super User</option>
                    <option value="superuser2">Super User 2</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>

                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div>
                <Label for="company" value="Company" />
                <vSelect
                    id="company"
                    v-model="form.company_id"
                    :options="companies"
                    label="name_company"
                    searchable="true"
                    track-by="id"
                    multiple
                    requred
                    placeholder="Select a company"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                />
                <InputError class="mt-2" :message="form.errors.company_id" />
            </div>

            <!-- <div>
                <Label for="company" value="Company" />
                <select
                    id="company"
                    v-model="form.company_id"
                    required
                    :class="[
                        'py-2 border-gray-400 rounded-md',
                        'focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white',
                        'dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full',
                    ]"
                >
                    <option value="" disabled>Select a company</option>
                    <option
                        v-for="company in companies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.name_company }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.company_id" />
            </div> -->

            <div>
                <Label for="view_company" value="View Company" />
                <vSelect
                    id="view_company"
                    v-model="form.id_view_name_company"
                    :options="companies"
                    label="name_company"
                    searchable="true"
                    track-by="id"
                    multiple
                    requred
                    placeholder="Select a company"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.id_view_name_company"
                />
            </div>

            <!-- <div>
                <Label for="view_customer" value="View Customer" />
                <vSelect
                    id="view_customer"
                    v-model="form.id_view_name_customer"
                    :options="companies"
                    label="name_company"
                    searchable="true"
                    track-by="id"
                    multiple
                    requred
                    placeholder="Select a company"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.id_view_name_customer"
                />
            </div> -->

            <div class="flex items-center gap-4">
                <Button :disabled="form.processing">Save</Button>

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                    class="transition ease-in-out"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
