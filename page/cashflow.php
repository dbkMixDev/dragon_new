<div class="page-content">
    <div class="container-fluid">
 
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
  <div class="page-title-box d-sm-flex align-items-center justify-content-between">
    <h4 class="mb-sm-0 font-size-18">Cash Flow</h4>

    <!-- Container tombol di kanan -->
    <div class="btn-icon-group" style="display: flex; gap: 10px;">
        
      
  <div class="d-flex justify-content-end align-items-end mb-0">
                        <div class="me-2">
                            <!-- <label for="date_start" class="form-label mb-0">Start Date :</label> -->
                            <input type="date" id="date_start" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="me-2">-</div>
                        <div class="me-2">
                            <!-- <label for="date_end" class="form-label mb-0">End Date :</label> -->
                            <input type="date" id="date_end" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                        <!-- <div class="align-self-end">
                            <button id="btnFilter" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt"></i> Filter</button>
                        </div> -->
                    </div>


    </div>
    
  </div>
</div>

<!-- Jangan lupa load boxicons kalau belum -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

        </div>
        <!-- end page title -->

        <div class="col-12">
            <div class="card">
                <div class="card-body">

                  
                    <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Date Trans</th>
                                <th>Details</th>
                               
                                <th>IN</th>
                                <th>OUT</th>
                                 <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>

        <!-- Required datatable js -->
        <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/jszip/jszip.min.js"></script>
        <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <!-- Responsive examples -->
        <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="assets/js/pages/datatables.init.js"></script>

        <script src="assets/js/app.js"></script>
    <script>



let transactionTable;

$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#datatable-buttons')) {
        $('#datatable-buttons').DataTable().destroy();
    }

    // Initialize DataTable with configuration
    transactionTable = $('#datatable-buttons').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true,
        processing: true,
        serverSide: false,
        
        ajax: {
            url: 'controller/fetch_cashflow.php',
            type: 'GET',
            data: function(d) {
                // Clear default DataTables parameters we don't need
                delete d.draw;
                delete d.columns;
                delete d.order;
                delete d.start;
                delete d.length;
                delete d.search;
                
                // Add our date parameters
                d.start = $('#date_start').val() || new Date().toISOString().slice(0, 8) + '01';
                d.end = $('#date_end').val() || new Date().toISOString().slice(0, 10);
            },
            dataSrc: function(json) {
                console.log('Server response:', json);
                
                // Handle server response
                if (json.error) {
                    console.error('Server Error:', json.error);
                    // showAlert('Error loading transactions: ' + json.error, 'danger');
                    return [];
                }
                
                // Validate data structure
                if (!json.data || !Array.isArray(json.data)) {
                    console.warn('Invalid data structure received:', json);
                    // showAlert('Invalid data format received from server', 'warning');
                    return [];
                }
                
                // Show success message
                if (json.data.length > 0) {
                    // showAlert(`Successfully loaded ${json.data.length} transaction(s)`, 'success');
                } else {
                    // showAlert('No transactions found for the selected date range', 'info');
                }
                
                return json.data;
            },
            error: function(xhr, status, error) {
                console.error('Ajax Error:', {xhr, status, error});
                // showAlert('Failed to load transactions. Please check your connection and try again.', 'danger');
                return [];
            }
        },
        columns: [
             { 
                data: 'no_urut',
                title: 'No',
                defaultContent: '',
                width: '5%',
            },
            { 
                data: 'tanggal',
                title: 'Date Trans',
                defaultContent: '-',
                width: '15%',
            },
            { 
                data: 'details',
                title: 'Details',
                defaultContent: '<i class="text-muted">No details available</i>',
                width: '30%',
            },
            { 
                data: 'in',
                title: 'IN',
                
                defaultContent: 'Rp 0',
                width: '10%',
            },
            { 
                data: 'out',
                title: 'OUT',
                defaultContent: '-',
                width: '8%',
            },
            { 
                data: 'saldo',
                title: 'Saldo',
                defaultContent: '-',
                width: '10%',
             }


        ],
        order: [[0, 'asc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            emptyTable: "No transactions found for the selected date range",
            zeroRecords: "No matching transactions found",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "Showing 0 to 0 of 0 transactions",
            infoFiltered: "(filtered from _MAX_ total transactions)"
        },
        drawCallback: function(settings) {
            // Update summary after each draw
            updateTableSummary(this.api().data().toArray());
        }
    });

    // Filter button click event
    $('#btnFilter').on('click', function(e) {
        e.preventDefault();
        
        const startDate = $('#date_start').val();
        const endDate = $('#date_end').val();
        
        // Validate dates
        if (!startDate || !endDate) {
            showAlert('Please select both start and end dates', 'warning');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            showAlert('Start date cannot be greater than end date', 'warning');
            return;
        }

        // Show loading state
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Loading...');
        
        // Reload data
        transactionTable.ajax.reload(function(json) {
            // Reset button state
            $btn.prop('disabled', false).html('<i class="bx bx-filter-alt"></i> Filter');
        }, false); // false = don't reset paging
    });

    // Auto-filter when date inputs change (with debounce)
    let filterTimeout;
    $('#date_start, #date_end').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            const startDate = $('#date_start').val();
            const endDate = $('#date_end').val();
            
            // Only auto-reload if both dates are selected and valid
            if (startDate && endDate && new Date(startDate) <= new Date(endDate)) {
                transactionTable.ajax.reload();
            }
        }, 500); // Wait 500ms after user stops typing
    });
});

/**
 * Show alert message
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    $('.custom-alert').remove();
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert">
            <i class="bx ${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert before the card
    $('.card').before(alertHtml);
    
    // Auto-hide success and info alerts after 5 seconds
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            $('.custom-alert').fadeOut(() => {
                $('.custom-alert').remove();
            });
        }, 5000);
    }
}

/**
 * Get appropriate icon for alert type
 * @param {string} type - Alert type
 * @returns {string} Icon class
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'bx-check-circle',
        'danger': 'bx-error-circle',
        'warning': 'bx-error',
        'info': 'bx-info-circle'
    };
    return icons[type] || 'bx-info-circle';
}

/**
 * Update table summary information
 * @param {Array} data - Transaction data
 */
function updateTableSummary(data) {
    if (!data || data.length === 0) return;
    
    try {
        // Calculate totals by category
        const summary = data.reduce((acc, row) => {
            const category = row.kategori || 'Unknown';
            // Extract numeric value from formatted currency
            const totalStr = (row.total || '0').toString();
            const numericStr = totalStr.replace(/[^\d,-]/g, '').replace(/\./g, '').replace(',', '.');
            const total = parseFloat(numericStr) || 0;
            
            if (!acc[category]) {
                acc[category] = { count: 0, total: 0 };
            }
            acc[category].count++;
            acc[category].total += total;
            
            return acc;
        }, {});
        
        // Log summary for debugging
        console.log('Transaction Summary:', summary);
        
        // Calculate grand total
        const grandTotal = Object.values(summary).reduce((sum, cat) => sum + cat.total, 0);
        console.log('Grand Total:', formatCurrency(grandTotal));
        
    } catch (error) {
        console.error('Error calculating summary:', error);
    }
}

/**
 * Refresh transactions data
 */
function refreshTransactions() {
    if (transactionTable && transactionTable.ajax) {
        transactionTable.ajax.reload();
    } else {
        console.warn('DataTable not initialized yet');
        location.reload(); // Fallback to page reload
    }
}

/**
 * Export transactions data
 * @param {string} format - Export format (csv, excel, pdf)
 */
function exportTransactions(format) {
    if (!transactionTable) {
        showAlert('Table not initialized yet', 'warning');
        return;
    }
    
    try {
        const button = transactionTable.button(`0:name(${format})`);
        if (button && button.length > 0) {
            button.trigger();
        } else {
            // Fallback export methods
            switch(format.toLowerCase()) {
                case 'csv':
                    transactionTable.button('.buttons-csv').trigger();
                    break;
                case 'excel':
                    transactionTable.button('.buttons-excel').trigger();
                    break;
                case 'pdf':
                    transactionTable.button('.buttons-pdf').trigger();
                    break;
                default:
                    showAlert('Export format not supported', 'warning');
            }
        }
    } catch (error) {
        console.error('Export error:', error);
        showAlert('Export failed', 'danger');
    }
}

/**
 * Utility function to format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Initialize table manually if needed
 */
function initializeTable() {
    if (!transactionTable) {
        console.log('Initializing table manually...');
        $(document).ready(function() {
            // Trigger the initialization
        });
    }
}
// Global error handler for DataTables
$(document).on('error.dt', function(e, settings, techNote, message) {
    console.error('DataTables Error:', {
        error: e,
        settings: settings,
        techNote: techNote,
        message: message
    });
    Swal.fire('Error', 'Table error occurred. Please refresh the page.', 'error');
});

$('#datatable-buttons').on('click', '.btn-delete-transaction', function() {
    const id = $(this).data('id');
    const source = $(this).data('source');

    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Data akan disembunyikan dari tabel.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'controller/delete_trans.php',
                type: 'POST',
                data: { id: id, source: source },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', 'Transaksi berhasil disembunyikan.', 'success');
                        transactionTable.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message || 'Gagal menghapus transaksi.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', error);
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
        }
    });
});


// Prevent multiple initializations
window.transactionTableInitialized = true;
    </script>
       
    </div>
</div>