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
const selectedTime = ref(timeFromValue(props.modelValue) ?? props.defaultTime);
const visibleMonth = ref(monthFromValue(props.modelValue) ?? new Date().getMonth());
const visibleYear = ref(yearFromValue(props.modelValue) ?? new Date().getFullYear());

const selectedDate = computed(() => dateOnly(props.modelValue));
const effectiveMinDateTime = computed(() => props.minDateTime ?? (props.mode === 'start' ? todayStartValue() : null));
const minDate = computed(() => dateOnly(effectiveMinDateTime.value));
const referenceDate = computed(() => dateOnly(props.referenceDateTime));

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const values = new Set<number>();

    for (let year = currentYear; year <= currentYear + 5; year += 1) {
        values.add(year);
    }

    if (selectedDate.value) {
        values.add(selectedDate.value.getFullYear());
    }

    if (referenceDate.value) {
        values.add(referenceDate.value.getFullYear());
    }

    return Array.from(values).sort((a, b) => a - b);
});

const calendarDays = computed(() => {
    const firstDay = new Date(visibleYear.value, visibleMonth.value, 1);
    const firstWeekday = (firstDay.getDay() + 6) % 7;
    const daysInMonth = new Date(visibleYear.value, visibleMonth.value + 1, 0).getDate();
    const days: Array<Date | null> = [];

    for (let index = 0; index < firstWeekday; index += 1) {
        days.push(null);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        days.push(new Date(visibleYear.value, visibleMonth.value, day));
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
        hour: '2-digit',
        minute: '2-digit',
    }).format(parsed);
});

watch(() => props.modelValue, (next) => {
    selectedTime.value = timeFromValue(next) ?? selectedTime.value;

    const date = parseLocalDateTime(next);
    if (date) {
        visibleMonth.value = date.getMonth();
        visibleYear.value = date.getFullYear();
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

function yearFromValue(value: string): number | null {
    return parseLocalDateTime(value)?.getFullYear() ?? null;
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

function isDisabled(day: Date | null): boolean {
    if (!day || !minDate.value) {
        return false;
    }

    return day.getTime() < minDate.value.getTime();
}

function dayClasses(day: Date | null): string {
    if (!day) {
        return 'invisible';
    }

    if (isDisabled(day)) {
        return 'btn-disabled opacity-35';
    }

    if (isSameDate(day, selectedDate.value)) {
        return 'btn-primary';
    }

    if (isSameDate(day, referenceDate.value) && props.mode === 'end') {
        return 'border border-[#5A002A] text-[#5A002A]';
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

function updateTime(event: Event): void {
    const input = event.target as HTMLInputElement;
    selectedTime.value = input.value || props.defaultTime;

    if (selectedDate.value) {
        selectDate(selectedDate.value);
    }
}

function previousMonth(): void {
    if (visibleMonth.value === 0) {
        visibleMonth.value = 11;
        visibleYear.value -= 1;
        return;
    }

    visibleMonth.value -= 1;
}

function nextMonth(): void {
    if (visibleMonth.value === 11) {
        visibleMonth.value = 0;
        visibleYear.value += 1;
        return;
    }

    visibleMonth.value += 1;
}

function selectToday(): void {
    const today = new Date();
    visibleMonth.value = today.getMonth();
    visibleYear.value = today.getFullYear();
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
            type="button"
            class="cooper-datetime-baseline input input-bordered flex w-full items-center justify-between pr-3 text-left"
            @click="isOpen = !isOpen"
        >
            <span class="cooper-baseline truncate" :class="displayValue ? 'text-base-content' : 'text-base-content/35'">
                {{ displayValue || label }}
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-base-content/55 transition-colors duration-200 ease-out hover:text-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path v-for="path in iconPath()" :key="path" :d="path" />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute left-0 top-[calc(100%+0.5rem)] z-30 w-full min-w-[22rem] border border-base-300 bg-white p-4 shadow-xl"
        >
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-ghost btn-sm px-2" @click="previousMonth">
                    <span class="cooper-baseline">←</span>
                </button>
                <select v-model.number="visibleMonth" class="select select-bordered select-sm min-h-9 flex-1 font-cooper">
                    <option v-for="(month, index) in months" :key="month" :value="index">
                        {{ month }}
                    </option>
                </select>
                <select v-model.number="visibleYear" class="select select-bordered select-sm min-h-9 w-24 font-cooper">
                    <option v-for="year in years" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
                <button type="button" class="btn btn-ghost btn-sm px-2" @click="nextMonth">
                    <span class="cooper-baseline">→</span>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-medium text-base-content/45">
                <span v-for="day in weekDays" :key="day" class="cooper-baseline py-1">{{ day }}</span>
            </div>

            <div class="mt-1 grid grid-cols-7 gap-1">
                <button
                    v-for="(day, index) in calendarDays"
                    :key="day?.toISOString() ?? `blank-${index}`"
                    type="button"
                    class="btn btn-sm min-h-9 px-0 font-cooper"
                    :class="dayClasses(day)"
                    :disabled="isDisabled(day)"
                    @click="selectDate(day)"
                >
                    <span class="cooper-baseline">{{ day?.getDate() }}</span>
                </button>
            </div>

            <div class="mt-4 flex items-end justify-between gap-3">
                <button v-if="mode === 'start'" type="button" class="btn btn-ghost btn-sm font-cooper" @click="selectToday">
                    <span class="cooper-baseline">Aujourd’hui</span>
                </button>
                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline text-xs font-medium text-base-content/55">Heure</span>
                    <input
                        :value="selectedTime"
                        type="time"
                        class="cooper-input-baseline input input-bordered input-sm h-10 w-32 font-cooper"
                        @input="updateTime"
                    />
                </label>
                <p v-if="mode === 'end' && referenceDateTime" class="cooper-text-baseline text-xs text-base-content/45">
                    Début : {{ new Intl.DateTimeFormat('fr-CH', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(parseLocalDateTime(referenceDateTime) ?? new Date()) }}
                </p>
                <button type="button" class="btn btn-primary btn-sm font-cooper" @click="isOpen = false">
                    <span class="cooper-baseline">Valider</span>
                </button>
            </div>
        </div>
    </div>
</template>
