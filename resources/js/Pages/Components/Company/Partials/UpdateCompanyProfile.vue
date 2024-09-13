<script setup>
import axios from "axios";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import "@vueform/multiselect/themes/default.css";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

const { props } = usePage();

let form = useForm({
    id: ref(props.data.id),
    name: ref(props.data.name_company),
    agent: props.data.agents.map((agent) => agent.id),
    // agent: ref(props.data.agents.map((agent) => agent.name_agent)),
});

const agents = props.agents; // Semua agent untuk pilihan

// console.log(agents);
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

        <form
            @submit.prevent="
                form.patch(route('company.update', { id: props.data.id }))
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

            <!-- <div>
                <Label for="agent" value="Agent List" />
                <vSelect
                    id="agent"
                    v-model="form.agent"
                    :options="agents"
                    label="name_agent"
                    multiple
                    track-by="id"
                    placeholder="Select agents"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                />
                <InputError class="mt-2" :message="form.errors.agent" />
            </div> -->

            <div>
                <Label for="agent" value="Agent List" />
                <vSelect
                    id="agent"
                    v-model="form.agent"
                    :options="agents"
                    label="name_agent"
                    multiple
                    track-by="id"
                    placeholder="Select agents"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                    :reduce="(agent) => agent.id"
                />
                <InputError class="mt-2" :message="form.errors.agent" />
            </div>

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
