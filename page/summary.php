<style>
 .text-muted { color: #6c757d !important; }
        .mb-2 { margin-bottom: 0.35rem !important; }
        .mb-0 { margin-bottom: 0 !important; }
        .font-size-15 { font-size: 13px !important; }
        .font-size-10 { font-size: 9px !important; }

        /* Nett Profit Container - Enhanced for mobile */
        #nett-profit-container {
            background: white;
            padding: 5px 5px;
            border-radius: 12px;
            box-shadow: 0 15px 20px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: inline-block;
            min-width: 180px;
            border: 2px solid transparent;
            font-size: 12px;
            font-weight: 600;
            color: #2d3748;
            /* Mobile touch optimization */
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        #nett-profit-container:hover,
        #nett-profit-container:active {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
            border-color: #4299e1;
        }

        #nett-profit-container.clicked {
            animation: clickPulse 0.2s ease;
        }

        @keyframes clickPulse {
            0% { transform: scale(1); }
            50% { transform: scale(0.96); }
            100% { transform: scale(1); }
        }

        #nett-profit-container::after {
            content: '💰 tap for details';
            position: absolute;
            bottom: -26px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #4299e1;
            opacity: 0;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        #nett-profit-container:hover::after,
        #nett-profit-container:active::after {
            opacity: 1;
            bottom: -30px;
        }

        /* Popup Overlay - Android optimized */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            /* Android safe area */
            padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
            /* Prevent scrolling on body */
            overscroll-behavior: contain;
        }

        .popup-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Popup Content - Fully responsive */
        .popup-content {
            background: white;
            border-radius: 16px;
            padding: 0;
            width: calc(100% - 32px);
            max-width: 450px;
            max-height: calc(100vh - 80px);
            overflow: hidden;
            transform: scale(0.8) translateY(40px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            /* Mobile optimization */
            -webkit-transform: scale(0.8) translateY(40px);
            -webkit-transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .popup-overlay.show .popup-content {
            transform: scale(1) translateY(0);
            -webkit-transform: scale(1) translateY(0);
        }

        /* Header - Mobile optimized */
        .popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 12px;
            text-align: center;
            position: relative;
            /* Prevent text selection */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .popup-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .popup-subtitle {
            font-size: 13px;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.3;
        }

        /* Close Button - Touch optimized */
        .close-btn {
            position: absolute;
            top: 12px;
            right: 16px;
            width: 40px;
            height: 40px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-weight: 300;
            /* Touch optimization */
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .close-btn:hover,
        .close-btn:active {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Body - Responsive padding */
        .popup-body {
            padding: 10px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            -webkit-overflow-scrolling: touch;
        }

        /* Payment Grid - Responsive layout */
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        /* Payment Item - Touch friendly */
        .payment-item {
            background: #f8fafc;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            opacity: 0;
            animation: slideInUp 0.5s ease forwards;
            /* Touch optimization */
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .payment-item:hover,
        .payment-item:active {
            background: #f1f5f9;
            border-color: #cbd5e0;
            transform: translateY(-2px);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .payment-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .payment-icon.cash {
            background: linear-gradient(135deg, #48bb78, #38a169);
        }

        .payment-icon.ewallet {
            background: linear-gradient(135deg, #4299e1, #3182ce);
        }

        .payment-icon.debit {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
        }

        .payment-icon.credit {
            background: linear-gradient(135deg, #9f7aea, #805ad5);
        }

        .payment-label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            flex: 1;
            line-height: 1.2;
        }

        .payment-count {
            font-size: 11px;
            color: #718096;
            font-weight: 500;
        }

        /* Payment amounts styling - UPDATED */
        .payment-amounts {
            margin-top: 12px;
        }

        .payment-gross,
        .payment-promo, 
        .payment-spending,
        .payment-net {
            font-size: 12px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            line-height: 1.3;
        }

        .payment-gross {
            color: #2d5a2d;
            font-weight: 600;
        }

        .payment-promo {
            color:rgb(229, 165, 62);
            font-style: italic;
        }

        .payment-spending {
            color: #c53030;
           font-style: italic;
        }

        .payment-net {
            color: #2d3748;
            font-weight: 700;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 13px;
        }

        .amount-label {
            font-size: 10px;
            opacity: 0.8;
            margin-right: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Loading animations */
        .amount-loading,
        .amount-loading-promo,
        .amount-loading-spending,
        .amount-loading-net {
            width: 50px;
            height: 12px;
            background: linear-gradient(90deg, #e2e8f0 25%, #f7fafc 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
            display: inline-block;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Total Section */
        .total-section {
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            padding: 18px 22px;
            margin: 0;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 16px 16px;
        }

        .total-label {
            font-size: 14px;
            font-weight: 600;
            color: #718096;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .total-amount {
            font-size: 16px;
            font-weight: 800;
            color: #2d3748;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        /* Summary Stats - NEW */
        .summary-stats {
            background: #f0f9ff;
            padding: 0px 0px;
            margin: 2px 0;
            border-radius: 10px;
            border: 1px solid #bae6fd;
        }

        .summary-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }

        .summary-stat {
            text-align: center;
        }

        .summary-stat-value {
            font-size: 12px;
            font-weight: 700;
            color: #0c4a6e;
            margin-bottom: 2px;
        }

        .summary-stat-label {
            font-size: 10px;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Demo button - Touch optimized */
        .demo-btn {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .demo-btn:hover,
        .demo-btn:active {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Android specific optimizations */
        @media screen and (max-width: 480px) {
            /* Mobile popup adjustments */
            .popup-content {
                width: calc(100% - 16px);
                max-height: calc(100vh - 40px);
                border-radius: 12px;
                max-width: 400px;
            }

            .popup-header {
                padding: 14px 18px;
            }

            .popup-title {
                font-size: 15px;
            }

            .popup-subtitle {
                font-size: 12px;
            }

            .close-btn {
                width: 36px;
                height: 36px;
                font-size: 16px;
                top: 10px;
                right: 14px;
            }

            .popup-body {
                padding: 16px;
                max-height: calc(90vh - 140px);
            }

            /* Single column layout for small screens */
            .payment-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 14px;
            }

            .payment-item {
                padding: 14px;
            }

            .payment-icon {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .payment-label {
                font-size: 13px;
            }

            .payment-count {
                font-size: 10px;
            }

            .payment-amounts {
                margin-top: 10px;
            }

            .payment-gross,
            .payment-promo, 
            .payment-spending,
            .payment-net {
                font-size: 11px;
                margin-bottom: 4px;
            }

            .payment-net {
                font-size: 12px;
                padding-top: 6px;
                margin-top: 6px;
            }

            .total-section {
                padding: 16px 18px;
            }

            .total-label {
                font-size: 13px;
                margin-bottom: 6px;
            }

            .total-amount {
                font-size: 20px;
            }

            .summary-stats {
                padding: 12px 16px;
                margin: 12px 0;
            }

            .summary-stats-grid {
                gap: 10px;
            }

            .summary-stat-value {
                font-size: 14px;
            }

            .summary-stat-label {
                font-size: 9px;
            }

            /* Adjust container for mobile */
            #nett-profit-container {
                padding: 12px 16px;
                min-width: 140px;
                font-size: 13px;
            }

            #nett-profit-container::after {
                font-size: 10px;
                bottom: -24px;
            }

            #nett-profit-container:hover::after,
            #nett-profit-container:active::after {
                bottom: -28px;
            }
        }

        /* Extra small screens (Android phones in portrait) */
        @media screen and (max-width: 360px) {
            .popup-content {
                width: calc(100% - 12px);
                border-radius: 8px;
            }

            .popup-body {
                padding: 12px;
            }

            .payment-item {
                padding: 12px;
            }

            .payment-icon {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .total-section {
                padding: 14px 16px;
            }

            .summary-stats {
                padding: 10px 14px;
            }
        }

        /* Landscape orientation adjustments */
        @media screen and (max-height: 600px) and (orientation: landscape) {
            .popup-content {
                max-height: calc(100vh - 20px);
            }

            .popup-body {
                max-height: calc(100vh - 140px);
                padding: 12px 20px;
            }

            .payment-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 10px;
            }

            .payment-item {
                padding: 10px;
            }

            .total-section {
                padding: 12px 20px;
            }

            .summary-stats {
                padding: 10px 18px;
            }
        }

        /* High DPI Android displays */
        @media screen and (-webkit-min-device-pixel-ratio: 2) {
            .popup-content {
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35);
            }

            .payment-item {
                border-width: 0.5px;
            }

            .close-btn {
                border: 0.5px solid rgba(255, 255, 255, 0.1);
            }
        }
</style>
<?php
require_once './include/config.php'; // koneksi database
require_once './include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik
$username = $_SESSION['username'];
$r = $con->query("SELECT * FROM userx WHERE username = '$username'");
foreach ($r as $rr) {
        $merchand = $rr['merchand'];
        $level = $rr['level'];
        $license = $rr['license'];
         $exp = $rr['license_exp'];
        $cabang = $rr['cabang'];
         $logox = $rr['logox'];
}

$montNOW =  date("M Y");
$montNOWS =  date("Y-m");
$monthNOWsimple =  date("m/Y");
$dateNOW =  date("d M Y");
$dateNOW2 =  date("d M Y H:i:s");

$datting =  date('Y-m-d');
$yesterNOW =  date('d M Y',strtotime("-1 days"));
$dateNOWsimple =  date("d/m/Y");
$yearNOW =  date("Y");
$yearNOWsimple =  date("Y");
?>

<script>
// ========== UTILITY FUNCTIONS ==========
function formatRupiahSingkat(val) {
    if (val >= 1000000) {
        return (val / 1000000).toFixed(1) + 'M';
    } else if (val >= 1000) {
        return (val / 1000).toFixed(1) + 'K';
    } else {
        return val;
    }
}

function formatNumber(num) {
    return num.toLocaleString('id-ID');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function getChartColorsArray(chartId) {
    if (document.getElementById(chartId)) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        if (colors) {
            colors = JSON.parse(colors);
            return colors.map(function(value) {
                var color = value.replace(" ", "");
                if (color.indexOf(",") === -1) {
                    var cssColor = getComputedStyle(document.documentElement).getPropertyValue(color);
                    return cssColor.trim() || color;
                }
                var parts = color.split(",");
                if (parts.length !== 2) return color;
                return "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(parts[0]) + "," + parts[1] + ")";
            });
        }
    }
    return ["#008FFB", "#FEB019", "#775DD0", "#FF4560"];
}

const chartColors = getChartColorsArray("stacked-column-charts");

// ========== LOADING FUNCTIONS ==========
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
    
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    if (dateStart) dateStart.disabled = true;
    if (dateEnd) dateEnd.disabled = false;
    
    showLoadingTable();
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
    
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    if (dateStart) dateStart.disabled = false;
    if (dateEnd) dateEnd.disabled = false;
}

function showLoadingTable() {
    const tableBody = document.getElementById('ps-table-body');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading PS data...</span>
                </td>
            </tr>
        `;
    }
}

function showError(message) {
    // Chart error
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    updateElement("highest-summary", '<i class="bx bx-error"></i> Error loading data');
    updateElement("lowest-summary", '<i class="bx bx-error"></i> Error loading data');
    
    // Table error
    const tableBody = document.getElementById('ps-table-body');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4 text-danger">
                    <i class="bx bx-error-circle"></i>
                    <span class="ms-2">${message}</span>
                </td>
            </tr>
        `;
    }
}

// ========== MAIN LOAD FUNCTION ==========
function loadAllData() {
    const dateStartElement = document.getElementById('date_start');
    const dateEndElement = document.getElementById('date_end');
    
    if (!dateStartElement || !dateEndElement) {
        console.error('Date input elements not found');
        return;
    }
    
    const dateStart = dateStartElement.value;
    const dateEnd = dateEndElement.value;
    
    showLoading();
    
    const url = `./xapi/ajax_summary.php?date_start=${dateStart}&date_end=${dateEnd}`;
    
    fetch(url)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                // Update Chart Data
                updateChartData(response.data.data, response.data.summary, response.data.statistics);
                
                // Update PS Table Data
                if (response.data.ps_data) {
                    updatePsTable(response.data.ps_data.rows, response.data.ps_data.totals);
                    updatePsSummaryCards(response.data.ps_data.totals, response.data.ps_data.statistics);
                    
                    if (response.data.ps_data.statistics && response.data.ps_data.statistics.performance) {
                        updatePerformanceInsights(response.data.ps_data.statistics.performance);
                    }
                }
            } else {
                showError('Failed to load data');
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            showError('Error loading data');
        })
        .finally(() => {
            hideLoading();
        });
}

// ========== CHART UPDATE FUNCTIONS ==========
function updateChartData(data, summary, statistics) {
    const categories = data.map(item => item.date);
    const rentData = data.map(item => item.rent);
    const fnbData = data.map(item => item.fnb);
    const othersData = data.map(item => item.others);
    const spendingData = data.map(item => item.spending);

    const totalRent = summary.total.rent;
    const totalFnB = summary.total.fnb;  
    const totalProduct = summary.total.others;
    const totalSpending = summary.total.spending;
   
    const totalPromo = summary.total.promo;
    const totalGross = summary.total.net;
     const totalNett = summary.total.net - totalPromo ;

    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };

    updateElement("total-rentals", `<span class="font-size-10 text-muted">Rp </span>${totalRent.toLocaleString()}`);
    updateElement("total-product", `<span class="font-size-10 text-muted">Rp </span>${totalProduct.toLocaleString()}`);
    updateElement("total-fnb", `<span class="font-size-10 text-muted">Rp </span>${totalFnB.toLocaleString()}`);
    updateElement("total-spending", `<span class="font-size-10 text-muted">Rp </span>${totalSpending.toLocaleString()}`);
    updateElement("total-nett", `<span class="font-size-10 text-muted">Rp </span>${totalNett.toLocaleString()}`);
   updateElement("total-promo", `<span class="text-warning">(<span class="font-size-10 text-warning">Rp </span>${totalPromo.toLocaleString()})</span>`);

    updateElement("total-gross", `<span class="font-size-10 text-muted">Rp </span>${totalGross.toLocaleString()}`);

    if (statistics) {
        updateElement("total-days", `${statistics.total_days}`);
        updateElement("work-days", `${statistics.work_days}`);
        updateElement("total-trans", statistics.total_transactions.toLocaleString());
    }

    if (summary.max && summary.max.net && summary.min && summary.min.net) {
        const highest = summary.max.net;
        const lowest = summary.min.net;
        
        updateElement("highest-summary", `<i class="bx bx-trending-up align-bottom me-1"></i> Rp.${highest.value.toLocaleString()} highest`);
        updateElement("lowest-summary", `<i class="bx bx-trending-down align-bottom me-1"></i> Rp.${lowest.value.toLocaleString()} lowest`);
        
        const highestElement = document.getElementById("highest-summary");
        const lowestElement = document.getElementById("lowest-summary");
        if (highestElement) highestElement.title = highest.date;
        if (lowestElement) lowestElement.title = lowest.date;
    }

    updateApexChart(categories, rentData, fnbData, othersData, spendingData);
}

function updateApexChart(categories, rentData, fnbData, othersData, spendingData) {
    const options = {
        chart: {
            height: 360,
            type: "bar",
            stacked: true,
            toolbar: { show: false },
            zoom: { enabled: true }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "15%",
                endingShape: "rounded"
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '10px',
                colors: ['#000'],
            },
            formatter: formatRupiahSingkat,
        },
        tooltip: {
            shared: true,
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const getVal = (s, i) => (series[s] && series[s][i] != null) ? series[s][i] : 0;

                const rent = getVal(0, dataPointIndex);
                const fnb = getVal(1, dataPointIndex);
                const others = getVal(2, dataPointIndex);
                const spending = getVal(3, dataPointIndex);
                const net = (rent + fnb + others) - spending;

                const currentDate = categories[dataPointIndex] || 'Unknown Date';

                return `
                    <div class="px-2 py-1">
                       <strong>${currentDate}</strong><br/>
                        Rent: Rp.${rent.toLocaleString()}<br/>
                        FnB: Rp.${fnb.toLocaleString()}<br/>
                        Others: Rp.${others.toLocaleString()}<br/>
                        Spending: Rp.${spending.toLocaleString()}<br/>
                        <b>Net: Rp.${net.toLocaleString()}</b>
                    </div>
                `;
            }
        },
        series: [
            { name: "Rent", data: rentData },
            { name: "FnB", data: fnbData },
            { name: "Others", data: othersData },
            { name: "Spending", data: spendingData }
        ],
        xaxis: {
            categories: categories,
            labels: {
                style: {
                    fontSize: '10px'
                }
            }
        },
        colors: chartColors,
        yaxis: {
            labels: {
                style: {
                    fontSize: '10px'
                },
                formatter: formatRupiahSingkat
            }
        },
        legend: {
            position: "bottom"
        },
        fill: {
            opacity: 1
        }
    };

    const chartElement = document.querySelector("#stacked-column-charts");
    if (chartElement) {
        chartElement.innerHTML = '';
        new ApexCharts(chartElement, options).render();
    }
}

// ========== PS TABLE UPDATE FUNCTIONS ==========
function updatePsTable(psData, totals) {
    const tableBody = document.getElementById('ps-table-body');
    if (!tableBody) {
        console.error('PS table body not found');
        return;
    }
    
    let tableHTML = '';
    
    psData.forEach(row => {
        const isActive = row.total_hours > 0;
        const utilizationColor = row.utilization_rate > 10 ? 'success' : 
                               row.utilization_rate > 5 ? 'warning' : 'danger';
        const hoursColor = row.total_hours > 10 ? 'success' : 
                          row.total_hours > 5 ? 'warning' : 'secondary';
        
        tableHTML += `
            <tr>
                <td class="text-start">
                    ${escapeHtml(row.console_name)}
                    
                </td>
                <td>
                    <span class="badge bg-${hoursColor}">${row.total_hours} hrs</span>
                </td>
                <td>${row.total_rentals}x</td>
                <td>${row.avg_duration} hrs</td>
                <td>${row.active_days} days</td>
                <td>${row.avg_hours_per_day} hrs/day</td>
                <td><strong>Rp ${formatNumber(row.revenue)}</strong></td>
                <td>Rp ${formatNumber(row.revenue_per_hour)}</td>
                <td>
                    <div class="progress" style="height: 20px; background-color: #EEEEEE;">
                        <div class="progress-bar bg-${utilizationColor}" 
                             role="progressbar" 
                             style="width: ${Math.min(row.utilization_rate * 2, 100)}%">
                            ${row.utilization_rate}%
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
        <tr class="fw-bold">
            <td class="text-start">TOTAL OPERATIONS</td>
            <td>${totals.total_hours} hrs</td>
            <td>${totals.total_rentals}x</td>
            <td>-</td>
            <td>${totals.active_days} days</td>
            <td>${totals.avg_hours_per_day} hrs/day</td>
            <td>Rp ${formatNumber(totals.total_revenue)}</td>
            <td>Rp ${formatNumber(totals.avg_revenue_per_hour)}</td>
            <td>${totals.utilization_rate}%</td>
        </tr>
    `;
    
    tableBody.innerHTML = tableHTML;
}

function updatePsSummaryCards(totals, statistics) {
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    
    updateElement("ps-total-revenue", `Rp ${formatNumber(totals.total_revenue)}`);
    updateElement("ps-total-hours", `${totals.total_hours.toFixed(2)} hours`);
    updateElement("ps-active-consoles", `${totals.active_consoles}/${totals.total_consoles}`);
    updateElement("ps-avg-revenue-hour", `Rp ${formatNumber(totals.avg_revenue_per_hour)}`);
}

function updatePerformanceInsights(performance) {
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    
    updateElement("most-used-console", 
        `${performance.most_used_console.console_name} (${performance.most_used_console.total_hours} hrs)`);
    updateElement("highest-revenue-console", 
        `${performance.highest_revenue_console.console_name} (Rp ${formatNumber(performance.highest_revenue_console.revenue)})`);
    updateElement("best-utilization-console", 
        `${performance.best_utilization_console.console_name} (${performance.best_utilization_console.utilization_rate}%)`);
    updateElement("least-used-console", 
        `${performance.least_used_console.console_name} (${performance.least_used_console.total_hours} hrs)`);
}

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', function() {
    loadAllData();
    
    const dateStartElement = document.getElementById('date_start');
    const dateEndElement = document.getElementById('date_end');
    
    if (dateStartElement) {
        dateStartElement.addEventListener('change', loadAllData);
    }
    
    if (dateEndElement) {
        dateEndElement.addEventListener('change', loadAllData);
    }
});

// Backward compatibility
function loadChartData() {
    loadAllData();
}

function refreshAllData() {
    loadAllData();
}
</script>

<div class="page-content">

    <div class="container-fluid">
        <!-- start page title -->
    
<!-- HTML Structure (to be integrated into your existing page) -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup-content">
        <div class="popup-header">
            <button class="close-btn" id="closeBtn" onclick="closePopup()">&times;</button>
            <div class="popup-title">Payment Methods Details</div>
            <div class="popup-subtitle">Income, Spending & Net per Method</div>
        </div>
        
        <div class="popup-body">
            <!-- Summary Stats Section -->
            <div class="summary-container">
                <!-- Content akan diisi oleh JavaScript berdasarkan data API -->
            </div>
            
            <!-- Payment Grid akan di-generate secara dinamis -->
            <div class="payment-grid">
                <!-- Content akan diisi oleh JavaScript berdasarkan data API -->
            </div>
        </div>

        <!-- Total Section - Moved outside popup-body -->
        <div class="total-section">
            <div class="total-label">Total Net Profit</div>
            <div class="total-amount">Rp <span id="popup-total">0</span></div>
        </div>
    </div>
</div>
        <div class="row">
            
            <div class="col-12">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Summary Report</h4>
                        
                        <!-- Container tombol di kanan -->
                        <div class="btn-icon-group" style="display: flex; gap: 10px;">
                            <div class="d-flex justify-content-end align-items-end mb-0">
                                <div class="me-2">
                                    <input type="date" id="date_start" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                                </div>
                                <div class="me-2">-</div>
                                <div class="me-2">
                                    <input type="date" id="date_end" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="me-2">
                                    <button class="btn btn-sm btn-primary" onclick="refreshAllData()">
                                        <i class="bx bx-paper-plane"></i>                                    </button>
                                </div>
                            </div>
                        </div>
                           <!-- Loading Overlay -->
                        <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(43, 43, 43, 0.65); z-index: 9999; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-3">
                                    <h6 class="text-white">Loading data...</h6>
                                    <p class="text-white mb-0">Please wait while we fetch your data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-4">
                                <img src="<?=$logox?>" alt="Logo" width="64" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted">
                                    <h5><?=$merchand?></h5>
                                    <p class="mb-1"><?=$cabang?></p>
                                    <p class="mb-0"><?=$license?></p>
                                </div>
                            </div>
                            <!-- <div class="dropdown ms-2">
                                <a class="text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    <div class="card-body border-top" id="nett-profit-container">
                        <div class="row">
    <div class="col-sm-12">
        <div class="p-3 rounded bg-light text-center shadow-sm">
            <p class="text-muted mb-1" style="font-size: 1 rem;">Net Profit</p>
            <h2 id="total-nett" class="mb-0 fw-bold text-info">
                <span class="me-1 text-muted" style="font-size: 1rem;">Rp</span>0
            </h2>
        </div>
    </div>
</div>

                    </div>
                    <div class="card-body border-top">
    <p class="text-muted mb-1">In this period</p>
    <div class="text-center">
        <div class="row">
            <div class="col-4">
                <div>
                   <div style="position: relative; display: inline-block; font-size: 24px; color: #17a2b8;" class="mb-2">
    <i class="fas fa-calendar-alt"></i>
    <span id="total-days" style="
        position: absolute;
        bottom: 0;
        right: 0;
        background: #FF8282;
        color: white;
        border-radius: 25%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
        transform: translate(40%, 25%);
        ">
        0
    </span>
</div>

                    <p class="text-muted mb-2">Total Days</p>
                 
                </div>
            </div>
            <div class="col-4">
                <div>
                    <div style="position: relative; display: inline-block; font-size: 24px; color: #17a2b8;" class="mb-2">
                        <i class="bx bx-calendar-event"></i>
                         <span id="work-days" style="
        position: absolute;
        bottom: 0;
        right: 0;
        background: #FF8282;
        color: white;
        border-radius: 25%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
        transform: translate(40%, 25%);
        ">
        0
    </span>
                    </div>
                    <p class="text-muted mb-2">Work Days</p>
                    
                </div>
            </div>
            <div class="col-4">
                <div>
                    <div style="position: relative; display: inline-block; font-size: 24px; color: #17a2b8;" class="mb-2">
                        <i class="bx bx-wallet"></i>
                        <span id="total-trans" style="
        position: absolute;
        bottom: 0;
        right: 0;
        background: #FF8282;
        color: white;
        border-radius: 25%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
        transform: translate(40%, 25%);
        ">
        0
    </span>
                    </div>
                    <p class="text-muted mb-2">Total Trans</p>
                    
                </div>
            </div>
        </div>
    </div>
</div>
</div>
                <div class="col-xl-12 col-md-12">
                                <div class="card jobs-categories">
                                    <div class="card-body">
                                        <a href="#!" class="px-3 py-2 rounded bg-light bg-opacity-50 d-block mb-2">Total Rental Hours <span class="badge text-bg-info float-end bg-opacity-100"  id="ps-total-hours"></span></a>
                                        <a href="#!" class="px-3 py-2 rounded bg-light bg-opacity-50 d-block mb-2">Active Rentals <span class="badge text-bg-info float-end bg-opacity-100" id="ps-active-consoles"></span></a>
                                        <a href="#!" class="px-3 py-2 rounded bg-light bg-opacity-50 d-block mb-2">Avg Revenue/Hour <span class="badge text-bg-info float-end bg-opacity-100" id="ps-avg-revenue-hour"></span></a>
                                        
                                    </div>
                                </div>
                            </div>
            </div>

            <div class="col-xl-8">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-1 align-self-center">
                                        <i class="bx bx-joystick h3 text-info mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Rentals</p>
                                        <h5 id="total-rentals" class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp </span>0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-1 align-self-center">
                                        <i class="bx bxs-basket h3 text-warning mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">FnB</p>
                                        <h5 id="total-fnb" class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp </span>0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-1 align-self-center">
    <i class="bx bx-cube h3 mb-0" style="color: #775DD0;"></i>
</div>

                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Others</p>
                                        <h5 id="total-product" class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp </span>0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-1 align-self-center">
                                        <i class="bx bx-archive-out h3 text-danger mb-0"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1">Spending</p>
                                        <h5 id="total-spending" class="mb-0 font-size-13"><span class="font-size-10 text-muted">Rp </span>0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-sm-flex flex-wrap">
                            <h4 class="card-title mb-4">Net Profit</h4>
                            <div class="ms-auto">
                                <p class="mb-0">
                                    <span id="highest-summary" class="badge badge-soft-success me-2" title="">
                                        <i class="bx bx-trending-up align-bottom me-1"></i> Rp.0 highest
                                    </span>
                                    <span id="lowest-summary" class="badge badge-soft-danger" title="">
                                        <i class="bx bx-trending-down align-bottom me-1"></i> Rp.0 lowest
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div id="stacked-column-charts" class="apex-charts" data-colors='["--bs-primary", "--bs-success", "--bs-danger"]'></div>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- PS Data Table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Rentals Operations</h4>
                        <div class="mt-4">
                          <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                            <thead>
                                        <tr>
                                            <th ></th>
                                            <th>Total Hours</th>
                                            <th>Total Rentals</th>
                                            <th>Avg. Duration</th>
                                            <th>Active Days</th>
                                            <th>Avg. Hours/Day</th>
                                            <th>Revenue</th>
                                            <th>Revenue/Hour</th>
                                            <th>Utilization %</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ps-table-body">
                                        <!-- Data akan diisi oleh JavaScript -->
                                        <tr scope="row">
                                            <td colspan="9" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading PS data...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Insights -->
        <div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title mb-1">Most Used Console</h6>
                <p class="mb-0"><strong id="most-used-console">-</strong></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title mb-1">Highest Revenue</h6>
                <p class="mb-0"><strong id="highest-revenue-console">-</strong></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title mb-1">Best Utilization</h6>
                <p class="mb-0"><strong id="best-utilization-console">-</strong></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title mb-1">Least Used</h6>
                <p class="mb-0"><strong id="least-used-console">-</strong></p>
            </div>
        </div>
    </div>
</div>

        <!-- end row -->
        
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->

<!-- JAVASCRIPT -->
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- crypto-wallet init -->
<script src="assets/js/pages/crypto-wallet.init.js"></script>

<script src="assets/js/app.js"></script>

<script>
// ========== GLOBAL VARIABLES ==========
let currentPaymentData = {
    cash: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
    ewalet: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
    debit: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
    qris: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 }
};

let currentTotalData = {
    net_profit: 0,
    total_amount: 0,
    total_gross: 0,
    total_promo: 0,
    total_spending: 0,
    total_net: 0
};

// ========== UTILITY FUNCTIONS ==========
function formatRupiahSingkat(val) {
    if (val >= 1000000) {
        return (val / 1000000).toFixed(1) + 'M';
    } else if (val >= 1000) {
        return (val / 1000).toFixed(1) + 'K';
    } else {
        return val;
    }
}

function formatNumber(num) {
    return num.toLocaleString('id-ID');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// ========== PAYMENT METHODS MAPPING ==========
function mapPaymentMethodName(methodName) {
    const mapping = {
        'cash': 'Cash',
        'ewalet': 'E-Wallet',
        'debit': 'Bank Transfer',
        'qris': 'QRIS'
    };
    return mapping[methodName] || methodName;
}

function mapPaymentMethodIcon(methodName) {
    const iconMapping = {
        'cash': 'bx-money',
        'ewalet': 'bx-wallet',
        'debit': 'bx-credit-card',
        'qris': 'bx-qr'
    };
    return iconMapping[methodName] || 'bx-money';
}

function mapPaymentMethodClass(methodName) {
    const classMapping = {
        'cash': 'cash',
        'ewalet': 'ewallet',
        'debit': 'debit',
        'qris': 'credit'
    };
    return classMapping[methodName] || 'cash';
}

// ========== LOADING FUNCTIONS ==========
function showLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
    
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    if (dateStart) dateStart.disabled = true;
    if (dateEnd) dateEnd.disabled = false;
    
    showLoadingTable();
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
    
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    if (dateStart) dateStart.disabled = false;
    if (dateEnd) dateEnd.disabled = false;
}

function showLoadingTable() {
    const tableBody = document.getElementById('ps-table-body');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading PS data...</span>
                </td>
            </tr>
        `;
    }
}

function showError(message) {
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    updateElement("highest-summary", '<i class="bx bx-error"></i> Error loading data');
    updateElement("lowest-summary", '<i class="bx bx-error"></i> Error loading data');
    
    const tableBody = document.getElementById('ps-table-body');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4 text-danger">
                    <i class="bx bx-error-circle"></i>
                    <span class="ms-2">${message}</span>
                </td>
            </tr>
        `;
    }
}

// ========== MAIN LOAD FUNCTION ==========
function loadAllData() {
    const dateStartElement = document.getElementById('date_start');
    const dateEndElement = document.getElementById('date_end');
    
    if (!dateStartElement || !dateEndElement) {
        console.error('Date input elements not found');
        return;
    }
    
    const dateStart = dateStartElement.value;
    const dateEnd = dateEndElement.value;
    
    showLoading();
    
    const url = `./xapi/ajax_summary.php?date_start=${dateStart}&date_end=${dateEnd}`;
    
    fetch(url)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                // Update Chart Data
                updateChartData(response.data.data, response.data.summary, response.data.statistics);
                
                // Update Payment Methods Data
                if (response.data.payment_methods) {
                    updatePaymentMethodsData(response.data.payment_methods);
                }
                
                // Update PS Table Data
                if (response.data.ps_data) {
                    updatePsTable(response.data.ps_data.rows, response.data.ps_data.totals);
                    updatePsSummaryCards(response.data.ps_data.totals, response.data.ps_data.statistics);
                    
                    if (response.data.ps_data.statistics && response.data.ps_data.statistics.performance) {
                        updatePerformanceInsights(response.data.ps_data.statistics.performance);
                    }
                }
            } else {
                showError('Failed to load data');
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            showError('Error loading data');
        })
        .finally(() => {
            hideLoading();
        });
}

// ========== PAYMENT METHODS UPDATE - ENHANCED ==========
function updatePaymentMethodsData(paymentMethodsData) {
    // Reset current payment data
    currentPaymentData = {
        cash: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
        ewalet: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
        debit: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 },
        qris: { gross: 0, promo: 0, spending: 0, net: 0, income_count: 0, spending_count: 0 }
    };
    
    let totalGross = 0;
    let totalPromo = 0;
    let totalSpending = 0;
    let totalNet = 0;
    
    // Update with API data
    if (paymentMethodsData.data) {
        paymentMethodsData.data.forEach(method => {
            const methodKey = method.payment_method === 'ewalet' ? 'ewalet' : method.payment_method;
            if (currentPaymentData.hasOwnProperty(methodKey)) {
                currentPaymentData[methodKey] = {
                    gross: method.total_amount || 0,
                    promo: method.total_promo || 0,
                    spending: method.total_spending || 0,
                    net: method.net_amount || 0,
                    income_count: method.transaction_count || 0,
                    spending_count: method.spending_count || 0
                };
                
                totalGross += method.total_amount || 0;
                totalPromo += method.total_promo || 0;
                totalSpending += method.total_spending || 0;
                totalNet += method.net_amount || 0;
            }
        });
    }
    
    // Update total data from API
    currentTotalData = {
        net_profit: paymentMethodsData.totals ? paymentMethodsData.totals.total_net : totalNet,
        total_amount: paymentMethodsData.totals ? paymentMethodsData.totals.total_amount : totalGross,
        total_gross: totalGross,
        total_promo: totalPromo,
        total_spending: paymentMethodsData.totals ? paymentMethodsData.totals.total_spending : totalSpending,
        total_net: paymentMethodsData.totals ? paymentMethodsData.totals.total_net : totalNet
    };
    
    console.log('Updated payment data:', currentPaymentData);
    console.log('Updated total data:', currentTotalData);
}

// ========== CHART UPDATE FUNCTIONS ==========
function updateChartData(data, summary, statistics) {
    const categories = data.map(item => item.date);
    const rentData = data.map(item => item.rent);
    const fnbData = data.map(item => item.fnb);
    const othersData = data.map(item => item.others);
    const spendingData = data.map(item => item.spending);

    const totalRent = summary.total.rent;
    const totalFnB = summary.total.fnb;  
    const totalProduct = summary.total.others;
    const totalSpending = summary.total.spending;
   
    const totalPromo = summary.total.promo;
    const totalGross = summary.total.net;
    const totalNett = summary.total.net - totalPromo;

    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };

    updateElement("total-rentals", `<span class="font-size-10 text-muted">Rp </span>${totalRent.toLocaleString()}`);
    updateElement("total-product", `<span class="font-size-10 text-muted">Rp </span>${totalProduct.toLocaleString()}`);
    updateElement("total-fnb", `<span class="font-size-10 text-muted">Rp </span>${totalFnB.toLocaleString()}`);
    updateElement("total-spending", `<span class="font-size-10 text-muted">Rp </span>${totalSpending.toLocaleString()}`);
    updateElement("total-nett", `<span class="font-size-10 text-muted">Rp </span>${totalNett.toLocaleString()}`);
    updateElement("total-promo", `<span class="text-warning">(<span class="font-size-10 text-warning">Rp </span>${totalPromo.toLocaleString()})</span>`);
    updateElement("total-gross", `<span class="font-size-10 text-muted">Rp </span>${totalGross.toLocaleString()}`);

    if (statistics) {
        updateElement("total-days", `${statistics.total_days}`);
        updateElement("work-days", `${statistics.work_days}`);
        updateElement("total-trans", statistics.total_transactions.toLocaleString());
    }

    if (summary.max && summary.max.net && summary.min && summary.min.net) {
        const highest = summary.max.net;
        const lowest = summary.min.net;
        
        updateElement("highest-summary", `<i class="bx bx-trending-up align-bottom me-1"></i> Rp.${highest.value.toLocaleString()} highest`);
        updateElement("lowest-summary", `<i class="bx bx-trending-down align-bottom me-1"></i> Rp.${lowest.value.toLocaleString()} lowest`);
        
        const highestElement = document.getElementById("highest-summary");
        const lowestElement = document.getElementById("lowest-summary");
        if (highestElement) highestElement.title = highest.date;
        if (lowestElement) lowestElement.title = lowest.date;
    }

    updateApexChart(categories, rentData, fnbData, othersData, spendingData);
}

function updateApexChart(categories, rentData, fnbData, othersData, spendingData) {
    const chartColors = getChartColorsArray("stacked-column-charts");
    
    const options = {
        chart: {
            height: 360,
            type: "bar",
            stacked: true,
            toolbar: { show: false },
            zoom: { enabled: true }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "15%",
                endingShape: "rounded"
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '10px',
                colors: ['#000'],
            },
            formatter: formatRupiahSingkat,
        },
        tooltip: {
            shared: true,
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const getVal = (s, i) => (series[s] && series[s][i] != null) ? series[s][i] : 0;

                const rent = getVal(0, dataPointIndex);
                const fnb = getVal(1, dataPointIndex);
                const others = getVal(2, dataPointIndex);
                const spending = getVal(3, dataPointIndex);
                const net = (rent + fnb + others) - spending;

                const currentDate = categories[dataPointIndex] || 'Unknown Date';

                return `
                    <div class="px-2 py-1">
                       <strong>${currentDate}</strong><br/>
                        Rent: Rp.${rent.toLocaleString()}<br/>
                        FnB: Rp.${fnb.toLocaleString()}<br/>
                        Others: Rp.${others.toLocaleString()}<br/>
                        Spending: Rp.${spending.toLocaleString()}<br/>
                        <b>Net: Rp.${net.toLocaleString()}</b>
                    </div>
                `;
            }
        },
        series: [
            { name: "Rent", data: rentData },
            { name: "FnB", data: fnbData },
            { name: "Others", data: othersData },
            { name: "Spending", data: spendingData }
        ],
        xaxis: {
            categories: categories,
            labels: {
                style: {
                    fontSize: '10px'
                }
            }
        },
        colors: chartColors,
        yaxis: {
            labels: {
                style: {
                    fontSize: '10px'
                },
                formatter: formatRupiahSingkat
            }
        },
        legend: {
            position: "bottom"
        },
        fill: {
            opacity: 1
        }
    };

    const chartElement = document.querySelector("#stacked-column-charts");
    if (chartElement) {
        chartElement.innerHTML = '';
        new ApexCharts(chartElement, options).render();
    }
}

// ========== PS TABLE UPDATE FUNCTIONS ==========
function updatePsTable(psData, totals) {
    const tableBody = document.getElementById('ps-table-body');
    if (!tableBody) {
        console.error('PS table body not found');
        return;
    }
    
    let tableHTML = '';
    
    psData.forEach(row => {
        const isActive = row.total_hours > 0;
        const utilizationColor = row.utilization_rate > 10 ? 'success' : 
                               row.utilization_rate > 5 ? 'warning' : 'danger';
        const hoursColor = row.total_hours > 10 ? 'success' : 
                          row.total_hours > 5 ? 'warning' : 'secondary';
        
        tableHTML += `
            <tr>
                <td class="text-start">
                    ${escapeHtml(row.console_name)}
                </td>
                <td>
                    <span class="badge bg-${hoursColor}">${row.total_hours} hrs</span>
                </td>
                <td>${row.total_rentals}x</td>
                <td>${row.avg_duration} hrs</td>
                <td>${row.active_days} days</td>
                <td>${row.avg_hours_per_day} hrs/day</td>
                <td><strong>Rp ${formatNumber(row.revenue)}</strong></td>
                <td>Rp ${formatNumber(row.revenue_per_hour)}</td>
                <td>
                    <div class="progress" style="height: 20px; background-color: #EEEEEE;">
                        <div class="progress-bar bg-${utilizationColor}" 
                             role="progressbar" 
                             style="width: ${Math.min(row.utilization_rate * 2, 100)}%">
                            ${row.utilization_rate}%
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
        <tr class="fw-bold">
            <td class="text-start">TOTAL OPERATIONS</td>
            <td>${totals.total_hours} hrs</td>
            <td>${totals.total_rentals}x</td>
            <td>-</td>
            <td>${totals.active_days} days</td>
            <td>${totals.avg_hours_per_day} hrs/day</td>
            <td>Rp ${formatNumber(totals.total_revenue)}</td>
            <td>Rp ${formatNumber(totals.avg_revenue_per_hour)}</td>
            <td>${totals.utilization_rate}%</td>
        </tr>
    `;
    
    tableBody.innerHTML = tableHTML;
}

function updatePsSummaryCards(totals, statistics) {
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    
    updateElement("ps-total-revenue", `Rp ${formatNumber(totals.total_revenue)}`);
    updateElement("ps-total-hours", `${totals.total_hours.toFixed(2)} hours`);
    updateElement("ps-active-consoles", `${totals.active_consoles}/${totals.total_consoles}`);
    updateElement("ps-avg-revenue-hour", `Rp ${formatNumber(totals.avg_revenue_per_hour)}`);
}

function updatePerformanceInsights(performance) {
    const updateElement = (id, content) => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = content;
        }
    };
    
    updateElement("most-used-console", 
        `${performance.most_used_console.console_name} (${performance.most_used_console.total_hours} hrs)`);
    updateElement("highest-revenue-console", 
        `${performance.highest_revenue_console.console_name} (Rp ${formatNumber(performance.highest_revenue_console.revenue)})`);
    updateElement("best-utilization-console", 
        `${performance.best_utilization_console.console_name} (${performance.best_utilization_console.utilization_rate}%)`);
    updateElement("least-used-console", 
        `${performance.least_used_console.console_name} (${performance.least_used_console.total_hours} hrs)`);
}

// ========== POPUP FUNCTIONS - ENHANCED ==========
function generatePaymentGrid() {
    let gridHTML = '';
    let animationDelay = 0.1;
    
    // Generate payment items based on current data
    Object.keys(currentPaymentData).forEach(methodKey => {
        const methodData = currentPaymentData[methodKey];
        const displayName = mapPaymentMethodName(methodKey);
        const iconClass = mapPaymentMethodIcon(methodKey);
        const cssClass = mapPaymentMethodClass(methodKey);
        
        // Show all methods, even if no transactions
        const totalTransactions = methodData.income_count + methodData.spending_count;
        
        gridHTML += `
            <div class="payment-item ${cssClass}" style="animation-delay: ${animationDelay}s">
                <div class="payment-info">
                    <div class="payment-icon ${cssClass}"><i class="bx ${iconClass} text-white"></i></div>
                    <div>
                        <div class="payment-label">${displayName}</div>
                        <div class="payment-count">${methodData.income_count}tx in | ${methodData.spending_count}tx out</div>
                    </div>
                </div>
                <div class="payment-amounts">
                    <div class="payment-gross" data-amount="${methodData.gross}">
                        <span class="amount-label">Income:</span> +Rp <span class="amount-loading"></span>
                    </div>
                    ${methodData.promo > 0 ? `
                        <div class="payment-promo" data-amount="${methodData.promo}">
                            <span class="amount-label">Promo:</span> -Rp <span class="amount-loading-promo"></span>
                        </div>
                    ` : ''}
                    ${methodData.spending > 0 ? `
                        <div class="payment-spending" data-amount="${methodData.spending}">
                            <span class="amount-label">Spending:</span> -Rp <span class="amount-loading-spending"></span>
                        </div>
                    ` : ''}
                    <div class="payment-net" data-amount="${methodData.net}">
                        <strong><span class="amount-label">Net:</span> Rp <span class="amount-loading-net"></span></strong>
                    </div>
                </div>
            </div>
        `;
        animationDelay += 0.1;
    });
    
    return gridHTML;
}

function generateSummaryStats() {
    return `
        <div class="summary-stats">
            <div class="summary-stats-grid">
                <div class="summary-stat">
                    <div class="summary-stat-value text-info"><span style="font-size:9px">Rp</span><span style="font-size:12px"> ${formatNumber(currentTotalData.total_gross)}</span></div>
                    <div class="summary-stat-label text-info">Income</div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-value text-danger"><span style="font-size:9px">Rp</span> <span style="font-size:12px"> ${formatNumber(currentTotalData.total_spending)}</span></div>
                    <div class="summary-stat-label text-danger">Spending</div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-value text-warning"><span style="font-size:9px">Rp</span> <span style="font-size:12px"> ${formatNumber(currentTotalData.total_promo)}</span></div>
                    <div class="summary-stat-label text-warning">Promo</div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-value text-success"><span style="font-size:9px">Rp</span> <span style="font-size:12px"> ${formatNumber(currentTotalData.total_net)}</span></div>
                    <div class="summary-stat-label text-success">Net</div>
                </div>
            </div>
        </div>
    `;
}

function animateNumber(element, finalValue, duration = 1000) {
    let startValue = 0;
    const increment = finalValue / (duration / 16);
    
    const animate = () => {
        startValue += increment;
        if (startValue < finalValue) {
            element.textContent = Math.floor(startValue).toLocaleString('id-ID');
            requestAnimationFrame(animate);
        } else {
            element.textContent = finalValue.toLocaleString('id-ID');
        }
    };
    
    animate();
}

function showPopup() {
    const nettProfitContainer = document.getElementById('nett-profit-container');
    const popupOverlay = document.getElementById('popupOverlay');
    
    nettProfitContainer.classList.add('clicked');
    popupOverlay.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Update popup content with current data
    const paymentGrid = document.querySelector('.payment-grid');
    if (paymentGrid) {
        paymentGrid.innerHTML = generatePaymentGrid();
    }
    
    // Update summary stats
    const summaryContainer = document.querySelector('.summary-container');
    if (summaryContainer) {
        summaryContainer.innerHTML = generateSummaryStats();
    }
    
    // Use total from API
    const totalAmount = currentTotalData.total_net;
    
    console.log('Showing popup with total:', totalAmount);
    console.log('Current payment data:', currentPaymentData);
    
    // Animate payment items
    setTimeout(() => {
        const paymentItems = document.querySelectorAll('.payment-item');
        paymentItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('animate');
                
                // Animate amounts after item appears
                setTimeout(() => {
                    // Animate gross amount
                    const grossElement = item.querySelector('.payment-gross');
                    if (grossElement) {
                        const grossAmount = parseInt(grossElement.getAttribute('data-amount'));
                        const grossLoadingElement = grossElement.querySelector('.amount-loading');
                        animateAmount(grossLoadingElement, grossAmount);
                    }
                    
                    // Animate promo amount
                    const promoElement = item.querySelector('.payment-promo');
                    if (promoElement) {
                        const promoAmount = parseInt(promoElement.getAttribute('data-amount'));
                        const promoLoadingElement = promoElement.querySelector('.amount-loading-promo');
                        animateAmount(promoLoadingElement, promoAmount);
                    }
                    
                    // Animate spending amount
                    const spendingElement = item.querySelector('.payment-spending');
                    if (spendingElement) {
                        const spendingAmount = parseInt(spendingElement.getAttribute('data-amount'));
                        const spendingLoadingElement = spendingElement.querySelector('.amount-loading-spending');
                        animateAmount(spendingLoadingElement, spendingAmount);
                    }
                    
                    // Animate net amount
                    const netElement = item.querySelector('.payment-net');
                    if (netElement) {
                        const netAmount = parseInt(netElement.getAttribute('data-amount'));
                        const netLoadingElement = netElement.querySelector('.amount-loading-net');
                        animateAmount(netLoadingElement, netAmount);
                    }
                    
                }, 300);
                
            }, index * 150);
        });
        
        // Animate total amount using API data
        setTimeout(() => {
            const popupTotalElement = document.getElementById('popup-total');
            if (popupTotalElement) {
                console.log('Animating total element with value:', totalAmount);
                // Reset to 0 first
                popupTotalElement.textContent = '0';
                animateNumber(popupTotalElement, totalAmount, 1000);
            } else {
                console.error('popup-total element not found!');
                // Fallback: try to find it again
                setTimeout(() => {
                    const fallbackElement = document.getElementById('popup-total');
                    if (fallbackElement) {
                        console.log('Found popup-total on retry');
                        fallbackElement.textContent = totalAmount.toLocaleString('id-ID');
                    }
                }, 100);
            }
        }, 800);
        
    }, 200);
    
    // Remove clicked class
    setTimeout(() => {
        nettProfitContainer.classList.remove('clicked');
    }, 300);
}

// Helper function to animate individual amounts
function animateAmount(element, finalValue) {
    if (!element) return;
    
    let currentAmount = 0;
    const increment = finalValue / 30;
    
    const countUp = () => {
        currentAmount += increment;
        if (currentAmount < finalValue) {
            element.textContent = Math.floor(currentAmount).toLocaleString('id-ID');
            requestAnimationFrame(countUp);
        } else {
            element.textContent = finalValue.toLocaleString('id-ID');
            element.classList.remove('amount-loading', 'amount-loading-promo', 'amount-loading-spending', 'amount-loading-net');
        }
    };
    
    countUp();
}

function hidePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    popupOverlay.classList.remove('show');
    document.body.style.overflow = 'auto';
    
    // Reset animations
    setTimeout(() => {
        const paymentItems = document.querySelectorAll('.payment-item');
        paymentItems.forEach(item => {
            item.classList.remove('animate');
            
            // Reset all loading elements
            const loadingElements = item.querySelectorAll('.amount-loading, .amount-loading-promo, .amount-loading-spending, .amount-loading-net');
            loadingElements.forEach(element => {
                element.classList.add('amount-loading');
                element.textContent = '';
            });
        });
        
        const popupTotalElement = document.getElementById('popup-total');
        if (popupTotalElement) {
            popupTotalElement.textContent = '0';
        }
    }, 400);
}

function closePopup() {
    hidePopup();
}

// ========== EVENT LISTENERS ==========
document.addEventListener('DOMContentLoaded', function() {
    loadAllData();
    
    const dateStartElement = document.getElementById('date_start');
    const dateEndElement = document.getElementById('date_end');
    
    if (dateStartElement) {
        dateStartElement.addEventListener('change', loadAllData);
    }
    
    if (dateEndElement) {
        dateEndElement.addEventListener('change', loadAllData);
    }
    
    // Popup event listeners
    const nettProfitContainer = document.getElementById('nett-profit-container');
    const popupOverlay = document.getElementById('popupOverlay');
    const closeBtn = document.getElementById('closeBtn');
    
    if (nettProfitContainer) {
        nettProfitContainer.addEventListener('click', showPopup);
        
        nettProfitContainer.addEventListener('mouseenter', () => {
            if (!popupOverlay.classList.contains('show')) {
                nettProfitContainer.style.transform = 'translateY(-3px) scale(1.02)';
            }
        });
        
        nettProfitContainer.addEventListener('mouseleave', () => {
            if (!popupOverlay.classList.contains('show')) {
                nettProfitContainer.style.transform = 'translateY(0) scale(1)';
            }
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
    }
    
    // Close popup when clicking outside
    if (popupOverlay) {
        popupOverlay.addEventListener('click', (e) => {
            if (e.target === popupOverlay) {
                hidePopup();
            }
        });
    }
    
    // Close popup with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popupOverlay && popupOverlay.classList.contains('show')) {
            hidePopup();
        }
    });
});

// Backward compatibility
function loadChartData() {
    loadAllData();
}

function refreshAllData() {
    loadAllData();
}

function getChartColorsArray(chartId) {
    if (document.getElementById(chartId)) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        if (colors) {
            colors = JSON.parse(colors);
            return colors.map(function(value) {
                var color = value.replace(" ", "");
                if (color.indexOf(",") === -1) {
                    var cssColor = getComputedStyle(document.documentElement).getPropertyValue(color);
                    return cssColor.trim() || color;
                }
                var parts = color.split(",");
                if (parts.length !== 2) return color;
                return "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(parts[0]) + "," + parts[1] + ")";
            });
        }
    }
    return ["#008FFB", "#FEB019", "#775DD0", "#FF4560"];
}
</script>