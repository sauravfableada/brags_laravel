<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100 dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRAGS API Documentation - Admin, Seller & Customer APIs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Fira Code"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #090d16; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 8px 32px -8px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-200 selection:bg-indigo-500 selection:text-white flex flex-col">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 glass-panel border-b border-slate-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-indigo-500/20">
                B
            </div>
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    BRAGS API Documentation
                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 font-semibold border border-indigo-500/20">v1.0 API</span>
                </h1>
                <p class="text-xs text-slate-400">RESTful API documentation for Admin, Seller (Vendor), and Customer Roles</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900/80 border border-slate-800 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Base URL: <code class="text-indigo-300 font-mono" id="baseUrlDisplay">{{ $baseUrl }}</code>
            </div>
            <button onclick="copyBaseUrl()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Copy URL
            </button>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="w-72 border-r border-slate-800/80 bg-slate-950/80 hidden lg:flex flex-col flex-shrink-0">
            <!-- Search in Sidebar -->
            <div class="p-4 border-b border-slate-800/80">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="sidebarSearch" placeholder="Search API endpoints..." class="w-full bg-slate-900 border border-slate-800 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Quick Navigation Groups -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6" id="sidebarMenu">
                <!-- Javascript will populate quick links here -->
            </div>

            <!-- Footer Stats -->
            <div class="p-4 border-t border-slate-800/80 text-xs text-slate-500 flex justify-between items-center">
                <span>Total Endpoints: <strong id="totalCount" class="text-indigo-400 font-semibold">0</strong></span>
                <span>Active Filters: <strong id="activeFilterCount" class="text-slate-300">0</strong></span>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto bg-slate-950/50">
            
            <!-- Filters & Search Bar Section -->
            <div class="sticky top-0 z-40 bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80 px-4 lg:px-8 py-4 space-y-4">
                <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full max-w-xl">
                        <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" id="searchInput" oninput="renderEndpoints()" placeholder="Filter by endpoint path, description, parameters..." class="w-full bg-slate-900/90 border border-slate-800 rounded-xl pl-10 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <button onclick="clearSearch()" id="clearSearchBtn" class="hidden absolute right-3 top-3 text-slate-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Status Code Quick Legend -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-medium text-slate-400">
                        <span class="mr-1 text-slate-500">Status Codes:</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">200 OK</span>
                        <span class="px-2 py-0.5 rounded bg-teal-500/10 text-teal-400 border border-teal-500/20">201 Created</span>
                        <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">400 Bad Req</span>
                        <span class="px-2 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20">401 Auth</span>
                        <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">422 Validation</span>
                        <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-400 border border-red-500/20">500 Server</span>
                    </div>
                </div>

                <!-- Filter Tabs: User Role & HTTP Method -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <!-- User Role Filter Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 bg-slate-900/80 p-1 rounded-xl border border-slate-800" id="roleFilters">
                        <button onclick="setRoleFilter('ALL')" data-role="ALL" class="role-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition bg-indigo-600 text-white shadow-sm">
                            All Roles
                        </button>
                        <button onclick="setRoleFilter('Admin')" data-role="Admin" class="role-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
                            🛡️ Admin <span class="ml-1 text-[10px] opacity-75" id="count-Admin">0</span>
                        </button>
                        <button onclick="setRoleFilter('Seller')" data-role="Seller" class="role-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
                            🏬 Seller (Vendor) <span class="ml-1 text-[10px] opacity-75" id="count-Seller">0</span>
                        </button>
                        <button onclick="setRoleFilter('Customer')" data-role="Customer" class="role-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
                            👤 Customer <span class="ml-1 text-[10px] opacity-75" id="count-Customer">0</span>
                        </button>
                        <button onclick="setRoleFilter('General')" data-role="General" class="role-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 transition">
                            🌐 General / Auth <span class="ml-1 text-[10px] opacity-75" id="count-General">0</span>
                        </button>
                    </div>

                    <!-- Method Filter Pills -->
                    <div class="flex items-center gap-1.5 bg-slate-900/80 p-1 rounded-xl border border-slate-800" id="methodFilters">
                        <button onclick="setMethodFilter('ALL')" data-method="ALL" class="method-btn px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-800 text-white">All</button>
                        <button onclick="setMethodFilter('GET')" data-method="GET" class="method-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-400 hover:bg-emerald-500/10">GET</button>
                        <button onclick="setMethodFilter('POST')" data-method="POST" class="method-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold text-sky-400 hover:bg-sky-500/10">POST</button>
                        <button onclick="setMethodFilter('PUT')" data-method="PUT" class="method-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold text-amber-400 hover:bg-amber-500/10">PUT</button>
                        <button onclick="setMethodFilter('DELETE')" data-method="DELETE" class="method-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-400 hover:bg-rose-500/10">DELETE</button>
                    </div>
                </div>
            </div>

            <!-- Endpoint List Rendering Container -->
            <div class="p-4 lg:p-8 space-y-6 flex-1" id="endpointContainer">
                <!-- JavaScript renders endpoint cards here -->
            </div>

        </main>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-indigo-600 text-white px-4 py-2.5 rounded-xl shadow-2xl flex items-center gap-2 border border-indigo-400/30 text-xs font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span id="toastMessage">Copied to clipboard!</span>
    </div>

    <!-- API Dataset & Logic Script -->
    <script>
        const API_DATA = @json($apiData);
        const BASE_URL = @json($baseUrl);


        // State Management
        let currentRole = 'ALL';
        let currentMethod = 'ALL';

        // DOM Load
        document.addEventListener('DOMContentLoaded', () => {
            calculateCounts();
            renderEndpoints();
            renderSidebar();
        });

        function calculateCounts() {
            const counts = { Admin: 0, Seller: 0, Customer: 0, General: 0 };
            API_DATA.forEach(api => {
                if (counts[api.role] !== undefined) counts[api.role]++;
            });
            document.getElementById('count-Admin').innerText = counts.Admin;
            document.getElementById('count-Seller').innerText = counts.Seller;
            document.getElementById('count-Customer').innerText = counts.Customer;
            document.getElementById('count-General').innerText = counts.General;
            document.getElementById('totalCount').innerText = API_DATA.length;
        }

        function setRoleFilter(role) {
            currentRole = role;
            document.querySelectorAll('.role-btn').forEach(btn => {
                const isSelected = btn.getAttribute('data-role') === role;
                btn.className = `role-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                    isSelected 
                    ? 'bg-indigo-600 text-white shadow-sm ring-1 ring-indigo-400/50' 
                    : 'text-slate-400 hover:text-slate-200'
                }`;
            });
            renderEndpoints();
            renderSidebar();
        }

        function setMethodFilter(method) {
            currentMethod = method;
            document.querySelectorAll('.method-btn').forEach(btn => {
                const isSelected = btn.getAttribute('data-method') === method;
                btn.className = `method-btn px-2.5 py-1.5 rounded-lg text-xs font-semibold transition ${
                    isSelected ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800'
                }`;
            });
            renderEndpoints();
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearchBtn').classList.add('hidden');
            renderEndpoints();
        }

        function filterData() {
            const searchQuery = (document.getElementById('searchInput').value || '').toLowerCase().trim();
            document.getElementById('clearSearchBtn').classList.toggle('hidden', !searchQuery);

            return API_DATA.filter(item => {
                const matchRole = (currentRole === 'ALL' || item.role === currentRole);
                const matchMethod = (currentMethod === 'ALL' || item.method === currentMethod);
                
                const matchSearch = !searchQuery || 
                    item.title.toLowerCase().includes(searchQuery) ||
                    item.path.toLowerCase().includes(searchQuery) ||
                    item.description.toLowerCase().includes(searchQuery) ||
                    item.role.toLowerCase().includes(searchQuery);

                return matchRole && matchMethod && matchSearch;
            });
        }

        function renderEndpoints() {
            const filtered = filterData();
            const container = document.getElementById('endpointContainer');
            document.getElementById('activeFilterCount').innerText = filtered.length;

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16 px-4 glass-card rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-slate-800/80 mx-auto flex items-center justify-center text-slate-500 mb-3">
                            🔍
                        </div>
                        <h3 class="text-base font-semibold text-slate-200">No matching API endpoints found</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Try clearing search keywords or switching your filter tabs.</p>
                        <button onclick="setRoleFilter('ALL'); setMethodFilter('ALL'); clearSearch();" class="mt-4 px-4 py-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/30 rounded-xl text-xs font-medium border border-indigo-500/30 transition">
                            Reset All Filters
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(item => createEndpointCardHtml(item)).join('');
        }

        function getMethodBadgeStyle(method) {
            switch(method) {
                case 'GET': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                case 'POST': return 'bg-sky-500/10 text-sky-400 border-sky-500/30';
                case 'PUT': return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                case 'DELETE': return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                default: return 'bg-slate-800 text-slate-300';
            }
        }

        function getRoleTagStyle(role) {
            switch(role) {
                case 'Admin': return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
                case 'Seller': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                case 'Customer': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                default: return 'bg-slate-800 text-slate-400 border-slate-700';
            }
        }

        function createEndpointCardHtml(item) {
            const methodBadgeClass = getMethodBadgeStyle(item.method);
            const roleTagClass = getRoleTagStyle(item.role);

            // Generate status code tabs HTML
            const statusCodes = Object.keys(item.responses);
            const defaultStatusCode = statusCodes[0];

            return `
                <div id="${item.id}" class="glass-card rounded-2xl p-5 border transition-all duration-200">
                    <!-- Endpoint Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-800/80">
                        <div class="flex items-center flex-wrap gap-2.5">
                            <span class="px-3 py-1 rounded-lg text-xs font-mono font-bold border ${methodBadgeClass}">
                                ${item.method}
                            </span>
                            <code class="text-sm font-mono font-semibold text-slate-100 bg-slate-900/90 px-3 py-1 rounded-lg border border-slate-800">
                                ${item.path}
                            </code>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium border ${roleTagClass}">
                                ${item.role}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-slate-400 border border-slate-800 flex items-center gap-1 font-mono">
                                <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                ${item.auth}
                            </span>
                            <button onclick="copyToClipboard('${item.path}', 'Endpoint URL copied!')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 font-medium transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                Copy URL
                            </button>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <div class="mt-3.5">
                        <h2 class="text-base font-bold text-white tracking-tight">${item.title}</h2>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">${item.description}</p>
                    </div>

                    <!-- Request & Response Grid -->
                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                        
                        <!-- Left Column: Request Details -->
                        <div class="space-y-4">
                            
                            <!-- Request Headers -->
                            <div>
                                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                    <span>Headers</span>
                                    <span class="text-[10px] text-slate-500 font-normal">HTTP Headers</span>
                                </h3>
                                <div class="bg-slate-900/90 rounded-xl p-3 border border-slate-800/80 font-mono text-xs space-y-1.5">
                                    ${Object.entries(item.headers).map(([k, v]) => `
                                        <div class="flex justify-between items-center text-slate-300">
                                            <span class="text-indigo-400 font-semibold">${k}:</span>
                                            <span class="text-slate-400 truncate max-w-[200px]">${v}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>

                            <!-- Parameters Table (If any) -->
                            ${item.params && item.params.length > 0 ? `
                                <div>
                                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Request Body / Parameters</h3>
                                    <div class="overflow-x-auto rounded-xl border border-slate-800/80">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-slate-900 text-slate-400 font-semibold border-b border-slate-800">
                                                <tr>
                                                    <th class="p-2.5">Field</th>
                                                    <th class="p-2.5">Type</th>
                                                    <th class="p-2.5">Status</th>
                                                    <th class="p-2.5">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                                                ${item.params.map(p => `
                                                    <tr>
                                                        <td class="p-2.5 font-mono text-indigo-300 font-semibold">${p.name}</td>
                                                        <td class="p-2.5 font-mono text-slate-400 text-[11px]">${p.type}</td>
                                                        <td class="p-2.5">
                                                            ${p.required 
                                                                ? '<span class="text-[10px] font-bold text-rose-400 bg-rose-500/10 px-1.5 py-0.5 rounded border border-rose-500/20">Required</span>' 
                                                                : '<span class="text-[10px] text-slate-500">Optional</span>'
                                                            }
                                                        </td>
                                                        <td class="p-2.5 text-slate-400 text-[11px]">${p.desc}</td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ` : ''}

                            <!-- Request JSON Body Example -->
                            ${item.requestExample ? `
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Payload Example</h3>
                                        <button onclick="copyToClipboard(\`${JSON.stringify(item.requestExample, null, 2).replace(/`/g, '\\`')}\`, 'Payload JSON copied!')" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-medium">Copy JSON</button>
                                    </div>
                                    <pre class="bg-slate-900/90 text-slate-200 font-mono text-[11px] p-3 rounded-xl border border-slate-800/80 overflow-x-auto"><code>${JSON.stringify(item.requestExample, null, 2)}</code></pre>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Right Column: Response Format & Status Codes -->
                        <div class="flex flex-col space-y-2">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Responses & Status Codes</h3>
                                <span class="text-[10px] text-slate-500">Click tab to switch view</span>
                            </div>

                            <!-- Status Code Tabs -->
                            <div class="flex items-center gap-1.5 bg-slate-900/90 p-1 rounded-xl border border-slate-800">
                                ${statusCodes.map((code, idx) => `
                                    <button onclick="switchStatusTab('${item.id}', '${code}')" id="tab-${item.id}-${code}" class="status-tab-${item.id} px-3 py-1 rounded-lg text-xs font-mono font-bold transition ${idx === 0 ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-slate-200'}">
                                        HTTP ${code}
                                    </button>
                                `).join('')}
                            </div>

                            <!-- Status Code JSON Response Views -->
                            ${statusCodes.map((code, idx) => `
                                <div id="response-${item.id}-${code}" class="response-view-${item.id} ${idx === 0 ? '' : 'hidden'} flex-1 flex flex-col mt-2">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-mono font-semibold text-slate-400">Response Envelope (${code}):</span>
                                        <button onclick="copyToClipboard(\`${JSON.stringify(item.responses[code], null, 2).replace(/`/g, '\\`')}\`, 'Response JSON copied!')" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            Copy JSON
                                        </button>
                                    </div>
                                    <pre class="bg-slate-900/90 text-emerald-400 font-mono text-[11px] p-3 rounded-xl border border-slate-800/80 overflow-x-auto flex-1 max-h-72 overflow-y-auto"><code>${JSON.stringify(item.responses[code], null, 2)}</code></pre>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        function switchStatusTab(cardId, statusCode) {
            document.querySelectorAll(`.status-tab-${cardId}`).forEach(tab => {
                tab.classList.remove('bg-indigo-600', 'text-white', 'shadow');
                tab.classList.add('text-slate-400');
            });
            document.querySelectorAll(`.response-view-${cardId}`).forEach(view => {
                view.classList.add('hidden');
            });

            const activeTab = document.getElementById(`tab-${cardId}-${statusCode}`);
            if (activeTab) {
                activeTab.classList.add('bg-indigo-600', 'text-white', 'shadow');
                activeTab.classList.remove('text-slate-400');
            }
            const activeView = document.getElementById(`response-${cardId}-${statusCode}`);
            if (activeView) activeView.classList.remove('hidden');
        }

        function renderSidebar() {
            const sidebarMenu = document.getElementById('sidebarMenu');
            const searchVal = (document.getElementById('sidebarSearch')?.value || '').toLowerCase();
            
            const roles = ['Admin', 'Seller', 'Customer', 'General'];
            let html = '';

            roles.forEach(role => {
                if (currentRole !== 'ALL' && currentRole !== role) return;

                const items = API_DATA.filter(api => api.role === role && (!searchVal || api.title.toLowerCase().includes(searchVal) || api.path.toLowerCase().includes(searchVal)));
                if (items.length === 0) return;

                html += `
                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                            <span>${role} APIs</span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px]">${items.length}</span>
                        </h4>
                        <div class="space-y-1">
                            ${items.map(api => `
                                <a href="#${api.id}" class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-900 text-xs text-slate-300 hover:text-white transition">
                                    <span class="truncate font-medium">${api.title}</span>
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded ${getMethodBadgeStyle(api.method)}">${api.method}</span>
                                </a>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            sidebarMenu.innerHTML = html || '<div class="text-xs text-slate-500 py-4">No matching quick links</div>';
        }

        document.getElementById('sidebarSearch')?.addEventListener('input', renderSidebar);

        function copyBaseUrl() {
            copyToClipboard(BASE_URL, 'Base URL copied!');
        }

        function copyToClipboard(text, message = 'Copied to clipboard!') {
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById('toast');
                document.getElementById('toastMessage').innerText = message;
                toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
                }, 2500);
            });
        }
    </script>
</body>
</html>
