import m from 'mithril';
import icon from '../helpers/icon';
import App from '../utils/App';
import link from '../helpers/link';
import LoginProviders from '../utils/LoginProviders';

export default {
    oninit(vnode) {
        vnode.state.loading = false;
        vnode.state.errors = [];
    },
    view() {
        const user = App.user();

        if (!user) {
            return link('/login', 'Login to access your account');
        }

        return m('', [
            m('h2', 'My Account'),
            m('.card.mt-3', [
                m('.card-body', [
                    m('h2.card-title', 'My Data'),
                    m('ul', [
                        m('li', 'Name: ' + user.attributes.name),
                        m('li', 'Email: ' + user.attributes.email),
                        m('li', m('a[href=/password/reset]', 'Change password')),
                    ]),
                ]),
            ]),
            m('.card.mt-3', [
                m('.card-body', [
                    m('h2.card-title', 'Social Logins'),
                    m('ul', LoginProviders.map(
                        provider => {
                            const state = user.relationships.socialLogins.data.find(login => login.attributes.driver === provider.key);

                            return m('li', [
                                icon(provider.icon),
                                ' ',
                                provider.title,
                                ': ',
                                (state ? 'Already connected' : m('a.btn.btn-secondary.btn-sm', {
                                    href: '/auth/' + provider.key,
                                }, [
                                    'Connect',
                                ])),
                            ]);
                        }
                    )),
                ]),
            ]),
        ]);
    },
}
