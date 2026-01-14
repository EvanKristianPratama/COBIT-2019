@extends('layouts.app')

@section('content')
<!-- JSpreadsheet CE CDN (v4) -->
<script src="https://bossanova.uk/jspreadsheet/v4/jexcel.js"></script>
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v4/jexcel.css" type="text/css" />
<script src="https://jsuites.net/v4/jsuites.js"></script>
<link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />

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
        border: 1px solid #e0e0e0;
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

    /* JSpreadsheet Styling */
    .jexcel_container {
        font-family: 'Nunito', 'Arial', sans-serif;
    }
    
    /* Toolbar - Google Sheets Style */
    .jexcel_toolbar {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 8px 12px !important;
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .jexcel_toolbar_item {
        color: #5f6368 !important;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .jexcel_toolbar_item:hover {
        background-color: #e8eaed !important;
        color: #202124 !important;
    }
    
    .jexcel_toolbar_item.active {
        background-color: #e8f0fe !important;
        color: #1a73e8 !important;
    }

    .jexcel_toolbar_item i {
        font-size: 18px;
    }
    
    /* Separator */
    .jexcel_toolbar_item.jexcel_toolbar_divisor {
        width: 1px;
        height: 24px;
        background-color: #dadce0;
        margin: 0 6px;
        border: none;
        cursor: default;
    }
    
    /* Table Styling */
    .jexcel {
        border-top: none !important;
    }

    .jexcel td {
        border-right: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
    }

    .jexcel thead td {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #5f6368;
    }

    .jexcel tbody tr td:first-child {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #5f6368;
    }

    .jexcel td.highlight {
        background-color: #e8f0fe !important;
    }

    .jexcel td.highlight-left, .jexcel td.highlight-top,
    .jexcel td.highlight-right, .jexcel td.highlight-bottom {
        border-color: #1a73e8 !important;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const container = document.getElementById('spreadsheet-container');
    const saveBtn = document.getElementById('btn-save');
    const statusBar = document.getElementById('status-bar');
    const statusMessage = document.getElementById('status-message');
    
    // Show status
    function showStatus(message, type = 'info') {
        statusBar.className = `alert alert-${type} py-2 px-3 mb-3`;
        statusMessage.textContent = message;
        statusBar.classList.remove('d-none');
        if (type === 'success') {
            setTimeout(() => statusBar.classList.add('d-none'), 3000);
        }
    }

    // Hide status
    function hideStatus() {
        statusBar.classList.add('d-none');
    }

    // Default grid generator - creates a proper 2D array
    function getDefaultGrid(rows, cols) {
        rows = rows || 25;
        cols = cols || 15;
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

    // Validate and fix data to ensure it's a proper 2D array
    function validateData(data) {
        if (!data || !Array.isArray(data) || data.length === 0) {
            return getDefaultGrid();
        }
        
        // Check if first row is an array
        if (!Array.isArray(data[0])) {
            return getDefaultGrid();
        }
        
        // Ensure all rows have same length
        var maxCols = 0;
        for (var i = 0; i < data.length; i++) {
            if (Array.isArray(data[i]) && data[i].length > maxCols) {
                maxCols = data[i].length;
            }
        }
        
        if (maxCols === 0) {
            return getDefaultGrid();
        }
        
        // Normalize rows
        for (var i = 0; i < data.length; i++) {
            if (!Array.isArray(data[i])) {
                data[i] = [];
            }
            while (data[i].length < maxCols) {
                data[i].push('');
            }
        }
        
        return data;
    }

    try {
        console.log("[Spreadsheet] Initializing...");
        
        // Clean container
        container.innerHTML = '';

        // Parse saved data from PHP
        var rawData = {!! json_encode($spreadsheet->data) !!};
        console.log("[Spreadsheet] Raw data type:", typeof rawData);
        console.log("[Spreadsheet] Raw data:", rawData);

        // Handle if data is a JSON string (double-encoded)
        if (typeof rawData === 'string' && rawData.length > 0) {
            try {
                rawData = JSON.parse(rawData);
                console.log("[Spreadsheet] Parsed string to:", rawData);
            } catch (e) {
                console.warn("[Spreadsheet] Could not parse string:", e);
                rawData = null;
            }
        }

        // Prepare configuration
        var cellData = getDefaultGrid();
        var cellStyle = null; // null instead of {} to avoid jspreadsheet issues
        
        if (rawData && typeof rawData === 'object') {
            // Check if it's our structured format with cells property
            if (rawData.cells && Array.isArray(rawData.cells)) {
                console.log("[Spreadsheet] Found structured format with cells");
                cellData = validateData(rawData.cells);
                
                // Only use style if it has actual properties
                if (rawData.style && typeof rawData.style === 'object' && Object.keys(rawData.style).length > 0) {
                    cellStyle = rawData.style;
                }
            } 
            // Legacy: plain 2D array
            else if (Array.isArray(rawData)) {
                console.log("[Spreadsheet] Found legacy array format");
                cellData = validateData(rawData);
            }
        }

        console.log("[Spreadsheet] Final cell data:", cellData);
        console.log("[Spreadsheet] Cell data rows:", cellData.length, "cols:", cellData[0] ? cellData[0].length : 0);

        // JSpreadsheet instance reference
        var spreadsheetInstance = null;

        // Build config object
        var config = {
            data: cellData,
            minDimensions: [15, 25],
            allowInsertRow: true,
            allowInsertColumn: true,
            allowDeleteRow: true,
            allowDeleteColumn: true,
            allowRenameColumn: true,
            columnSorting: false,
            wordWrap: true,
            tableOverflow: true,
            tableWidth: '100%',
            tableHeight: '70vh',
            toolbar: [
                { 
                    type: 'i', 
                    content: 'undo',
                    onclick: function() { if(spreadsheetInstance) spreadsheetInstance.undo(); }
                },
                { 
                    type: 'i', 
                    content: 'redo',
                    onclick: function() { if(spreadsheetInstance) spreadsheetInstance.redo(); }
                },
                { 
                    type: 'i', 
                    content: 'save',
                    onclick: function() { saveBtn.click(); }
                },
                { type: 'divisor' },
                { 
                    type: 'i', 
                    content: 'format_bold',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'font-weight', 'bold');
                        }
                    }
                },
                { 
                    type: 'i', 
                    content: 'format_italic',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'font-style', 'italic');
                        }
                    }
                },
                { 
                    type: 'i', 
                    content: 'format_underlined',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'text-decoration', 'underline');
                        }
                    }
                },
                { type: 'divisor' },
                { type: 'color', content: 'format_color_text', k: 'color' },
                { type: 'color', content: 'format_color_fill', k: 'background-color' },
                { type: 'divisor' },
                { 
                    type: 'i', 
                    content: 'format_align_left',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'text-align', 'left');
                        }
                    }
                },
                { 
                    type: 'i', 
                    content: 'format_align_center',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'text-align', 'center');
                        }
                    }
                },
                { 
                    type: 'i', 
                    content: 'format_align_right',
                    onclick: function() { 
                        if(spreadsheetInstance) {
                            var sel = spreadsheetInstance.getSelected();
                            if(sel) spreadsheetInstance.setStyle(sel, 'text-align', 'right');
                        }
                    }
                },
                { type: 'divisor' },
                { 
                    type: 'i', 
                    content: 'add',
                    onclick: function() { if(spreadsheetInstance) spreadsheetInstance.insertRow(); }
                },
                { 
                    type: 'i', 
                    content: 'view_column',
                    onclick: function() { if(spreadsheetInstance) spreadsheetInstance.insertColumn(); }
                },
                { type: 'divisor' },
                { 
                    type: 'i', 
                    content: 'file_download',
                    onclick: function() { if(spreadsheetInstance) spreadsheetInstance.download(); }
                }
            ]
        };

        // Only add style if we have valid styles
        if (cellStyle !== null) {
            config.style = cellStyle;
        }

        // Initialize JSpreadsheet
        console.log("[Spreadsheet] Creating jspreadsheet with config:", config);
        spreadsheetInstance = jspreadsheet(container, config);
        console.log("[Spreadsheet] Initialized successfully!");

        // Save functionality
        saveBtn.addEventListener('click', function() {
            var btn = this;
            var originalHTML = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            showStatus('Saving your spreadsheet...', 'warning');

            if (!spreadsheetInstance) {
                showStatus('Error: Spreadsheet instance not found', 'danger');
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                return;
            }

            // Collect data
            var payload = {
                cells: spreadsheetInstance.getData(),
                style: spreadsheetInstance.getStyle(),
                colWidths: spreadsheetInstance.getWidth()
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
                console.log("[Spreadsheet] Save response:", result);
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
