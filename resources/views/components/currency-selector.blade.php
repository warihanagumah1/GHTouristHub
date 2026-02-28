@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $selectedCurrency = $currencyService->selectedCurrencyCode();
    $popularCodes = $currencyService->popularCurrencyCodes();

    $currencyRows = $currencyService
        ->activeCurrencies()
        ->map(function ($currency) use ($currencyService): array {
            $meta = $currencyService->meta((string) $currency->code);
            $code = (string) $currency->code;
            $name = (string) $currency->name;
            $symbol = (string) $currency->symbol;
            $country = (string) $meta['country'];

            return [
                'code' => $code,
                'name' => $name,
                'symbol' => $symbol,
                'country' => $country,
                'flag' => $meta['flag'],
                'search' => strtolower(trim("{$code} {$name} {$symbol} {$country}")),
            ];
        })
        ->values();

    $selectedRow = $currencyRows->firstWhere('code', $selectedCurrency)
        ?? ['code' => $selectedCurrency, 'name' => $selectedCurrency, 'symbol' => $selectedCurrency, 'country' => 'Global', 'flag' => '🌐'];

    $popularRows = $currencyRows
        ->filter(fn (array $row): bool => in_array($row['code'], $popularCodes, true))
        ->values();
    $allRows = $currencyRows
        ->reject(fn (array $row): bool => in_array($row['code'], $popularCodes, true))
        ->values();

    $dropdownId = 'currency_dropdown_'.uniqid();
@endphp

@if ($currencyRows->isNotEmpty())
    <div
        x-data="{
            open: false,
            search: '',
            select(code) {
                this.$refs.currencyCode.value = code;
                this.$refs.currencyForm.submit();
            }
        }"
        class="relative"
        id="{{ $dropdownId }}"
    >
        <form method="POST" action="{{ route('currency.update') }}" x-ref="currencyForm">
            @csrf
            <input type="hidden" name="currency_code" x-ref="currencyCode" value="{{ $selectedRow['code'] }}">
        </form>

        <button
            type="button"
            @click="open = !open"
            class="inline-flex h-11 items-center gap-2 rounded-2xl border-[3px] border-tertiary bg-white px-4 text-sm font-semibold text-primary shadow-sm transition hover:border-secondary"
        >
            <span class="text-lg leading-none">{{ $selectedRow['flag'] }}</span>
            <span>{{ $selectedRow['code'] }}</span>
            <svg class="h-4 w-4 text-primary/60" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 12l4-4 4 4" />
            </svg>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition
            @click.away="open = false"
            class="absolute right-0 z-50 mt-2 w-[23rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        >
            <div class="border-b border-slate-200 p-4">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="9" r="7"></circle>
                        <path d="M14.5 14.5L18 18"></path>
                    </svg>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search currencies..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-primary focus:border-tertiary focus:outline-none focus:ring-tertiary"
                    >
                </div>
            </div>

            <div class="max-h-[22rem] overflow-y-auto">
                @if ($popularRows->isNotEmpty())
                    <p class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Popular Currencies
                    </p>
                    @foreach ($popularRows as $row)
                        <button
                            type="button"
                            data-search="{{ $row['search'] }}"
                            x-show="search === '' || $el.dataset.search.includes(search.trim().toLowerCase())"
                            @click="select('{{ $row['code'] }}'); open = false;"
                            class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50"
                            @class(['bg-secondary/10' => $row['code'] === $selectedCurrency])
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-xl leading-none">{{ $row['flag'] }}</span>
                                <div>
                                    <p class="text-base font-semibold text-primary">
                                        {{ $row['code'] }} <span class="font-medium text-primary/75">{{ $row['name'] }}</span>
                                    </p>
                                    <p class="text-xs text-primary/60">{{ $row['country'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg text-primary/70">{{ $row['symbol'] }}</span>
                                @if ($row['code'] === $selectedCurrency)
                                    <span class="text-secondary">✓</span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                @endif

                <p class="border-y border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    All Currencies
                </p>
                @foreach ($allRows as $row)
                    <button
                        type="button"
                        data-search="{{ $row['search'] }}"
                        x-show="search === '' || $el.dataset.search.includes(search.trim().toLowerCase())"
                        @click="select('{{ $row['code'] }}'); open = false;"
                        class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-xl leading-none">{{ $row['flag'] }}</span>
                            <div>
                                <p class="text-base font-semibold text-primary">
                                    {{ $row['code'] }} <span class="font-medium text-primary/75">{{ $row['name'] }}</span>
                                </p>
                                <p class="text-xs text-primary/60">{{ $row['country'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg text-primary/70">{{ $row['symbol'] }}</span>
                            @if ($row['code'] === $selectedCurrency)
                                <span class="text-secondary">✓</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endif
