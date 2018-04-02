import m from 'mithril';
import link from '../helpers/link';
import icon from '../helpers/icon';
import App from '../utils/App';

export default {
    oninit(vnode) {
        vnode.state.copyrightDate = (new Date).getFullYear();
    },
    view(vnode) {
        const user = App.user();

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
                            m('li.nav-item', m('a.nav-link', {
                                href: App.flarumServices,
                                target: '_blank',
                            }, ['Flarum services by Clark Winkelmann ', icon('external-link')])),
                            m('li.nav-item', m('a.nav-link', {
                                href: App.discuss,
                                target: '_blank',
                            }, ['Discuss thread ', icon('external-link')])),
                            (user ? [
                                m('li.nav-item.dropdown', [
                                    m('a[href=#][data-toggle=dropdown][aria-haspopup=true][aria-expanded=false].nav-link.dropdown-toggle', user.attributes.name),
                                    m('.dropdown-menu', [
                                        link('/account', {className: 'dropdown-item'}, 'My Account'),
                                        m('button.dropdown-item', {
                                            onclick() {
                                                const form = document.createElement('form');
                                                form.method = 'POST';
                                                form.action = '/logout';

                                                const token = document.createElement('input');
                                                token.type = 'hidden';
                                                token.name = '_token';
                                                token.value = App.csrfToken;

                                                form.appendChild(token);
                                                document.body.appendChild(form);
                                                form.submit();
                                            },
                                        }, 'Logout'),
                                    ]),
                                ]),
                            ] : [
                                m('li.nav-item', link('/login', {className: 'nav-link'}, 'Login')),
                            ]),
                        ]),
                    ]),
                ])),
            ]),
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
                m('p', '© MigrateToFlarum ' + vnode.state.copyrightDate),
            ])),
        ]);
    },
}
