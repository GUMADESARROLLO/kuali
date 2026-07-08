<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

const model = defineModel<string>({ required: true });

withDefaults(defineProps<{ placeholder?: string }>(), { placeholder: 'Seleccionar fecha' });

const open = ref(false);
const viewYear = ref(new Date().getFullYear());
const viewMonth = ref(new Date().getMonth());

const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

const daysInMonth = computed(() => new Date(viewYear.value, viewMonth.value + 1, 0).getDate());
const firstDayOfWeek = computed(() => new Date(viewYear.value, viewMonth.value, 1).getDay());

const calendarDays = computed(() => {
    const days: (number | null)[] = [];
    for (let i = 0; i < firstDayOfWeek.value; i++) days.push(null);
    for (let d = 1; d <= daysInMonth.value; d++) days.push(d);
    return days;
});

const selectedDay = ref<number | null>(null);
const selectedMonth = ref(viewMonth.value);
const selectedYear = ref(viewYear.value);

const displayValue = computed(() => {
    if (!model.value) return '';
    const parts = model.value.split('-');
    if (parts.length !== 3) return model.value;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
});

const selectDay = (day: number) => {
    selectedDay.value = day;
    selectedMonth.value = viewMonth.value;
    selectedYear.value = viewYear.value;
    const y = viewYear.value;
    const m = String(viewMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    model.value = `${y}-${m}-${d}`;
    open.value = false;
};

const prevMonth = () => {
    if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value--; }
    else viewMonth.value--;
};

const nextMonth = () => {
    if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++; }
    else viewMonth.value++;
};

const isSelected = (day: number) => {
    return selectedDay.value === day && selectedMonth.value === viewMonth.value && selectedYear.value === viewYear.value;
};

const onClickOutside = (e: MouseEvent) => {
    const el = (e.target as HTMLElement);
    if (!el.closest('.date-picker-container')) open.value = false;
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div class="date-picker-container relative">
        <button type="button" @click.stop="open = !open"
            class="w-full flex items-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-body-sm hover:border-primary focus:ring-2 focus:ring-primary outline-none transition-all">
            <span class="material-symbols-outlined text-outline text-[18px]">calendar_today</span>
            <span>{{ displayValue || placeholder }}</span>
        </button>
        <div v-if="open" class="absolute top-full mt-1 left-0 z-50 bg-white dark:bg-gray-800 border border-border-subtle dark:border-gray-700 rounded-xl shadow-lg p-4 w-72">
            <div class="flex items-center justify-between mb-3">
                <button type="button" @click="prevMonth" class="p-1 hover:bg-surface-container-low dark:hover:bg-gray-700 rounded transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-outline">chevron_left</span>
                </button>
                <span class="text-label-md font-semibold text-on-surface dark:text-gray-200">{{ months[viewMonth] }} {{ viewYear }}</span>
                <button type="button" @click="nextMonth" class="p-1 hover:bg-surface-container-low dark:hover:bg-gray-700 rounded transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-outline">chevron_right</span>
                </button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center">
                <div v-for="d in ['Do','Lu','Ma','Mi','Ju','Vi','Sa']" :key="d" class="text-label-sm text-outline py-1">{{ d }}</div>
                <template v-for="(day, i) in calendarDays" :key="i">
                    <div v-if="day === null" class="py-1" />
                    <button v-else type="button" @click="selectDay(day)"
                        class="py-1.5 rounded-lg text-label-sm transition-colors"
                        :class="isSelected(day)
                            ? 'bg-deep-navy text-white font-bold'
                            : 'text-on-surface dark:text-gray-200 hover:bg-surface-container-low dark:hover:bg-gray-700'">
                        {{ day }}
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
