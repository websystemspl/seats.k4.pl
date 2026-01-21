<template>
    <div class="relative p-6">
        <div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Święta w sobotę</p>
                    <p class="text-sm text-gray-600">Wylicz liczbę dni do odbioru dla wybranego roku.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <div>
                        <label for="holidayYear" class="form-label inline-block mb-1 text-gray-700">Rok</label>
                        <input type="number" min="1970" max="2100" class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" v-model="holidayYear" id="holidayYear" />
                    </div>
                    <button type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" @click="syncHolidayYear()" :disabled="holidaySyncLoading" class="inline-block px-4 py-2 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out disabled:opacity-50">Przelicz</button>
                </div>
            </div>
            <p v-if="holidaySyncResult" class="mt-3 text-sm text-gray-600">Rok {{ holidaySyncResult.year }}: {{ holidaySyncResult.saturday_holiday_count }} dni do odbioru</p>
            <p v-if="holidaySyncError" class="mt-3 text-sm text-red-500">{{ holidaySyncError }}</p>
            <div v-if="holidayYears.length" class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Dostępne lata</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="holidayYear in holidayYears" :key="holidayYear.year" class="flex items-center justify-between rounded border border-gray-200 bg-white px-3 py-2">
                        <span class="text-sm text-gray-600">{{ holidayYear.year }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ holidayYear.saturday_holiday_count }}</span>
                    </div>
                </div>
            </div>
        </div>

        <ul v-auto-animate>
            <draggable v-model="employees" 
                group="employees" 
                @start="drag=true" 
                @end="endDrag"
                :itemKey="'id'">
                <template #item="employee">
                    <li class="p-2 flex justify-between items-center even:bg-gray-50 odd:bg-white">
                        <span class="cursor-move">{{ employee.index + 1 }}. {{ employee.element.name }}</span>
                        <div>
                            <label class="inline-block pl-[0.15rem] hover:cursor-pointer mr-2" :for="'flexSwitchCheckDefault_' + employee.index">{{ employee.element.is_active === true ? 'Aktywny' : 'Nieaktywny' }}</label>
                            <input class="mr-3 mt-[0.3rem] h-3.5 w-8 appearance-none rounded-[0.4375rem] bg-neutral-300 before:pointer-events-none before:absolute before:h-3.5 before:w-3.5 before:rounded-full before:bg-transparent before:content-[''] after:absolute after:z-[2] after:-mt-[0.1875rem] after:h-5 after:w-5 after:rounded-full after:border-none after:bg-neutral-100 after:shadow-[0_0px_3px_0_rgb(0_0_0_/_7%),_0_2px_2px_0_rgb(0_0_0_/_4%)] after:transition-[background-color_0.2s,transform_0.2s] after:content-[''] checked:bg-primary checked:after:absolute checked:after:z-[2] checked:after:-mt-[3px] checked:after:ml-[1.0625rem] checked:after:h-5 checked:after:w-5 checked:after:rounded-full checked:after:border-none checked:after:bg-primary checked:after:shadow-[0_3px_1px_-2px_rgba(0,0,0,0.2),_0_2px_2px_0_rgba(0,0,0,0.14),_0_1px_5px_0_rgba(0,0,0,0.12)] checked:after:transition-[background-color_0.2s,transform_0.2s] checked:after:content-[''] hover:cursor-pointer focus:outline-none focus:ring-0 focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[3px_-1px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-5 focus:after:w-5 focus:after:rounded-full focus:after:content-[''] checked:focus:border-primary checked:focus:bg-primary checked:focus:before:ml-[1.0625rem] checked:focus:before:scale-100 checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] dark:bg-neutral-600 dark:after:bg-neutral-400 dark:checked:bg-primary dark:checked:after:bg-primary dark:focus:before:shadow-[3px_-1px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[3px_-1px_0px_13px_#3b71ca]" type="checkbox" role="switch" :id="'flexSwitchCheckDefault_' + employee.index" v-model="employee.element.is_active" @change="changeStatus(employee.element.id)" />
                            <button type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" @click="openPopup('edit', employee.element.id)" class="mr-2 inline-block px-3 py-1.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out"><svg xmlns="http://www.w3.org/2000/svg" class="fill-white h-3" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0S96 57.3 96 128s57.3 128 128 128zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H322.8c-3.1-8.8-3.7-18.4-1.4-27.8l15-60.1c2.8-11.3 8.6-21.5 16.8-29.7l40.3-40.3c-32.1-31-75.7-50.1-123.9-50.1H178.3zm435.5-68.3c-15.6-15.6-40.9-15.6-56.6 0l-29.4 29.4 71 71 29.4-29.4c15.6-15.6 15.6-40.9 0-56.6l-14.4-14.4zM375.9 417c-4.1 4.1-7 9.2-8.4 14.9l-15 60.1c-1.4 5.5 .2 11.2 4.2 15.2s9.7 5.6 15.2 4.2l60.1-15c5.6-1.4 10.8-4.3 14.9-8.4L576.1 358.7l-71-71L375.9 417z"/></svg></button>
                            <button type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" @click="openPopup('delete', employee.element.id)" class="inline-block px-3 py-1.5 bg-red-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-lg transition duration-150 ease-in-out"><svg xmlns="http://www.w3.org/2000/svg" class="fill-white h-3" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M352 128c0 70.7-57.3 128-128 128s-128-57.3-128-128S153.3 0 224 0s128 57.3 128 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM472 200H616c13.3 0 24 10.7 24 24s-10.7 24-24 24H472c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/></svg></button>
                        </div>
                    </li>
                </template>
            </draggable>
        </ul>

        <div tabindex="0" class="z-40 bg-black bg-opacity-50 fixed top-0 bottom-0 left-0 right-0 w-full h-full overflow-auto scrolling-touch" v-if="popup">
            <div class="z-50 max-w-lg px-3 py-12 relative mx-auto">
                <div class="bg-white rounded shadow relative overflow-visible">
                    <button type="button" @click="closePopup()" class="absolute top-0 right-0 z-10 flex items-center justify-center w-8 h-8 -m-3 text-gray-700 transition ease-in-out bg-gray-100 rounded-full shadow duration-400 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 hover:bg-gray-200 disabled:text-opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>


                    <div class="relative p-3" v-if="action==='delete' && employeeId">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto mb-2 bg-gray-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M352 128c0 70.7-57.3 128-128 128s-128-57.3-128-128S153.3 0 224 0s128 57.3 128 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM472 200H616c13.3 0 24 10.7 24 24s-10.7 24-24 24H472c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/></svg>
                        </div>
                        <div class="flex flex-col justify-center w-full">
                            <div class="">
                                <h3 class="text-lg font-medium leading-6 text-center text-gray-900">Jesteś pewien?</h3>
                            </div>
                            <div class="w-full text-center">
                                <p class="text-sm text-gray-500">Tej czynności nie można cofnąć</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative p-3" v-if="action==='edit' && employeeId">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto mb-2 bg-gray-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" viewBox="0 0 640 512"><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0S96 57.3 96 128s57.3 128 128 128zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H322.8c-3.1-8.8-3.7-18.4-1.4-27.8l15-60.1c2.8-11.3 8.6-21.5 16.8-29.7l40.3-40.3c-32.1-31-75.7-50.1-123.9-50.1H178.3zm435.5-68.3c-15.6-15.6-40.9-15.6-56.6 0l-29.4 29.4 71 71 29.4-29.4c15.6-15.6 15.6-40.9 0-56.6l-14.4-14.4zM375.9 417c-4.1 4.1-7 9.2-8.4 14.9l-15 60.1c-1.4 5.5 .2 11.2 4.2 15.2s9.7 5.6 15.2 4.2l60.1-15c5.6-1.4 10.8-4.3 14.9-8.4L576.1 358.7l-71-71L375.9 417z"/></svg>
                        </div>
                        <div class="flex flex-col justify-center w-full">
                            <div class="">
                                <h3 class="text-lg font-medium leading-6 text-center text-gray-900">Edycja pracownika</h3>
                            </div>
                            <div class="w-full text-center">
                                <div class="flex justify-center">
                                    <div class="my-3 xl:w-96 text-left">
                                        <label for="editName" class="form-label inline-block mb-2 text-gray-700">Imię pracownika:</label>
                                        <input type="text" class="mb-4 form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" v-model="name" id="editName" autocomplete="name" />
                                        <label for="editWorkingTime" class="form-label inline-block mb-2 text-gray-700">Dzienny wymiar godzin pracy:</label>
                                        <input type="number" step="1" min="1" max="24" class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" v-model="workingTime" id="editWorkingTime" />
                                        <template v-for="error in errors">
                                            <p class="text-red-500 my-2 text-sm">{{ error }}</p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center p-3 space-x-4 bg-gray-100 rounded-b">
                        <button type="button" @click="closePopup()" data-mdb-ripple="true" data-mdb-ripple-color="light" class="block w-full max-w-xs px-4 py-2 transition duration-100 ease-in-out bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100 focus:border-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Anuluj">Anuluj</button>
                        <button @click="deleteEmployee()" v-if="action==='delete' && employeeId" type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" class="block w-full max-w-xs px-4 py-2 text-white transition duration-100 ease-in-out bg-blue-500 border border-transparent rounded shadow-sm hover:bg-blue-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Potwierdzam">Potwierdzam</button>
                        <button @click="editEmployee()" v-if="action==='edit' && employeeId" type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" class="block w-full max-w-xs px-4 py-2 text-white transition duration-100 ease-in-out bg-blue-500 border border-transparent rounded shadow-sm hover:bg-blue-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Zapisz">Zapisz</button>
                    </div>

                    <div v-if="loading" class="w-full h-full absolute top-0 left-0 bg-gray-200/70 grid content-center justify-center">
                        <div class="spinner-grow inline-block w-12 h-12 bg-current rounded-full opacity-0" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
import axios from 'axios';
import draggable from 'vuedraggable';

export default {
    name: "Settings",
    components: {
        draggable
    },
    data() {
        return {
            drag: false,
            loading: false,
            popup: false,
            employeeId: null,
            action: null,
            employees: [],
            name: null,
            workingTime: null,
            errors: [],
            holidayYear: new Date().getFullYear(),
            holidayYears: [],
            holidaySyncLoading: false,
            holidaySyncResult: null,
            holidaySyncError: null
        };
    },
    methods: {
        async getHolidayYears() {
            try {
                const response = await axios.get('/holidayYears');
                this.holidayYears = response.data.holidayYears || [];
            } catch (e) {
                this.holidayYears = [];
            }
        },
        getEmployees() {
            const self = this;
            fetch('/users')
                .then(function(response) {
                    return response.json()
                })
                .then(function(data) {
                    self.employees = data.users;
                })
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
                this.name = this.employees.find(({ id }) => id === employeeId).name;
                this.workingTime = this.employees.find(({ id }) => id === employeeId).working_time;
            }
        },
        closePopup() {
            this.popup = false;
            this.employeeId = null;
            this.action = null;
            this.loading = false;
            this.name = null;
        },
        deleteEmployee() {
            if(this.action === 'delete' && this.employeeId) {
                this.loading = true;
                const self = this;
                const data = {
                    employeeId: this.employeeId,
                };
                axios.post(
                    '/deleteUser',
                    data
                ).then(function(response){
                    self.getEmployees();
                    self.closePopup();
                }).catch(function (response) {
                    self.loading = false;
                });
            }
        },
        async editEmployee() {
            if(this.action === 'edit' && this.employeeId) {
                this.loading = true;
                const self = this;
                this.errors = [];
                try {
                    const data = {
                        employeeId: self.employeeId,
                        name: self.name,
                        workingTime: self.workingTime
                    };
                    const response = await axios.post('/editUser', data);
                    if (response.data.errors.length === 0) {
                        self.employees = self.employees.map(employee => {
                            if (employee.id === response.data.user.id) {
                                return {...employee, name: response.data.user.name, working_time: response.data.user.working_time};
                            }
                            return employee;
                        });
                        self.closePopup();
                    } else {
                        this.errors = response.data.errors;
                    }
                } catch (e) {
                    console.log(e);
                }
                this.loading = false;
            }
        },
        changeStatus(employeeId) {
            this.employeeId = employeeId;
            const data = {
                employeeId: this.employeeId,
                is_active: this.employees.find(({ id }) => id === this.employeeId).is_active
            };
            axios.post(
                '/changeStatus',
                data
            ).then(function(response){
                this.employeeId = null;
            }).catch(error => {
                this.employeeId = null;
            });
        },
        endDrag() {
            this.drag = false;
            if (this.drag === false) {
                const self = this;
                const data = {
                    employees: this.employees
                };
                axios.post(
                    '/updateOrder',
                    data
                )
            }
        },
    },
    mounted() {
        this.getEmployees();
        this.getHolidayYears();
    }
}
</script>
<style>
</style>
