<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => { form.reset(); },
        onError: () => {
            if (form.errors.password) { form.reset('password', 'password_confirmation'); passwordInput.value?.focus(); }
            if (form.errors.current_password) { form.reset('current_password'); currentPasswordInput.value?.focus(); }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Update Password</h2>
            <p class="mt-1 text-body-sm text-outline dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Current Password</label>
                <input ref="currentPasswordInput" v-model="form.current_password" type="password" autocomplete="current-password"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                <InputError :message="form.errors.current_password" />
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">New Password</label>
                <input ref="passwordInput" v-model="form.password" type="password" autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" :disabled="form.processing"
                    class="px-5 py-2.5 bg-deep-navy text-white rounded-lg text-label-md hover:shadow-md transition-all disabled:opacity-50">Save</button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-outline">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
