@extends('layouts.app')

@section('content')
<!-- JSpreadsheet v5 CDN -->
<script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
<script src="https://jsuites.net/v5/jsuites.js"></script>
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" type="text/css" />
<link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" type="text/css" />

<!-- Material Icons (Required for Toolbar) -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card shadow-sm border-0 mb-3 rounded-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <div class="spreadsheet-icon me-3">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color: #0f2b5c;">{{ $spreadsheet->title }}</h4>
                        @if($spreadsheet->description)
                            <small class="text-muted">{{ $spreadsheet->description }}</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success px-4" id="btn-save">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                    <a href="{{ route('spreadsheet.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Bar -->
    <div id="status-bar" class="alert alert-info py-2 px-3 mb-3 d-none" role="alert">
        <small><i class="fas fa-info-circle me-2"></i><span id="status-message"></span></small>
    </div>

    <!-- Spreadsheet Container -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div id="spreadsheet-container"></div>
        </div>
    </div>

    <!-- Help Info -->
    <div class="mt-3 text-muted small">
        <i class="fas fa-keyboard me-1"></i>
        <strong>Shortcuts:</strong> 
        Ctrl+C (Copy) | Ctrl+V (Paste) | Ctrl+Z (Undo) | Ctrl+Y (Redo) | Delete (Clear)
    </div>
</div>

<style>
    /* Spreadsheet Container */
    #spreadsheet-container {
        overflow: auto;
        border-radius: 0.5rem;
    }

    /* Icon */
    .spreadsheet-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    /* JSpreadsheet v5 Styling */
    .jss {
        font-family: 'Nunito', 'Arial', sans-serif;
    }
    
    /* Toolbar Styling */
    .jtoolbar {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 8px 12px !important;
    }
    
    .jtoolbar-item {
        color: #5f6368 !important;
        border-radius: 6px;
        transition: all 0.15s ease;
    }

    .jtoolbar-item:hover {
        background-color: #e8eaed !important;
        color: #202124 !important;
    }
    
    .jtoolbar-item.selected {
        background-color: #e8f0fe !important;
        color: #1a73e8 !important;
    }

    /* Table Styling */
    .jss td {
        border-right: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .jss thead td {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #5f6368;
    }

    .jss tbody tr td:first-child {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #5f6368;
    }

    .jss td.highlight {
        background-color: #e8f0fe !important;
    }

    /* Buttons */
    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #218838, #1db98c);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    /* Tabs styling */
    .jss_tab {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 4px 4px 0 0;
        padding: 8px 16px;
        cursor: pointer;
    }
    .jss_tab.jss_tab_selected {
        background-color: #fff;
        border-bottom: 1px solid #fff;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    var container = document.getElementById('spreadsheet-container');
    var saveBtn = document.getElementById('btn-save');
    var statusBar = document.getElementById('status-bar');
    var statusMessage = document.getElementById('status-message');
    
    // Spreadsheet instance (array for v5)
    var spreadsheet = null;

    // Show status message
    function showStatus(message, type) {
        type = type || 'info';
        statusBar.className = 'alert alert-' + type + ' py-2 px-3 mb-3';
        statusMessage.textContent = message;
        statusBar.classList.remove('d-none');
        if (type === 'success') {
            setTimeout(function() { 
                statusBar.classList.add('d-none'); 
            }, 3000);
        }
    }

    // Hide status
    function hideStatus() {
        statusBar.classList.add('d-none');
    }

    // Default grid generator
    function getDefaultGrid(rows, cols) {
        rows = rows || 30;
        cols = cols || 20;
        var data = [];
        for (var i = 0; i < rows; i++) {
            var row = [];
            for (var j = 0; j < cols; j++) {
                row.push('');
            }
            data.push(row);
        }
        return data;
    }

    // Validate data
    function validateData(data) {
        if (!data || !Array.isArray(data) || data.length === 0) {
            return getDefaultGrid();
        }
        if (!Array.isArray(data[0])) {
            return getDefaultGrid();
        }
        return data;
    }

    // Get active worksheet
    function getWorksheet() {
        if (spreadsheet && spreadsheet[0]) {
            return spreadsheet[0];
        }
        return null;
    }

    try {
        console.log("[Spreadsheet] Initializing v5...");
        container.innerHTML = '';

        // Parse saved data
        var rawData = {!! json_encode($spreadsheet->data) !!};
        
        if (typeof rawData === 'string' && rawData.length > 0) {
            try {
                rawData = JSON.parse(rawData);
            } catch (e) {
                rawData = null;
            }
        }

        // Prepare data
        var cellData = getDefaultGrid();
        var cellStyle = {};
        var mergeCells = {};

        if (rawData && typeof rawData === 'object') {
            if (rawData.cells && Array.isArray(rawData.cells)) {
                cellData = validateData(rawData.cells);
                if (rawData.style) cellStyle = rawData.style;
                if (rawData.mergeCells) mergeCells = rawData.mergeCells;
            } else if (Array.isArray(rawData)) {
                cellData = validateData(rawData);
            }
        }

        // Initialize JSpreadsheet v5
        spreadsheet = jspreadsheet(container, {
            toolbar: true,
            worksheets: [{
                data: cellData,
                style: cellStyle,
                mergeCells: mergeCells,
                worksheetName: '{{ $spreadsheet->title }}',
                minDimensions: [20, 30],
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: '70vh',
                columnDrag: true,
                rowDrag: true,
                columns: (function() {
                    var cols = [];
                    for (var i = 0; i < 20; i++) {
                        cols.push({ type: 'text', width: 120 });
                    }
                    return cols;
                })()
            }],
            // Events
            onchange: function(instance, cell, x, y, value) {
                console.log('[Spreadsheet] Cell changed:', x, y, value);
            },
            onselection: function(instance, x1, y1, x2, y2, origin) {
                console.log('[Spreadsheet] Selection:', x1, y1, 'to', x2, y2);
            }
        });

        console.log("[Spreadsheet] v5 Initialized successfully!");

        // Save functionality
        saveBtn.addEventListener('click', function() {
            var btn = this;
            var originalHTML = btn.innerHTML;
            var ws = getWorksheet();

            if (!ws) {
                showStatus('Error: Worksheet not found', 'danger');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            showStatus('Saving your spreadsheet...', 'warning');

            // Collect data from worksheet
            var payload = {
                cells: ws.getData(),
                style: ws.getStyle(),
                mergeCells: ws.getMerge(),
                colWidths: ws.getWidth()
            };

            console.log("[Spreadsheet] Saving payload:", payload);

            fetch("{{ route('spreadsheet.save', $spreadsheet->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ data: payload })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(result) {
                if (result.success) {
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Saved!';
                    showStatus('Spreadsheet saved successfully!', 'success');
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Failed to save');
                }
            })
            .catch(function(error) {
                console.error("[Spreadsheet] Save error:", error);
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                showStatus('Error saving: ' + error.message, 'danger');
            });
        });

        hideStatus();

    } catch (error) {
        console.error("[Spreadsheet] Fatal error:", error);
        container.innerHTML = 
            '<div class="alert alert-danger m-4">' +
            '<h5><i class="fas fa-exclamation-triangle me-2"></i>Error Loading Spreadsheet</h5>' +
            '<p class="mb-0">Technical details: ' + error.message + '</p>' +
            '</div>';
    }
});
</script>
@endsection
