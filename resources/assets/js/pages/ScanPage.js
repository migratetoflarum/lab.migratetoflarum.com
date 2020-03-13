import m from 'mithril';
import Store from '../utils/Store';
import icon from '../helpers/icon';
import App from '../utils/App';
import LoadingScreen from '../components/LoadingScreen';
import OtherTools from '../components/OtherTools';
import moment from 'moment';
import DomainReport from '../components/DomainReport';
import ExtensionsReport from '../components/ExtensionsReport';
import Rating from '../components/Rating';
import RequestsReport from '../components/RequestsReport';
import getObjectKey from '../helpers/getObjectKey';
import FlarumVersionString from '../components/FlarumVersionString';
import TasksReport from '../components/TasksReport';

export default {
    oninit(vnode) {
        vnode.state.scanId = m.route.param('key');
        vnode.state.found = true;
        vnode.state.loading = false;
        vnode.state.listening = false;
        vnode.state.tasks = [];
        vnode.state.requests = [];

        function fetchScanData() {
            m.request({
                method: 'get',
                url: '/api/scans/' + vnode.state.scanId,
            }).then(response => {
                Store.load(response.data);

                listenForScanUpdate();
            }).catch(() => {
                vnode.state.found = false;
            });
        }

        function loadScanRelationships() {
            const scan = Store.get('scans', vnode.state.scanId);

            window.flarumLabReport = scan;

            vnode.state.tasks = scan.relationships.tasks.data;
            vnode.state.requests = scan.relationships.requests.data;
        }

        function listenForScanUpdate() {
            const scan = Store.get('scans', vnode.state.scanId);

            loadScanRelationships();

            if (!vnode.state.listening && !scan.attributes.scanned_at) {
                vnode.state.listening = true;

                window.Echo.channel('scans.' + scan.id).listen('ScanUpdated', data => {
                    if (data.type === 'scans' && data.id === vnode.state.scanId) {
                        Store.load(data);

                        loadScanRelationships();

                        m.redraw();

                        // Fetch full data
                        fetchScanData();

                        return;
                    }

                    alert('An error occurred with the sockets !');

                    console.error(data);
                });

                window.Echo.channel('scans.' + scan.id).listen('TaskUpdated', data => {
                    if (data.type === 'tasks') {
                        const taskIndex = vnode.state.tasks.findIndex(t => t.id === data.id);

                        if (taskIndex !== -1) {
                            vnode.state.tasks[taskIndex] = data;
                        } else {
                            vnode.state.tasks.push(data);
                        }

                        m.redraw();

                        return;
                    }

                    alert('An error occurred with the sockets !');

                    console.error(data);
                });

                window.Echo.channel('scans.' + scan.id).listen('TaskLog', data => {
                    const task = vnode.state.tasks.find(t => t.id === data.task_id);

                    if (task) {
                        if (!task.attributes.public_log) {
                            task.attributes.public_log = '';
                        }

                        task.attributes.public_log += '\n[' + data.time + '] ' + data.message;

                        m.redraw();
                    }

                    console.warn('Received task log for unknown task ' + data.task_id);
                });

                window.Echo.channel('scans.' + scan.id).listen('RequestUpdated', data => {
                    if (data.type === 'requests') {
                        vnode.state.requests.push(data);

                        m.redraw();

                        return;
                    }

                    alert('An error occurred with the sockets !');

                    console.error(data);
                });
            }
        }

        if (Store.get('scans', vnode.state.scanId)) {
            listenForScanUpdate();
        } else {
            fetchScanData();
        }
    },
    view(vnode) {
        const scan = Store.get('scans', vnode.state.scanId);
        const website = scan.relationships.website.data;

        if (!vnode.state.found) {
            return m('.alert.alert-danger', 'Error loading report');
        }

        if (!scan) {
            return m(LoadingScreen, {
                text: 'Loading...',
            });
        }

        if (!scan.attributes.scanned_at) {
            return m('div', [
                m(TasksReport, {
                    tasks: vnode.state.tasks,
                }),
                m(RequestsReport, {
                    requests: vnode.state.requests,
                }),
                m(LoadingScreen, {
                    text: 'Scan in progress...',
                }),
            ]);
        }

        if (scan.attributes.scanned_at && vnode.state.tasks.length === 0) {
            return m('div', [
                m('.alert.alert-info', 'You are looking at a report created before march 2020. Only the raw data can still be accessed'),
                m('details', m('pre', JSON.stringify(scan.attributes.report, null, 4))),
            ]);
        }

        if (scan.attributes.report && scan.attributes.report.failed) {
            return [
                m('.alert.alert-danger.my-5.py-5.text-center', [
                    m('p', m('strong', 'An error occured while performing this scan :(')),
                    m('p', 'The most likely cause is that the server took too long to answer multiple requests and caused the report to time out.'),
                    m('p', 'You may try scanning the website again later.'),
                    m('p', [
                        'If the problem persists, don\'t hesitate to report the issue on ',
                        m('a', {href: App.githubIssues}, 'GitHub'),
                        ' or the ',
                        m('a', {href: App.discuss}, 'Discuss thread'),
                        '.',
                    ]),
                    m('p', [
                        'Please include the following identifier when making a report: ',
                        m('strong', scan.id),
                    ]),
                ]),
                m('p', 'The following requests were made before the scan failed:'),
                m(RequestsReport, {
                    requests: vnode.state.requests,
                }),
            ];
        }

        let suggestions = [];

        const tasksKeyedByJob = {};
        let hasFailedTasks = false;

        vnode.state.tasks.forEach(task => {
            tasksKeyedByJob[task.attributes.job] = task;

            if (!hasFailedTasks && task.attributes.failed_at) {
                hasFailedTasks = true;
                suggestions.push({
                    danger: true,
                    title: 'Scan failed',
                    suggest: [
                        'One or more background tasks failed. The scan might be incomplete.',
                    ],
                });
            }
        });

        function reportKey(job, key, defaultValue = null) {
            const task = tasksKeyedByJob[job];

            if (!task) {
                console.warn('Trying to access unknown job ' + job);

                return defaultValue;
            }

            return getObjectKey(task.attributes.data, key, defaultValue);
        }

        const versions = reportKey('ScanHomePage', 'versions', []);

        if (versions.some(v => v === '0.1.0-beta.7' || v === '0.1.0-beta.8')) {
            suggestions.push({
                danger: true,
                title: 'Security vulnerability',
                suggest: [
                    'The forum is running a vulnerable Flarum version. ',
                    versions.some(v => v === '0.1.0-beta.7') ? 'Beta 7 was affected by a user details exposure. ' : '',
                    'Beta 8 and earlier were affected by a CSRF bypass issue. ',
                    'Update to beta 9 to fix those issues',
                ],
            });
        }

        reportKey('ScanExposedFiles', 'vulnerabilities', []).forEach(vulnerability => {
            switch (vulnerability) {
                case 'insecure-public-folder':
                    suggestions.push({
                        title: 'Insecure public folder',
                        suggest: [
                            'It appears you didn\'t complete the path customization to use Flarum without a public folder. ',
                            'This leaves all Flarum sensitive files unprotected. ',
                            'Update your webserver root to point to the public folder or complete the instructions to remove the public folder and use the suggested rewrite rules to restrict access to the files instead. ',
                            m('a', {
                                href: 'https://flarum.org/docs/install.html#customizing-paths',
                                target: '_blank',
                                rel: 'nofollow noopener',
                            }, 'Click here to go to the "Customizing Paths" of the Flarum install instructions'),
                            '.',
                        ],
                    });
                    break;
            }
        });

        if (reportKey('ScanExposedFiles', 'vendor.access') === true) {
            suggestions.push({
                danger: true,
                title: 'Vendor folder',
                suggest: [
                    'Your vendor folder is currently being served by your webserver. ',
                    'This could expose untrusted scripts to the world and compromise your security. ',
                    'Use a rewrite rule to prevent your webserver from serving this folder.',
                ],
            });
        }

        if (reportKey('ScanExposedFiles', 'storage.access') === true) {
            suggestions.push({
                danger: true,
                title: 'Storage folder',
                suggest: [
                    'Your storage folder is currently being served by your webserver. ',
                    'This could expose private data (including access tokens) and compromise your security. ',
                    'Use a rewrite rule to prevent your webserver from serving this folder.',
                ],
            });
        }

        if (reportKey('ScanExposedFiles', 'composer.access') === true) {
            suggestions.push({
                danger: true,
                title: 'Composer files',
                suggest: [
                    'Your Composer files are currently being served by your webserver. ',
                    'This could expose advanced information about your server configuration and installed packages. ',
                    'Use a rewrite rule to prevent your webserver from serving the composer.json and composer.lock files.',
                ],
            });
        }

        if (['www-http', 'apex-http'].some(key => reportKey('ScanAlternateUrlsAndHeaders', key, {}).type === 'ok')) {
            suggestions.push({
                title: 'HTTP',
                suggest: [
                    'The forum accepts HTTP connections, which puts your user\'s data and the integrity of your data at risk. ',
                    'Deploy HTTPS and redirect traffic to fix it.',
                ],
            });
        }

        const expectedBaseUrl = (reportKey('ScanResolveCanonical', 'destinationUrl') || '').replace(/\/$/, '');
        const baseUrl = reportKey('ScanHomePage', 'bootBaseUrl');

        if (reportKey('ScanAlternateUrlsAndHeaders', 'multipleUrls') === true) {
            suggestions.push({
                title: 'Multiple urls',
                suggest: [
                    'This Flarum is accepting connections via multiple urls which will result in an invalid config.url value being used for some of them. ',
                    'This will also impact your search engine ranking by creating duplicate content. ',
                    'Setup redirects so only the url defined in your config.php (' + baseUrl + ') can be used to access the forum to fix it.',
                ],
            });
        } else if (baseUrl && expectedBaseUrl !== baseUrl) {
            suggestions.push({
                title: 'config.php url',
                suggest: [
                    'The config.php url setting of your Flarum does not match the canonical url used to access it. ',
                    'This will prevent Flarum from loading and working correctly. ',
                    'Set the url setting in your config.php to "' + expectedBaseUrl + '" to fix this (currently set to "' + baseUrl + '").',
                ],
            });
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
                                website_id: website.id,
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
                m('h1', 'Report for ' + (reportKey('ScanResolveCanonical', 'normalizedUrl') || website.attributes.normalized_url)),
            ]),
            suggestions.map(
                suggestion => m('.alert', {
                    className: suggestion.danger ? 'alert-danger' : 'alert-warning',
                }, m('.row', [
                    m('.col-md-2', m('h5.mt-2', suggestion.title)),
                    m('.col-md-10', m('p', suggestion.suggest)),
                ]))
            ),
            m('.row', [
                m('.col-md-6', [
                    m('.card.mt-3', [
                        m('.card-body', [
                            m('h2.card-title', 'Rating'),
                            m('.row.ScanRatingDetails', [
                                m('.col-md-2.text-center', [
                                    m(Rating, {
                                        rating: reportKey('ScanRate', 'rating'),
                                    }),
                                ]),
                                m('.col-md-10', [
                                    m('ul.list-group.list-group-flush', reportKey('ScanRate', 'criteria', []).map(
                                        rule => m('li.list-group-item', [
                                            (rule.cap ? (rule.cap === '-' ? 'Not rating' : 'Capped to ' + rule.cap) : ''),
                                            (rule.bonus === '+' ? 'Bonus' : ''),
                                            (rule.bonus === '-' ? 'Malus' : ''),
                                            ': ',
                                            rule.description,
                                        ]),
                                    )),
                                ]),
                            ]),
                            m('h3.card-title', 'Details'),
                            m('p', 'Scan performed on ' + moment(scan.attributes.scanned_at).format('YYYY-MM-DD HH:mm:ss')),
                            m('p', 'Visibility: ' + (website.attributes.ignore ? 'This website has opted out and won\'t be visible on the homepage' : (scan.attributes.hidden ? 'this scan won\'t show up on the homepage' : 'this scan might show up on the homepage'))),
                            m('p', [
                                'Flarum version: ',
                                m(FlarumVersionString, {
                                    versions: reportKey('ScanHomePage', 'versions'),
                                }),
                            ]),
                        ]),
                    ]),
                    m('.card.mt-3', [
                        m('.card-body', [
                            m('h2.card-title', 'Canonical url'),
                            reportKey('ScanAlternateUrlsAndHeaders', 'multipleUrls') ? [
                                m('.alert.alert-warning', m('p', 'Your forum is answering to multiple urls. Set redirects to a single canonical url. Assets won\'t load correctly on the non-canonical domains')),
                            ] : [
                                m('p', ['The forum canonical url is ', m('a', {
                                    href: expectedBaseUrl,
                                    target: '_blank',
                                    rel: 'nofollow noopener',
                                }, expectedBaseUrl)]),
                            ],
                            (baseUrl ? [
                                m('p', ['config.url is ', m('code', baseUrl)]),
                            ] : [
                                m('p', 'Could not find the configured config.url'),
                            ]),
                            m('.list-group', [
                                m(DomainReport, {
                                    website,
                                    address: reportKey('ScanResolveCanonical', 'normalizedUrl'),
                                    httpReport: reportKey('ScanAlternateUrlsAndHeaders', 'apex-http'),
                                    httpsReport: reportKey('ScanAlternateUrlsAndHeaders', 'apex-https'),
                                }),
                                m(DomainReport, {
                                    website,
                                    address: 'www.' + reportKey('ScanResolveCanonical', 'normalizedUrl'),
                                    httpReport: reportKey('ScanAlternateUrlsAndHeaders', 'www-http'),
                                    httpsReport: reportKey('ScanAlternateUrlsAndHeaders', 'www-https'),
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
                                {
                                    job: 'ScanExposedFiles',
                                    key: 'vendor.access',
                                    good: 'vendor folder seem protected',
                                    bad: 'your vendor folder is publicly reachable',
                                    neutral: 'Skipped vendor folder check',
                                },
                                {
                                    job: 'ScanExposedFiles',
                                    key: 'storage.access',
                                    good: 'storage folder seem protected',
                                    bad: 'your storage folder is publicly reachable',
                                    neutral: 'Skipped storage folder check',
                                },
                                {
                                    job: 'ScanExposedFiles',
                                    key: 'composer.access',
                                    good: 'Composer files not exposed',
                                    bad: 'your composer.json and/or composer.lock files are publicly readable',
                                    neutral: 'Skipped composer files check',
                                },
                                {
                                    job: 'ScanHomePage',
                                    key: 'debug',
                                    good: 'Debug mode is off',
                                    bad: 'Debug mode is on',
                                    neutral: 'Skipped debug mode check',
                                },
                            ].map(
                                access => m('li', reportKey(access.job, access.key) === true ? [
                                    icon('times', {className: 'text-danger'}),
                                    ' ',
                                    access.bad,
                                ] : (reportKey(access.job, access.key) === false) ? [
                                    icon('check', {className: 'text-success'}),
                                    ' ',
                                    access.good,
                                ] : [
                                    icon('fast-forward', {className: 'text-muted'}),
                                    ' ',
                                    access.neutral,
                                ])
                            )),
                            reportKey('ScanExposedFiles', 'vulnerabilities', []).length > 0 ? m('p.text-danger', 'Known Flarum vulnerabilities detected. See the top of the page for details') : null,
                        ]),
                    ]),
                ]),
            ]),
            m(RequestsReport, {
                requests: vnode.state.requests,
            }),
            m(TasksReport, {
                tasks: vnode.state.tasks,
            }),
            m(OtherTools),
        ];
    },
}
