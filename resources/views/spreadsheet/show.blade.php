@extends('layouts.app')

@section('content')
<!-- JSpreadsheet v5 CDN -->
<script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
<script src="https://jsuites.net/v5/jsuites.js"></script>
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" type="text/css" />
<link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" type="text/css" />

<!-- Material Icons (Required for Toolbar) -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />

<div class="spreadsheet-app" id="spreadsheet-app">
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
                    <button class="btn btn-outline-primary" id="btn-fullscreen" title="Toggle Fullscreen">
                        <i class="fas fa-expand" id="fullscreen-icon"></i>
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
    <div class="card shadow-sm border-0 rounded-3" style="border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important;">
        <div class="card-body p-0">
            <div id="spreadsheet-container"></div>
        </div>
    </div>

    <!-- Sheet Tabs -->
    <div class="card shadow-sm border-0 rounded-3 mt-0" style="border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;">
        <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-1 flex-wrap" id="sheet-tabs"></div>
            <button class="btn btn-sm btn-outline-success rounded-circle" id="btn-add-sheet" title="Add Sheet" style="width: 28px; height: 28px; padding: 0;">
                <i class="fas fa-plus" style="font-size: 12px;"></i>
            </button>
        </div>
    </div>

    <!-- Help Info -->
    <div class="mt-3 text-muted small" id="help-info">
        <i class="fas fa-keyboard me-1"></i>
        <strong>Shortcuts:</strong> 
        Ctrl+C (Copy) | Ctrl+V (Paste) | Ctrl+Z (Undo) | Ctrl+Y (Redo) | Delete (Clear)
    </div>
</div>

<style>
    /* Fullscreen Mode */
    .spreadsheet-app.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        background: #fff;
        padding: 15px;
        overflow: auto;
    }
    .spreadsheet-app.fullscreen #help-info { display: none; }

    /* Spreadsheet Container */
    #spreadsheet-container {
        overflow: auto;
        border-radius: 0.5rem 0.5rem 0 0;
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
        white-space: pre-wrap !important;
        word-wrap: break-word !important;
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

    /* Sheet Tabs */
    .sheet-tab {
        padding: 6px 16px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #f8f9fa;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sheet-tab:hover { background: #e9ecef; }
    .sheet-tab.active { background: #0f2b5c; color: white; border-color: #0f2b5c; }
    .sheet-tab .btn-close-tab { font-size: 10px; opacity: 0.6; cursor: pointer; }
    .sheet-tab .btn-close-tab:hover { opacity: 1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    var container = document.getElementById('spreadsheet-container');
    var saveBtn = document.getElementById('btn-save');
    var statusBar = document.getElementById('status-bar');
    var statusMessage = document.getElementById('status-message');
    var sheetTabsContainer = document.getElementById('sheet-tabs');
    var addSheetBtn = document.getElementById('btn-add-sheet');
    var appContainer = document.getElementById('spreadsheet-app');
    var fullscreenIcon = document.getElementById('fullscreen-icon');
    
    // State
    var spreadsheet = null;
    var sheetsData = [];
    var activeSheetIndex = 0;
    var isFullscreen = false;

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

    // ===== FULLSCREEN =====
    document.getElementById('btn-fullscreen').addEventListener('click', function() {
        isFullscreen = !isFullscreen;
        if (isFullscreen) {
            appContainer.classList.add('fullscreen');
            fullscreenIcon.className = 'fas fa-compress';
        } else {
            appContainer.classList.remove('fullscreen');
            fullscreenIcon.className = 'fas fa-expand';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isFullscreen) {
            isFullscreen = false;
            appContainer.classList.remove('fullscreen');
            fullscreenIcon.className = 'fas fa-expand';
        }
    });

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

    // Default columns
    function getDefaultColumns(count) {
        count = count || 20;
        var cols = [];
        for (var i = 0; i < count; i++) {
            cols.push({ type: 'text', width: 120 });
        }
        return cols;
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

    // Get default sheet
    function getDefaultSheet(name) {
        return {
            name: name || 'Sheet 1',
            cells: getDefaultGrid(),
            style: {},
            mergeCells: {},
            colWidths: null
        };
    }

    // Get active worksheet
    function getWorksheet() {
        if (spreadsheet && spreadsheet[0]) {
            return spreadsheet[0];
        }
        return null;
    }

    // ===== SHEET TABS =====
    function renderSheetTabs() {
        sheetTabsContainer.innerHTML = '';
        sheetsData.forEach(function(sheet, index) {
            var tab = document.createElement('div');
            tab.className = 'sheet-tab' + (index === activeSheetIndex ? ' active' : '');
            tab.innerHTML = '<span>' + sheet.name + '</span>' +
                (sheetsData.length > 1 ? '<i class="fas fa-times btn-close-tab" data-index="' + index + '"></i>' : '');
            
            tab.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-close-tab')) return;
                switchSheet(index);
            });
            
            tab.addEventListener('dblclick', function() {
                var newName = prompt('Sheet name:', sheet.name);
                if (newName && newName.trim()) {
                    sheetsData[index].name = newName.trim();
                    renderSheetTabs();
                }
            });
            
            sheetTabsContainer.appendChild(tab);
        });
        
        document.querySelectorAll('.btn-close-tab').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var idx = parseInt(this.dataset.index);
                if (sheetsData.length > 1 && confirm('Delete "' + sheetsData[idx].name + '"?')) {
                    deleteSheet(idx);
                }
            });
        });
    }

    function saveCurrentSheetState() {
        var ws = getWorksheet();
        if (ws && sheetsData[activeSheetIndex]) {
            sheetsData[activeSheetIndex].cells = ws.getData();
            sheetsData[activeSheetIndex].style = ws.getStyle();
            sheetsData[activeSheetIndex].mergeCells = ws.getMerge();
            try { sheetsData[activeSheetIndex].colWidths = ws.getWidth(); } catch(e) {}
        }
    }

    function switchSheet(index) {
        if (index === activeSheetIndex) return;
        saveCurrentSheetState();
        activeSheetIndex = index;
        loadSheet(sheetsData[index]);
        renderSheetTabs();
    }

    function loadSheet(sheetData) {
        container.innerHTML = '';
        
        var columns = getDefaultColumns();
        if (sheetData.colWidths && sheetData.colWidths.length) {
            columns = sheetData.colWidths.map(function(w) {
                return { type: 'text', width: w || 120 };
            });
        }
        
        // Use toolbar: true for built-in Jspreadsheet toolbar
        spreadsheet = jspreadsheet(container, {
            toolbar: true,
            worksheets: [{
                data: sheetData.cells || getDefaultGrid(),
                style: sheetData.style || {},
                mergeCells: sheetData.mergeCells || {},
                worksheetName: sheetData.name,
                minDimensions: [20, 30],
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: '65vh',
                columnDrag: true,
                rowDrag: true,
                columnResize: true,
                rowResize: true,
                wordWrap: true,
                columns: columns
            }],
            onchange: function(instance, cell, x, y, value) {
                console.log('[Spreadsheet] Cell changed:', x, y, value);
            },
            onselection: function(instance, x1, y1, x2, y2, origin) {
                console.log('[Spreadsheet] Selection:', x1, y1, 'to', x2, y2);
            }
        });
        
        console.log('[Spreadsheet] Loaded sheet:', sheetData.name);
    }

    function addSheet() {
        saveCurrentSheetState();
        var newSheet = getDefaultSheet('Sheet ' + (sheetsData.length + 1));
        sheetsData.push(newSheet);
        activeSheetIndex = sheetsData.length - 1;
        loadSheet(newSheet);
        renderSheetTabs();
    }

    function deleteSheet(index) {
        if (sheetsData.length <= 1) return;
        sheetsData.splice(index, 1);
        if (activeSheetIndex >= sheetsData.length) {
            activeSheetIndex = sheetsData.length - 1;
        }
        loadSheet(sheetsData[activeSheetIndex]);
        renderSheetTabs();
    }

    addSheetBtn.addEventListener('click', addSheet);

    // ===== INITIALIZATION =====
    try {
        console.log("[Spreadsheet] Initializing v5...");

        var rawData = {!! json_encode($spreadsheet->data) !!};
        
        if (typeof rawData === 'string' && rawData.length > 0) {
            try { rawData = JSON.parse(rawData); } catch (e) { rawData = null; }
        }

        // Check for multi-sheet format
        if (rawData && rawData.sheets && Array.isArray(rawData.sheets)) {
            sheetsData = rawData.sheets;
            activeSheetIndex = rawData.activeSheet || 0;
        } else if (rawData && typeof rawData === 'object') {
            // Legacy single-sheet format
            var legacySheet = getDefaultSheet();
            if (rawData.cells && Array.isArray(rawData.cells)) {
                legacySheet.cells = validateData(rawData.cells);
                if (rawData.style) legacySheet.style = rawData.style;
                if (rawData.mergeCells) legacySheet.mergeCells = rawData.mergeCells;
                if (rawData.colWidths) legacySheet.colWidths = rawData.colWidths;
            } else if (Array.isArray(rawData)) {
                legacySheet.cells = validateData(rawData);
            }
            sheetsData = [legacySheet];
        } else {
            sheetsData = [getDefaultSheet()];
        }

        loadSheet(sheetsData[activeSheetIndex]);
        renderSheetTabs();

        console.log("[Spreadsheet] Initialized with " + sheetsData.length + " sheet(s)!");

        // ===== SAVE =====
        saveBtn.addEventListener('click', function() {
            var btn = this;
            var originalHTML = btn.innerHTML;

            saveCurrentSheetState();

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            showStatus('Saving...', 'warning');

            var payload = {
                sheets: sheetsData,
                activeSheet: activeSheetIndex
            };

            console.log("[Spreadsheet] Saving:", payload);

            fetch("{{ route('spreadsheet.save', $spreadsheet->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ data: payload })
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success) {
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Saved!';
                    showStatus('Saved!', 'success');
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Failed');
                }
            })
            .catch(function(error) {
                console.error("[Spreadsheet] Save error:", error);
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                showStatus('Error: ' + error.message, 'danger');
            });
        });

        hideStatus();

    } catch (error) {
        console.error("[Spreadsheet] Error:", error);
        container.innerHTML = 
            '<div class="alert alert-danger m-4">' +
            '<h5><i class="fas fa-exclamation-triangle me-2"></i>Error Loading Spreadsheet</h5>' +
            '<p class="mb-0">Technical details: ' + error.message + '</p>' +
            '</div>';
    }
});
</script>
@endsection
