<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Profile Information</h2>
            <p class="mt-1 text-body-sm text-outline dark:text-gray-400">Update your account's profile information and email address.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Name</label>
                <input v-model="form.name" type="text" required autofocus autocomplete="name"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Email</label>
                <input v-model="form.email" type="email" required autocomplete="username"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                <InputError :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm text-on-surface dark:text-gray-200">Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="text-primary dark:text-blue-400 underline hover:no-underline">Click here to re-send the verification email.</Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-success-green">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" :disabled="form.processing"
                    class="px-5 py-2.5 bg-deep-navy text-white rounded-lg text-label-md hover:shadow-md transition-all disabled:opacity-50">
                    Save
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-outline">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
