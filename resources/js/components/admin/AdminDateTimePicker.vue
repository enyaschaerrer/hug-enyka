<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: string;
    label: string;
    mode: 'start' | 'end';
    disabled?: boolean;
    minDateTime?: string | null;
    pairedDateTime?: string | null;
    referenceDateTime?: string | null;
    blockedRanges?: Array<{ start: string; end: string }>;
    defaultTime?: string;
}>(), {
    disabled: false,
    minDateTime: null,
    pairedDateTime: null,
    referenceDateTime: null,
    blockedRanges: () => [],
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
const currentYear = new Date().getFullYear();
const isOpen = ref(false);
const hoveredDate = ref<Date | null>(null);
const openUpward = ref(false);
const rootRef = ref<HTMLDivElement | null>(null);
const triggerRef = ref<HTMLButtonElement | null>(null);
const selectedTime = ref(timeFromValue(props.modelValue) ?? props.defaultTime);
const selectedHour = ref(parseInt(selectedTime.value.split(':')[0], 10));
const initialVisibleDate = parseLocalDateTime(props.modelValue) ?? new Date();
const visibleMonth = ref(initialVisibleDate.getMonth());
const visibleYear = ref(initialVisibleDate.getFullYear());

function toggleOpen() {
    if (props.disabled) {
        isOpen.value = false;
        return;
    }

    if (!isOpen.value && triggerRef.value) {
        const rect = triggerRef.value.getBoundingClientRect();
        openUpward.value = window.innerHeight - rect.bottom < 420;
        syncVisibleDateOnOpen();
    }
    isOpen.value = !isOpen.value;
}

function handleDocumentMouseDown(event: MouseEvent): void {
    if (!isOpen.value || !rootRef.value) {
        return;
    }

    const path = typeof event.composedPath === 'function' ? event.composedPath() : [];

    if (!path.includes(rootRef.value)) {
        isOpen.value = false;
    }
}

const selectedDate = computed(() => dateOnly(props.modelValue));
const effectiveMinDateTime = computed(() => props.minDateTime ?? (props.mode === 'start' ? todayStartValue() : null));
const minDate = computed(() => dateOnly(effectiveMinDateTime.value));
const referenceDate = computed(() => dateOnly(props.referenceDateTime));

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

    const date = new Intl.DateTimeFormat('fr-CH', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(parsed);

    return `${date} : ${String(parsed.getHours()).padStart(2, '0')}h00`;
});

const canGoPreviousMonth = computed(() => visibleYear.value === currentYear && visibleMonth.value > 0);
const canGoNextMonth = computed(() => visibleYear.value === currentYear && visibleMonth.value < 11);

watch(() => props.modelValue, (next) => {
    const time = timeFromValue(next) ?? selectedTime.value;
    selectedTime.value = time;
    selectedHour.value = parseInt(time.split(':')[0], 10);

    const date = parseLocalDateTime(next);
    if (date) {
        setVisibleDate(date);
    }
});

watch(selectedHour, (hour) => {
    selectedTime.value = `${String(hour).padStart(2, '0')}:00`;
    if (props.modelValue) {
        const date = parseLocalDateTime(props.modelValue);
        if (date) {
            emit('update:modelValue', toDateTimeValue(date, selectedTime.value));
        }
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

watch(() => props.disabled, (disabled) => {
    if (disabled) {
        isOpen.value = false;
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

function syncVisibleDateOnOpen(): void {
    const targetDate = parseLocalDateTime(props.modelValue)
        ?? parseLocalDateTime(props.referenceDateTime)
        ?? parseLocalDateTime(effectiveMinDateTime.value)
        ?? new Date();

    setVisibleDate(targetDate);
}

function setVisibleDate(date: Date): void {
    visibleMonth.value = date.getMonth();
    visibleYear.value = currentYear;
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
    if (!day) {
        return false;
    }

    if (minDate.value && day.getTime() < minDate.value.getTime()) {
        return true;
    }

    if (isBlockedByExistingRange(day)) {
        return true;
    }

    return false;
}

function dayIntersectsBlockedRange(day: Date, range: { start: string; end: string }): boolean {
    const rangeStart = dateOnly(range.start);
    const rangeEnd = dateOnly(range.end);

    if (!rangeStart || !rangeEnd) {
        return false;
    }

    return day.getTime() >= rangeStart.getTime() && day.getTime() <= rangeEnd.getTime();
}

function selectionOverlapsBlockedRange(
    day: Date,
    range: { start: string; end: string },
    anchorDate: Date | null,
): boolean {
    const rangeStart = dateOnly(range.start);
    const rangeEnd = dateOnly(range.end);

    if (!rangeStart || !rangeEnd) {
        return false;
    }

    const intervalStart = anchorDate && anchorDate.getTime() < day.getTime() ? anchorDate : day;
    const intervalEnd = anchorDate && anchorDate.getTime() > day.getTime() ? anchorDate : day;

    return intervalStart.getTime() <= rangeEnd.getTime() && intervalEnd.getTime() >= rangeStart.getTime();
}

function isBlockedByExistingRange(day: Date): boolean {
    const anchorDate = dateOnly(props.mode === 'start' ? props.pairedDateTime : props.referenceDateTime);

    return props.blockedRanges.some((range) => {
        if (!anchorDate) {
            return dayIntersectsBlockedRange(day, range);
        }

        return selectionOverlapsBlockedRange(day, range, anchorDate);
    });
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
        return 'border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] text-white';
    }

    if (props.mode === 'end') {
        if (isSameDate(day, hoveredDate.value)) {
            return 'bg-[var(--color-razzmatazz-50)] text-[var(--color-razzmatazz-700)]';
        }

        if (isInRange(day)) {
            return 'bg-[var(--color-razzmatazz-50)] text-[var(--color-razzmatazz-700)] rounded-none';
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
    if (!canGoPreviousMonth.value) {
        return;
    }

    visibleMonth.value -= 1;
}

function nextMonth(): void {
    if (!canGoNextMonth.value) {
        return;
    }

    visibleMonth.value += 1;
}

function selectToday(): void {
    const today = new Date();
    setVisibleDate(today);
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

onMounted(() => {
    document.addEventListener('mousedown', handleDocumentMouseDown);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleDocumentMouseDown);
});
</script>

<template>
    <div ref="rootRef" class="relative">
        <button
            ref="triggerRef"
            type="button"
            class="group input input-bordered flex w-full items-center justify-between pr-3 text-left font-cooper font-medium text-sm"
            :class="props.disabled ? 'cursor-not-allowed border-base-300 bg-base-200/60 text-base-content/35' : 'cursor-pointer'"
            :disabled="props.disabled"
            @click="toggleOpen"
        >
            <span
                class="truncate transition-colors duration-200 ease-out"
                :class="[
                    displayValue ? 'text-base-content' : 'text-base-content/35',
                    props.disabled ? '!text-base-content/35' : 'group-hover:text-[var(--color-razzmatazz-700)]',
                ]"
            >
                {{ displayValue || label }}
            </span>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 shrink-0 text-base-content/55 transition-colors duration-200 ease-out"
                :class="props.disabled ? 'cursor-not-allowed !text-base-content/35' : 'cursor-pointer group-hover:text-[var(--color-razzmatazz-700)]'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path v-for="path in iconPath()" :key="path" :d="path" />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute left-0 z-30 w-full min-w-[22rem] border border-base-300 bg-white p-4 shadow-xl"
            :class="openUpward ? 'bottom-[calc(100%+0.5rem)]' : 'top-[calc(100%+0.5rem)]'"
            @mousedown.stop
            @click.stop
        >
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="btn btn-ghost btn-sm px-2"
                    :class="!canGoPreviousMonth ? 'cursor-not-allowed opacity-35 hover:bg-transparent hover:text-current' : ''"
                    :disabled="!canGoPreviousMonth"
                    @click.stop="previousMonth"
                >
                    <span>←</span>
                </button>
                <select v-model.number="visibleMonth" class="select select-bordered select-sm min-h-9 flex-1 font-cooper font-medium text-sm">
                    <option v-for="(month, index) in months" :key="month" :value="index">
                        {{ month }}
                    </option>
                </select>
                <span class="min-w-14 text-center text-sm font-medium text-base-content/70">
                    {{ visibleYear }}
                </span>
                <button
                    type="button"
                    class="btn btn-ghost btn-sm px-2"
                    :class="!canGoNextMonth ? 'cursor-not-allowed opacity-35 hover:bg-transparent hover:text-current' : ''"
                    :disabled="!canGoNextMonth"
                    @click.stop="nextMonth"
                >
                    <span>→</span>
                </button>
            </div>

            <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-medium text-base-content/45">
                <span v-for="day in weekDays" :key="day" class="py-1">{{ day }}</span>
            </div>

            <div class="mt-1 grid grid-cols-7 gap-1" @mouseleave="setHovered(null)">
                <button
                    v-for="(day, index) in calendarDays"
                    :key="day?.toISOString() ?? `blank-${index}`"
                    type="button"
                    class="btn btn-sm min-h-9 px-0 font-cooper"
                    :class="dayClasses(day)"
                    :aria-disabled="isDisabled(day) || !day"
                    @mouseenter="setHovered(day)"
                    @click.stop="selectDate(day)"
                    @mousedown.stop
                >
                    <span>{{ day?.getDate() }}</span>
                </button>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <button v-if="mode === 'start'" type="button" class="btn btn-ghost btn-sm font-cooper" @click.stop="selectToday">
                    <span>Aujourd&#39;hui</span>
                </button>
                <select
                    v-model.number="selectedHour"
                    class="select select-bordered select-sm font-cooper font-medium text-sm"
                    :class="mode === 'end' ? 'flex-1' : 'mx-auto'"
                    @click.stop
                    @mousedown.stop
                >
                    <option v-for="h in 24" :key="h - 1" :value="h - 1">
                        {{ String(h - 1).padStart(2, '0') }}h00
                    </option>
                </select>
                <button
                    type="button"
                    class="btn btn-sm border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                    @click.stop="isOpen = false"
                >
                    <span>Valider</span>
                </button>
            </div>
        </div>
    </div>
</template>
