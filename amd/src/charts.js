// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Client-side search, filtering, and export modal helper for report_quizanalytics.
 *
 * @module     report_quizanalytics/charts
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    'use strict';

    var tableState = {
        currentPage: 1,
        pageSize: 10,
        filteredIndices: []
    };

    /**
     * Recompute matching row indices.
     */
    function updateFilteredIndices() {
        var searchInput = document.getElementById('student-search-input');
        var statusFilter = document.getElementById('student-status-filter');
        var rows = document.querySelectorAll('.student-attempt-row');

        if (!rows || rows.length === 0) {
            tableState.filteredIndices = [];
            return;
        }

        var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var status = statusFilter ? statusFilter.value : 'all';
        var indices = [];

        rows.forEach(function(row, idx) {
            var name = (row.getAttribute('data-name') || '').toLowerCase();
            var email = (row.getAttribute('data-email') || '').toLowerCase();
            var idnumber = (row.getAttribute('data-idnumber') || '').toLowerCase();
            var rowStatus = row.getAttribute('data-status') || '';

            var matchesQuery = !query ||
                (name.indexOf(query) !== -1) ||
                (email.indexOf(query) !== -1) ||
                (idnumber.indexOf(query) !== -1);

            var matchesStatus = (status === 'all') || (rowStatus === status);

            if (matchesQuery && matchesStatus) {
                indices.push(idx);
            }
        });

        tableState.filteredIndices = indices;
    }

    /**
     * Render the pagination and update visible rows.
     */
    function renderPagination() {
        var rows = document.querySelectorAll('.student-attempt-row');
        var noMatchRow = document.getElementById('no-matching-row');
        var fromElem = document.getElementById('pag-from');
        var toElem = document.getElementById('pag-to');
        var totalElem = document.getElementById('pag-total');
        var badgeElem = document.getElementById('records-counter-badge');
        var pagContainer = document.getElementById('student-pagination-list');

        if (!rows || rows.length === 0) {
            return;
        }

        var totalFiltered = tableState.filteredIndices.length;
        var isAll = (tableState.pageSize === 'all');
        var pSize = isAll ? totalFiltered : parseInt(tableState.pageSize, 10);
        if (pSize < 1) {
            pSize = 10;
        }

        var totalPages = isAll ? 1 : Math.max(1, Math.ceil(totalFiltered / pSize));
        if (tableState.currentPage > totalPages) {
            tableState.currentPage = totalPages;
        }
        if (tableState.currentPage < 1) {
            tableState.currentPage = 1;
        }

        var startIndex = isAll ? 0 : (tableState.currentPage - 1) * pSize;
        var endIndex = isAll ? totalFiltered : Math.min(startIndex + pSize, totalFiltered);

        // Hide all rows
        rows.forEach(function(r) {
            r.style.display = 'none';
        });

        // Show current slice
        for (var i = startIndex; i < endIndex; i++) {
            var rowIdx = tableState.filteredIndices[i];
            if (rows[rowIdx]) {
                rows[rowIdx].style.display = '';
            }
        }

        // Update indicators
        if (fromElem) {
            fromElem.textContent = (totalFiltered === 0) ? '0' : (startIndex + 1);
        }
        if (toElem) {
            toElem.textContent = endIndex;
        }
        if (totalElem) {
            totalElem.textContent = totalFiltered;
        }
        if (badgeElem) {
            badgeElem.innerHTML = 'Showing <strong>' + totalFiltered + '</strong> records';
        }
        if (noMatchRow) {
            noMatchRow.style.display = (totalFiltered === 0) ? '' : 'none';
        }

        // Update pagination buttons
        if (!pagContainer) {
            return;
        }
        pagContainer.innerHTML = '';

        if (totalPages <= 1) {
            return;
        }

        function addPageItem(text, pageNum, isActive, isDisabled) {
            var li = document.createElement('li');
            li.className = 'page-item' + (isActive ? ' active' : '') + (isDisabled ? ' disabled' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#!';
            a.innerHTML = text;
            if (!isDisabled && !isActive) {
                a.onclick = function(e) {
                    e.preventDefault();
                    tableState.currentPage = pageNum;
                    renderPagination();
                };
            }
            li.appendChild(a);
            pagContainer.appendChild(li);
        }

        // Previous
        addPageItem('&laquo;', tableState.currentPage - 1, false, tableState.currentPage === 1);

        var maxVisible = 5;
        var startP = Math.max(1, tableState.currentPage - Math.floor(maxVisible / 2));
        var endP = Math.min(totalPages, startP + maxVisible - 1);
        if (endP - startP + 1 < maxVisible) {
            startP = Math.max(1, endP - maxVisible + 1);
        }

        if (startP > 1) {
            addPageItem('1', 1, false, false);
            if (startP > 2) {
                var ell = document.createElement('li');
                ell.className = 'page-item disabled';
                ell.innerHTML = '<span class="page-link">&hellip;</span>';
                pagContainer.appendChild(ell);
            }
        }

        for (var p = startP; p <= endP; p++) {
            addPageItem(p.toString(), p, p === tableState.currentPage, false);
        }

        if (endP < totalPages) {
            if (endP < totalPages - 1) {
                var ell2 = document.createElement('li');
                ell2.className = 'page-item disabled';
                ell2.innerHTML = '<span class="page-link">&hellip;</span>';
                pagContainer.appendChild(ell2);
            }
            addPageItem(totalPages.toString(), totalPages, false, false);
        }

        // Next
        addPageItem('&raquo;', tableState.currentPage + 1, false, tableState.currentPage === totalPages);
    }

    /**
     * Filter student table rows based on search input and status filter.
     */
    function filterStudentTable() {
        updateFilteredIndices();
        tableState.currentPage = 1;
        renderPagination();
    }

    /**
     * Save export field preferences to localStorage.
     */
    function saveExportPreferences() {
        try {
            var selectedFields = [];
            document.querySelectorAll('.export-field:checked').forEach(function(cb) {
                selectedFields.push(cb.value);
            });
            localStorage.setItem('report_quizanalytics_export_fields', JSON.stringify(selectedFields));

            var checkedFormat = document.querySelector('.export-format-radio:checked');
            if (checkedFormat) {
                localStorage.setItem('report_quizanalytics_export_format', checkedFormat.value);
            }
        } catch (e) {
            // Ignore localStorage errors if cookies/storage blocked.
        }
    }

    /**
     * Load export field preferences from localStorage.
     */
    function loadExportPreferences() {
        try {
            var savedFields = localStorage.getItem('report_quizanalytics_export_fields');
            if (savedFields) {
                var fieldList = JSON.parse(savedFields);
                if (Array.isArray(fieldList) && fieldList.length > 0) {
                    document.querySelectorAll('.export-field').forEach(function(cb) {
                        cb.checked = (fieldList.indexOf(cb.value) !== -1);
                    });
                }
            }

            var savedFormat = localStorage.getItem('report_quizanalytics_export_format');
            if (savedFormat) {
                var formatRadio = document.querySelector('.export-format-radio[value="' + savedFormat + '"]');
                if (formatRadio) {
                    formatRadio.checked = true;
                }
            }
        } catch (e) {
            // Ignore localStorage errors.
        }
    }

    /**
     * Close modal completely.
     */
    function closeModal() {
        var modalEl = document.getElementById('exportModal');
        if (!modalEl) return;

        // Try jQuery modal hide
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $('#exportModal').modal('hide');
        }

        // Try native bootstrap 5
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
            var bsModal = window.bootstrap.Modal.getInstance(modalEl);
            if (bsModal) {
                bsModal.hide();
            }
        }

        // Click close/dismiss button
        var dismissBtn = modalEl.querySelector('[data-bs-dismiss="modal"], [data-dismiss="modal"], .btn-close');
        if (dismissBtn) {
            dismissBtn.click();
        }

        // Remove backdrops if lingering
        setTimeout(function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(bd) {
                bd.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }, 150);
    }

    return {
        /**
         * Initialize all frontend features.
         */
        init: function() {
            // 1. Search and Filter
            var searchInput = document.getElementById('student-search-input');
            var statusFilter = document.getElementById('student-status-filter');
            var clearBtn = document.getElementById('btn-clear-filters');

            var pageSizeSelect = document.getElementById('table-page-size');

            if (searchInput) {
                searchInput.addEventListener('input', filterStudentTable);
                searchInput.addEventListener('keyup', filterStudentTable);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterStudentTable);
            }
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', function() {
                    tableState.pageSize = this.value;
                    tableState.currentPage = 1;
                    renderPagination();
                });
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (statusFilter) statusFilter.value = 'all';
                    filterStudentTable();
                });
            }

            // Initial calculation and pagination render
            updateFilteredIndices();
            renderPagination();

            // 2. Select / Deselect all export fields
            var selectAllBtn = document.getElementById('btn-select-all');
            var deselectAllBtn = document.getElementById('btn-deselect-all');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.export-field').forEach(function(cb) {
                        cb.checked = true;
                    });
                    saveExportPreferences();
                });
            }
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.export-field').forEach(function(cb) {
                        cb.checked = false;
                    });
                    saveExportPreferences();
                });
            }

            // 3. Load & Listen on field checkboxes
            loadExportPreferences();
            document.querySelectorAll('.export-field, .export-format-radio').forEach(function(el) {
                el.addEventListener('change', saveExportPreferences);
            });

            // 4. Form submission - Save & Close modal
            var exportForm = document.getElementById('export-report-form');
            if (exportForm) {
                exportForm.addEventListener('submit', function() {
                    saveExportPreferences();
                    setTimeout(closeModal, 100);
                });
            }

            var downloadBtn = document.getElementById('btn-submit-export');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    saveExportPreferences();
                    setTimeout(closeModal, 150);
                });
            }
        }
    };
});
