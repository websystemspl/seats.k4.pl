<template>
    <div class="relative p-6 space-y-6">

        <!-- WIDGET: Pracownicy -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3 rounded-t-lg">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-600">Pracownicy</h2>
                <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                    <input type="checkbox" v-model="hideInactive" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5" />
                    Ukryj nieaktywnych
                </label>
            </div>
            <div class="p-4">
                <ul v-auto-animate>
                    <draggable v-model="visibleEmployees"
                        group="employees"
                        @start="drag=true"
                        @end="endDrag"
                        :itemKey="'id'">
                        <template #item="employee">
                            <li class="p-3 flex flex-col gap-2 even:bg-gray-50 odd:bg-white rounded">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <span class="cursor-move text-gray-400 text-sm">&#9776;</span>
                                        <div>
                                            <span class="font-medium text-gray-800">{{ employee.element.name }}</span>
                                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-gray-500">
                                                <span v-if="employee.element.employment_start_date">
                                                    Od: {{ formatDate(employee.element.employment_start_date) }}
                                                </span>
                                                <span v-if="employee.element.tenure_years !== undefined && employee.element.employment_start_date">
                                                    Staż: {{ formatTenure(employee.element.tenure_years) }}
                                                </span>
                                                <span v-if="employee.element.vacation_entitlement && employee.element.employment_start_date"
                                                    :class="employee.element.vacation_entitlement >= 26 ? 'text-green-600 font-semibold' : 'text-blue-600 font-semibold'">
                                                    Wymiar: {{ employee.element.vacation_entitlement }} dni
                                                </span>
                                                <span class="text-gray-400" v-if="employee.element.education_years > 0">
                                                    ({{ educationLabel(employee.element.education_years) }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <label class="text-xs text-gray-500 cursor-pointer" :for="'switch_' + employee.index">
                                            {{ employee.element.is_active ? 'Aktywny' : 'Nieaktywny' }}
                                        </label>
                                        <input
                                            class="h-4 w-8 appearance-none rounded-full bg-gray-300 transition-colors checked:bg-blue-600 relative cursor-pointer
                                            before:content-[''] before:absolute before:top-0.5 before:left-0.5 before:h-3 before:w-3 before:rounded-full before:bg-white before:shadow before:transition-transform
                                            checked:before:translate-x-4"
                                            type="checkbox"
                                            role="switch"
                                            :id="'switch_' + employee.index"
                                            v-model="employee.element.is_active"
                                            @change="changeStatus(employee.element.id)" />
                                        <button @click="openPopup('employment', employee.element.id)" class="p-1.5 bg-green-600 text-white rounded shadow-sm hover:bg-green-700 transition" title="Staż pracy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 fill-white" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5zm2 4h5v5H7v-5z"/></svg>
                                        </button>
                                        <button @click="openPopup('edit', employee.element.id)" class="p-1.5 bg-blue-600 text-white rounded shadow-sm hover:bg-blue-700 transition" title="Edytuj">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 fill-white" viewBox="0 0 512 512"><path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0z"/></svg>
                                        </button>
                                        <button @click="openPopup('delete', employee.element.id)" class="p-1.5 bg-red-600 text-white rounded shadow-sm hover:bg-red-700 transition" title="Usuń">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 fill-white" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </draggable>
                </ul>

                <p class="mt-4 text-xs text-gray-400 italic">
                    Do stażu pracy wliczane są ukończone studia (wg Kodeksu Pracy art. 155 §1).
                    Urlop na żądanie: 4 dni z przysługującej puli (20 lub 26 dni).
                </p>
            </div>
        </div>

        <!-- WIDGET: Święta -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3 rounded-t-lg">
                <h2 class="text-sm font-bold uppercase tracking-widest text-gray-600">Święta w sobotę</h2>
            </div>
            <div class="p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <p class="text-sm text-gray-600">Wylicz liczbę dni do odbioru dla wybranego roku.</p>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                        <div>
                            <label for="holidayYear" class="form-label inline-block mb-1 text-gray-700 text-sm">Rok</label>
                            <input type="number" min="1970" max="2100" class="block w-full px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="holidayYear" id="holidayYear" />
                        </div>
                        <button @click="syncHolidayYear()" :disabled="holidaySyncLoading" class="px-4 py-1.5 bg-blue-600 text-white text-xs font-medium uppercase rounded shadow hover:bg-blue-700 disabled:opacity-50 transition">Przelicz</button>
                    </div>
                </div>
                <p v-if="holidaySyncResult" class="mt-3 text-sm text-gray-600">Rok {{ holidaySyncResult.year }}: {{ holidaySyncResult.saturday_holiday_count }} dni do odbioru</p>
                <p v-if="holidaySyncError" class="mt-3 text-sm text-red-500">{{ holidaySyncError }}</p>
                <div v-if="holidayYears.length" class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Dostępne lata</p>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="hy in holidayYears" :key="hy.year" class="flex items-center justify-between rounded border border-gray-200 bg-gray-50 px-3 py-2">
                            <span class="text-sm text-gray-600">{{ hy.year }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ hy.saturday_holiday_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div tabindex="0" class="z-40 bg-black bg-opacity-50 fixed top-0 bottom-0 left-0 right-0 w-full h-full overflow-auto" v-if="popup">
            <div class="z-50 max-w-lg px-3 py-12 relative mx-auto">
                <div class="bg-white rounded-lg shadow-lg relative overflow-visible">
                    <button @click="closePopup()" class="absolute top-0 right-0 z-10 flex items-center justify-center w-8 h-8 -m-3 text-gray-700 bg-gray-100 rounded-full shadow hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <!-- Delete modal -->
                    <div class="relative p-5" v-if="action==='delete' && employeeId">
                        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Jesteś pewien?</h3>
                        <p class="text-sm text-gray-500 text-center">Tej czynności nie można cofnąć.</p>
                    </div>

                    <!-- Edit modal -->
                    <div class="relative p-5" v-if="action==='edit' && employeeId">
                        <h3 class="text-lg font-medium text-center text-gray-900 mb-4">Edycja pracownika</h3>
                        <div class="space-y-3">
                            <div>
                                <label for="editName" class="text-sm text-gray-700">Imię i nazwisko:</label>
                                <input type="text" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="name" id="editName" />
                            </div>
                            <div>
                                <label for="editWorkingTime" class="text-sm text-gray-700">Dzienny wymiar godzin pracy:</label>
                                <input type="number" step="1" min="1" max="24" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="workingTime" id="editWorkingTime" />
                            </div>
                            <p v-for="error in errors" class="text-red-500 text-sm">{{ error }}</p>
                        </div>
                    </div>

                    <!-- Employment modal -->
                    <div class="relative p-5" v-if="action==='employment' && employeeId">
                        <h3 class="text-lg font-medium text-center text-gray-900 mb-1">Staż pracy</h3>
                        <p class="text-sm text-gray-500 text-center mb-4">{{ currentEmployeeName }}</p>
                        <div class="space-y-3">
                            <div>
                                <label for="empStartDate" class="text-sm text-gray-700">Data rozpoczęcia pracy:</label>
                                <input type="date" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="empStartDate" id="empStartDate" />
                            </div>
                            <div>
                                <label for="empEducation" class="text-sm text-gray-700">Ukończone wykształcenie:</label>
                                <select class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none bg-white" v-model="empEducationYears" id="empEducation">
                                    <option :value="0">Brak / podstawowe</option>
                                    <option :value="3">Zasadnicza zawodowa (3 lata)</option>
                                    <option :value="4">Liceum ogólnokształcące (4 lata)</option>
                                    <option :value="5">Technikum (5 lat)</option>
                                    <option :value="6">Szkoła policealna (6 lat)</option>
                                    <option :value="8">Szkoła wyższa (8 lat)</option>
                                </select>
                            </div>
                            <div v-if="empEducationYears > 0">
                                <label for="empEdCompleted" class="text-sm text-gray-700">Data ukończenia nauki:</label>
                                <input type="date" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="empEdCompletedDate" id="empEdCompleted" />
                            </div>
                            <div v-if="empStartDate" class="mt-3 p-3 bg-blue-50 rounded border border-blue-200">
                                <p class="text-sm text-gray-700">
                                    Wyliczony staż: <span class="font-semibold">{{ calculatedTenureDisplay }}</span>
                                </p>
                                <p class="text-sm text-gray-700">
                                    Wymiar urlopu: <span class="font-semibold" :class="calculatedEntitlement >= 26 ? 'text-green-600' : 'text-blue-600'">{{ calculatedEntitlement }} dni</span>
                                    <span class="text-gray-500 text-xs">(w tym 4 na żądanie)</span>
                                </p>
                            </div>
                            <p class="text-xs text-gray-400 italic">Zmiany stażu pracy są logowane.</p>
                        </div>

                        <!-- Employment logs -->
                        <div v-if="empLogs.length" class="mt-4 border-t border-gray-200 pt-3">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Historia zmian</p>
                            <div class="max-h-32 overflow-y-auto space-y-1">
                                <div v-for="log in empLogs" :key="log.id" class="text-xs text-gray-500">
                                    {{ formatDateTime(log.created_at) }} — {{ log.admin?.name || 'Admin' }}: {{ fieldLabel(log.field) }} {{ log.old_value || '(brak)' }} → {{ log.new_value || '(brak)' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center p-3 space-x-4 bg-gray-100 rounded-b-lg">
                        <button @click="closePopup()" class="block w-full max-w-xs px-4 py-2 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100 text-sm">Anuluj</button>
                        <button v-if="action==='delete'" @click="deleteEmployee()" class="block w-full max-w-xs px-4 py-2 text-white bg-red-500 rounded shadow-sm hover:bg-red-600 text-sm">Potwierdzam</button>
                        <button v-if="action==='edit'" @click="editEmployee()" class="block w-full max-w-xs px-4 py-2 text-white bg-blue-500 rounded shadow-sm hover:bg-blue-600 text-sm">Zapisz</button>
                        <button v-if="action==='employment'" @click="saveEmployment()" class="block w-full max-w-xs px-4 py-2 text-white bg-green-600 rounded shadow-sm hover:bg-green-700 text-sm">Zapisz</button>
                    </div>

                    <div v-if="loading" class="w-full h-full absolute top-0 left-0 bg-gray-200/70 grid content-center justify-center rounded-lg">
                        <div class="spinner-grow inline-block w-12 h-12 bg-current rounded-full opacity-0" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
import axios from 'axios';
import draggable from 'vuedraggable';
import moment from 'moment';

export default {
    name: "Settings",
    components: { draggable },
    data() {
        return {
            drag: false,
            loading: false,
            popup: false,
            employeeId: null,
            action: null,
            employees: [],
            hideInactive: true,
            name: null,
            workingTime: null,
            errors: [],
            holidayYear: new Date().getFullYear(),
            holidayYears: [],
            holidaySyncLoading: false,
            holidaySyncResult: null,
            holidaySyncError: null,
            empStartDate: null,
            empEducationYears: 0,
            empEdCompletedDate: null,
            empLogs: [],
        };
    },
    computed: {
        visibleEmployees: {
            get() {
                if (this.hideInactive) {
                    return this.employees.filter(e => e.is_active);
                }
                return this.employees;
            },
            set(val) {
                if (this.hideInactive) {
                    const inactiveIds = this.employees.filter(e => !e.is_active).map(e => e.id);
                    const inactive = this.employees.filter(e => inactiveIds.includes(e.id));
                    this.employees = [...val, ...inactive];
                } else {
                    this.employees = val;
                }
            }
        },
        currentEmployeeName() {
            if (!this.employeeId) return '';
            const emp = this.employees.find(e => e.id === this.employeeId);
            return emp ? emp.name : '';
        },
        calculatedTenure() {
            if (!this.empStartDate) return 0;
            const today = new Date();
            const start = new Date(this.empStartDate);
            let workYears = (today - start) / (365.25 * 24 * 3600 * 1000);
            const edYears = parseInt(this.empEducationYears) || 0;

            if (edYears <= 0) return Math.max(0, workYears);
            if (!this.empEdCompletedDate) return Math.max(edYears, workYears);

            const edCompleted = new Date(this.empEdCompletedDate);
            const edStarted = new Date(edCompleted);
            edStarted.setFullYear(edStarted.getFullYear() - edYears);

            const overlapEnd = edCompleted < today ? edCompleted : today;
            const overlapStart = edStarted > start ? edStarted : start;

            let overlap = 0;
            if (overlapEnd > overlapStart) {
                overlap = (overlapEnd - overlapStart) / (365.25 * 24 * 3600 * 1000);
            }

            return Math.max(0, edYears + workYears - overlap);
        },
        calculatedTenureDisplay() {
            const years = this.calculatedTenure;
            const y = Math.floor(years);
            const m = Math.round((years - y) * 12);
            if (y === 0 && m === 0) return '< 1 mies.';
            let parts = [];
            if (y > 0) parts.push(y + ' ' + this.pluralYears(y));
            if (m > 0) parts.push(m + ' mies.');
            return parts.join(' ');
        },
        calculatedEntitlement() {
            return this.calculatedTenure >= 10 ? 26 : 20;
        },
    },
    methods: {
        pluralYears(n) {
            if (n === 1) return 'rok';
            if (n >= 2 && n <= 4) return 'lata';
            return 'lat';
        },
        formatDate(date) {
            if (!date) return '';
            return moment(String(date)).format('DD.MM.YYYY');
        },
        formatDateTime(dt) {
            if (!dt) return '';
            return moment(String(dt)).format('DD.MM.YYYY HH:mm');
        },
        formatTenure(years) {
            const y = Math.floor(years);
            const m = Math.round((years - y) * 12);
            let parts = [];
            if (y > 0) parts.push(y + ' ' + this.pluralYears(y));
            if (m > 0) parts.push(m + ' mies.');
            return parts.join(' ') || '< 1 mies.';
        },
        educationLabel(years) {
            const labels = { 3: 'zawodowa', 4: 'liceum', 5: 'technikum', 6: 'policealna', 8: 'wyższe' };
            return labels[years] || '';
        },
        fieldLabel(field) {
            const labels = {
                'employment_start_date': 'Data rozpoczęcia pracy',
                'education_years': 'Wykształcenie (lata)',
                'education_completed_date': 'Data ukończenia nauki'
            };
            return labels[field] || field;
        },
        async getHolidayYears() {
            try {
                const response = await axios.get('/holidayYears');
                this.holidayYears = response.data.holidayYears || [];
            } catch (e) {
                this.holidayYears = [];
            }
        },
        getEmployees() {
            fetch('/users')
                .then(r => r.json())
                .then(data => { this.employees = data.users; });
        },
        async syncHolidayYear() {
            this.holidaySyncLoading = true;
            this.holidaySyncError = null;
            this.holidaySyncResult = null;
            const year = parseInt(this.holidayYear, 10);
            if (Number.isNaN(year)) {
                this.holidaySyncError = 'Podaj poprawny rok.';
                this.holidaySyncLoading = false;
                return;
            }
            try {
                const response = await axios.post('/holidayYears/sync', { year });
                this.holidaySyncResult = response.data.holidayYear;
                await this.getHolidayYears();
            } catch (e) {
                this.holidaySyncError = 'Nie udało się przeliczyć świąt. Spróbuj ponownie.';
            }
            this.holidaySyncLoading = false;
        },
        openPopup(action, employeeId = null) {
            this.popup = true;
            this.employeeId = employeeId;
            this.action = action;
            this.errors = [];
            if (employeeId && action === 'edit') {
                const emp = this.employees.find(e => e.id === employeeId);
                this.name = emp.name;
                this.workingTime = emp.working_time;
            }
            if (employeeId && action === 'employment') {
                const emp = this.employees.find(e => e.id === employeeId);
                this.empStartDate = emp.employment_start_date ? moment(emp.employment_start_date).format('YYYY-MM-DD') : null;
                this.empEducationYears = emp.education_years || 0;
                this.empEdCompletedDate = emp.education_completed_date ? moment(emp.education_completed_date).format('YYYY-MM-DD') : null;
                this.loadEmploymentLogs(employeeId);
            }
        },
        closePopup() {
            this.popup = false;
            this.employeeId = null;
            this.action = null;
            this.loading = false;
            this.name = null;
            this.empLogs = [];
        },
        async loadEmploymentLogs(userId) {
            try {
                const response = await axios.get('/employmentLogs/' + userId);
                this.empLogs = response.data.logs || [];
            } catch (e) {
                this.empLogs = [];
            }
        },
        deleteEmployee() {
            if (this.action === 'delete' && this.employeeId) {
                this.loading = true;
                axios.post('/deleteUser', { employeeId: this.employeeId })
                    .then(() => {
                        this.getEmployees();
                        this.closePopup();
                    })
                    .catch(() => { this.loading = false; });
            }
        },
        async editEmployee() {
            if (this.action === 'edit' && this.employeeId) {
                this.loading = true;
                this.errors = [];
                try {
                    const response = await axios.post('/editUser', {
                        employeeId: this.employeeId,
                        name: this.name,
                        workingTime: this.workingTime
                    });
                    if (response.data.errors.length === 0) {
                        this.employees = this.employees.map(emp => {
                            if (emp.id === response.data.user.id) {
                                return { ...emp, name: response.data.user.name, working_time: response.data.user.working_time };
                            }
                            return emp;
                        });
                        this.closePopup();
                    } else {
                        this.errors = response.data.errors;
                    }
                } catch (e) {
                    console.error(e);
                }
                this.loading = false;
            }
        },
        async saveEmployment() {
            if (this.action === 'employment' && this.employeeId) {
                this.loading = true;
                try {
                    const response = await axios.post('/updateEmployment', {
                        employeeId: this.employeeId,
                        employment_start_date: this.empStartDate || null,
                        education_years: parseInt(this.empEducationYears) || 0,
                        education_completed_date: this.empEducationYears > 0 ? this.empEdCompletedDate : null,
                    });
                    const updated = response.data.user;
                    this.employees = this.employees.map(emp => {
                        if (emp.id === updated.id) {
                            return {
                                ...emp,
                                employment_start_date: updated.employment_start_date,
                                education_years: updated.education_years,
                                education_completed_date: updated.education_completed_date,
                                tenure_years: updated.tenure_years,
                                vacation_entitlement: updated.vacation_entitlement,
                            };
                        }
                        return emp;
                    });
                    this.closePopup();
                } catch (e) {
                    console.error(e);
                }
                this.loading = false;
            }
        },
        changeStatus(employeeId) {
            const emp = this.employees.find(e => e.id === employeeId);
            axios.post('/changeStatus', { employeeId, is_active: emp.is_active });
        },
        endDrag() {
            this.drag = false;
            axios.post('/updateOrder', { employees: this.employees });
        },
    },
    mounted() {
        this.getEmployees();
        this.getHolidayYears();
    }
}
</script>
