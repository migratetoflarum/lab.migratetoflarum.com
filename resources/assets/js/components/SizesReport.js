import m from 'mithril';
import getObjectKey from '../helpers/getObjectKey';
import SizeReport from './SizeReport';
import formatBytes from '../helpers/formatBytes';

export default {
    oninit(vnode) {
        vnode.state.tab = 'forum';
    },
    view(vnode) {
        const {javascriptSize} = vnode.attrs;

        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', 'Assets size'),
                javascriptSize ? [
                    m('ul.nav.nav-tabs', [
                        m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            onclick(event) {
                                event.preventDefault();
                                vnode.state.tab = 'forum';
                            },
                            className: vnode.state.tab === 'forum' ? 'active' : '',
                        }, [
                            'Forum JS ',
                            m('span.badge.badge-light', formatBytes(getObjectKey(javascriptSize, 'forum.total', '?'))),
                        ])),
                        javascriptSize.admin ? m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            onclick(event) {
                                event.preventDefault();
                                vnode.state.tab = 'admin';
                            },
                            className: vnode.state.tab === 'admin' ? 'active' : '',
                        }, [
                            'Admin JS ',
                            m('span.badge.badge-light', formatBytes(getObjectKey(javascriptSize, 'admin.total', '?'))),
                        ])) : null,
                    ]),
                    m(SizeReport, {
                        total: getObjectKey(javascriptSize, vnode.state.tab + '.total', 1),
                        modules: getObjectKey(javascriptSize, vnode.state.tab + '.modules', []),
                    }),
                ] : m('p', 'No assets size report available.'),
            ]),
        ]);
    },
}