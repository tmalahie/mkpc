(function() {
    'use strict';

    var SCROLL_THRESHOLD = 50;
    var DEBOUNCE_DELAY = 100;
    var MAX_ELEMENTS = 100;

    var sectionStates = {};

    function initSection(container) {
        var section = container.dataset.section;
        if (!section) return;

        var offset = parseInt(container.dataset.offset, 10) || 0;
        var limit = parseInt(container.dataset.limit, 10) || 10;

        sectionStates[section] = {
            container: container,
            offset: offset,
            limit: limit,
            loading: false,
            hasMore: true
        };

        var scrollTimeout = null;
        container.addEventListener('scroll', function() {
            if (scrollTimeout) clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                checkAndLoad(section);
            }, DEBOUNCE_DELAY);
        });

        setTimeout(function() {
            checkIfNeedsMoreContent(section);
        }, 100);
    }

    function checkIfNeedsMoreContent(section) {
        var state = sectionStates[section];
        if (!state || state.loading || !state.hasMore) return;

        var container = state.container;
        if (container.scrollHeight <= container.clientHeight) {
            loadMore(section);
        }
    }

    function checkAndLoad(section) {
        var state = sectionStates[section];
        if (!state || state.loading || !state.hasMore) return;

        var container = state.container;
        var scrollBottom = container.scrollHeight - container.scrollTop - container.clientHeight;

        if (scrollBottom <= SCROLL_THRESHOLD) {
            loadMore(section);
        }
    }

    function loadMore(section) {
        var state = sectionStates[section];
        if (!state || state.loading || !state.hasMore) return;

        state.loading = true;

        var loadingIndicator = createLoadingIndicator();
        var insertTarget = getInsertTarget(state.container, section);
        if (insertTarget.tagName === 'TABLE' || insertTarget.tagName === 'TBODY') {
            var loadingRow = document.createElement('tr');
            loadingRow.className = 'loading-row';
            var loadingCell = document.createElement('td');
            loadingCell.colSpan = 2;
            loadingCell.appendChild(loadingIndicator);
            loadingRow.appendChild(loadingCell);
            insertTarget.appendChild(loadingRow);
        } else {
            state.container.appendChild(loadingIndicator);
        }

        var params = 'section=' + encodeURIComponent(section) +
                     '&offset=' + state.offset +
                     '&limit=' + state.limit;

        o_xhr('home-sections.php', params, function(html) {
            removeLoadingIndicator(state.container);

            html = html.trim();
            if (!html || html === '') {
                state.hasMore = false;
                state.loading = false;
                return true;
            }

            appendContent(state.container, section, html);

            state.offset += state.limit;
            state.loading = false;

            if (state.offset > MAX_ELEMENTS) {
                state.hasMore = false;
            }

            checkIfNeedsMoreContent(section);
            return true;
        });
    }

    function getInsertTarget(container, section) {
        if (section === 'creations') {
            var table = container.querySelector('table');
            if (table) {
                var tbody = table.querySelector('tbody') || table;
                return tbody;
            }
        }
        return container;
    }

    function itemExists(container, dataId) {
        if (!dataId) return false;
        return container.querySelector('[data-id="' + dataId + '"]') !== null;
    }

    function appendContent(container, section, html) {
        var target = getInsertTarget(container, section);
        
        if (section === 'creations') {
            var temp = document.createElement('table');
            temp.innerHTML = '<tbody>' + html + '</tbody>';
            var tbody = temp.querySelector('tbody');
            var rows = tbody.querySelectorAll(':scope > tr');
            rows.forEach(function(row) {
                var dataId = row.getAttribute('data-id');
                if (!itemExists(container, dataId)) {
                    target.appendChild(row);
                }
            });
            if (typeof loadCircuitImgs === 'function') {
                loadCircuitImgs();
            }
        } else {
            var temp = document.createElement('div');
            temp.innerHTML = html;
            while (temp.firstChild) {
                var child = temp.firstChild;
                var dataId = child.nodeType === 1 ? child.getAttribute('data-id') : null;
                if (dataId && itemExists(container, dataId)) {
                    temp.removeChild(child);
                } else {
                    target.appendChild(child);
                }
            }
        }
    }

    function createLoadingIndicator() {
        var indicator = document.createElement('div');
        indicator.className = 'infinite-scroll-loading';
        indicator.innerHTML = '<span class="loading-spinner"></span><span class="loading-text">' + loadingMsg + '...</span>';
        return indicator;
    }

    function removeLoadingIndicator(container) {
        var indicators = container.querySelectorAll('.infinite-scroll-loading, .loading-row');
        indicators.forEach(function(indicator) {
            indicator.parentNode.removeChild(indicator);
        });
    }

    function initAllSections() {
        var sections = document.querySelectorAll('.right_subsection[data-section]');
        sections.forEach(function(section) {
            initSection(section);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllSections);
    } else {
        initAllSections();
    }
})();

