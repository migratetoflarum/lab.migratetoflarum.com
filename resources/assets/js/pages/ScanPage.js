import m from 'mithril';
import Store from '../utils/Store';
import icon from '../helpers/icon';
import App from '../utils/App';
import LoadingScreen from '../components/LoadingScreen';
import OtherTools from '../components/OtherTools';
import moment from 'moment';
import DomainReport from '../components/DomainReport';
import ExtensionsReport from '../components/ExtensionsReport';

export default {
    oninit(vnode) {
        vnode.state.scanId = m.route.param('key');
        vnode.state.found = true;
        vnode.state.loading = false;

        vnode.state.scanLoaded = () => {
            const scan = Store.get('scans', vnode.state.scanId);

            if (!scan.attributes.scanned_at) {
                window.Echo.channel('scans.' + scan.id).listen('ScanUpdated', data => {
                    if (data.type === 'scans') {
                        Store.load(data);

                        m.redraw();

                        return;
                    }

                    alert('An error occurred with the sockets !');

                    console.error(data);
                });
            }
        };

        if (Store.get('scans', vnode.state.scanId)) {
            vnode.state.scanLoaded();
        } else {
            m.request({
                method: 'get',
                url: '/api/scans/' + id,
            }).then(response => {
                Store.load(response.data);

                vnode.state.scanLoaded();
            }).catch(() => {
                vnode.state.found = false;
            });
        }
    },
    view(vnode) {
        const scan = Store.get('scans', vnode.state.scanId);

        if (!vnode.state.found) {
            return m('.alert.alert-danger', 'Error loading report');
        }

        if (!scan) {
            return m(LoadingScreen, {
                text: 'Loading...',
            });
        }

        if (!scan.attributes.scanned_at) {
            return m(LoadingScreen, {
                text: 'Scan in progress...',
            });
        }

        if (scan.attributes.report.failed) {
            return m('.alert.alert-danger.my-5.py-5.text-center', [
                m('p', m('strong', 'An error occured while performing this scan :(')),
            ]);
        }

        function reportKey(key, defaultValue = null) {
            const parts = ('attributes.report.' + key).split('.');

            let data = scan;

            for (let part in parts) {
                if (typeof data !== 'object' || data === null || typeof data[parts[part]] === 'undefined') {
                    return defaultValue;
                }

                data = data[parts[part]];
            }

            return data;
        }

        let suggestions = [];

        const urls = reportKey('urls', {});

        if (Object.keys(urls).some(key => key.split('-')[1] === 'http' && urls[key].type === 'ok')) {
            suggestions.push([
                'The forum accepts HTTP connections, which puts your user\'s data and the integrity of your data at risk. ',
                'Deploy HTTPS and redirect traffic to fix it.',
            ]);
        }

        const expectedBaseUrl = reportKey('canonical_url', '').replace(/\/$/, '');
        const baseUrl = reportKey('homepage.boot.base_url');

        if (baseUrl && expectedBaseUrl !== baseUrl) {
            suggestions.push([
                'The config.url of your Flarum does not match the canonical url. ',
                'This will prevent any resource from loading correctly. ',
                'Set the url setting in your config.php to "' + expectedBaseUrl + '" to fix this (currently set to ' + reportKey('homepage.boot.base_url') + '")',
            ]);
        }

        if (reportKey('malicious_access.vendor')) {
            suggestions.push([
                'Your vendor folder is currently being served by your webserver. ',
                'This could expose untrusted scripts to the world and compromise your security. ',
                'Use a rewrite rule to prevent your webserver from serving this folder.',
            ]);
        }

        if (reportKey('malicious_access.storage')) {
            suggestions.push([
                'Your storage folder is currently being served by your webserver. ',
                'This could expose private data (including access tokens) and compromise your security. ',
                'Use a rewrite rule to prevent your webserver from serving this folder.',
            ]);
        }

        return [
            m('', [
                m('button.btn.btn-secondary.float-right', {
                    onclick() {
                        vnode.state.loading = true;

                        m.request({
                            method: 'post',
                            url: '/api/scans',
                            data: {
                                _token: App.csrfToken,
                                website_id: scan.relationships.website.data.id,
                                hidden: scan.attributes.hidden,
                            },
                        }).then(response => {
                            Store.load(response.data);

                            m.route.set('/scans/' + response.data.id);

                            vnode.state.loading = false;
                        }).catch(err => {
                            vnode.state.loading = false;

                            if (err.errors && err.errors.website_id) {
                                alert(err.errors.website_id.join());

                                return;
                            }

                            alert('An error occurred !');

                            console.error(err);
                        });
                    },
                }, 'Scan again'),
                m('h1', 'Report for ' + reportKey('canonical_url')),
            ]),
            suggestions.map(
                suggestion => m('.alert.alert-danger', m('p', suggestion))
            ),
            m('.row', [
                m('.col-md-6', [
                    m('.card.mt-3', [
                        m('.card-body', [
                            m('h2.card-title', 'General'),
                            m('p', 'Scan performed on ' + moment(scan.attributes.scanned_at).format('YYYY-MM-DD HH:mm:ss')),
                            m('p', 'Visibility: ' + (scan.attributes.hidden ? 'this scan won\'t show up on the homepage' : 'this scan might show up on the homepage' )),
                        ]),
                    ]),
                    m('.card.mt-3', [
                        m('.card-body', [
                            m('h2.card-title', 'Canonical url'),
                            (reportKey('multiple_urls') ? [
                                m('.alert.alert-warning', m('p', 'Your forum is answering to multiple urls. Set redirects to a single canonical url. Assets won\'t load correctly on the non-canonical domains')),
                            ] : [
                                m('p', ['The forum canonical url is ', m('a', {
                                    href: reportKey('canonical_url'),
                                    target: '_blank',
                                    rel: 'nofollow',
                                }, reportKey('canonical_url'))]),
                            ]),
                            (baseUrl ? [
                                m('p', ['config.url is ', m('code', baseUrl)]),
                            ] : [
                                m('p', 'Could not find the configured config.url'),
                            ]),
                            m('.list-group', [
                                m(DomainReport, {
                                    address: reportKey('base_address'),
                                    httpReport: reportKey('urls.apex-http'),
                                    httpsReport: reportKey('urls.apex-https'),
                                }),
                                m(DomainReport, {
                                    address: 'www.' + reportKey('base_address'),
                                    httpReport: reportKey('urls.www-http'),
                                    httpsReport: reportKey('urls.www-https'),
                                }),
                            ]),
                        ]),
                    ]),
                ]),
                m('.col-md-6', [
                    m(ExtensionsReport, {
                        scan,
                    }),
                    m('.card.mt-3', [
                        m('.card-body', [
                            m('h2.card-title', 'Security'),
                            m('ul', [
                                m('li', reportKey('malicious_access.vendor') ? [
                                    icon('times', {className: 'text-danger'}),
                                    ' your vendor folder is publicly reachable',
                                ] : [
                                    icon('check', {className: 'text-success'}),
                                    ' vendor folder seem protected',
                                ]),
                                m('li', reportKey('malicious_access.storage') ? [
                                    icon('times', {className: 'text-danger'}),
                                    ' your storage folder is publicly reachable',
                                ] : [
                                    icon('check', {className: 'text-success'}),
                                    ' storage folder seem protected',
                                ]),
                            ]),
                        ]),
                    ]),
                ]),
            ]),
            m(OtherTools),
        ];
    },
}
