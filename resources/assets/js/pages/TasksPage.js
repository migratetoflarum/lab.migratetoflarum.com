import m from 'mithril';
import moment from 'moment';

export default {
    oninit(vnode) {
        vnode.state.tasks = [];
        vnode.state.totalResults = 0;
        vnode.state.nextPage = '/api/tasks';

        vnode.state.loadPage = () => {
            m.request({
                method: 'get',
                url: vnode.state.nextPage,
                body: {
                    horizon_token: m.parseQueryString(location.search).horizon_token,
                },
            }).then(response => {
                vnode.state.nextPage = response.links.next;
                vnode.state.totalResults = response.meta.total;

                if (response.meta.current_page === 1) {
                    vnode.state.tasks = response.data;
                } else {
                    vnode.state.tasks = vnode.state.tasks.concat(response.data);
                }
            }).catch(error => {
                alert('Error while loading tasks');

                console.error(error);
            });
        };

        vnode.state.loadPage();
    },
    view(vnode) {
        return [
            m('table.table.taskslist', [
                m('thead', m('tr', [
                    m('th', 'ID'),
                    m('th', 'Job'),
                    m('th', 'Started'),
                    m('th', 'Completed'),
                    m('th', 'Data'),
                    m('th', 'Log'),
                ])),
                m('tbody', vnode.state.tasks.map(task => m('tr', [
                    m('td', task.id),
                    m('td', task.attributes.job),
                    m('td', {
                        title: task.attributes.started_at,
                    }, moment(task.attributes.started_at).fromNow()),
                    m('td', {
                        title: task.attributes.completed_at ? task.attributes.completed_at : task.attributes.failed_at,
                    }, task.attributes.completed_at ? [
                        m('span.badge.badge-success', 'Completed'),
                        ' ' + moment(task.attributes.completed_at).fromNow(),
                    ] : [
                        m('span.badge.badge-danger', 'Failed'),
                        ' ' + moment(task.attributes.failed_at).fromNow(),
                    ]),
                    m('td', m('pre', JSON.stringify(task.attributes.data, null, 2))),
                    m('td', [
                        m('pre', task.attributes.public_log),
                        m('br'),
                        m('br'),
                        m('pre', task.attributes.private_log),
                    ]),
                ]))),
            ]),
            (vnode.state.nextPage && vnode.state.tasks.length ? m('button[type=button].btn.btn-secondary.btn-block.mt-3', {
                onclick() {
                    vnode.state.loadPage();
                },
            }, 'Load more') : null),
            m('p.text-center', vnode.state.totalResults + ' tasks'),
        ];
    },
}
