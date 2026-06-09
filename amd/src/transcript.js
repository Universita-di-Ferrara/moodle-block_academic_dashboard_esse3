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
 * Transcript dashboard interactions.
 *
 * @module     block_academic_dashboard_esse3/transcript
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/str', 'core/templates'], function($, ajax, Str, Templates) {
    return {
        init: function(params) {
            // Moodle may pass the object directly or as part of arguments.
            let blockId = (params && typeof params === 'object') ? params.uniqueid : params;

            if (!blockId) {
                return;
            }

            // Get localized strings once.
            const stringsToFetch = [
                {key: 'syllabus_objectives', component: 'block_academic_dashboard_esse3'},
                {key: 'syllabus_prerequisites', component: 'block_academic_dashboard_esse3'},
                {key: 'syllabus_content', component: 'block_academic_dashboard_esse3'},
                {key: 'syllabus_methods', component: 'block_academic_dashboard_esse3'},
                {key: 'syllabus_assessment', component: 'block_academic_dashboard_esse3'},
                {key: 'syllabus_textbooks', component: 'block_academic_dashboard_esse3'},
                {key: 'no_syllabus', component: 'block_academic_dashboard_esse3'},
                {key: 'error_loading_syllabus', component: 'block_academic_dashboard_esse3'},
                {key: 'ajax_error', component: 'block_academic_dashboard_esse3'}
            ];

            let localizedStrings = {};
            Str.get_strings(stringsToFetch).then(function(strings) {
                stringsToFetch.forEach((s, index) => {
                    localizedStrings[s.key] = strings[index];
                });
                return strings;
            }).catch(function() {
                return false;
            });

            let container = $('#transcript-block-' + blockId);
            if (!container.length) {
                // Fallback: try to find any block container if there's only one.
                if ($('.block-esse3-transcript-container').length === 1) {
                    container = $('.block-esse3-transcript-container');
                } else {
                    return;
                }
            }

            const itemsContainer = container.find('.transcript-items-container');
            const noResults = container.find('.transcript-no-results');
            const originTabs = container.find('.transcript-origin-tablink');

            /**
             * Filter the course list.
             */
            const applyFilters = () => {
                const searchInput = container.find('.transcript-search');
                const yearFilter = container.find('.transcript-filter-year');
                const statusFilter = container.find('.transcript-filter-status');

                const searchValue = searchInput.val().toLowerCase().trim();
                const yearValue = yearFilter.val();
                const statusValue = statusFilter.val();
                const activeOrigin = String(originTabs.filter('.active').first().data('origin-filter') || '');

                let visibleCount = 0;
                const items = container.find('.transcript-item');

                items.each(function() {
                    const item = $(this);
                    const searchData = String(item.data('search') || '').toLowerCase();
                    const yearData = String(item.data('year') || '');
                    const statusData = String(item.data('status') || '');
                    const originData = String(item.data('origin') || '');

                    const matchesSearch = searchValue === '' || searchData.indexOf(searchValue) !== -1;
                    const matchesYear = yearValue === '' || yearData === yearValue;
                    const matchesStatus = statusValue === '' || statusData === statusValue;
                    const matchesOrigin = activeOrigin === '' || originData === activeOrigin;

                    if (matchesSearch && matchesYear && matchesStatus && matchesOrigin) {
                        // Reset animation and show
                        item.css('animation', 'none');
                        // Trigger reflow
                        void item[0].offsetHeight;

                        item.removeClass('hidden');

                        // Add staggered delay
                        item.css('animation', 'fadeIn 0.4s ease forwards');
                        item.css('animation-delay', (visibleCount * 0.05) + 's');

                        visibleCount++;
                    } else {
                        item.addClass('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.removeClass('d-none');
                    itemsContainer.addClass('d-none');
                } else {
                    noResults.addClass('d-none');
                    itemsContainer.removeClass('d-none');
                }
            };

            /**
             * Switch between grid and list views.
             *
             * @param {string} viewType The view type (grid or list).
             */
            const switchView = (viewType) => {
                const viewToggles = container.find('.transcript-view-toggle');
                viewToggles.removeClass('btn-primary active').addClass('btn-outline-secondary');
                // Remove inline overrides for inactive state
                viewToggles.each(function() {
                    this.style.removeProperty('background-color');
                    this.style.removeProperty('border-color');
                    this.style.removeProperty('color');
                });

                const currentBtn = container.find('.transcript-view-toggle[data-view="' + viewType + '"]');
                currentBtn.addClass('active').removeClass('btn-outline-secondary');
                // Force inline accent color for active state
                currentBtn[0].style.setProperty('background-color', 'var(--transcript-primary, #0056b3)');
                currentBtn[0].style.setProperty('border-color', 'var(--transcript-primary, #0056b3)');
                currentBtn[0].style.setProperty('color', 'white');

                if (viewType === 'grid') {
                    itemsContainer.removeClass('transcript-list-container').addClass('transcript-grid-container');
                } else {
                    itemsContainer.removeClass('transcript-grid-container').addClass('transcript-list-container');
                }

                localStorage.setItem('block_academic_dashboard_esse3_view_preference', viewType);
            };

            /**
             * Renders the syllabus data into the content container using Mustache templates.
             *
             * @param {jQuery} targetEl The container to inject into.
             * @param {Array} syllabusList List of syllabus items from Esse3.
             */
            const renderSyllabus = (targetEl, syllabusList) => {
                if (!syllabusList || syllabusList.length === 0) {
                    targetEl.html('<p class="text-muted">' + localizedStrings.no_syllabus + '</p>');
                    return;
                }

                const sectionDefs = [
                    {label: localizedStrings.syllabus_objectives, key: 'obiettiviFormativi', icon: 'fa-bullseye'},
                    {label: localizedStrings.syllabus_prerequisites, key: 'prerequisiti', icon: 'fa-list-ul'},
                    {label: localizedStrings.syllabus_content, key: 'contenuti', icon: 'fa-book-open'},
                    {label: localizedStrings.syllabus_methods, key: 'metodiDidattici', icon: 'fa-chalkboard-teacher'},
                    {label: localizedStrings.syllabus_assessment, key: 'modalitaVerificaApprendimento', icon: 'fa-tasks'},
                    {label: localizedStrings.syllabus_textbooks, key: 'testiRiferimento', icon: 'fa-bookmark'}
                ];

                // Build render promises for each section across all syllabus entries.
                const renderPromises = [];
                syllabusList.forEach((item, itemIndex) => {
                    let isFirstSection = true;
                    sectionDefs.forEach((s) => {
                        if (item[s.key]) {
                            const context = {
                                icon: s.icon,
                                label: s.label,
                                content: item[s.key],
                                divider: itemIndex > 0 && isFirstSection
                            };
                            renderPromises.push(
                                Templates.render('block_academic_dashboard_esse3/syllabus_section', context)
                            );
                            isFirstSection = false;
                        }
                    });
                });

                if (renderPromises.length === 0) {
                    targetEl.html('<p class="text-muted">' + localizedStrings.no_syllabus + '</p>');
                    return;
                }

                $.when.apply($, renderPromises).then(function() {
                    const fragments = arguments;
                    for (let i = 0; i < fragments.length; i++) {
                        targetEl.append(fragments[i]);
                    }
                    return true;
                }).catch(function() {
                    targetEl.html('<p class="text-muted">' + localizedStrings.no_syllabus + '</p>');
                });
            };

            // Event Listeners using delegation on the container
            container.on('keyup', '.transcript-search', applyFilters);
            container.on('change', '.transcript-filter-year', applyFilters);
            container.on('change', '.transcript-filter-status', applyFilters);
            container.on('click', '.transcript-origin-tablink', function(e) {
                e.preventDefault();
                const tab = $(this);
                if (tab.hasClass('active')) {
                    return;
                }

                originTabs.removeClass('active').attr('aria-selected', 'false');
                tab.addClass('active').attr('aria-selected', 'true');
                applyFilters();
            });

            container.on('click', '.transcript-view-toggle', function(e) {
                e.preventDefault();
                const view = $(this).attr('data-view');
                switchView(view);
            });

            // Initial view setup
            const savedView = localStorage.getItem('block_academic_dashboard_esse3_view_preference') || 'grid';
            switchView(savedView);
            applyFilters();

            // Syllabus Loading
            container.on('click', '.transcript-syllabus-btn', function(e) {
                e.preventDefault();
                const btn = $(this);
                const matId = btn.data('matid');
                const adsceId = btn.data('adsceid');
                const blockId = btn.data('blockid');
                const title = btn.data('title');

                const modal = $('#transcript-syllabus-modal-' + blockId);
                const loading = modal.find('.transcript-syllabus-loading');
                const content = modal.find('.transcript-syllabus-content');
                const modalTitle = modal.find('.modal-title');

                modalTitle.text(title + ' - Syllabus');
                content.empty();
                loading.removeClass('d-none');
                modal.modal('show');

                ajax.call([{
                    methodname: 'block_academic_dashboard_esse3_get_syllabus',
                    args: {
                        matId: matId,
                        adsceId: adsceId
                    }
                }])[0].then(function(data) {
                    loading.addClass('d-none');
                    if (data.status === 'success' && data.data) {
                        renderSyllabus(content, data.data);
                    } else {
                        content.html('<div class="alert alert-danger">' +
                            (data.message || localizedStrings.error_loading_syllabus) +
                            '</div>');
                    }
                    return data;
                }).fail(function(ex) {
                    loading.addClass('d-none');
                    content.html('<div class="alert alert-danger">' +
                        localizedStrings.ajax_error.replace('{$a}', ex.message) +
                        '</div>');
                });
            });
        }
    };
});
