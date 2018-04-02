import m from 'mithril';
import App from '../utils/App';
import link from '../helpers/link';
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
                        m('h2.card-title.text-center', 'Create a password-based account'),
                        m('form', {
                            onsubmit(event) {
                                event.preventDefault();

                                vnode.state.loading = true;
                                vnode.state.errors = [];

                                m.request({
                                    method: 'post',
                                    url: '/register',
                                    data: {
                                        _token: App.csrfToken,
                                        name: document.getElementById('name').value,
                                        email: document.getElementById('email').value,
                                        password: document.getElementById('password').value,
                                        password_confirmation: document.getElementById('password-confirm').value,
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
                                label: 'Name',
                                input: {
                                    id: 'name',
                                    type: 'text',
                                    required: true,
                                    autofocus: true,
                                },
                                errors: vnode.state.errors.name || [],
                            }),
                            m(FormGroup, {
                                label: 'Email',
                                input: {
                                    id: 'email',
                                    type: 'email',
                                    required: true,
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
                            m(FormGroup, {
                                label: 'Confirm Password',
                                input: {
                                    id: 'password-confirm',
                                    type: 'password',
                                    required: true,
                                },
                                errors: vnode.state.errors.password_confirmation || [],
                            }),
                            m('.form-group.row', [
                                m('.col-md-8.offset-md-4', [
                                    m('button[type=submit].btn.btn-primary', 'Register'),
                                    link('/login', {className: 'btn btn-link'}, 'Already got an account ?'),
                                ]),
                            ]),
                        ]),
                    ])),
                ]),
            ]),
        ]);
    },
}
