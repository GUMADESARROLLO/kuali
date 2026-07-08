<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => { confirmingUserDeletion.value = true; nextTick(() => passwordInput.value?.focus()); };

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => { confirmingUserDeletion.value = false; form.clearErrors(); form.reset(); };
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100">Delete Account</h2>
            <p class="mt-1 text-body-sm text-outline dark:text-gray-400">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
        </header>

        <button @click="confirmUserDeletion"
            class="px-5 py-2.5 bg-error text-white rounded-lg text-label-md hover:opacity-90 transition-all">
            Delete Account
        </button>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeModal">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-lg p-6 w-full max-w-md mx-4">
                    <h2 class="text-headline-sm font-semibold text-on-surface dark:text-gray-100 mb-2">Are you sure you want to delete your account?</h2>
                    <p class="text-body-sm text-outline dark:text-gray-400 mb-4">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.</p>

                    <div class="space-y-2 mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Password</label>
                        <input ref="passwordInput" v-model="form.password" type="password" placeholder="Password" @keyup.enter="deleteUser"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="closeModal"
                            class="px-4 py-2 border border-border-subtle rounded-lg text-label-sm text-outline hover:bg-surface-container-low transition-all">Cancel</button>
                        <button @click="deleteUser" :disabled="form.processing"
                            class="px-4 py-2 bg-error text-white rounded-lg text-label-sm hover:opacity-90 transition-all disabled:opacity-50">Delete Account</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
