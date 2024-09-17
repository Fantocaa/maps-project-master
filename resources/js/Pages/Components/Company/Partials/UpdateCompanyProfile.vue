<script setup>
import { ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import InputError from "@/Components/InputError.vue";
import Label from "@/Components/Label.vue";
import Button from "@/Components/Button.vue";
import Input from "@/Components/Input.vue";

const { props } = usePage();

const form = useForm({
    id: props.data.id,
    name: props.data.name_company,
    agent: props.data.agents.map((agent) => agent.id),
});

const agents = computed(() => props.agents);

const allAgents = computed(() => props.agents.map((agent) => agent.id));

const isSelectAllChecked = computed({
    get() {
        return form.agent.length === allAgents.value.length;
    },
    set(value) {
        if (value) {
            form.agent = allAgents.value;
        } else {
            form.agent = [];
        }
    },
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

            <div>
                <Label for="agent" value="Agent List" />
                <!-- <vSelect
                    id="agent"
                    v-model="form.agent"
                    :options="agents"
                    label="name_agent"
                    multiple
                    track-by="id"
                    placeholder="Select agents"
                    class="border-gray-400 rounded-md focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:text-gray-300 dark:focus:ring-offset-dark-eval-1 w-full bg-dark-eval-0"
                    :reduce="(agent) => agent.id"
                    @input="handleSelectChange"
                /> -->

                <div class="flex items-center mt-2 mb-2">
                    <input
                        type="checkbox"
                        :checked="isSelectAllChecked"
                        @change="isSelectAllChecked = $event.target.checked"
                        class="mr-2 cursor-pointer"
                    />
                    <label>Select All</label>
                </div>

                <div class="space-y-2">
                    <div
                        v-for="agent in agents"
                        :key="agent.id"
                        class="flex items-center"
                    >
                        <input
                            type="checkbox"
                            :value="agent.id"
                            v-model="form.agent"
                            class="mr-2 cursor-pointer"
                        />
                        <label>{{ agent.name_agent }}</label>
                    </div>
                </div>
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
