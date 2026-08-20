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

    /**
     * Filter student table rows based on search input and status filter.
     */
    function filterStudentTable() {
        var searchInput = document.getElementById('student-search-input');
        var statusFilter = document.getElementById('student-status-filter');
        var rows = document.querySelectorAll('.student-attempt-row');
        var noMatchRow = document.getElementById('no-matching-row');
        var visibleCountElem = document.getElementById('visible-count');

        if (!rows || rows.length === 0) {
            return;
        }

        var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var status = statusFilter ? statusFilter.value : 'all';
        var visibleCount = 0;

        rows.forEach(function(row) {
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
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCountElem) {
            visibleCountElem.textContent = visibleCount;
        }

        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
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

            if (searchInput) {
                searchInput.addEventListener('input', filterStudentTable);
                searchInput.addEventListener('keyup', filterStudentTable);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterStudentTable);
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (statusFilter) statusFilter.value = 'all';
                    filterStudentTable();
                });
            }

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
