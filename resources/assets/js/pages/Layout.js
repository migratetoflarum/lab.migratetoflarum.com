import m from 'mithril';
import link from '../helpers/link';
import icon from '../helpers/icon';

export default {
    oninit(vnode) {
        vnode.state.copyrightDate = (new Date).getFullYear();
    },
    view(vnode) {
        return m('.app', {
            'data-url': m.route.get(),
        }, [
            m('header', [
                m('nav.navbar.navbar-expand-lg.navbar-dark.bg-dark', m('.container', [
                    link('/', {
                        className: 'navbar-brand',
                    }, [icon('flask'), ' MigrateToFlarum Lab']),
                    m('button.navbar-toggler[type=button][data-toggle=collapse][data-target=#navbar][aria-controls=navbar][aria-expanded=false][aria-label=Toggle navigation]', m('span.navbar-toggler-icon')),
                    m('#navbar.collapse.navbar-collapse', [
                        m('ul.navbar-nav.ml-auto', [
                            m('li.nav-item', link('/', {className: 'nav-link'}, 'Scan a forum')),
                            m('li.nav-item', link('https://clarkwinkelmann.com/flarum', {
                                className: 'nav-link',
                            }, 'Flarum services by Clark Winkelmann')),
                            m('li.nav-item', link('https://discuss.flarum.org/', {
                                className: 'nav-link',
                                target: '_blank',
                            }, 'Flarum Discuss')),
                        ]),
                    ]),
                ])),
            ]),
            m('.container.py-3', vnode.children),
            m('footer.py-3', m('.container.text-center.text-muted', [
                m('p', [
                    'This is a free and ',
                    m('a[href=https://github.com/migratetoflarum/lab.migratetoflarum.com]', 'open-source'),
                    ' service created by ',
                    m('a[href=https://clarkwinkelmann.com/]', 'Clark Winkelmann'),
                ]),
                m('p', '© MigrateToFlarum ' + vnode.state.copyrightDate),
            ])),
        ]);
    },
}
