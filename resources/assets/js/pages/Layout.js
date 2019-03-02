import m from 'mithril';
import link from '../helpers/link';
import icon from '../helpers/icon';
import App from '../utils/App';
import Sponsoring from '../components/Sponsoring';

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
                            m('li.nav-item', link('/showcase', {className: 'nav-link'}, 'Showcase')),
                            m('li.nav-item', link('/extensions', {className: 'nav-link'}, 'Extension Analysis')),
                            m('li.nav-item', link('/opt-out', {className: 'nav-link'}, 'Opt Out')),
                            m('li.nav-item', m('a.nav-link', {
                                href: App.discuss,
                                target: '_blank',
                                rel: 'noopener',
                            }, ['Discuss thread ', icon('external-link')])),
                        ]),
                    ]),
                ])),
            ]),
            m(Sponsoring),
            m('.container.py-3', vnode.children),
            m('footer.py-3', m('.container.text-center.text-muted', [
                m('p', [
                    'This is a free and ',
                    m('a', {href: App.githubRepo}, 'open-source'),
                    ' service created by ',
                    m('a[href=https://clarkwinkelmann.com/]', 'Clark Winkelmann'),
                ]),
                m('p', [
                    'Please report any issue on ',
                    m('a', {href: App.githubIssues}, 'GitHub'),
                    ' or in the ',
                    m('a', {href: App.discuss}, 'Discuss thread'),
                    '. Contact ',
                    m('a', {href: 'mailto:' + App.supportEmail}, App.supportEmail),
                    ' for security or legal issues',
                ]),
                m('p', [
                    'Interested in sponsoring the lab ? Contact ',
                    m('a', {href: 'mailto:' + App.sponsoringEmail}, App.sponsoringEmail),
                ]),
                m('p', '© MigrateToFlarum ' + vnode.state.copyrightDate),
            ])),
        ]);
    },
}
