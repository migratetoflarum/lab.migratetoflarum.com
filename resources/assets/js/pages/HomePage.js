import m from 'mithril';
import icon from '../helpers/icon';
import sortByAttribute from '../helpers/sortByAttribute';
import sortByRating from '../helpers/sortByRating';
import App from '../utils/App';
import Store from '../utils/Store';
import ScansList from '../components/ScansList';

export default {
    oninit(vnode) {
        vnode.state.url = '';
        vnode.state.hidden = false;
        vnode.state.loading = false;
        vnode.state.errors = [];
    },
    view(vnode) {
        const recentScans = Store.all('scans').sort(sortByAttribute('scanned_at', 'desc')).slice(0, 5);
        const bestScans = Store.all('scans').sort(sortByRating()).slice(0, 5);

        return m('.page-home', [
            m('h2.text-center', 'Check the configuration of your Flarum'),
            m('.row.justify-content-center.new-scan-area', [
                m('form.col-md-6', {
                    onsubmit(event) {
                        event.preventDefault();

                        vnode.state.loading = true;
                        vnode.state.errors = [];

                        m.request({
                            method: 'post',
                            url: '/api/scans',
                            data: {
                                _token: App.csrfToken,
                                url: vnode.state.url,
                                hidden: vnode.state.hidden,
                            },
                        }).then(response => {
                            Store.load(response.data);

                            m.route.set('/scans/' + response.data.id);

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
                    },
                }, [
                    m('.position-relative', [
                        m('.form-group.mt-3', m('.input-group', [
                            m('input.form-control[type=url]', {
                                className: vnode.state.errors.length ? 'is-invalid' : '',
                                placeholder: 'https://yourflarum.tld',
                                value: vnode.state.url,
                                oninput: m.withAttr('value', value => {
                                    vnode.state.url = value;
                                }),
                                disabled: vnode.state.loading,
                            }),
                            m('.input-group-append', m('button.btn.btn-primary[type=submit]', {
                                disabled: vnode.state.loading,
                            }, vnode.state.loading ? 'Processing...' : ['Scan ', icon('chevron-right')])),
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
                    m('.form-group.text-center', m('label', m('input[type=checkbox]', {
                        checked: vnode.state.hidden,
                        disabled: vnode.state.loading,
                        onchange() {
                            vnode.state.hidden = !vnode.state.hidden;
                        },
                    }), ' Do not show the results on the homepage')),
                ]),
            ]),
            m('.row', [
                m('.col-md-6', [
                    m('h5', 'Best ratings'),
                    m(ScansList, {
                        scans: bestScans,
                    }),
                ]),
                m('.col-md-6', [
                    m('h5', 'Recent scans'),
                    m(ScansList, {
                        scans: recentScans,
                    }),
                ]),
            ]),
        ]);
    },
}
