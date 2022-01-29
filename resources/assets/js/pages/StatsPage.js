import m from 'mithril';
import moment from 'moment';
import App from '../utils/App';
import Rating from '../components/Rating';

export default {
    view(vnode) {
        const ratings = ['A+', 'A', 'A-', 'B', 'B-', 'C', 'C-', 'D'].filter(rating => {
            // Only show ratings that have at least one result
            return [App.stats['30d'], App.stats.lifetime].some(group => {
                // We don't need to check both "scans" and "website" groups, because websites is just a subset of scans
                return group.scans.ratings[rating] > 0;
            })
        });

        const columns = [
            [
                m('th', 'Total'),
                m('th', 'Rating'),
                ...ratings.map(rating => m('th', m(Rating, {
                    rating,
                }))),
                m('th', 'Extension count'),
                m('th', 'max'),
                m('th', 'avg'),
                m('th', 'min'),
            ],
            ...[App.stats['30d'], App.stats.lifetime].map(statGroups => {
                const stats = statGroups[vnode.state.websites ? 'websites' : 'scans'];

                return [
                    m('td', stats.total),
                    m('td'),
                    ...ratings.map(rating => m('td', stats.ratings[rating])),
                    m('td'),
                    m('td', stats.extensionCount.max),
                    m('td', stats.extensionCount.avg),
                    m('td', stats.extensionCount.min),
                ];
            }),
        ];

        return m('.row.justify-content-center', [
            m('.col-md-6', [
                m('.btn-group.float-right', [
                    m('.btn', {
                        className: vnode.state.websites ? 'btn-outline-primary' : 'btn-primary active',
                        onclick: () => {
                            vnode.state.websites = false;
                        },
                    }, 'Scans'),
                    m('.btn', {
                        className: vnode.state.websites ? 'btn-primary active' : 'btn-outline-primary',
                        onclick: () => {
                            vnode.state.websites = true;
                        },
                    }, 'Websites'),
                ]),
                m('h2', 'Stats'),
                m('table.table.table-sm.table-hover', m('tbody', [
                    m('tr', [
                        m('th'),
                        m('th', 'last 30 days'),
                        m('th', 'lifetime'),
                    ]),
                    columns[0].map((header, index) => {
                        return m('tr', [
                            header,
                            columns[1][index],
                            columns[2][index],
                        ]);
                    }),
                ])),
                m('p.text-muted', 'Updated ' + moment(App.stats.time).fromNow()),
            ]),
        ]);
    },
}
