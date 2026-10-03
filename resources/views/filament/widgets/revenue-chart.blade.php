<div wire:poll.30s="loadData">
    <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Revenue Trend</h3>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Last 30 days</span>
        </div>
        <div wire:ignore>
            <div id="dashboard-revenue-chart" style="height: 280px; display: flex; align-items: center; justify-content: center;">
                <span id="dashboard-revenue-error" class="text-gray-500 text-sm" style="display: none;">Chart could not be loaded</span>
            </div>
        </div>
    </div>

    @script
    <script>
        (function() {
            let revenueChart = null;
            let currentChartData = @js($chartData);
            let initialized = false;

            function getRevenueOptions(data) {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    chart: {
                        type: 'area',
                        height: 280,
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        background: 'transparent',
                        animations: { enabled: true, easing: 'easeinout', speed: 600 },
                    },
                    series: data.series || [],
                    xaxis: {
                        categories: data.categories || [],
                        labels: {
                            style: { colors: isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' },
                            rotate: -45,
                            rotateAlways: false,
                            hideOverlappingLabels: true,
                            trim: true,
                            maxHeight: 60,
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        tickAmount: 8,
                    },
                    yaxis: {
                        labels: {
                            style: { colors: isDark ? '#9ca3af' : '#6b7280', fontSize: '11px' },
                            formatter: (val) => '₱' + val.toLocaleString(),
                        },
                    },
                    colors: ['#f59e0b'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 100] },
                    },
                    stroke: { curve: 'smooth', width: 2.5 },
                    grid: {
                        borderColor: isDark ? '#374151' : '#e5e7eb',
                        strokeDashArray: 4,
                        padding: { left: 8, right: 8 },
                    },
                    dataLabels: { enabled: false },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: { formatter: (val) => '₱' + val.toLocaleString(undefined, { minimumFractionDigits: 2 }) },
                    },
                    theme: { mode: isDark ? 'dark' : 'light' },
                };
            }

            function initRevenueChart() {
                const el = document.getElementById('dashboard-revenue-chart');
                const errorEl = document.getElementById('dashboard-revenue-error');

                if (!el) {
                    return;
                }

                if (!window.ApexCharts) {
                    if (errorEl) errorEl.style.display = 'block';
                    console.error('ApexCharts not available for revenue chart');
                    return;
                }

                if (errorEl) errorEl.style.display = 'none';

                if (revenueChart) {
                    revenueChart.destroy();
                }

                revenueChart = new ApexCharts(el, getRevenueOptions(currentChartData));
                revenueChart.render();
                initialized = true;
            }

            function waitForApexCharts() {
                if (window.ApexCharts) {
                    initRevenueChart();
                } else {
                    window.addEventListener('amiga:apexcharts-ready', function() {
                        initRevenueChart();
                    }, { once: true });

                    // Fallback error log if event doesn't fire in 10 seconds
                    setTimeout(function() {
                        if (!initialized && !window.ApexCharts) {
                            console.error('ApexCharts did not load after 10 seconds');
                            const errorEl = document.getElementById('dashboard-revenue-error');
                            if (errorEl) errorEl.style.display = 'block';
                        }
                    }, 10000);
                }
            }

            // Listen for updates
            $wire.on('revenue-chart-updated', ({ chartData }) => {
                if (chartData) {
                    currentChartData = chartData;
                    initRevenueChart();
                }
            });

            // Dark mode observer
            const observer = new MutationObserver(() => {
                initRevenueChart();
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Start initialization
            waitForApexCharts();
        })();
    </script>
    @endscript
</div>
