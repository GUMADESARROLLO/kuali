<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import CategorySelect from '@/Components/CategorySelect.vue';
import FileUploader from '@/Components/FileUploader.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    categories: { id: number; name: string }[];
}>();

const form = useForm({
    title: '',
    description: '',
    category_id: null as number | null,
    subcategory_id: null as number | null,
    priority: 'media',
    attachments: [] as File[],
});

const submit = () => {
    form.post(route('user.tickets.store'), {
        forceFormData: true,
        onSuccess: () => form.reset('title', 'description', 'attachments'),
    });
};
</script>

<template>
    <AppLayout active-nav="dashboard">
        <Head title="Nuevo Ticket" />

        <div class="w-full">
            <div class="mb-8">
                <h2 class="text-display-lg text-deep-navy dark:text-blue-300 mb-1">Nuevo Ticket</h2>
                <p class="text-body-md text-outline dark:text-gray-400">
                    Reporta un problema o solicita soporte t&eacute;cnico.
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl border border-border-subtle dark:border-gray-700 shadow-sm p-8 space-y-6">
                <!-- Title -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">T&iacute;tulo</label>
                    <input
                        v-model="form.title"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        placeholder="Describe el problema brevemente"
                    />
                    <p v-if="form.errors.title" class="text-error text-label-sm mt-1">{{ form.errors.title }}</p>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Descripci&oacute;n</label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 resize-none"
                        placeholder="Explica el problema con el mayor detalle posible..."
                    />
                    <p v-if="form.errors.description" class="text-error text-label-sm mt-1">{{ form.errors.description }}</p>
                </div>

                <!-- Category / Subcategory -->
                <CategorySelect
                    v-model:category="form.category_id"
                    v-model:subcategory="form.subcategory_id"
                    :categories="categories"
                />
                <p v-if="form.errors.category_id" class="text-error text-label-sm mt-1">{{ form.errors.category_id }}</p>
                <p v-if="form.errors.subcategory_id" class="text-error text-label-sm mt-1">{{ form.errors.subcategory_id }}</p>

                <!-- Priority -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Prioridad</label>
                    <select
                        v-model="form.priority"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    >
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>

                <!-- Attachments -->
                <FileUploader v-model="form.attachments" />
                <p v-if="form.errors.attachments" class="text-error text-label-sm mt-1">{{ form.errors.attachments }}</p>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border-subtle dark:border-gray-700">
                    <a :href="route('user.dashboard')" class="px-4 py-2 text-label-md text-outline hover:text-on-surface transition-colors">
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-deep-navy text-white rounded-lg text-label-md hover:shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined animate-spin text-sm">refresh</span>
                        Enviar Ticket
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
