import m from 'mithril';
import icon from '../helpers/icon';
import urlError from '../helpers/urlError';
import Warning from './Warning';
import parseHSTS from '../helpers/parseHSTS';
import moment from 'moment';

const HeaderReport = {
    view(vnode) {
        let viewIcon = null;

        switch (vnode.attrs.rate) {
            case 'good':
                viewIcon = icon('check', {className: 'text-success'});
                break;
            case 'neutral':
                viewIcon = icon('meh-o', {className: 'text-warning'});
                break;
            case 'bad':
                viewIcon = icon('times', {className: 'text-danger'});
                break;

        }

        return m('tr', [
            m('th', vnode.attrs.name),
            m('td', [
                viewIcon,
                ' ',
                (vnode.attrs.rate === 'bad' ? ['Not found. ', vnode.attrs.suggest] : vnode.attrs.status),
            ]),
        ]);
    },
};

const HSTSReport = {
    view(vnode) {
        const header = vnode.attrs.report.headers && vnode.attrs.report.headers['Strict-Transport-Security'];

        let hsts = null;

        if (header) {
            hsts = parseHSTS(header);
        }

        return m(HeaderReport, {
            name: 'HSTS',
            rate: hsts ? 'good' : 'bad',
            status: (hsts ?
                'Enabled, max age: ' + moment.duration(hsts.maxAge, 'seconds').humanize().replace('a ', '1 ') +
                ', include subdomains: ' + (hsts.includeSubDomains ? 'yes' : 'no') +
                ', preloaded: ' + (hsts.preload ? 'yes' : 'no') : null),
            suggest: ['An HSTS header prevents traffic from being downgraded to HTTP. ', m('a[href=https://scotthelme.co.uk/hsts-the-missing-link-in-tls/]', 'Learn more about it')],
        });
    },
};

const CSPReport = {
    view(vnode) {
        const cspEnforce = vnode.attrs.report.headers && vnode.attrs.report.headers['Content-Security-Policy'];
        const cspReport = vnode.attrs.report.headers && vnode.attrs.report.headers['Content-Security-Policy-Report-Only'];

        return m(HeaderReport, {
            name: 'CSP',
            rate: cspEnforce ? 'good' : (cspReport ? 'neutral' : 'bad'),
            status: cspEnforce ? 'CSP is enabled' : 'CSP is in report only mode',
            suggest: ['CSP headers allow you to whitelist what resources can be used on your page and is an effective mesure against XSS. ', m('a[href=https://scotthelme.co.uk/content-security-policy-an-introduction/]', 'Learn more about it')],
        });
    },
};

const UrlReport = {
    view(vnode) {
        const type = vnode.attrs.type;
        const report = vnode.attrs.report;

        return m('.row', [
            m('.col-sm-2', type.toUpperCase()),
            m('.col-sm-10', [
                (report.type === 'ok' ? [
                    m('p', [icon('check', {className: 'text-success'}), ' responded 200 ok']),
                    (type === 'http' ? m(Warning, {
                        description: 'HTTP is insecure and should\'t be used to serve any content',
                        suggestion: 'Serve a redirect to HTTPS instead',
                    }) : null),
                ] : null),
                (report.type === 'redirect' ? [
                    m('p', [icon('long-arrow-right'), ' redirects to ', report.redirect_to]),
                    (report.status === 302 ? m(Warning, {
                        description: 'This is a temporary redirect',
                        suggestion: 'Consider using a 301 permanent redirect',
                    }) : null),
                    (report.status === 302 && type === 'http' && report.redirect_to.indexOf('https://') === 0 ? m(Warning, {
                        description: 'Redirecting to https with a temporary redirect is insecure',
                        suggestion: 'Consider using a 301 permanent redirect',
                    }) : null),
                ] : null),
                (report.type === 'error' ? (() => {
                    const error = urlError(report);

                    return [
                        icon('times', {className: 'text-danger'}),
                        m(Warning, {
                            description: error.description,
                            suggestion: error.suggest,
                            log: report.exception_message,
                        }),
                    ];
                })() : null),
                (type === 'https' ? [
                    m('table.table', [
                        m('tbody', [
                            m(HSTSReport, {
                                report,
                            }),
                            (report.type === 'ok' ? m(CSPReport, {
                                report,
                            }) : null),
                        ]),
                    ]),
                ] : null),
            ]),
        ]);
    },
};

export default {
    view(vnode) {
        const sameError = vnode.attrs.httpReport.type === 'error' && vnode.attrs.httpsReport.type === 'error' && vnode.attrs.httpReport.exception_message === vnode.attrs.httpsReport.exception_message;

        let error;

        if (sameError) {
            error = urlError(vnode.attrs.httpReport);
        }

        return m('.list-group-item', [
            m('p', '//' + vnode.attrs.address),
            (sameError ? [
                m(Warning, {
                    description: error.description,
                    suggestion: error.suggest,
                    log: vnode.attrs.httpReport.exception_message,
                }),
            ] : [
                m(UrlReport, {
                    report: vnode.attrs.httpReport,
                    type: 'http',
                }),
                m(UrlReport, {
                    report: vnode.attrs.httpsReport,
                    type: 'https',
                }),
            ]),
        ]);
    },
}
