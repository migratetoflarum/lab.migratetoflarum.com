import m from 'mithril';
import ExtensionStats from '../components/ExtensionStats';

const SORTING = [
    {
        key: 'id',
        title: 'Extension ID',
    },
    {
        key: 'update_time',
        title: 'Latest version release',
    },
    {
        key: 'time',
        title: 'Initial extension release',
    },
];

const SORTING_ORDERS = [
    {
        prefix: '',
        title: 'Ascending',
    },
    {
        prefix: '-',
        title: 'Descending',
    },
];

const FILTERS = [
    {
        key: 'abandoned',
        title: 'Abandoned',
    },
    {
        key: 'locale',
        title: 'Language Pack',
    },
];

const FILTER_OPTIONS = [
    {
        key: 'all',
        title: 'All',
    },
    {
        key: 'yes',
        title: 'Yes',
    },
    {
        key: 'no',
        title: 'No',
    },
];

export default {
    oninit(vnode) {
        vnode.state.extensions = [];
        vnode.state.nextPage = null;
        vnode.state.totalResults = 0;
        vnode.state.query = {
            sort: '-update_time',
            filter: {
                abandoned: 'no',
                locale: 'no',
            },
        };

        vnode.state.loadPage = () => {
            m.request({
                method: 'get',
                url: vnode.state.nextPage,
            }).then(response => {
                vnode.state.nextPage = response.links.next;
                vnode.state.totalResults = response.meta.total;

                if (response.meta.current_page === 1) {
                    vnode.state.extensions = response.data;
                } else {
                    vnode.state.extensions = vnode.state.extensions.concat(response.data);
                }
            }).catch(error => {
                alert('Error while loading extensions');

                console.error(error);
            });
        };

        const resetNextPage = () => {
            vnode.state.nextPage = '/api/extensions?' + m.buildQueryString(vnode.state.query);
        };

        vnode.state.changeSort = sort => {
            vnode.state.query.sort = sort;

            resetNextPage();

            vnode.state.loadPage();
        };

        vnode.state.changeFilter = (filter, value) => {
            vnode.state.query.filter[filter] = value;

            resetNextPage();

            vnode.state.loadPage();
        };

        vnode.state.changeSearch = q => {
            vnode.state.query.filter.q = q;

            resetNextPage();

            vnode.state.loadPage();
        };

        resetNextPage();
        vnode.state.loadPage();
    },
    view(vnode) {
        return [
            m('h1', 'Extension Analysis'),
            m('p', 'We collect and analyse data about the available extensions. For now the focus has been put on language packs.'),
            m('p', 'Please note that some errors reported by the lab do not actually happen inside Flarum. This is because Flarum is using older libraries that are a bit more tolerant with invalid files. Fixing the errors anyway might save you issues once Flarum gets its libraries up to date.'),
            m('.form-group', m('input[type=text].form-control', {
                value: vnode.state.query.filter.q,
                onchange: m.withAttr('value', value => {
                    vnode.state.changeSearch(value);
                }),
                placeholder: 'Search for a package name or vendor',
            })),
            m('.form-group', m('select.form-control', {
                value: vnode.state.query.sort,
                onchange: m.withAttr('value', value => {
                    vnode.state.changeSort(value);
                }),
            }, SORTING.map(sort => SORTING_ORDERS.map(order => m('option', {
                value: order.prefix + sort.key,
            }, sort.title + ' (' + order.title + ')'))))),
            FILTERS.map(filter => m('.form-group', m('select.form-control', {
                value: vnode.state.query.filter[filter.key],
                onchange: m.withAttr('value', value => {
                    vnode.state.changeFilter(filter.key, value);
                }),
            }, FILTER_OPTIONS.map(option => m('option', {
                value: option.key,
            }, filter.title + ': ' + option.title))))),
            m('p', vnode.state.totalResults + ' extensions matching current filter'),
            vnode.state.extensions.map(
                extension => m(ExtensionStats, {extension})
            ),
            (vnode.state.nextPage && vnode.state.extensions.length ? m('button[type=button].btn.btn-secondary.btn-block.mt-3', {
                onclick() {
                    vnode.state.loadPage();
                },
            }, 'Load more') : null),
        ];
    },
}
