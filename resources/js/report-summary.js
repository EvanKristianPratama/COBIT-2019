document.addEventListener('DOMContentLoaded', function () {
    // Store spreadsheet instances by objectiveId
    window.roadmapSpreadsheets = {};

    // Default grid generator
    const getDefaultGrid = (rows = 50, cols = 10) => {
        let data = [];
        for (let i = 0; i < rows; i++) {
            let row = [];
            for (let j = 0; j < cols; j++) {
                row.push('');
            }
            data.push(row);
        }
        return data;
    };

    // Default columns generator
    const getDefaultColumns = (count = 10) => {
        let cols = [];
        for (let i = 0; i < count; i++) {
            cols.push({ type: 'text', width: 150 });
        }
        return cols;
    };

    // Function to initialize spreadsheet
    window.initSpreadsheet = function (objectiveId) {
        const containerId = 'roadmap-rekomendasi-container-' + objectiveId;
        const dataInputId = 'roadmap-rekomendasi-data-' + objectiveId;

        const container = document.getElementById(containerId);
        const dataInput = document.getElementById(dataInputId);

        console.log('[Roadmap] Init:', objectiveId, 'Container:', !!container, 'DataInput:', !!dataInput);

        if (!container || !dataInput) return;

        let savedData = null;
        try {
            const dataValue = dataInput.value;
            if (dataValue) {
                savedData = JSON.parse(dataValue);
                console.log('[Roadmap] Loaded saved data:', savedData);
            }
        } catch (e) {
            console.error('[Roadmap] Error parsing saved data:', e);
        }

        // Extract cells, style, mergeCells, colWidths from saved data
        let gridData = getDefaultGrid();
        let styleData = {};
        let mergeCellsData = {};
        let columns = getDefaultColumns();

        if (savedData) {
            // Check if savedData is new format (object with cells property) or legacy (just array)
            if (savedData.cells && Array.isArray(savedData.cells)) {
                gridData = savedData.cells;
                styleData = savedData.style || {};
                mergeCellsData = savedData.mergeCells || {};
                if (savedData.colWidths && savedData.colWidths.length) {
                    columns = savedData.colWidths.map(w => ({ type: 'text', width: w || 150 }));
                }
            } else if (Array.isArray(savedData)) {
                // Legacy format: just array of cells
                gridData = savedData;
            }
        }

        // Set container styles for scrolling
        container.style.overflow = 'hidden';
        container.style.height = '400px';
        container.style.border = '1px solid #ddd';
        container.style.display = 'block';

        // Initialize JSpreadsheet with scroll enabled and store reference
        try {
            const spreadsheetInstance = jspreadsheet(container, {
                toolbar: true,
                worksheets: [{
                    data: gridData,
                    style: styleData,
                    mergeCells: mergeCellsData,
                    minDimensions: [10, 50],
                    tableOverflow: true,
                    tableWidth: 'calc(100% - 2px)',
                    tableHeight: '350px',
                    columnDrag: true,
                    rowDrag: true,
                    columnResize: true,
                    rowResize: true,
                    wordWrap: true,
                    columns: columns,
                }]
            });

            // Store reference for later retrieval
            window.roadmapSpreadsheets[objectiveId] = spreadsheetInstance;

            console.log('[Roadmap] Spreadsheet initialized with styles:', objectiveId);
        } catch (err) {
            console.error('[Roadmap] Error initializing spreadsheet:', err);
        }
    };

    // Function to get spreadsheet data including styles
    window.getAllSpreadsheetData = function (objectiveId) {
        // Get from stored reference
        const spreadsheetInstance = window.roadmapSpreadsheets[objectiveId];

        if (!spreadsheetInstance) {
            console.warn('[Roadmap] No spreadsheet instance found for:', objectiveId);
            return null;
        }

        try {
            // JSpreadsheet v5 returns an array, first element is the worksheet
            const worksheet = spreadsheetInstance[0];
            if (worksheet) {
                const data = {
                    cells: worksheet.getData ? worksheet.getData() : [],
                    style: worksheet.getStyle ? worksheet.getStyle() : {},
                    mergeCells: worksheet.getMerge ? worksheet.getMerge() : {},
                    colWidths: []
                };

                // Try to get column widths
                try {
                    if (worksheet.getWidth) {
                        data.colWidths = worksheet.getWidth();
                    }
                } catch (e) {
                    console.warn('[Roadmap] Could not get column widths:', e);
                }

                console.log('[Roadmap] Got full data for', objectiveId, ':', data);
                return data;
            }
            return null;
        } catch (err) {
            console.error('[Roadmap] Error getting data:', err);
            return null;
        }
    };

    // Handle Flash Messages from hidden DOM elements
    const successFlash = document.getElementById('flash-success');
    if (successFlash) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: successFlash.dataset.message,
            timer: 2000,
            showConfirmButton: false
        });
    }

    const errorFlash = document.getElementById('flash-error');
    if (errorFlash) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: errorFlash.dataset.message
        });
    }

    // Save All Notes functionality
    const saveAllBtn = document.getElementById('saveAllNotesBtn');
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', function () {
            // Get all forms on the page
            const forms = document.querySelectorAll('form[action*="save-note"]');

            if (forms.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada Form',
                    text: 'Tidak ada catatan untuk disimpan.'
                });
                return;
            }

            // Collect all form data
            let savePromises = [];
            let savedCount = 0;

            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang menyimpan semua catatan',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            forms.forEach(form => {
                // Get objective ID and capture spreadsheet data first
                const objectiveIdInput = form.querySelector('input[name="objective_id"]');
                if (objectiveIdInput) {
                    const objectiveId = objectiveIdInput.value;
                    const spreadsheetData = getAllSpreadsheetData(objectiveId);

                    console.log('[Roadmap] Saving - Objective:', objectiveId, 'Spreadsheet Data:', spreadsheetData);

                    // Update hidden input with spreadsheet data before creating FormData
                    const dataInput = form.querySelector('input[name="roadmap_rekomendasi"]');
                    if (dataInput && spreadsheetData) {
                        dataInput.value = JSON.stringify(spreadsheetData);
                    }
                }

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                const promise = fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(response => {
                    if (response.ok) {
                        savedCount++;
                    }
                    return response;
                });

                savePromises.push(promise);
            });

            Promise.all(savePromises)
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: `${savedCount} catatan berhasil disimpan.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan catatan.'
                    });
                });
        });
    }

    // Initialize Roadmap Spreadsheets
    console.log('[Roadmap] Initializing spreadsheets...');

    // Get all roadmap containers
    const roadmapContainers = document.querySelectorAll('[id^="roadmap-rekomendasi-container-"]');
    roadmapContainers.forEach(container => {
        const objectiveId = container.id.replace('roadmap-rekomendasi-container-', '');
        initSpreadsheet(objectiveId);
    });

    // Override form submit untuk capture spreadsheet data
    const forms = document.querySelectorAll('form[action*="summary.save-note"]');
    console.log('[Roadmap] Found forms:', forms.length);

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const objectiveId = form.querySelector('input[name="objective_id"]').value;
            const spreadsheetData = getAllSpreadsheetData(objectiveId);

            console.log('[Roadmap] Form submit - Objective:', objectiveId, 'Data:', spreadsheetData);

            // Set hidden input dengan data spreadsheet
            const dataInput = form.querySelector('input[name="roadmap_rekomendasi"]');
            if (dataInput && spreadsheetData) {
                dataInput.value = JSON.stringify(spreadsheetData);
            }
        });
    });

    // View Toggle for Evidence (GAMO vs Practice vs Activity)
    const viewToggleButtons = document.querySelectorAll('.btn-group button[data-view]');
    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const view = this.dataset.view; // 'gamo', 'practice', or 'activity'
            const targetIndex = this.dataset.target;

            // Find sibling buttons in the same group and update active state
            const btnGroup = this.closest('.btn-group');
            btnGroup.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('active');
                // Reset button styles
                if (btn.dataset.view === 'gamo') {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline-primary');
                } else if (btn.dataset.view === 'practice') {
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-outline-secondary');
                } else if (btn.dataset.view === 'activity') {
                    btn.classList.remove('btn-info');
                    btn.classList.add('btn-outline-info');
                }
            });

            // Set active button
            this.classList.add('active');
            if (view === 'gamo') {
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
            } else if (view === 'practice') {
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-secondary');
            } else if (view === 'activity') {
                this.classList.remove('btn-outline-info');
                this.classList.add('btn-info');
            }

            // Toggle view sections
            const gamoView = document.getElementById('view-gamo-' + targetIndex);
            const practiceView = document.getElementById('view-practice-' + targetIndex);
            const activityView = document.getElementById('view-activity-' + targetIndex);

            if (view === 'gamo') {
                if (gamoView) gamoView.style.display = 'block';
                if (practiceView) practiceView.style.display = 'none';
                if (activityView) activityView.style.display = 'none';
            } else if (view === 'practice') {
                if (gamoView) gamoView.style.display = 'none';
                if (practiceView) practiceView.style.display = 'block';
                if (activityView) activityView.style.display = 'none';
            } else if (view === 'activity') {
                if (gamoView) gamoView.style.display = 'none';
                if (practiceView) practiceView.style.display = 'none';
                if (activityView) activityView.style.display = 'block';
            }
        });
    });
});
