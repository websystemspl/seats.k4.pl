<template>
    <div class="relative p-6">

        <!-- Tab switch (admin only) -->
        <div v-if="isAdmin" class="mb-4 flex border-b border-gray-200">
            <button @click="switchTab('my')" :class="activeTab === 'my' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition">
                Moje urlopy
            </button>
            <button @click="switchTab('all')" :class="activeTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition">
                Wszyscy pracownicy
            </button>
        </div>

        <!-- ========== MY VACATIONS TAB ========== -->
        <div v-if="activeTab === 'my'">

            <!-- Entitlement summary -->
            <div v-if="hasEmploymentData" class="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm">
                    <span class="text-gray-600">Wymiar: <span class="font-semibold" :class="entitlement >= 26 ? 'text-green-600' : 'text-blue-600'">{{ entitlement }} dni</span></span>
                    <span class="text-gray-600" v-if="tenure">Staż: <span class="font-semibold">{{ formatTenure(tenure) }}</span></span>
                    <span class="text-gray-400 text-xs">(w tym 4 dni na żądanie)</span>
                </div>
                <div v-if="currentYearSaturdayTotal > 0" class="mt-1 flex flex-wrap gap-x-6 text-sm">
                    <span class="text-gray-600">Soboty do odbioru: <span class="font-semibold text-purple-600">{{ currentYearSaturdayTotal }}</span></span>
                    <span class="text-gray-600">Odebrane: <span class="font-semibold">{{ currentYearSaturdayUsed }}</span></span>
                    <span class="text-gray-600">Pozostałe: <span class="font-semibold" :class="(currentYearSaturdayTotal - currentYearSaturdayUsed) > 0 ? 'text-purple-600' : 'text-gray-400'">{{ currentYearSaturdayTotal - currentYearSaturdayUsed }}</span></span>
                </div>
            </div>
            <div v-else class="mb-4 p-4 bg-yellow-50 rounded border border-yellow-200">
                <p class="text-sm text-yellow-700 mb-2">Brak danych o stażu pracy. Uzupełnij poniższe dane (jednorazowo), aby system wyliczył Twój wymiar urlopu.</p>
                <div class="space-y-2">
                    <div>
                        <label class="text-xs text-gray-600">Data rozpoczęcia pracy w firmie:</label>
                        <input type="date" v-model="selfEmpStartDate" class="mt-0.5 block w-full max-w-xs px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Ukończone wykształcenie:</label>
                        <select v-model="selfEmpEducationYears" class="mt-0.5 block w-full max-w-xs px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none bg-white">
                            <option :value="0">Brak / podstawowe</option>
                            <option :value="3">Zasadnicza zawodowa (3 lata)</option>
                            <option :value="4">Liceum ogólnokształcące (4 lata)</option>
                            <option :value="5">Technikum (5 lat)</option>
                            <option :value="6">Szkoła policealna (6 lat)</option>
                            <option :value="8">Szkoła wyższa (8 lat)</option>
                        </select>
                    </div>
                    <div v-if="selfEmpEducationYears > 0">
                        <label class="text-xs text-gray-600">Data ukończenia nauki:</label>
                        <input type="date" v-model="selfEmpEdCompletedDate" class="mt-0.5 block w-full max-w-xs px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" />
                    </div>
                    <button @click="saveSelfEmployment()" :disabled="!selfEmpStartDate" class="mt-2 px-4 py-1.5 bg-green-600 text-white text-xs font-medium rounded shadow hover:bg-green-700 disabled:opacity-50 transition">Zapisz dane</button>
                    <p v-if="selfEmpError" class="text-xs text-red-500 mt-1">{{ selfEmpError }}</p>
                </div>
            </div>

            <!-- Year summaries -->
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Podsumowanie urlopów</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3" v-if="orderedYears.length">
                    <div v-for="year in orderedYears" :key="year" class="rounded border border-gray-200 bg-gray-50 px-3 py-2">
                        <span class="text-sm text-gray-600">{{ year }}</span>
                        <div class="flex flex-col space-y-0.5 mt-1">
                            <span class="text-xs text-gray-500">Wykorzystane: <span class="font-semibold text-gray-800">{{ vacationDaysByYear[year] || 0 }}</span></span>
                            <span v-if="hasEmploymentData" class="text-xs text-gray-500">Pozostało: <span class="font-semibold" :class="(entitlement - (vacationDaysByYear[year] || 0)) > 0 ? 'text-green-600' : 'text-red-600'">{{ entitlement - (vacationDaysByYear[year] || 0) }}</span></span>
                            <span v-if="carryover[year]" class="text-xs text-orange-600">Przeniesione z {{ carryover[year].from_year }}: <span class="font-semibold">{{ carryover[year].days }}</span> dni</span>
                            <span v-if="holidayDaysByYear[year]" class="text-xs text-purple-600">Soboty: <span class="font-semibold">{{ saturdayReplacements[year] || 0 }}/{{ holidayDaysByYear[year] }}</span> odebranych</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar view -->
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Kalendarz</p>
                    <div class="flex items-center gap-1">
                        <button @click="calendarYear--; buildCalendar()" class="p-1 text-gray-500 hover:text-gray-700">&larr;</button>
                        <span class="text-sm font-medium text-gray-700 w-12 text-center">{{ calendarYear }}</span>
                        <button @click="calendarYear++; buildCalendar()" class="p-1 text-gray-500 hover:text-gray-700">&rarr;</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    <div v-for="month in calendarMonths" :key="month.index" class="border border-gray-200 rounded p-2">
                        <p class="text-xs font-semibold text-gray-600 mb-1 text-center">{{ month.name }}</p>
                        <div class="grid grid-cols-7 gap-px text-center">
                            <span v-for="d in ['Pn','Wt','Śr','Cz','Pt','So','Nd']" :key="d" class="text-[10px] text-gray-400 font-medium">{{ d }}</span>
                            <span v-for="blank in month.startBlank" :key="'b'+blank"></span>
                            <span v-for="day in month.days" :key="day.day"
                                class="text-[11px] leading-5 rounded-sm"
                                :class="dayClass(day)"
                                :title="day.isVacation ? 'Urlop' : (day.isHoliday ? 'Święto' : '')">
                                {{ day.day }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-3 text-[10px] text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-500 inline-block"></span> Urlop</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-purple-500 inline-block"></span> Odbiór za sobotę</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 inline-block"></span> Weekend</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-200 inline-block"></span> Święto</span>
                </div>
            </div>

            <!-- Vacation list -->
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Lista urlopów</p>
            <ul v-auto-animate>
                <li class="p-2 flex justify-between items-center even:bg-gray-50 odd:bg-white" v-for="(vacation, index) in vacations" :key="vacation.id || index">
                    <span>
                        <small class="text-gray-400">{{ (index + 1).toString().padStart(2, '0') }}.</small>
                        {{ formatDate(vacation.start_date) }} - {{ formatDate(vacation.end_date) }}
                        <span v-if="vacation.type === 'saturday_replacement'" class="ml-1 text-[10px] px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded font-medium">za sobotę {{ formatDate(vacation.saturday_holiday_date) }}</span>
                        <span v-if="vacation.carryover_from_year" class="ml-1 text-[10px] px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded font-medium">zaległy z {{ vacation.carryover_from_year }}</span>
                    </span>
                    <div class="flex gap-1">
                        <button class="px-2 py-1 bg-blue-500 text-white text-xs rounded shadow-sm hover:bg-blue-600 transition" @click="getVacationCard(vacation.id)" title="PDF">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-white" viewBox="0 -960 960 960"><path d="M640-640v-120H320v120h-80v-200h480v200h-80Zm-480 80h640-640Zm560 100q17 0 28.5-11.5T760-500q0-17-11.5-28.5T720-540q-17 0-28.5 11.5T680-500q0 17 11.5 28.5T720-460Zm-80 260v-160H320v160h320Zm80 80H240v-160H80v-240q0-51 35-85.5t85-34.5h560q51 0 85.5 34.5T880-520v240H720v160Zm80-240v-160q0-17-11.5-28.5T760-560H200q-17 0-28.5 11.5T160-520v160h80v-80h480v80h80Z"/></svg>
                        </button>
                        <button class="px-2 py-1 bg-red-600 text-white text-xs rounded shadow-sm hover:bg-red-700 transition" @click="deleteVacation(vacation.id)" title="Usuń">
                            <svg class="w-4 h-4 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"><path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/></svg>
                        </button>
                    </div>
                </li>
            </ul>
            <div class="flex justify-center my-3">
                <button class="px-6 py-2 bg-green-500 text-white text-xs font-medium uppercase rounded shadow hover:bg-green-600 transition" @click="openPopup('add')">Dodaj urlop</button>
            </div>
        </div>

        <!-- ========== ALL EMPLOYEES TAB (Admin) ========== -->
        <div v-if="activeTab === 'all' && isAdmin">

            <!-- Year selector -->
            <div class="mb-4 flex items-center gap-3">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Rok:</p>
                <div class="flex items-center gap-1">
                    <button @click="adminYear--; loadAdminData()" class="p-1 text-gray-500 hover:text-gray-700">&larr;</button>
                    <span class="text-sm font-medium text-gray-700 w-12 text-center">{{ adminYear }}</span>
                    <button @click="adminYear++; loadAdminData()" class="p-1 text-gray-500 hover:text-gray-700">&rarr;</button>
                </div>
            </div>

            <!-- Employee filter -->
            <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Filtruj pracowników</p>
                    <label class="flex items-center gap-1 text-[10px] text-gray-400 cursor-pointer">
                        <input type="checkbox" v-model="adminHideInactive" @change="filterAdminVacations" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3 w-3" />
                        Ukryj nieaktywnych
                    </label>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    <label class="text-xs text-gray-500 cursor-pointer flex items-center gap-1 mr-3">
                        <input type="checkbox" :checked="allEmployeesSelected" @change="toggleAllEmployees" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3 w-3" />
                        <span class="font-semibold">Wszyscy</span>
                    </label>
                    <label v-for="user in filterableAdminUsers" :key="user.id" class="text-xs text-gray-600 cursor-pointer flex items-center gap-1">
                        <input type="checkbox" :value="user.id" v-model="selectedUserIds" @change="filterAdminVacations" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-3 w-3" />
                        {{ user.name }}
                    </label>
                </div>
            </div>

            <!-- Entitlements table -->
            <div class="mb-4 overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-600 font-semibold">Pracownik</th>
                            <th class="px-3 py-2 text-center text-gray-600 font-semibold">Staż</th>
                            <th class="px-3 py-2 text-center text-gray-600 font-semibold">Wymiar</th>
                            <th class="px-3 py-2 text-center text-gray-600 font-semibold">Zaległy</th>
                            <th class="px-3 py-2 text-center text-gray-600 font-semibold">Wykorzystane</th>
                            <th class="px-3 py-2 text-center text-gray-600 font-semibold">Pozostało</th>
                            <th class="px-3 py-2 text-center text-purple-600 font-semibold">Soboty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in filteredAdminUsers" :key="user.id" class="border-t border-gray-100 even:bg-gray-50" :class="{ 'opacity-40': !user.is_active }">
                            <td class="px-3 py-1.5 text-gray-700">
                                {{ user.name }}
                                <span v-if="!user.is_active" class="text-[9px] text-red-500 block">nieaktywny</span>
                                <span v-else-if="!adminEntitlements[user.id]?.has_employment_data" class="text-[9px] text-yellow-600 block">brak danych o stażu</span>
                            </td>
                            <td class="px-3 py-1.5 text-center text-gray-500">
                                <template v-if="adminEntitlements[user.id]?.has_employment_data">{{ formatTenure(adminEntitlements[user.id].tenure) }}</template>
                                <span v-else class="text-gray-300">-</span>
                            </td>
                            <td class="px-3 py-1.5 text-center font-semibold" :class="adminEntitlements[user.id]?.total_days >= 26 ? 'text-green-600' : 'text-blue-600'">
                                {{ adminEntitlements[user.id]?.has_employment_data ? adminEntitlements[user.id]?.total_days : '-' }}
                            </td>
                            <td class="px-3 py-1.5 text-center text-orange-600">
                                <template v-if="adminEntitlements[user.id]?.carryover_from_prev !== null && adminEntitlements[user.id]?.carryover_from_prev > 0">{{ adminEntitlements[user.id].carryover_from_prev }}</template>
                                <span v-else-if="adminEntitlements[user.id]?.has_employment_data" class="text-gray-300">0</span>
                                <span v-else class="text-gray-300">-</span>
                            </td>
                            <td class="px-3 py-1.5 text-center text-gray-700">{{ adminEntitlements[user.id]?.used ?? '-' }}</td>
                            <td class="px-3 py-1.5 text-center font-semibold" :class="(adminEntitlements[user.id]?.remaining ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ adminEntitlements[user.id]?.has_employment_data ? (adminEntitlements[user.id]?.remaining ?? '-') : '-' }}
                            </td>
                            <td class="px-3 py-1.5 text-center text-purple-600">
                                {{ adminEntitlements[user.id]?.saturday_used || 0 }}/{{ adminSaturdayHolidayCount }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="mt-1 text-[10px] text-gray-400">Urlop na żądanie: 4 dni z puli przysługującego wymiaru.</p>
            </div>

            <!-- Admin calendar view -->
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Kalendarz {{ adminYear }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    <div v-for="month in adminCalendarMonths" :key="month.index" class="border border-gray-200 rounded p-2">
                        <p class="text-xs font-semibold text-gray-600 mb-1 text-center">{{ month.name }}</p>
                        <div class="grid grid-cols-7 gap-px text-center">
                            <span v-for="d in ['Pn','Wt','Śr','Cz','Pt','So','Nd']" :key="d" class="text-[10px] text-gray-400 font-medium">{{ d }}</span>
                            <span v-for="blank in month.startBlank" :key="'b'+blank"></span>
                            <span v-for="day in month.days" :key="day.day"
                                class="text-[11px] leading-5 rounded-sm"
                                :class="adminDayClass(day)"
                                :title="day.vacationUsers ? day.vacationUsers.join(', ') : (day.isHoliday ? 'Święto' : '')">
                                {{ day.day }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-3 text-[10px] text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-500 inline-block"></span> Urlop</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-300 inline-block"></span> Urlop (kilka osób)</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 inline-block"></span> Weekend</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-200 inline-block"></span> Święto</span>
                </div>
            </div>

            <!-- Admin vacation list -->
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-2">Lista urlopów</p>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-600 font-semibold">Pracownik</th>
                            <th class="px-3 py-2 text-left text-gray-600 font-semibold">Od</th>
                            <th class="px-3 py-2 text-left text-gray-600 font-semibold">Do</th>
                            <th class="px-3 py-2 text-left text-gray-600 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="vac in filteredAdminVacations" :key="vac.id" class="border-t border-gray-100 even:bg-gray-50">
                            <td class="px-3 py-1.5 text-gray-700">{{ getUserName(vac.user_id) }}</td>
                            <td class="px-3 py-1.5 text-gray-600">{{ formatDate(vac.start_date) }}</td>
                            <td class="px-3 py-1.5 text-gray-600">{{ formatDate(vac.end_date) }}</td>
                            <td class="px-3 py-1.5">
                                <span v-if="vac.type === 'saturday_replacement'" class="text-[10px] px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded font-medium">za sobotę {{ formatDate(vac.saturday_holiday_date) }}</span>
                                <span v-if="vac.carryover_from_year" class="text-[10px] px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded font-medium">zaległy z {{ vac.carryover_from_year }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vacation rules info (both user and admin) -->
        <div class="mt-6 border border-gray-200 rounded-lg overflow-hidden">
            <button @click="showRules = !showRules" class="w-full flex items-center justify-between px-4 py-2.5 bg-gray-50 text-xs font-semibold uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">
                Zasady urlopu wypoczynkowego
                <span class="text-gray-400">{{ showRules ? '▲' : '▼' }}</span>
            </button>
            <div v-if="showRules" class="p-4 text-xs text-gray-600 space-y-2 bg-white">
                <p><span class="font-semibold">Wymiar urlopu:</span> 20 dni (staż &lt; 10 lat) lub 26 dni (staż &ge; 10 lat).</p>
                <p><span class="font-semibold">Urlop na żądanie:</span> 4 dni z przysługującej puli (20 lub 26 dni).</p>
                <p><span class="font-semibold">Wykształcenie wyższe a staż:</span> Ukończenie studiów (licencjat lub magister) wlicza się jako 8 lat stażu pracy. Licencjat i magister nie sumują się — liczy się tylko 8 lat z tytułu ukończenia szkoły wyższej.</p>
                <p><span class="font-semibold">Próg 26 dni:</span> Po studiach (8 lat) trzeba przepracować na etacie jeszcze 2 lata, aby uzyskać wymiar 26 dni.</p>
                <p><span class="font-semibold">Pierwsza praca:</span> W roku podjęcia pierwszej pracy urlop nabywa się proporcjonalnie — 1/12 z 20 dni za każdy przepracowany miesiąc (~1,66 dnia).</p>
                <p><span class="font-semibold">Urlop zaległy:</span> Niewykorzystany urlop przenosi się na rok następny i powinien zostać wykorzystany do 30 września.</p>
                <p class="text-gray-400 italic">Źródło: Kodeks Pracy art. 154 §1, art. 155 §1, art. 168 — stan na 2026 r.</p>
            </div>
        </div>

        <!-- Add vacation modal -->
        <div tabindex="0" class="z-40 bg-black bg-opacity-50 fixed top-0 bottom-0 left-0 right-0 w-full h-full overflow-auto" v-if="popup">
            <div class="z-50 max-w-lg px-3 py-12 relative mx-auto">
                <div class="bg-white rounded-lg shadow-lg relative overflow-visible">
                    <button @click="closePopup()" class="absolute top-0 right-0 z-10 flex items-center justify-center w-8 h-8 -m-3 text-gray-700 bg-gray-100 rounded-full shadow hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="relative p-5" v-if="action==='add'">
                        <h3 class="text-lg font-medium text-center text-gray-900 mb-4">Dodawanie urlopu</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-gray-700">Typ:</label>
                                <div class="mt-1 flex gap-4">
                                    <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                        <input type="radio" v-model="vacationType" value="vacation" class="text-blue-600 focus:ring-blue-500" />
                                        Urlop wypoczynkowy
                                    </label>
                                    <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                        <input type="radio" v-model="vacationType" value="saturday_replacement" class="text-purple-600 focus:ring-purple-500" />
                                        Odbiór za sobotę
                                    </label>
                                </div>
                            </div>
                            <div v-if="vacationType === 'saturday_replacement'">
                                <label for="saturday_holiday" class="text-sm text-gray-700">Święto w sobotę:</label>
                                <select class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none bg-white" v-model="saturdayHolidayDate" id="saturday_holiday">
                                    <option value="">-- wybierz święto --</option>
                                    <option v-for="h in availableSaturdayHolidays" :key="h" :value="h">{{ formatDate(h) }}</option>
                                </select>
                                <p class="mt-1 text-[10px] text-gray-400">Wybierz dzień wolny — data początku i końca muszą być tym samym dniem.</p>
                            </div>
                            <div>
                                <label for="request_date" class="text-sm text-gray-700">Data wniosku:</label>
                                <input type="date" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="requestDate" id="request_date" />
                            </div>
                            <div>
                                <label for="start_date" class="text-sm text-gray-700">{{ vacationType === 'saturday_replacement' ? 'Dzień odbioru:' : 'Data początku urlopu:' }}</label>
                                <input type="date" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="startDate" id="start_date" />
                            </div>
                            <div v-if="vacationType !== 'saturday_replacement'">
                                <label for="end_date" class="text-sm text-gray-700">Data końca urlopu:</label>
                                <input type="date" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="endDate" id="end_date" />
                            </div>
                            <div>
                                <label for="working_time" class="text-sm text-gray-700">Dzienny wymiar godzin pracy:</label>
                                <input type="number" step="1" min="1" max="24" class="mt-1 block w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-blue-500 focus:outline-none" v-model="workingTime" id="working_time" />
                            </div>
                            <p v-for="error in errors" class="text-red-500 text-sm">{{ error }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center p-3 space-x-4 bg-gray-100 rounded-b-lg">
                        <button @click="closePopup()" class="block w-full max-w-xs px-4 py-2 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-100 text-sm">Anuluj</button>
                        <button v-if="action==='add'" @click="addVacation()" class="block w-full max-w-xs px-4 py-2 text-white bg-blue-500 rounded shadow-sm hover:bg-blue-600 text-sm">Dodaj</button>
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
import moment from 'moment';

const MONTH_NAMES = ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'];

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
            requestDate: null,
            startDate: null,
            endDate: null,
            tenure: null,
            entitlement: null,
            hasEmploymentData: false,
            isAdmin: false,
            activeTab: 'my',
            calendarYear: new Date().getFullYear(),
            calendarMonths: [],
            calendarHolidays: [],
            carryover: {},
            showRules: false,
            saturdayReplacements: {},
            vacationType: 'vacation',
            saturdayHolidayDate: '',
            availableSaturdayHolidays: [],
            selfEmpStartDate: null,
            selfEmpEducationYears: 0,
            selfEmpEdCompletedDate: null,
            selfEmpError: '',

            adminYear: new Date().getFullYear(),
            adminUsers: [],
            adminVacations: [],
            adminHolidays: [],
            adminEntitlements: {},
            adminCalendarMonths: [],
            selectedUserIds: [],
            adminHideInactive: true,
            adminSaturdayHolidayCount: 0,
            adminSaturdayHolidays: [],
        };
    },
    computed: {
        orderedYears() {
            const years = new Set([
                ...Object.keys(this.vacationDaysByYear),
                ...Object.keys(this.holidayDaysByYear)
            ]);
            return Array.from(years).map(Number).sort((a, b) => b - a);
        },
        currentYearSaturdayTotal() {
            const year = new Date().getFullYear();
            return this.holidayDaysByYear[year] || 0;
        },
        currentYearSaturdayUsed() {
            const year = new Date().getFullYear();
            return this.saturdayReplacements[year] || 0;
        },
        filterableAdminUsers() {
            if (this.adminHideInactive) {
                return this.adminUsers.filter(u => u.is_active);
            }
            return this.adminUsers;
        },
        allEmployeesSelected() {
            return this.filterableAdminUsers.length > 0 && this.selectedUserIds.length === this.filterableAdminUsers.length;
        },
        filteredAdminUsers() {
            return this.filterableAdminUsers.filter(u => this.selectedUserIds.includes(u.id));
        },
        filteredAdminVacations() {
            return this.adminVacations.filter(v => this.selectedUserIds.includes(v.user_id));
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
        formatTenure(years) {
            if (years === null || years === undefined) return '-';
            const y = Math.floor(years);
            const m = Math.round((years - y) * 12);
            let parts = [];
            if (y > 0) parts.push(y + ' ' + this.pluralYears(y));
            if (m > 0) parts.push(m + ' mies.');
            return parts.join(' ') || '< 1 mies.';
        },
        getUserName(userId) {
            const user = this.adminUsers.find(u => u.id === userId);
            return user ? user.name : 'Pracownik #' + userId;
        },
        switchTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab === 'all' ? '#all' : '#my';
            if (tab === 'all') this.loadAdminData();
        },
        toggleAllEmployees() {
            if (this.allEmployeesSelected) {
                this.selectedUserIds = [];
            } else {
                this.selectedUserIds = this.filterableAdminUsers.map(u => u.id);
            }
            this.filterAdminVacations();
        },
        filterAdminVacations() {
            this.buildAdminCalendar();
        },

        // Calendar building
        buildCalendar() {
            const year = this.calendarYear;
            const vacDates = new Set();
            const satDates = new Set();
            this.vacations.forEach(v => {
                const start = new Date(v.start_date);
                const end = new Date(v.end_date);
                const isSat = v.type === 'saturday_replacement';
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    if (d.getFullYear() === year) {
                        const dateStr = d.toISOString().slice(0, 10);
                        if (isSat) { satDates.add(dateStr); }
                        else { vacDates.add(dateStr); }
                    }
                }
            });

            const holidaySet = new Set(this.calendarHolidays);

            this.calendarMonths = [];
            for (let m = 0; m < 12; m++) {
                const firstDay = new Date(year, m, 1);
                const daysInMonth = new Date(year, m + 1, 0).getDate();
                let startBlank = (firstDay.getDay() + 6) % 7;
                const days = [];
                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(year, m, d);
                    const dateStr = date.toISOString().slice(0, 10);
                    const dow = date.getDay();
                    days.push({
                        day: d,
                        isWeekend: dow === 0 || dow === 6,
                        isHoliday: holidaySet.has(dateStr),
                        isVacation: vacDates.has(dateStr),
                        isSaturdayReplacement: satDates.has(dateStr),
                    });
                }
                this.calendarMonths.push({
                    index: m,
                    name: MONTH_NAMES[m],
                    startBlank,
                    days,
                });
            }
        },
        dayClass(day) {
            if (day.isSaturdayReplacement) return 'bg-purple-500 text-white font-semibold';
            if (day.isVacation) return 'bg-blue-500 text-white font-semibold';
            if (day.isHoliday) return 'bg-red-200 text-red-700';
            if (day.isWeekend) return 'bg-gray-200 text-gray-400';
            return 'text-gray-700';
        },

        buildAdminCalendar() {
            const year = this.adminYear;
            const selectedIds = this.selectedUserIds;
            const vacByDate = {};
            this.adminVacations.forEach(v => {
                if (!selectedIds.includes(v.user_id)) return;
                const start = new Date(v.start_date);
                const end = new Date(v.end_date);
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    if (d.getFullYear() === year) {
                        const dateStr = d.toISOString().slice(0, 10);
                        if (!vacByDate[dateStr]) vacByDate[dateStr] = [];
                        const userName = this.getUserName(v.user_id);
                        if (!vacByDate[dateStr].includes(userName)) {
                            vacByDate[dateStr].push(userName);
                        }
                    }
                }
            });

            const holidaySet = new Set(this.adminHolidays);

            this.adminCalendarMonths = [];
            for (let m = 0; m < 12; m++) {
                const firstDay = new Date(year, m, 1);
                const daysInMonth = new Date(year, m + 1, 0).getDate();
                let startBlank = (firstDay.getDay() + 6) % 7;
                const days = [];
                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(year, m, d);
                    const dateStr = date.toISOString().slice(0, 10);
                    const dow = date.getDay();
                    const vacUsers = vacByDate[dateStr] || null;
                    days.push({
                        day: d,
                        isWeekend: dow === 0 || dow === 6,
                        isHoliday: holidaySet.has(dateStr),
                        isVacation: !!vacUsers,
                        vacationCount: vacUsers ? vacUsers.length : 0,
                        vacationUsers: vacUsers,
                    });
                }
                this.adminCalendarMonths.push({
                    index: m,
                    name: MONTH_NAMES[m],
                    startBlank,
                    days,
                });
            }
        },
        adminDayClass(day) {
            if (day.isVacation && day.vacationCount > 1) return 'bg-blue-300 text-white font-semibold';
            if (day.isVacation) return 'bg-blue-500 text-white font-semibold';
            if (day.isHoliday) return 'bg-red-200 text-red-700';
            if (day.isWeekend) return 'bg-gray-200 text-gray-400';
            return 'text-gray-700';
        },

        // Data loading
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
                this.tenure = response.data.tenure;
                this.entitlement = response.data.entitlement;
                this.hasEmploymentData = response.data.hasEmploymentData || false;
                this.isAdmin = response.data.isAdmin || false;
                this.carryover = response.data.carryover || {};
                this.saturdayReplacements = response.data.saturdayReplacements || {};
                this.loadCalendarHolidays();
                this.loadSaturdayHolidays();
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },
        async loadCalendarHolidays() {
            try {
                const response = await axios.post('/holidayYears/sync', { year: this.calendarYear });
                if (response.data.holidayYear?.holidays) {
                    this.calendarHolidays = response.data.holidayYear.holidays;
                }
            } catch (e) {
                this.calendarHolidays = [];
            }
            this.buildCalendar();
        },
        async loadSaturdayHolidays() {
            try {
                const year = new Date().getFullYear();
                const response = await axios.get('/getSaturdayHolidays', { params: { year } });
                this.availableSaturdayHolidays = response.data.saturdayHolidays || [];
            } catch (e) {
                this.availableSaturdayHolidays = [];
            }
        },
        async loadAdminData() {
            if (!this.isAdmin) return;
            try {
                const response = await axios.get('/getAdminVacations', { params: { year: this.adminYear } });
                this.adminUsers = response.data.users || [];
                this.adminVacations = response.data.vacations || [];
                this.adminHolidays = response.data.holidays || [];
                this.adminEntitlements = response.data.entitlements || {};
                this.adminSaturdayHolidayCount = response.data.saturdayHolidayCount || 0;
                this.adminSaturdayHolidays = response.data.saturdayHolidays || [];
                if (this.selectedUserIds.length === 0) {
                    this.selectedUserIds = this.adminUsers.filter(u => u.is_active).map(u => u.id);
                }
                this.buildAdminCalendar();
            } catch (e) {
                console.error(e);
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
            this.vacationType = 'vacation';
            this.saturdayHolidayDate = '';
        },
        async addVacation() {
            this.loading = true;
            const isSatReplacement = this.vacationType === 'saturday_replacement';
            try {
                const response = await axios.post('/addVacation', {
                    requestDate: this.requestDate,
                    startDate: this.startDate,
                    endDate: isSatReplacement ? this.startDate : this.endDate,
                    workingTime: this.workingTime,
                    type: this.vacationType,
                    saturdayHolidayDate: isSatReplacement ? this.saturdayHolidayDate : null,
                });
                if (response.data.errors.length === 0) {
                    this.vacations = response.data.vacations;
                    this.vacationDaysByYear = response.data.vacationDaysByYear || this.vacationDaysByYear;
                    this.vacationDaysByYearLoaded = true;
                    if (response.data.holidayDaysByYear) {
                        this.holidayDaysByYear = response.data.holidayDaysByYear;
                    }
                    if (response.data.saturdayReplacements) {
                        this.saturdayReplacements = response.data.saturdayReplacements;
                    }
                    this.closePopup();
                    this.buildCalendar();
                } else {
                    this.errors = response.data.errors;
                }
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },
        async deleteVacation(id) {
            this.loading = true;
            try {
                const response = await axios.post('/deleteVacation', { vacationId: id });
                if (response.data) {
                    this.vacations = response.data.vacations;
                    this.vacationDaysByYear = response.data.vacationDaysByYear || this.vacationDaysByYear;
                    if (response.data.holidayDaysByYear) {
                        this.holidayDaysByYear = response.data.holidayDaysByYear;
                    }
                    if (response.data.saturdayReplacements) {
                        this.saturdayReplacements = response.data.saturdayReplacements;
                    }
                    this.buildCalendar();
                }
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },
        async saveSelfEmployment() {
            this.selfEmpError = '';
            try {
                const response = await axios.post('/selfUpdateEmployment', {
                    employment_start_date: this.selfEmpStartDate,
                    education_years: parseInt(this.selfEmpEducationYears) || 0,
                    education_completed_date: this.selfEmpEducationYears > 0 ? this.selfEmpEdCompletedDate : null,
                });
                this.hasEmploymentData = true;
                this.tenure = response.data.user.tenure_years;
                this.entitlement = response.data.user.vacation_entitlement;
            } catch (e) {
                this.selfEmpError = e.response?.data?.error || 'Błąd zapisu danych.';
            }
        },
        getVacationCard(id) {
            window.open('/getVacationCard/' + id, '_blank');
        }
    },
    mounted() {
        const el = document.getElementById('vacations');
        if (el && el.dataset.isAdmin === '1') {
            this.isAdmin = true;
        }
        const hash = window.location.hash;
        if (hash === '#all' && this.isAdmin) {
            this.activeTab = 'all';
        }
        this.getVacations();
        if (this.activeTab === 'all') {
            this.loadAdminData();
        }
        this.requestDate = moment().format('YYYY-MM-DD');
    }
}
</script>
