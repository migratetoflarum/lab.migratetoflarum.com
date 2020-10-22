import m from 'mithril';
import icon from '../helpers/icon';

export default {
    oninit(vnode) {
        vnode.state.extended = false;
    },
    view(vnode) {
        const {
            task,
            taskMeta,
            showLiveLoading,
        } = vnode.attrs;

        let iconNode = icon('hourglass');
        let statusText = 'Waiting';

        if (task) {
            if (task.attributes.completed_at) {
                iconNode = icon('check');
                statusText = 'Done';
            } else if (task.attributes.failed_at) {
                iconNode = icon('times');
                statusText = 'Failed';
            } else {
                iconNode = icon('spinner', {className: 'fa-pulse'});

                if (task.attributes.public_log && task.attributes.public_log.length) {
                    statusText = task.attributes.public_log[task.attributes.public_log.length - 1];
                } else {
                    statusText = 'In progress';
                }
            }
        } else if (!showLiveLoading) {
            iconNode = icon('forward');
            statusText = 'Skipped';
        }

        return [
            m('a.list-group-item.list-group-item-action', {
                href: '#',
                onclick(event) {
                    event.preventDefault();

                    vnode.state.extended = !vnode.state.extended;
                },
            }, [
                m('', [
                    icon(vnode.state.extended ? 'chevron-up' : 'chevron-down', {
                        className: 'float-right',
                    }),
                    iconNode,
                    m('strong', ' ' + taskMeta.title + ' '),
                    statusText,
                ]),
            ]),
            (vnode.state.extended ? (task ? m('.list-group-item', [
                m('h5', 'Meta'),
                m('dl.row', [
                    m('dt.col-sm-3', 'Created'),
                    m('dd.col-sm-9', task.attributes.created_at),
                    m('dt.col-sm-3', 'Started'),
                    m('dd.col-sm-9', task.attributes.started_at),
                    m('dt.col-sm-3', 'Completed'),
                    m('dd.col-sm-9', task.attributes.completed_at),
                    m('dt.col-sm-3', 'Failed'),
                    m('dd.col-sm-9', task.attributes.failed_at),
                ]),
                m('h5', 'Log'),
                m('pre', task.attributes.public_log),
            ]) : m('.list-group-item', m('p', 'No job launched for this task.'))) : null),
        ];
    },
}
