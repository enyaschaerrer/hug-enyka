<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: string;
    label: string;
    mode: 'start' | 'end';
    minDateTime?: string | null;
    referenceDateTime?: string | null;
    defaultTime?: string;
}>(), {
    minDateTime: null,
    referenceDateTime: null,
    defaultTime: '09:00',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const months = [
    'Janvier',
    'Février',
    'Mars',
    'Avril',
    'Mai',
    'Juin',
    'Juillet',
    'Août',
    'Septembre',
    'Octobre',
    'Novembre',
    'Décembre',
];

const weekDays = ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'];
const isOpen = ref(false);
const hoveredDate = ref<Date | null>(null);
const openUpward = ref(false);
const triggerRef = ref<HTMLButtonElement | null>(null);

function toggleOpen() {
    if (!isOpen.value && triggerRef.value) {
        const rect = triggerRef.value.getBoundingClientRect();
        openUpward.value = window.innerHeight - rect.bottom < 420;
    }
    isOpen.value = !isOpen.value;
}
const selectedTime = ref(timeFromValue(props.modelValue) ?? props.defaultTime);
const visibleMonth = ref(monthFromValue(props.modelValue) ?? new Date().getMonth());
const visibleYear = new Date().getFullYear();

const selectedDate = computed(() => dateOnly(props.modelValue));
const effectiveMinDateTime = computed(() => props.minDateTime ?? (props.mode === 'start' ? todayStartValue() : null));
const minDate = computed(() => dateOnly(effectiveMinDateTime.value));
const referenceDate = computed(() => dateOnly(props.referenceDateTime));

const calendarDays = computed(() => {
    const firstDay = new Date(visibleYear, visibleMonth.value, 1);
    const firstWeekday = (firstDay.getDay() + 6) % 7;
    const daysInMonth = new Date(visibleYear, visibleMonth.value + 1, 0).getDate();
    const days: Array<Date | null> = [];

    for (let index = 0; index < firstWeekday; index += 1) {
        days.push(null);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        days.push(new Date(visibleYear, visibleMonth.value, day));
    }

    while (days.length % 7 !== 0) {
        days.push(null);
    }

    return days;
});

const displayValue = computed(() => {
    const parsed = parseLocalDateTime(props.modelValue);

    if (!parsed) {
        return '';
    }

    return new Intl.DateTimeFormat('fr-CH', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(parsed);
});

watch(() => props.modelValue, (next) => {
    selectedTime.value = timeFromValue(next) ?? selectedTime.value;

    const date = parseLocalDateTime(next);
    if (date) {
        visibleMonth.value = date.getMonth();
    }
});

watch(effectiveMinDateTime, (newMin) => {
    if (!newMin || !props.modelValue) {
        return;
    }

    const current = parseLocalDateTime(props.modelValue);
    const min = parseLocalDateTime(newMin);

    if (current && min && current.getTime() < min.getTime()) {
        emit('update:modelValue', '');
    }
});

function parseLocalDateTime(value: string | null | undefined): Date | null {
    if (!value) {
        return null;
    }

    const [datePart, timePart] = value.split('T');
    if (!datePart || !timePart) {
        return null;
    }

    const [year, month, day] = datePart.split('-').map(Number);
    const [hours, minutes] = timePart.split(':').map(Number);

    if ([year, month, day, hours, minutes].some((part) => Number.isNaN(part))) {
        return null;
    }

    return new Date(year, month - 1, day, hours, minutes);
}

function dateOnly(value: string | null | undefined): Date | null {
    const parsed = parseLocalDateTime(value);
    return parsed ? new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate()) : null;
}

function monthFromValue(value: string): number | null {
    return parseLocalDateTime(value)?.getMonth() ?? null;
}

function timeFromValue(value: string): string | null {
    const parsed = parseLocalDateTime(value);

    if (!parsed) {
        return null;
    }

    return `${String(parsed.getHours()).padStart(2, '0')}:${String(parsed.getMinutes()).padStart(2, '0')}`;
}

function todayStartValue(): string {
    return toDateTimeValue(new Date(), '00:00');
}

function toDateTimeValue(date: Date, time: string): string {
    const [hours, minutes] = time.split(':').map(Number);
    const next = new Date(date.getFullYear(), date.getMonth(), date.getDate(), hours, minutes);

    return [
        `${next.getFullYear()}-${String(next.getMonth() + 1).padStart(2, '0')}-${String(next.getDate()).padStart(2, '0')}`,
        `${String(next.getHours()).padStart(2, '0')}:${String(next.getMinutes()).padStart(2, '0')}`,
    ].join('T');
}

function isSameDate(first: Date | null, second: Date | null): boolean {
    return Boolean(first && second
        && first.getFullYear() === second.getFullYear()
        && first.getMonth() === second.getMonth()
        && first.getDate() === second.getDate());
}

function isInRange(day: Date): boolean {
    if (props.mode !== 'end' || !referenceDate.value) {
        return false;
    }

    const end = hoveredDate.value ?? selectedDate.value;
    if (!end) {
        return false;
    }

    return day.getTime() > referenceDate.value.getTime() && day.getTime() < end.getTime();
}

function setHovered(day: Date | null): void {
    if (props.mode === 'end') {
        hoveredDate.value = day;
    }
}

function isDisabled(day: Date | null): boolean {
    if (!day || !minDate.value) {
        return false;
    }

    return day.getTime() <= minDate.value.getTime();
}

function dayClasses(day: Date | null): string {
    if (!day) {
        return 'invisible';
    }

    if (props.mode === 'end' && isSameDate(day, referenceDate.value)) {
        return 'bg-[#5A002A]/10 border border-[#5A002A]/50 text-[#5A002A] font-medium cursor-not-allowed';
    }

    if (isDisabled(day)) {
        return 'btn-disabled opacity-35';
    }

    if (isSameDate(day, selectedDate.value)) {
        return 'btn-primary';
    }

    if (props.mode === 'end') {
        if (isSameDate(day, hoveredDate.value)) {
            return 'bg-primary/25 text-primary';
        }

        if (isInRange(day)) {
            return 'bg-primary/10 text-primary rounded-none';
        }
    }

    if (isSameDate(day, dateOnly(toDateTimeValue(new Date(), '00:00')))) {
        return 'btn-outline';
    }

    return 'btn-ghost';
}

function selectDate(day: Date | null): void {
    if (!day || isDisabled(day)) {
        return;
    }

    let nextValue = toDateTimeValue(day, selectedTime.value);
    const nextDate = parseLocalDateTime(nextValue);
    const min = parseLocalDateTime(effectiveMinDateTime.value);

    if (nextDate && min && nextDate.getTime() < min.getTime()) {
        nextValue = toDateTimeValue(day, timeFromValue(effectiveMinDateTime.value ?? '') ?? selectedTime.value);
        selectedTime.value = timeFromValue(nextValue) ?? selectedTime.value;
    }

    emit('update:modelValue', nextValue);
}


function previousMonth(): void {
    if (visibleMonth.value > 0) {
        visibleMonth.value -= 1;
    }
}

function nextMonth(): void {
    if (visibleMonth.value < 11) {
        visibleMonth.value += 1;
    }
}

function selectToday(): void {
    const today = new Date();
    visibleMonth.value = today.getMonth();
    selectDate(today);
}

function iconPath(): string[] {
    return props.mode === 'start'
        ? [
            'm14 18 4-4 4 4',
            'M16 2v4',
            'M18 22v-8',
            'M21 11.343V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h9',
            'M3 10h18',
            'M8 2v4',
        ]
        : [
            'm14 18 4 4 4-4',
            'M16 2v4',
            'M18 14v8',
            'M21 11.354V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.343',
            'M3 10h18',
            'M8 2v4',
        ];
}
</script>

<template>
    <div class="relative">
        <button
            ref="triggerRef"
            type="button"
            class="cooper-datetime-baseline group input input-bordered flex w-full items-center justify-between pr-3 text-left font-cooper font-medium text-sm"
            @click="toggleOpen"
        >
            <span class="cooper-baseline truncate transition-colors duration-200 ease-out group-hover:text-primary" :class="displayValue ? 'text-base-content' : 'text-base-content/35'">
                {{ displayValue || label }}
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 cursor-pointer text-base-content/55 transition-colors duration-200 ease-out group-hover:text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path v-for="path in iconPath()" :key="path" :d="path" />
            </svg>
        </button>

        <div v-if="isOpen" class="fixed inset-0 z-20" @mousedown="isOpen = false"></div>

        <div
            v-if="isOpen"
            class="absolute left-0 z-30 w-full min-w-[22rem] border border-base-300 bg-white p-4 shadow-xl"
            :class="openUpward ? 'bottom-[calc(100%+0.5rem)]' : 'top-[calc(100%+0.5rem)]'"
        >
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-ghost btn-sm px-2" @click="previousMonth">
                    <span class="cooper-baseline">←</span>
                </button>
                <select v-model.number="visibleMonth" class="cooper-input-baseline select select-bordered select-sm min-h-9 flex-1 font-cooper font-medium text-sm">
                    <option v-for="(month, index) in months" :key="month" :value="index">
                        {{ month }}
                    </option>
                </select>
                <button type="button" class="btn btn-ghost btn-sm px-2" @click="nextMonth">
                    <span class="cooper-baseline">→</span>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-medium text-base-content/45">
                <span v-for="day in weekDays" :key="day" class="cooper-baseline py-1">{{ day }}</span>
            </div>

            <div class="mt-1 grid grid-cols-7 gap-1" @mouseleave="setHovered(null)">
                <button
                    v-for="(day, index) in calendarDays"
                    :key="day?.toISOString() ?? `blank-${index}`"
                    type="button"
                    class="btn btn-sm min-h-9 px-0 font-cooper"
                    :class="dayClasses(day)"
                    :disabled="isDisabled(day)"
                    @mouseenter="setHovered(day)"
                    @click="selectDate(day)"
                >
                    <span class="cooper-baseline">{{ day?.getDate() }}</span>
                </button>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <button v-if="mode === 'start'" type="button" class="btn btn-ghost btn-sm font-cooper" @click="selectToday">
                    <span class="cooper-baseline">Aujourd&#39;hui</span>
                </button>
                <p v-if="mode === 'end' && referenceDateTime" class="cooper-text-baseline text-xs text-base-content/45">
                    Début : {{ new Intl.DateTimeFormat('fr-CH', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(parseLocalDateTime(referenceDateTime) ?? new Date()) }}
                </p>
                <button type="button" class="btn btn-primary btn-sm font-cooper ml-auto" @click="isOpen = false">
                    <span class="cooper-baseline">Valider</span>
                </button>
            </div>
        </div>
    </div>
</template>
