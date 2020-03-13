import m from 'mithril';
import link from '../helpers/link';
import icon from '../helpers/icon';
import App from '../utils/App';
import Sponsoring from '../components/Sponsoring';
import Footer from "./Footer";

export default {
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
                            m('li.nav-item', m('a.nav-link', {
                                href: App.showcaseDomain || '/showcase',
                            }, 'Showcase')),
                            m('li.nav-item', link('/opt-out', {className: 'nav-link'}, 'Opt Out')),
                            m('li.nav-item', m('a.nav-link', {
                                href: App.discuss,
                                target: '_blank',
                                rel: 'noopener',
                            }, ['Discuss thread ', icon('external-link-alt')])),
                        ]),
                    ]),
                ])),
            ]),
            m(Sponsoring),
            m('.container.py-3', vnode.children),
            m(Footer),
        ]);
    },
}
