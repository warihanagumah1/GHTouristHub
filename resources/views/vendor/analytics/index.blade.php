<x-app-layout>
    @php
        $statusColors = [
            'pending_payment' => '#f59e0b',
            'paid' => '#10b981',
            'confirmed' => '#3b82f6',
            'completed' => '#8b5cf6',
            'cancelled' => '#ef4444',
        ];
        $statusLabels = collect($bookingsByStatus->keys())->map(fn ($status) => ucfirst(str_replace('_', ' ', $status)))->values();
        $statusValues = collect($bookingsByStatus->values())->values();
        $statusPalette = collect($bookingsByStatus->keys())->map(fn ($status) => $statusColors[$status] ?? '#7899cf')->values();
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">{{ $tenant->name }} • Analytics</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Analytics Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-5">
                    <div>
                        <x-input-label for="date_from" value="From" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$filters['date_from']" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="To" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$filters['date_to']" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Booking Status" />
                        <x-select-input id="status" name="status" class="mt-1">
                            <option value="">All</option>
                            @foreach (['pending_payment', 'paid', 'confirmed', 'cancelled', 'completed'] as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="type" value="Listing Type" />
                        <x-select-input id="type" name="type" class="mt-1">
                            <option value="">All</option>
                            <option value="tour" @selected($filters['type'] === 'tour')>Tour</option>
                            <option value="utility" @selected($filters['type'] === 'utility')>Utility</option>
                        </x-select-input>
                    </div>
                    <div class="flex items-end">
                        <x-button type="submit" variant="secondary" class="w-full">Apply</x-button>
                    </div>
                </form>
            </x-card>

            <div class="grid gap-4 md:grid-cols-4">
                <x-stat-card label="Revenue (USD)" :value="'$'.number_format((float) $gmvUsd, 2)" />
                <x-stat-card label="Bookings (Filtered)" :value="$dailyTrend->sum('bookings')" />
                <x-stat-card label="Paid / Confirmed / Completed" :value="$bookingsByStatus['paid'] + $bookingsByStatus['confirmed'] + $bookingsByStatus['completed']" />
                <x-stat-card label="Top Listings Tracked" :value="$topListings->count()" />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Bookings Trend">
                    <div class="relative h-64 w-full rounded-xl border border-slate-200 bg-gradient-to-br from-[#eff6ff] via-white to-[#ffe9dd] p-2">
                        <canvas id="vendorBookingsTrendChart" class="h-full w-full"></canvas>
                    </div>
                </x-card>

                <x-card title="Revenue Trend (USD)">
                    <div class="relative h-64 w-full rounded-xl border border-slate-200 bg-gradient-to-br from-[#ecfeff] via-white to-[#f5d0fe] p-2">
                        <canvas id="vendorRevenueTrendChart" class="h-full w-full"></canvas>
                    </div>
                </x-card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Bookings by Status">
                    <div class="relative h-64 w-full rounded-xl border border-slate-200 bg-gradient-to-br from-[#fff7ed] via-white to-[#ecfccb] p-2">
                        <canvas id="vendorStatusChart" class="h-full w-full"></canvas>
                    </div>
                </x-card>

                <x-card title="Paid Revenue by Currency">
                    <div class="space-y-2 text-sm">
                        @forelse ($currencyBreakdown as $item)
                            <p class="text-primary/80">
                                <span class="font-semibold">{{ $item->currency }}:</span>
                                {{ $currencyService->symbol((string) $item->currency) }}{{ number_format((float) $item->total, 2) }}
                            </p>
                        @empty
                            <p class="text-primary/70">No paid revenue in selected range.</p>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <x-card title="Top Listings">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topListings as $listing)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 text-primary">{{ $listing->title }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $listing->bookings_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-6 text-center text-primary/70">No listing analytics yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        (() => {
            const dailyTrend = @json($dailyTrend);
            const statusLabels = @json($statusLabels);
            const statusValues = @json($statusValues);
            const statusPalette = @json($statusPalette);

            const labels = dailyTrend.map((point) => point.date.slice(5));
            const bookingValues = dailyTrend.map((point) => Number(point.bookings));
            const revenueValues = dailyTrend.map((point) => Number(point.revenue_usd));

            const initializeCanvas = (canvas) => {
                const dpr = window.devicePixelRatio || 1;
                const rect = canvas.getBoundingClientRect();
                canvas.width = Math.max(1, Math.floor(rect.width * dpr));
                canvas.height = Math.max(1, Math.floor(rect.height * dpr));
                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);
                return { ctx, width: rect.width, height: rect.height };
            };

            const drawLineChart = (canvasId, chartLabels, chartValues, color, fill) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !chartValues.length) {
                    return;
                }

                const { ctx, width, height } = initializeCanvas(canvas);
                const pad = { top: 18, right: 20, bottom: 34, left: 36 };
                const graphWidth = width - pad.left - pad.right;
                const graphHeight = height - pad.top - pad.bottom;
                const maxValue = Math.max(1, ...chartValues);
                const minValue = 0;

                ctx.clearRect(0, 0, width, height);

                ctx.strokeStyle = '#d4dce8';
                ctx.lineWidth = 1;
                for (let i = 0; i <= 4; i++) {
                    const y = pad.top + (graphHeight / 4) * i;
                    ctx.beginPath();
                    ctx.moveTo(pad.left, y);
                    ctx.lineTo(width - pad.right, y);
                    ctx.stroke();
                }

                const points = chartValues.map((value, index) => {
                    const x = pad.left + (graphWidth * (chartValues.length === 1 ? 0.5 : index / (chartValues.length - 1)));
                    const y = pad.top + graphHeight - (((value - minValue) / (maxValue - minValue || 1)) * graphHeight);
                    return { x, y, value };
                });

                if (!points.length) {
                    return;
                }

                ctx.beginPath();
                ctx.moveTo(points[0].x, pad.top + graphHeight);
                points.forEach((point) => ctx.lineTo(point.x, point.y));
                ctx.lineTo(points[points.length - 1].x, pad.top + graphHeight);
                ctx.closePath();
                ctx.fillStyle = fill;
                ctx.fill();

                ctx.beginPath();
                ctx.moveTo(points[0].x, points[0].y);
                points.slice(1).forEach((point) => ctx.lineTo(point.x, point.y));
                ctx.lineWidth = 3;
                ctx.strokeStyle = color;
                ctx.stroke();

                ctx.fillStyle = color;
                points.forEach((point) => {
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, 3, 0, Math.PI * 2);
                    ctx.fill();
                });

                ctx.fillStyle = '#516585';
                ctx.font = '11px ui-sans-serif, system-ui, -apple-system, Segoe UI';
                ctx.textAlign = 'center';
                const step = Math.max(1, Math.floor(chartLabels.length / 6));
                chartLabels.forEach((label, index) => {
                    if (index % step !== 0 && index !== chartLabels.length - 1) {
                        return;
                    }
                    const x = pad.left + (graphWidth * (chartLabels.length === 1 ? 0.5 : index / (chartLabels.length - 1)));
                    ctx.fillText(label, x, height - 12);
                });
            };

            const drawBarChart = (canvasId, chartLabels, chartValues, colors) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !chartValues.length) {
                    return;
                }

                const { ctx, width, height } = initializeCanvas(canvas);
                const pad = { top: 20, right: 20, bottom: 24, left: 120 };
                const graphWidth = width - pad.left - pad.right;
                const graphHeight = height - pad.top - pad.bottom;
                const maxValue = Math.max(1, ...chartValues);
                const rowHeight = graphHeight / Math.max(1, chartLabels.length);

                ctx.clearRect(0, 0, width, height);

                chartLabels.forEach((label, index) => {
                    const y = pad.top + (rowHeight * index) + (rowHeight * 0.2);
                    const barHeight = rowHeight * 0.6;
                    const value = chartValues[index];
                    const barWidth = (value / maxValue) * graphWidth;

                    ctx.fillStyle = '#e2e8f0';
                    ctx.fillRect(pad.left, y, graphWidth, barHeight);

                    ctx.fillStyle = colors[index] || '#7899cf';
                    ctx.fillRect(pad.left, y, barWidth, barHeight);

                    ctx.fillStyle = '#2a194f';
                    ctx.font = '12px ui-sans-serif, system-ui, -apple-system, Segoe UI';
                    ctx.textAlign = 'left';
                    ctx.fillText(label, 8, y + (barHeight * 0.7));

                    ctx.textAlign = 'right';
                    ctx.fillText(String(value), width - 8, y + (barHeight * 0.7));
                });
            };

            drawLineChart('vendorBookingsTrendChart', labels, bookingValues, '#ef4444', 'rgba(239, 68, 68, 0.14)');
            drawLineChart('vendorRevenueTrendChart', labels, revenueValues, '#2563eb', 'rgba(37, 99, 235, 0.14)');
            drawBarChart('vendorStatusChart', statusLabels, statusValues, statusPalette);

            window.addEventListener('resize', () => {
                drawLineChart('vendorBookingsTrendChart', labels, bookingValues, '#ef4444', 'rgba(239, 68, 68, 0.14)');
                drawLineChart('vendorRevenueTrendChart', labels, revenueValues, '#2563eb', 'rgba(37, 99, 235, 0.14)');
                drawBarChart('vendorStatusChart', statusLabels, statusValues, statusPalette);
            });
        })();
    </script>
</x-app-layout>
