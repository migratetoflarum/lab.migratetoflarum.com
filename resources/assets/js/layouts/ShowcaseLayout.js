import m from 'mithril';
import link from '../helpers/link';
import icon from '../helpers/icon';
import App from '../utils/App';
import Footer from "./Footer";

export default {
    view(vnode) {
        return m('.app.showcase-app', {
            'data-url': m.route.get(),
        }, [
            m('header', [
                m('nav.navbar.navbar-expand-lg.navbar-light', m('.container', [
                    link('/', {
                        className: 'navbar-brand',
                    }, m('.builtwithflarum-logo', 'built with Flarum')),
                    m('button.navbar-toggler[type=button][data-toggle=collapse][data-target=#navbar][aria-controls=navbar][aria-expanded=false][aria-label=Toggle navigation]', m('span.navbar-toggler-icon')),
                    m('#navbar.collapse.navbar-collapse', [
                        m('em.navbar-text.text-muted', 'The user-submitted list of forums powered by Flarum'),
                        m('ul.navbar-nav.ml-auto', [
                            m('li.nav-item', m('a.nav-link', {
                                href: App.baseDomain + '/',
                            }, [
                                icon('flask'),
                                ' Submit forum',
                            ])),
                        ]),
                    ]),
                ])),
            ]),
            m('.container.py-3', vnode.children),
            m(Footer, {
                isShowcase: true,
            }),
        ]);
    },
}
