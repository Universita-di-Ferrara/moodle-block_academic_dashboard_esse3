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
 * Enrolled-course fallback interactions.
 *
 * @module     block_academic_dashboard_esse3/enrolled
 * @copyright 2026 Università degli Studi di Ferrara - Unife
 * @author    Andrea Bertelli <andrea.bertelli@unife.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    return {
        init: function(params) {
            var blockId = (params && typeof params === 'object') ? params.uniqueid : params;
            if (!blockId) {
                return;
            }

            var container = $('#transcript-block-' + blockId);
            if (!container.length) {
                return;
            }

            var searchInput = container.find('.transcript-enrolled-search');
            var noResults = container.find('.transcript-no-results');
            var groups = container.find('.transcript-category-group');
            var flatItems = container.children('.transcript-items-container').find('.transcript-item');

            var activateVisibleSubgroup = function(group) {
                var tabLinks = group.find('.transcript-subcategory-tablink');
                var visibleTabLinks = tabLinks.filter(':visible');

                if (!visibleTabLinks.length) {
                    return;
                }

                var activeLink = visibleTabLinks.filter('.active').first();
                if (!activeLink.length) {
                    activeLink = visibleTabLinks.first();
                }

                if (typeof activeLink.tab === 'function') {
                    activeLink.tab('show');
                    return;
                }

                tabLinks.removeClass('active').attr('aria-selected', 'false');
                group.find('.transcript-subcategory-pane').removeClass('show active');
                activeLink.addClass('active').attr('aria-selected', 'true');
                group.find(activeLink.attr('href')).addClass('show active');
            };

            var applySearch = function() {
                var term = String(searchInput.val() || '').toLowerCase().trim();
                var visibleCount = 0;

                if (!groups.length) {
                    flatItems.each(function() {
                        var item = $(this);
                        var haystack = String(item.data('search') || '').toLowerCase();
                        var match = term === '' || haystack.indexOf(term) !== -1;

                        if (match) {
                            item.removeClass('d-none');
                            visibleCount++;
                        } else {
                            item.addClass('d-none');
                        }
                    });

                    if (visibleCount > 0) {
                        noResults.addClass('d-none');
                    } else {
                        noResults.removeClass('d-none');
                    }
                    return;
                }

                groups.each(function() {
                    var group = $(this);
                    var groupVisible = 0;
                    var items = group.find('.transcript-item');
                    var collapse = group.find('.collapse').first();
                    var subgroups = group.find('.transcript-subcategory-pane');

                    items.each(function() {
                        var item = $(this);
                        var haystack = String(item.data('search') || '').toLowerCase();
                        var match = term === '' || haystack.indexOf(term) !== -1;

                        if (match) {
                            item.removeClass('d-none');
                            groupVisible++;
                            visibleCount++;
                        } else {
                            item.addClass('d-none');
                        }
                    });

                    subgroups.each(function() {
                        var subgroup = $(this);
                        var subgroupVisible = subgroup.find('.transcript-item:not(.d-none)').length > 0;
                        var paneId = subgroup.attr('id');
                        var trigger = group.find('.transcript-subcategory-tablink[href="#' + paneId + '"]');

                        if (subgroupVisible) {
                            subgroup.removeClass('d-none');
                            trigger.removeClass('d-none');
                        } else {
                            subgroup.addClass('d-none');
                            trigger.addClass('d-none');
                        }
                    });

                    if (groupVisible > 0) {
                        group.removeClass('d-none');
                        if (term !== '') {
                            if (typeof collapse.collapse === 'function') {
                                collapse.collapse('show');
                            } else {
                                collapse.addClass('show');
                            }
                        }
                        activateVisibleSubgroup(group);
                    } else {
                        group.addClass('d-none');
                    }
                });

                if (visibleCount > 0) {
                    noResults.addClass('d-none');
                } else {
                    noResults.removeClass('d-none');
                }
            };

            searchInput.on('keyup', applySearch);
            searchInput.on('change', applySearch);
            applySearch();
        }
    };
});
