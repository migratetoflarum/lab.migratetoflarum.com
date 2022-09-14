import m from 'mithril';
import formatBytes from '../helpers/formatBytes';
import icon from '../helpers/icon';

const CLASSES = ['bg-success', 'bg-primary', 'bg-warning', 'bg-danger'];

const SHOW_MORE_AFTER = 5;

export default {
    oninit(vnode) {
        vnode.state.more = false;
    },
    view(vnode) {
        const {
            total,
            modules,
        } = vnode.attrs;

        //const maxSize = modules.length ? Math.max(modules[0].size, 1) : 1;

        return m('table.table.table-sm', [
            m('thead', m('tr', [
                m('th'),
                m('th', formatBytes(total)),
                m('th', 'Total'),
            ])),
            m('tbody', [
                modules.slice(0, vnode.state.more ? modules.length : SHOW_MORE_AFTER).map((module, index) => {
                    let name = module.id;
                    let infoText;

                    switch (name) {
                        case 'core':
                            name = 'Flarum Core';
                            infoText = 'The base javascript required for Flarum';
                            break;
                        case 'textformatter':
                            name = 'Flarum TextFormatter';
                            infoText = 'The javascript added by Flarum\'s formatting features. Extensions can affect its size';
                            break;
                    }

                    const percent = module.size / total * 100;

                    return m('tr', [
                        m('td', m('.progress.module-size-progress', {
                            title: percent < 0.1 ? '<0.1%' : (Math.round(percent * 10) / 10) + '%',
                        }, m('.progress-bar', {
                            className: name === 'other' ? 'bg-dark' : CLASSES[index % CLASSES.length],
                            style: {
                                width: Math.max(percent, 6) + '%', // Minimum % wide
                            },
                        }))),
                        m('td', formatBytes(module.size)),
                        m('td', [
                            name,
                            infoText ? [
                                ' ',
                                icon('fas fa-info-circle', {
                                    className: 'text-secondary',
                                    title: infoText,
                                }),
                            ] : null,
                            module.dev ? [
                                ' ',
                                icon('fas fa-exclamation-triangle', {
                                    className: 'text-warning',
                                    title: 'This module appears to be compiled in development mode',
                                }),
                            ] : null,
                        ]),
                    ]);
                }),
                modules.length > SHOW_MORE_AFTER ? m('tr', m('td', {colspan: 3}, m('button.btn.btn-sm.btn-link.btn-block', {
                    onclick() {
                        vnode.state.more = !vnode.state.more;
                    },
                }, vnode.state.more ? 'Show less' : 'Show more'))) : null,
            ]),
        ]);
    },
}
