$(function () {

    // ==============================================================
    // Helper to get element or null
    // ==============================================================
    function getEl(selector) {
        var $el = $(selector);
        return $el.length ? $el[0] : null;
    }

    // ==============================================================
    // Campaign
    // ==============================================================
    var campaignEl = getEl('#campaign-v2') || getEl('#client-campaign-v2') || getEl('#admin-campaign-v2');
    if (campaignEl) {
        var chart1 = c3.generate({
            bindto: campaignEl,
            data: {
                columns: [
                    ['Direct Sales', 25],
                    ['Referral Sales', 15],
                    ['Afilliate Sales', 10],
                    ['Indirect Sales', 15]
                ],
                type: 'donut',
                tooltip: { show: true }
            },
            donut: {
                label: { show: false },
                title: 'Sales',
                width: 18
            },
            legend: { hide: true },
            color: {
                pattern: ['#edf2f6', '#5f76e8', '#ff4f70', '#01caf1']
            }
        });
        d3.select(campaignEl).select('.c3-chart-arcs-title').style('font-family', 'Rubik');
    }

    // ============================================================== 
    // income
    // ============================================================== 
    var incomeEl = getEl('.net-income');
    if (incomeEl) {
        var data = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            series: [[5, 4, 3, 7, 5, 10]]
        };
        var options = {
            axisX: { showGrid: false },
            seriesBarDistance: 1,
            chartPadding: { top: 15, right: 15, bottom: 5, left: 0 },
            plugins: [Chartist.plugins.tooltip()],
            width: '100%'
        };
        var responsiveOptions = [
            ['screen and (max-width: 640px)', {
                seriesBarDistance: 5,
                axisX: {
                    labelInterpolationFnc: function (value) { return value[0]; }
                }
            }]
        ];
        new Chartist.Bar(incomeEl, data, options, responsiveOptions);
    }

    // ============================================================== 
    // Visit By Location
    // ==============================================================
    var visitEl = getEl('#visitbylocate') || getEl('#client-visitbylocate') || getEl('#admin-visitbylocate');
    if (visitEl && typeof jQuery.fn.vectorMap !== 'undefined') {
        $(visitEl).vectorMap({
            map: 'world_mill_en',
            backgroundColor: 'transparent',
            borderColor: '#000',
            borderOpacity: 0,
            borderWidth: 0,
            zoomOnScroll: false,
            color: '#d5dce5',
            regionStyle: {
                initial: {
                    fill: '#d5dce5',
                    'stroke-width': 1,
                    'stroke': 'rgba(255, 255, 255, 0.5)'
                }
            },
            enableZoom: true,
            hoverColor: '#bdc9d7',
            hoverOpacity: null,
            normalizeFunction: 'linear',
            scaleColors: ['#d5dce5', '#d5dce5'],
            selectedColor: '#bdc9d7',
            selectedRegions: [],
            showTooltip: true,
            onRegionClick: function (element, code, region) {
                var message = 'You clicked "' + region + '" which has the code: ' + code.toUpperCase();
                alert(message);
            }
        });
    }

    // ==============================================================
    // Earning Stastics Chart
    // ==============================================================
    var statsEl = getEl('.stats');
    if (statsEl) {
        var chart = new Chartist.Line(statsEl, {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            series: [[11, 10, 15, 21, 14, 23, 12]]
        }, {
            low: 0,
            high: 28,
            showArea: true,
            fullWidth: true,
            plugins: [Chartist.plugins.tooltip()],
            axisY: {
                onlyInteger: true,
                scaleMinSpace: 40,
                offset: 20,
                labelInterpolationFnc: function (value) { return (value / 1) + 'k'; }
            },
        });

        chart.on('draw', function (ctx) {
            if (ctx.type === 'area') {
                ctx.element.attr({ x1: ctx.x1 + 0.001 });
            }
        });

        chart.on('created', function (ctx) {
            if (!ctx.svg) return;
            var defs = ctx.svg.elem('defs');
            defs.elem('linearGradient', {
                id: 'gradient',
                x1: 0, y1: 1, x2: 0, y2: 0
            }).elem('stop', {
                offset: 0, 'stop-color': 'rgba(255, 255, 255, 1)'
            }).parent().elem('stop', {
                offset: 1, 'stop-color': 'rgba(80, 153, 255, 1)'
            });
        });

        $(window).on('resize', function () {
            chart.update();
        });
    }
});
