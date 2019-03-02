import m from 'mithril';
import link from "../helpers/link";
import WebsiteShowcase from "../components/WebsiteShowcase";
import chunkArray from "../helpers/chunkArray";

const SORTING = [
    {
        key: 'domain',
        title: 'Domain name',
    },
    {
        key: 'name',
        title: 'Forum name',
    },
    {
        key: 'last_scan',
        title: 'Last scan',
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

export default {
    oninit(vnode) {
        vnode.state.websites = [];
        vnode.state.nextPage = null;
        vnode.state.totalResults = 0;
        vnode.state.query = {
            sort: 'name',
            filter: {},
        };

        vnode.state.loadPage = () => {
            m.request({
                method: 'get',
                url: vnode.state.nextPage,
            }).then(response => {
                vnode.state.nextPage = response.links.next;
                vnode.state.totalResults = response.meta.total;

                if (response.meta.current_page === 1) {
                    vnode.state.websites = response.data;
                } else {
                    vnode.state.websites = vnode.state.websites.concat(response.data);
                }
            }).catch(error => {
                alert('Error while loading showcase');

                console.error(error);
            });
        };

        const resetNextPage = () => {
            vnode.state.nextPage = '/api/websites?' + m.buildQueryString(vnode.state.query);
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
            m('h1', 'Forum showcase'),
            m('p', [
                'Here\'s the list of all forums submitted to the lab.',
                'If you don\'t want your forum on the list, consider ',
                link('/opt-out', 'opting out'),
                '.',
            ]),
            m('.form-group', m('input[type=text].form-control', {
                value: vnode.state.query.filter.q,
                onchange: m.withAttr('value', value => {
                    vnode.state.changeSearch(value);
                }),
                placeholder: 'Search for a forum name or url',
            })),
            m('.form-group', m('select.form-control', {
                value: vnode.state.query.sort,
                onchange: m.withAttr('value', value => {
                    vnode.state.changeSort(value);
                }),
            }, SORTING.map(sort => SORTING_ORDERS.map(order => m('option', {
                value: order.prefix + sort.key,
            }, sort.title + ' (' + order.title + ')'))))),
            m('p', vnode.state.totalResults + ' websites matching current filter'),
            chunkArray(vnode.state.websites, 2).map(
                websites => m('.row', websites.map(
                    website => m('.col-md-6.mb-3', m(WebsiteShowcase, {website}))
                ))
            ),
            (vnode.state.nextPage && vnode.state.websites.length ? m('button[type=button].btn.btn-secondary.btn-block.mt-3', {
                onclick() {
                    vnode.state.loadPage();
                },
            }, 'Load more') : null),
        ];
    },
}
