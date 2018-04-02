import m from 'mithril';
import icon from '../helpers/icon';
import App from '../utils/App';
import link from '../helpers/link';
import LoginProviders from '../utils/LoginProviders';
import FormGroup from '../components/FormGroup';

export default {
    oninit(vnode) {
        vnode.state.loading = false;
        vnode.state.errors = {};
    },
    view(vnode) {
        return m('', [
            m('.row.justify-content-center', [
                m('.col-md-8', [
                    m('.card', m('.card-body', [
                        m('h2.card-title.text-center', 'Social Login'),
                        m('ul.list-inline.text-center', LoginProviders.map(
                            provider => m('li.list-inline-item', m('a.btn.btn-dark', {
                                href: '/auth/' + provider.key,
                            }, [
                                icon(provider.icon),
                                ' Login with ',
                                provider.title,
                            ]))
                        )),
                    ])),
                    m('.card.mt-3', m('.card-body', [
                        m('h2.card-title.text-center', 'Password Login'),
                        m('form', {
                            onsubmit(event) {
                                event.preventDefault();

                                vnode.state.loading = true;
                                vnode.state.errors = [];

                                m.request({
                                    method: 'post',
                                    url: '/login',
                                    data: {
                                        _token: App.csrfToken,
                                        email: document.getElementById('email').value,
                                        password: document.getElementById('password').value,
                                    },
                                    extract: data => data,
                                }).then(() => {
                                    window.location = '/';

                                    vnode.state.loading = false;
                                }).catch(err => {
                                    vnode.state.loading = false;

                                    try {
                                        const data = JSON.parse(err.responseText);

                                        console.log(data);

                                        if (data.errors) {
                                            vnode.state.errors = data.errors;

                                            return;
                                        }
                                    } catch (e) {
                                        // silence error
                                    }

                                    alert('An error occurred !');

                                    console.error(err);
                                });
                            },
                        }, [
                            m(FormGroup, {
                                label: 'Email',
                                input: {
                                    id: 'email',
                                    type: 'email',
                                    required: true,
                                    autofocus: true,
                                },
                                errors: vnode.state.errors.email || [],
                            }),
                            m(FormGroup, {
                                label: 'Password',
                                input: {
                                    id: 'password',
                                    type: 'password',
                                    required: true,
                                },
                                errors: vnode.state.errors.password || [],
                            }),
                            m('.form-group.row', [
                                m('.col-md-8.offset-md-4', [
                                    m('button[type=submit].btn.btn-primary', 'Login'),
                                    m('a[href=/password/reset].btn.btn-link', 'Forgot your password ?'),
                                ]),
                            ]),
                            m('.text-center.mt-3', link('/register', {className: 'btn btn-secondary'}, 'Want to create a new password account ?')),
                        ]),
                    ])),
                ]),
            ]),
        ]);
    },
}
