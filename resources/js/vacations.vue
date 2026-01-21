<template>
    <div class="relative p-6">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Podsumowanie urlopów</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3" v-if="orderedYears.length">
                <div v-for="year in orderedYears" :key="year" class="rounded border border-gray-200 bg-gray-50 px-3 py-2">
                    <div class="flex flex-col space-y-1">
                        <span class="text-sm text-gray-600">{{ year }}</span>
                        <span class="text-xs text-gray-500">Wykorzystane: <span class="font-semibold text-gray-800">{{ vacationDaysByYear[year] }}</span></span>
                        <span class="text-xs text-gray-500">Do odbioru: <span v-if="holidayDaysByYear[year] !== null" class="font-semibold text-gray-800">{{ holidayDaysByYear[year] }}</span><span v-else class="text-gray-400">brak</span></span>
                    </div>
                </div>
            </div>
            <p v-else-if="vacationDaysByYearLoaded" class="mt-3 text-sm text-gray-500">Brak danych.</p>
        </div>
        <ul v-auto-animate>
            <li class="p-2 flex justify-between items-center even:bg-gray-50 odd:bg-white" v-for="(vacation, index) in vacations" :key="index">
                <span><small>{{ (index + 1).toString().padStart(2, '0') }}.</small> {{ formatDate(vacation.start_date) }} - {{ formatDate(vacation.end_date) }}</span>
                <div class="">
                    <button data-mdb-ripple="true" data-mdb-ripple-color="light" class="inline-block px-3 py-1.5 bg-blue-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-600 hover:shadow-lg focus:bg-blue-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-700 active:shadow-lg transition duration-150 ease-in-out" @click="getVacationCard(vacation.id)"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-white" viewBox="0 -960 960 960" width="24"><path d="M640-640v-120H320v120h-80v-200h480v200h-80Zm-480 80h640-640Zm560 100q17 0 28.5-11.5T760-500q0-17-11.5-28.5T720-540q-17 0-28.5 11.5T680-500q0 17 11.5 28.5T720-460Zm-80 260v-160H320v160h320Zm80 80H240v-160H80v-240q0-51 35-85.5t85-34.5h560q51 0 85.5 34.5T880-520v240H720v160Zm80-240v-160q0-17-11.5-28.5T760-560H200q-17 0-28.5 11.5T160-520v160h80v-80h480v80h80Z"/></svg></button>
                    <button type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" @click="deleteVacation(vacation.id)" class="ml-2 inline-block px-3 py-1.5 bg-red-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-lg transition duration-150 ease-in-out"><svg class="w-4 h-4 fill-white" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg></button>
                </div>
            </li>
        </ul>
        <div class="flex justify-center my-3">
            <button data-mdb-ripple="true" data-mdb-ripple-color="light" class="inline-block px-6 py-2.5 bg-green-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-green-600 hover:shadow-lg focus:bg-green-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-700 active:shadow-lg transition duration-150 ease-in-out" @click="openPopup('add')">Dodaj urlop</button>
        </div>

        <div tabindex="0" class="z-40 bg-black bg-opacity-50 fixed top-0 bottom-0 left-0 right-0 w-full h-full overflow-auto scrolling-touch" v-if="popup">
            <div class="z-50 max-w-lg px-3 py-12 relative mx-auto">
                <div class="bg-white rounded shadow relative overflow-visible">
                    <button type="button" @click="closePopup()" class="absolute top-0 right-0 z-10 flex items-center justify-center w-8 h-8 -m-3 text-gray-700 transition ease-in-out bg-gray-100 rounded-full shadow duration-400 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 hover:bg-gray-200 disabled:text-opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="relative p-3" v-if="action==='add'">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto mb-2 bg-gray-100 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m23,10c.553,0,1-.447,1-1v-1c0-2.757-2.243-5-5-5H6v2c0,.553-.447,1-1,1s-1-.447-1-1v-1.899C1.721,3.566,0,5.586,0,8v8c0,2.414,1.721,4.434,4,4.899v-1.899c0-.553.447-1,1-1s1,.447,1,1v2h13c2.757,0,5-2.243,5-5v-1c0-.553-.447-1-1-1-1.103,0-2-.897-2-2s.897-2,2-2ZM6,15c0,.553-.447,1-1,1s-1-.447-1-1v-1c0-.553.447-1,1-1s1,.447,1,1v1Zm0-5c0,.553-.447,1-1,1s-1-.447-1-1v-1c0-.553.447-1,1-1s1,.447,1,1v1Zm11,3h-1.5l-1.911,2.421c-.195.357-.569.579-.975.579h-.32c-.382,0-.644-.384-.506-.74l.879-2.26h-1.32c-.442,0-.862-.195-1.147-.533l-1.096-1.3c-.297-.52.078-1.167.677-1.167.207,0,.405.082.551.228l1.11.772h1.241l-.891-2.258c-.141-.356.122-.742.505-.742h.298c.407,0,.781.222.975.579l1.929,2.421h1.5c.552,0,1,.448,1,1s-.448,1-1,1Z"/></svg>
                        </div>
                        <div class="flex flex-col justify-center w-full">
                            <div class="">
                                <h3 class="text-lg font-medium leading-6 text-center text-gray-900">Dodawanie urlopu</h3>
                            </div>
                            <div class="w-full text-center">
                                <div class="flex justify-center">
                                    <div class="my-3 xl:w-96 text-left">
                                        <label for="request_date" class="form-label inline-block mb-2 text-gray-700">Data wniosku:</label>
                                        <input type="date" class="mb-4 form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" v-model="requestDate" id="request_date" autocomplete="off" />
                                        <label for="start_date" class="form-label inline-block mb-2 text-gray-700">Data początku urlopu:</label>
                                        <input type="date" class="mb-4 form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" id="start_date" autocomplete="off" />
                                        <label for="end_date" class="form-label inline-block mb-2 text-gray-700">Data końca urlopu:</label>
                                        <input type="date" class="mb-4 form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" id="end_date" autocomplete="off" />
                                        <label for="working_time" class="form-label inline-block mb-2 text-gray-700">Dzienny wymiar godzin pracy:</label>
                                        <input type="number" step="1" min="1" max="24" class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none border-gray-300" v-model="workingTime" id="working_time" />
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
                        <button @click="addVacation()" v-if="action==='add'" type="button" data-mdb-ripple="true" data-mdb-ripple-color="light" class="block w-full max-w-xs px-4 py-2 text-white transition duration-100 ease-in-out bg-blue-500 border border-transparent rounded shadow-sm hover:bg-blue-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Dodaj">Dodaj</button>
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
import moment from 'moment';

export default {
    name: "Vacations",
    data() {
        return {
            loading: false,
            popup: false,
            action: null,
            workingTime: null,
            vacations: [],
            vacationDaysByYear: {},
            vacationDaysByYearLoaded: false,
            holidayDaysByYear: {},
            holidayDaysByYearLoaded: false,
            errors: [],
            requestDate: null
        };
    },
    computed: {
        orderedYears() {
            const years = new Set([
                ...Object.keys(this.vacationDaysByYear),
                ...Object.keys(this.holidayDaysByYear)
            ]);
            return Array.from(years)
                .map(Number)
                .sort((a, b) => b - a);
        }
    },
    methods: {
        async getVacations() {
            this.loading = true;
            try {
                const response = await axios.get('/getUserVacations');
                this.vacations = response.data.vacations;
                this.workingTime = response.data.workingTime;
                this.vacationDaysByYear = response.data.vacationDaysByYear || {};
                this.vacationDaysByYearLoaded = true;
                this.holidayDaysByYear = response.data.holidayDaysByYear || {};
                this.holidayDaysByYearLoaded = true;
            } catch (e) {
                console.log(e);
            }
            this.loading = false;
        },
        formatDate(date){
         if (date) {
           return moment(String(date)).format('DD.MM.YYYY')
          }
        },
        openPopup(action) {
            this.action = action;
            this.popup = true;
        },
        closePopup() {
            this.popup = false;
            this.action = null;
            this.errors = [];
        },
        async addVacation() {
            this.loading = true;
            try {
                const response = await axios.post('/addVacation', {
                    requestDate: document.getElementById('request_date').value,
                    startDate: document.getElementById('start_date').value,
                    endDate: document.getElementById('end_date').value,
                    workingTime: this.workingTime
                });
                if (response.data.errors.length === 0) {
                    this.vacations = response.data.vacations;
                    this.vacationDaysByYear = response.data.vacationDaysByYear || this.vacationDaysByYear;
                    this.vacationDaysByYearLoaded = true;
                    if (response.data.holidayDaysByYear) {
                        this.holidayDaysByYear = response.data.holidayDaysByYear;
                        this.holidayDaysByYearLoaded = true;
                    }
                    this.closePopup();
                } else {
                    this.errors = response.data.errors;
                }
            } catch (e) {
                console.log(e);
            }
            this.loading = false;
        },
        async deleteVacation(id) {
            this.loading = true;
            try {
                const response = await axios.post('/deleteVacation', {
                    vacationId: id
                });
                if (response.data) {
                    this.vacations = response.data.vacations;
                    this.vacationDaysByYear = response.data.vacationDaysByYear || this.vacationDaysByYear;
                    this.vacationDaysByYearLoaded = true;
                    if (response.data.holidayDaysByYear) {
                        this.holidayDaysByYear = response.data.holidayDaysByYear;
                        this.holidayDaysByYearLoaded = true;
                    }
                } else {
                    this.errors = response.data.errors;
                }
            } catch (e) {
                console.log(e);
            }
            this.loading = false;
        },
        getVacationCard(id) {
            window.open('/getVacationCard/' + id, '_blank');
        }
    },
    mounted() {
        this.getVacations();
        this.requestDate = moment().format('YYYY-MM-DD');
    }
}
</script>
