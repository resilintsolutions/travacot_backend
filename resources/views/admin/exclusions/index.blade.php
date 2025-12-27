<x-app-layout>
    <div class="mb-3 text-xs text-gray-500">
        Exclusions &gt;
        <span class="font-medium text-gray-900">
            Automated / Manual Exclusion Rules - FUNNEL
        </span>
    </div>
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div id="successBanner" class="alert alert-success alert-dismissible fade show d-none">
                    <span id="successMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>


                {{-- PAGE TITLE --}}
                <h1 class="text-xl font-semibold mb-1">
                    Automated / Manual Exclusion Rules - FUNNEL
                </h1>
                <p class="text-sm text-gray-500 mb-6">
                    Manage automatic filters and manual overrides
                </p>

                {{-- AUTOMATIC EXCLUSION RULES CARD --}}
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">
                                Automatic Exclusion Rules
                            </h2>
                            <p class="text-sm text-gray-500">
                                These rules auto-hide low-quality or invalid hotels.
                            </p>
                        </div>

                        {{-- <button class="px-4 py-1.5 text-sm border rounded-full text-gray-600 bg-white">
                            Hide Details
                        </button> --}}
                    </div>

                    {{-- RULES --}}
                    <form id="autoRulesForm" class="space-y-4">

                        {{-- Rating --}}
                        <div class="flex items-center gap-4">
                            <div class="w-80 text-sm text-gray-800">
                                Exclude hotels with ratings below
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="number" step="0.1" min="0" max="10" name="min_rating"
                                    class="w-16 h-9 border rounded-md text-center text-sm bg-white">
                                <span class="text-sm text-gray-500">/ 10</span>
                            </div>

                            <div class="text-sm text-green-600">
                                Out of 5: <strong>3.5 / 5</strong>
                            </div>
                        </div>

                        {{-- Reviews --}}
                        <div class="flex items-center gap-4">
                            <div class="w-80 text-sm text-gray-800">
                                Exclude hotels with reviews less than
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="number" min="0" name="min_reviews"
                                    class="w-16 h-9 border rounded-md text-center text-sm bg-white">
                                <span class="text-sm text-gray-500">reviews</span>
                            </div>
                        </div>

                        {{-- Toggle rows --}}
                        @php
                            $toggles = [
                                'exclude_no_images' => 'Exclude hotels with missing images',
                                'exclude_no_description' => 'Exclude hotels with missing description',
                                'exclude_inactive' => 'Exclude inactive / unbookable hotels',
                            ];
                        @endphp

                        @foreach ($toggles as $name => $label)
                            <div class="flex items-center gap-4">
                                <div class="w-80 text-sm text-gray-800">
                                    {{ $label }}
                                </div>

                                <div class="flex gap-2">
                                    <button type="button" class="toggle-btn yes" data-name="{{ $name }}"
                                        data-value="1">
                                        Yes
                                    </button>
                                    <button type="button" class="toggle-btn no" data-name="{{ $name }}"
                                        data-value="0">
                                        No
                                    </button>
                                </div>

                                <input type="hidden" name="{{ $name }}" value="1">
                            </div>
                        @endforeach

                    </form>

                    {{-- DIVIDER --}}
                    <div class="border-t border-gray-200 my-6"></div>

                    {{-- SUMMARY --}}
                    {{-- <div class="text-sm">
                        <strong>Auto-Exclusion Summary:</strong>
                        <ul class="mt-2 space-y-1">
                            <li class="text-green-600">
                                • <span id="autoExcludedCount">0</span> hotels excluded automatically
                            </li>
                            <li class="text-green-600">
                                • <span id="remainingCount">0</span> hotels remaining
                            </li>
                        </ul>
                    </div> --}}

                    {{-- SAVE --}}
                    <div class="mt-6">
                        <button onclick="saveRules()" class="btn btn-primary">
                            Save Rules
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STYLES --}}
    <style>
        .toggle-btn {
            padding: 6px 14px;
            font-size: 0.875rem;
            border-radius: 9999px;
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
        }

        .toggle-btn.yes.active {
            border-color: #22c55e;
            background: #ecfdf5;
            color: #15803d;
        }

        .toggle-btn.no.active {
            border-color: #ef4444;
            background: #fef2f2;
            color: #b91c1c;
        }
    </style>

    {{-- JS --}}
    <script>
        /**
         * Base admin URL
         */
        const ADMIN_BASE = `${APP_URL}/admin`;

        /**
         * ----------------------------
         * TOGGLE (YES / NO) LOGIC
         * ----------------------------
         */
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const name = btn.dataset.name;
                const value = btn.dataset.value; // "1" or "0"

                // Remove active state from siblings
                document
                    .querySelectorAll(`.toggle-btn[data-name="${name}"]`)
                    .forEach(b => b.classList.remove('active'));

                // Activate clicked
                btn.classList.add('active');

                // Update hidden input as numeric value
                const hidden = document.querySelector(`input[name="${name}"]`);
                if (hidden) {
                    hidden.value = value === '1' ? 1 : 0;
                }
            });
        });

        /**
         * ----------------------------
         * LOAD RULES FROM BACKEND
         * ----------------------------
         */
        async function loadRules() {
            const res = await fetch(`${ADMIN_BASE}/exclusions/rules`);
            const rules = await res.json();

            // Numeric inputs
            document.querySelector('[name="min_rating"]').value = rules.min_rating ?? 0;
            document.querySelector('[name="min_reviews"]').value = rules.min_reviews ?? 0;

            // Boolean toggles
            [
                'exclude_no_images',
                'exclude_no_description',
                'exclude_inactive'
            ].forEach(key => {
                const value = rules[key] ? '1' : '0';

                // Hidden input
                const hidden = document.querySelector(`input[name="${key}"]`);
                if (hidden) hidden.value = value;

                // Toggle UI
                document
                    .querySelectorAll(`.toggle-btn[data-name="${key}"]`)
                    .forEach(btn => {
                        btn.classList.toggle('active', btn.dataset.value === value);
                    });
            });
        }

        /**
         * ----------------------------
         * LOAD SUMMARY STATS
         * ----------------------------
         */
        async function loadStats() {
            const res = await fetch(`${ADMIN_BASE}/exclusions/stats`);
            const data = await res.json();

            document.getElementById('autoExcludedCount').innerText = data.auto_excluded;
            document.getElementById('remainingCount').innerText = data.remaining;
        }

        /**
         * ----------------------------
         * SAVE RULES
         * ----------------------------
         */
        async function saveRules() {
            const form = document.getElementById('autoRulesForm');
            const fd = new FormData(form);

            // Normalize payload (IMPORTANT)
            const payload = {
                min_rating: parseFloat(fd.get('min_rating')),
                min_reviews: parseInt(fd.get('min_reviews'), 10),

                exclude_no_images: Number(fd.get('exclude_no_images')) === 1,
                exclude_no_description: Number(fd.get('exclude_no_description')) === 1,
                exclude_inactive: Number(fd.get('exclude_inactive')) === 1,
            };

            // Save rules
            await fetch(`${ADMIN_BASE}/exclusions/rules`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            });

            // Trigger async recalculation
            await fetch(`${ADMIN_BASE}/exclusions/recalculate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });

            // Refresh stats
            loadStats();

            showSuccess('Rules updated successfully');

        }

        function showSuccess(message) {
            const banner = document.getElementById('successBanner');
            const text = document.getElementById('successMessage');

            text.innerText = message;
            banner.classList.remove('d-none');

            // Auto-hide after 4 seconds (optional)
            setTimeout(() => {
                banner.classList.add('d-none');
            }, 4000);
        }


        /**
         * ----------------------------
         * INIT
         * ----------------------------
         */
        document.addEventListener('DOMContentLoaded', () => {
            loadRules();
            loadStats();
        });
    </script>


</x-app-layout>
