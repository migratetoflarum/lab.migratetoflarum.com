import m from 'mithril';
import icon from '../helpers/icon';
import App from '../utils/App';
import OptOutStatus from '../components/OptOutStatus';

export default {
    oninit(vnode) {
        vnode.state.url = '';
        vnode.state.loading = false;
        vnode.state.errors = [];
        vnode.state.websiteStatus = null;

        vnode.state.requestCheck = (checkNow = false) => {
            const body = {
                _token: App.csrfToken,
                url: vnode.state.url,
            };

            if (checkNow) {
                body.check_now = 1;
            }

            m.request({
                method: 'post',
                url: '/api/opt-out-check',
                body,
            }).then(response => {
                vnode.state.websiteStatus = response;

                if (response.check) {
                    window.Echo.channel('opt-out-checks.' + response.check.id).listen('OptOutCheckUpdated', data => {
                        vnode.state.websiteStatus.check = data;

                        m.redraw();
                    });
                }

                vnode.state.loading = false;
            }).catch(err => {
                vnode.state.loading = false;

                if (err.errors && err.errors.url) {
                    vnode.state.errors = err.errors.url;

                    return;
                }

                alert('An error occurred !');

                console.error(err);
            });
        };
    },
    view(vnode) {
        return m('', [
            m('h2.text-center', 'Opt out from the lab'),
            m('.row.justify-content-center', [
                m('form.col-md-6', {
                    onsubmit(event) {
                        event.preventDefault();

                        vnode.state.loading = true;
                        vnode.state.errors = [];

                        vnode.state.requestCheck();
                    },
                }, [
                    m('p', 'If you don\'t want your forum to show up in public scans or in the showcase, you can manually opt out.'),
                    m('p', 'You need to add (and keep) the following meta tag on the homepage of your forum. The easiest is to paste it in Admin > Appearance > Custom Header'),
                    m('pre', '<meta name="migratetoflarum-lab-opt-out" value="yes">'),
                    m('p', 'This won\'t prevent other people from scanning your forum, but the results will always be private and the website will never be added to the showcase.'),
                    m('p', 'Want to opt back in ? Just remove the meta tag.'),
                    m('p', 'Enter your forum url below to check opt out status and ask for a re-check if needed:'),
                    m('.position-relative', [
                        m('.form-group.mt-3', m('.input-group', [
                            m('input.form-control[type=url]', {
                                className: vnode.state.errors.length ? 'is-invalid' : '',
                                placeholder: 'https://yourflarum.tld',
                                value: vnode.state.url,
                                oninput: event => {
                                    vnode.state.url = event.target.value;
                                },
                                disabled: vnode.state.loading,
                            }),
                            m('.input-group-append', m('button.btn.btn-primary[type=submit]', {
                                disabled: vnode.state.loading,
                            }, vnode.state.loading ? 'Loading...' : ['Check ', icon('chevron-right')])),
                        ])),
                        vnode.state.errors.map(
                            error => m('.invalid-tooltip.d-block', {
                                onclick() {
                                    // Hide errors if you click on them
                                    vnode.state.errors = [];
                                },
                            }, error)
                        ),
                    ]),
                    vnode.state.websiteStatus ? m(OptOutStatus, {
                        status: vnode.state.websiteStatus,
                        checkNow() {
                            vnode.state.requestCheck(true);
                        },
                    }) : null,
                ]),
            ]),
        ]);
    },
}
