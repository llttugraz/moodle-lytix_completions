import Templates from 'core/templates';
import Widget from 'lytix_helper/widget';
import {makeLoggingFunction} from 'lytix_logs/logs';

const widgetId = 'activity-completion';

// This is meant to be rendered by mustache.
const view = {
    entries: [],
    filters: []
};

// File is stale.
// Groups row elements by module (== type of activity); used for filtering.
const rowsByModule = {};

// Cache for DOM elements.
const elements = {
    tableContainer: null,
    tbody: null,
    entryElements: null
};

let log; // Will be the logging function.

/**
 * Sorts the received data, caches it, and reorders the rows of the already rendered table.
 *
 * @param {String} criterium One of the following: open, name, module, done.
 *    Defaults to most recent criterium, defaults to ‘name’ on first call.
 * @param {bool} descending Specifiy if order should be descending or not (→ ascending).
 *    Defaults to most recent order, defaults to true on first call.
 */

// These are used to save sorting criterium and order between calls.
// Mostly necessary for sorting after filtering.
let
    currentCriterium = 'name',
    currentlyDescending = true;

const sortingFunctions = {
    name: (a, b) => a.name.localeCompare(b.name) * (currentlyDescending ? 1 : -1),
    module: (a, b) => a.module.localeCompare(b.module) * (currentlyDescending ? 1 : -1),
    open: (a, b) => currentlyDescending ? a.open < b.open : a.open > b.open,
    done: (a, b) => currentlyDescending ? a.open > b.open : a.open < b.open
};

const sort = (criterium = currentCriterium, descending = currentlyDescending) => {

    currentCriterium = criterium;
    currentlyDescending = descending;

    const sortedData = Array.from(view.entries).sort(sortingFunctions[criterium]);

    const hiddenElements = [];
    const length = sortedData.length;
    for (let i = 0; i < length; ++i) {
        const row = sortedData[i].element;
        if (row.hidden) {
            hiddenElements.push(row);
        } else {
            elements.tbody.appendChild(row);
        }
    }
    for (let i = hiddenElements.length - 1; i >= 0; --i) {
        elements.tbody.appendChild(hiddenElements[i]);
    }
};


const
    hide = element => {
        elements.tbody.appendChild(element);
        element.setAttribute('hidden', '');
    },
    reveal = element => element.removeAttribute('hidden');

/**
 * Hides or reveals rows of the specified module (== activity type).
 *
 * @function filter
 * @param {String} module A lowercase string (as received by backend).
 * @param {bool} isVisible Specifies if the given activity type shall be visible or hidden.
 */
const filter = (module, isVisible) => {
    const
        elements = rowsByModule[module],
        action = isVisible ? reveal : hide;
    for (let i = elements.length - 1; i >= 0; --i) {
        action(elements[i]);
    }
    if (isVisible) {
        sort();
    }
};


export const init = (userid, contextid, courseid) => {

    const dataPromise = Widget.getData(
        'lytix_completions_activity_completion_get',
        {contextid: contextid, courseid: courseid}
    )
    .then(data => {
        const count = data.Name.length;
        if (count > 0) {
            data.count = count;
            return data;
        }
        throw new Widget.NoDataError();
    });

    const stringsPromise = Widget.getStrings({
        lytix_completions: { // eslint-disable-line camelcase
            identical: [
                'forum',
                'grade',
                'submission',
                'resource',
                'quiz',
                'video',
                'bbb',
                'label',
                'feedback',
                'assign',
            ],
        },
    });

    elements.tableContainer = document.getElementById('activity-completion-table');
    log = makeLoggingFunction(userid, courseid, contextid, 'activity completion');

    Promise.all([stringsPromise, dataPromise])
    .then(values => {
        const
            strings = values[0],
            data = values[1];

        // This event handler is only a named function because it needs to be removed during its first invocation.
        const addCsvDownload = function(event) {
            event.preventDefault();

            // eslint-disable-next-line promise/catch-or-return
            Templates.render('lytix_completions/activity_completion_csv', view)
            .then(csv => {
                const csvData = new Blob([csv], {type: 'text/plain;charset=utf-8'});
                this.href = window.URL.createObjectURL(csvData);
                this.target = '_blank';
                this.download = '<Statistic_>' + new Date().toLocaleDateString() + '.csv';

                this.removeEventListener('click', addCsvDownload);
                this.click();
                log('DOWNLOAD', 'CSV');

                return;
            });
        };

        const
            count = data.count,
            entries = view.entries,
            hiddenModules = ['label'], // Modules that are hidden by default.
            hiddenModulesSet = new Set(hiddenModules);

        for (let i = 0; i < count; ++i) {
            // The terms ‘module’ and ‘type’ both refer to the same thing: They describe the kind of activity (forum, quiz, …).
            // In this context ‘type’ labels the string meant to be read by users.
            // ‘module’ is the string that’s being used internally.
            const
                module = data.Module[i],
                type = strings[module],
                done = data.Done[i],
                total = done + data.Open[i];

            view.entries.push({
                type: type,
                module: module,
                id: data.Id[i],
                name: data.Name[i],
                total: total,
                donePercentage: done * 100 / total,
                done: done,
                open: data.Open[i]
            });
            // Populate rowsByModule for filtering.
            if (!rowsByModule.hasOwnProperty(module)) {
                rowsByModule[module] = [];
                view.filters.push({
                    id: widgetId + '-filter-' + module, // Only needed for <label for='id'>.
                    module: module,
                    hidden: hiddenModulesSet.has(module),
                    label: type
                });
            }
        }

        // Make sure filtering options appear in alphabetical order.
        view.filters.sort((a, b) => {
            // TODO: maybe consider locale
            if (a.label < b.label) {
                return -1;
            }
            if (a.label > b.label) {
                return 1;
            }
            return 0;
        });

        return Templates.render('lytix_completions/activity_completion', view)
        .then(html => {
            elements.tableContainer.innerHTML = html;
            elements.tbody = document.getElementById('activity-completion-entries');

            // Connect each data entry with its <tr> element.
            elements.entryElements = elements.tbody.getElementsByTagName('tr');
            for (let i = entries.length - 1; i >= 0; --i) {
                const element = entries[i].element = elements.entryElements[i];
                rowsByModule[entries[i].module].push(element);
            }

            for (let i = hiddenModules.length - 1; i >= 0; --i) {
                const module = hiddenModules[i];
                if (rowsByModule.hasOwnProperty(module)) {
                    filter(module, false);
                }
            }

            elements.tableContainer.removeAttribute('hidden');

            // That <th> is currently used for sorting.
            let currentlySortedBy = document.querySelector('#activity-completion-head .sorted');
            document.getElementById('activity-completion-head').addEventListener('click', function(event) {
                let
                    th = event.target,
                    criterium = th.dataset.criterium;

                while (!criterium && th !== this) {
                    th = th.parentElement;
                    criterium = th.dataset.criterium;
                }
                if (!criterium) {
                    return;
                }

                currentlySortedBy?.classList.remove('sorted');
                currentlySortedBy = th;
                currentlySortedBy.classList.add('sorted');

                // Determine NEW order.
                const descending = th.dataset.order != 'descending';
                th.dataset.order = descending ? 'descending' : 'ascending';

                sort(criterium, descending);
                log('SORT', descending ? 'DESC' : 'ASC', criterium);
            });

            document.getElementById('activity-filter').addEventListener('change', event => {
                filter(event.target.dataset.module, event.target.checked);
                log('FILTER', 'ON', event.target.dataset.module);
            });

            document.getElementById('export-table-button').addEventListener('click', addCsvDownload);

            // Sort after activity, to have an explicitly defined sorting order (instead of whatever comes from backend).
            sort('module', true);

            return;
        });
    })
    .finally(() => {
        document.getElementById(widgetId).classList.remove('loading');
    })
    .catch(error => Widget.handleError(error, widgetId));
};
