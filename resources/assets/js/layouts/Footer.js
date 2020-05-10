import m from 'mithril';
import App from "../utils/App";
import icon from "../helpers/icon";

export default {
    oninit(vnode) {
        vnode.state.copyrightDate = (new Date).getFullYear();
    },
    view(vnode) {
        return m('footer', m('.container', [
            m('.row', [
                m('.col-md-6', [
                    m('h5', 'About'),
                    vnode.attrs.isShowcase ? m('p', [
                        'Built With Flarum is a service by ',
                        m('a', {href: 'https://clarkwinkelmann.com/'}, 'Clark Winkelmann'),
                        ' that lists Flarum forums from around the internet. New forums are discovered through the ',
                        m('a', {href: 'https://lab.migratetoflarum.com/'}, 'MigrateToFlarum Lab'),
                        '.',
                    ]) : m('p', [
                        'The Lab is a free and ',
                        m('a', {href: App.githubRepo}, 'open-source'),
                        ' service made for the Flarum community by ',
                        m('a', {href: 'https://clarkwinkelmann.com/'}, 'Clark Winkelmann'),
                        ' to help webmasters check their forum security.',
                    ]),
                    m('p', [
                        'The ',
                        m('a', {href: 'https://flarum.org/'}, 'Flarum'),
                        ' name and logo are property of the Flarum Foundation. ',
                        'This website is not affiliated with the Flarum team.',
                    ]),
                    vnode.attrs.isShowcase ? null : m('p', [
                        'Interested in sponsoring the lab ? ',
                        m('a', {href: 'mailto:' + App.sponsoringEmail}, 'Contact me'),
                        '.'
                    ]),
                ]),
                m('.col-md-3', [
                    m('h5', 'Support'),
                    m('ul', [
                        m('li', m('a', {href: vnode.attrs.isShowcase ? App.showcaseDiscuss : App.discuss}, 'Discuss thread')),
                        m('li', m('a', {href: App.githubIssues}, 'Open a GitHub issue')),
                        m('li', m('a', {href: 'mailto:' + App.supportEmail}, 'Security or legal inquiries')),
                    ]),
                ]),
                m('.col-md-3', [
                    m('h5', 'Ecosystem'),
                    m('ul', [
                        m('li', m('a', {href: 'https://flarum.org/'}, 'Flarum Foundation')),
                        m('li', m('a', {href: 'https://extiverse.com/'}, 'Extiverse')),
                        m('li', m('a', {href: 'https://www.freeflarum.com/'}, 'FreeFlarum')),
                        m('li', vnode.attrs.isShowcase ? m('a', {href: 'https://lab.migratetoflarum.com/'}, 'MigrateToFlarum Lab') : m('a', {href: 'https://builtwithflarum.com/'}, 'Built With Flarum')),
                        m('li', m('a', {href: 'https://query.flarum.dev/'}, 'Query')),
                    ]),
                ]),
            ]),
            m('p.text-center.text-muted.mt-3', '© Clark Winkelmann 2018 - ' + vnode.state.copyrightDate),
        ]));
    },
}
