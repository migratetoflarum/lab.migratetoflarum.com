import m from 'mithril';
import App from "../utils/App";
import icon from "../helpers/icon";

export default {
    oninit(vnode) {
        vnode.state.copyrightDate = (new Date).getFullYear();
    },
    view(vnode) {
        return m('footer.py-3', m('.container.text-center.text-muted', [
            m('p', [
                'Free and ',
                m('a', {href: App.githubRepo}, 'open-source'),
                ' service made with ',
                icon('far fa-heart'),
                ' for the Flarum community by ',
                m('a[href=https://clarkwinkelmann.com/]', 'Clark Winkelmann'),
            ]),
            m('p', [
                'The ',
                m('a[href=https://flarum.org/]', 'Flarum'),
                ' name and logo are property of Toby Zerner. ',
                'This website is not affiliated with the Flarum team',
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
        ]));
    },
}
