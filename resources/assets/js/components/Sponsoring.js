import m from 'mithril';
import App from '../utils/App';
import icon from '../helpers/icon';

export default {
    view() {
        if (!App.sponsoring.text) {
            return null;
        }

        return m('.alert.alert-warning.sponsoring', m('.container', m(App.sponsoring.link ? 'a' : 'div', {
            className: 'text-dark',
            href: App.sponsoring.link,
            target: '_blank',
            rel: 'noopener',
        }, [
            (App.sponsoring.opening ? m('span.font-weight-bold', App.sponsoring.opening + ': ') : null),
            App.sponsoring.text,
            (App.sponsoring.link ? [' ', icon('external-link-alt')] : null),
        ])));
    },
}
