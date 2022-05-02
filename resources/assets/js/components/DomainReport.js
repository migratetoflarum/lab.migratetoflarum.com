import m from 'mithril';
import icon from '../helpers/icon';
import urlError from '../helpers/urlError';
import Warning from './Warning';
import parseHSTS from '../helpers/parseHSTS';
import moment from 'moment';
import httpStatusCodes from '../helpers/httpStatusCodes';

const HeaderReport = {
    view(vnode) {
        let viewIcon = null;

        switch (vnode.attrs.rate) {
            case 'good':
                viewIcon = icon('check', {className: 'text-success'});
                break;
            case 'neutral':
                viewIcon = icon('meh', {className: 'text-warning'});
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
            suggest: [m('a[href=https://scotthelme.co.uk/hsts-the-missing-link-in-tls/]', 'HSTS headers'), ' prevent traffic from being downgraded to HTTP'],
        });
    },
};

function shouldShowCSPReport(report) {
    return report.type === 'ok' && report.headers && (report.headers['Content-Security-Policy'] || report.headers['Content-Security-Policy-Report-Only']);
}

const CSPReport = {
    view(vnode) {
        const cspEnforce = vnode.attrs.report.headers && vnode.attrs.report.headers['Content-Security-Policy'];
        const cspReport = vnode.attrs.report.headers && vnode.attrs.report.headers['Content-Security-Policy-Report-Only'];

        return m(HeaderReport, {
            name: 'CSP',
            rate: cspEnforce ? 'good' : (cspReport ? 'neutral' : 'bad'),
            status: cspEnforce ? 'CSP is enabled' : 'CSP is in report only mode',
            suggest: [m('a[href=https://scotthelme.co.uk/content-security-policy-an-introduction/]', 'CSP headers'), ' allow you to whitelist what resources can be used on your page and is an effective measure against XSS'],
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
                    m('p', [icon('check', {className: 'text-success'}), ' Got valid response']),
                    (type === 'http' ? m(Warning, {
                        description: 'HTTP is insecure and should\'t be used to serve any content',
                        suggestion: 'Serve a redirect to HTTPS instead',
                    }) : null),
                ] : null),
                (report.type === 'redirect' ? [
                    m('p', [icon('long-arrow-alt-right'), ' ' + (report.status === 301 ? 'Permanent' : 'Temporary') + ' redirect to ', (report.redirect_to ? report.redirect_to : m('em', '(no url)'))]),
                    (report.status === 302 && report.redirect_to ? [
                        m(Warning, {
                            description: report.redirect_to.indexOf('https://') === 0 ?
                                'Temporary redirects to https offer no security forward in time' :
                                'Temporary redirects aren\'t cached by browsers and search engines',
                            suggestion: 'Consider using a 301 permanent redirect instead (the browsers and search engines will cache it)',
                        }),
                    ] : null),
                    (report.redirect_to ? null : [
                        m(Warning, {
                            description: 'This redirect appears broken (no Location header found)',
                        }),
                    ]),
                ] : null),
                (report.type === 'httperror' ? (() => {
                    return [
                        icon('times', {className: 'text-danger'}),
                        ' Received status code ' + report.status + ' (' + (httpStatusCodes[report.status] || 'Unknown status code') + ')',
                    ];
                })() : null),
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
                            (shouldShowCSPReport(report) ? m(CSPReport, {
                                report,
                            }) : null),
                        ]),
                    ]),
                ] : null),
            ]),
        ]);
    },
};

function cleanCurlErrorMessage(address, message) {
    if (typeof message !== 'string') {
        return message;
    }

    // Some cURL error messages include the URL which prevent us from using exact string comparison to tell if it's the same problem
    // So we'll remove those URLs before comparing the strings
    return message.replace('http://' + address, '').replace('https://' + address, '');
}

function isSameError(address, report1, report2) {
    if (report1.type !== 'error' || report2.type !== 'error') {
        return false;
    }

    return cleanCurlErrorMessage(report1.exception_message) === cleanCurlErrorMessage(report2.exception_message);
}

export default {
    oninit(vnode) {
        vnode.state.showUnimportantDomain = false;
    },
    view(vnode) {
        // If the report does not exist or is incomplete for this domain we don't show anything
        // Most likely a report for an IP that has no www domain
        if (!vnode.attrs.httpReport || !vnode.attrs.httpsReport) {
            return null;
        }

        const sameError = isSameError(vnode.attrs.address, vnode.attrs.httpReport, vnode.attrs.httpsReport);

        let error;

        if (sameError) {
            error = urlError(vnode.attrs.httpReport);
        }

        let domainMatters = true;

        // The subdomain www is not important if it isn't directly under the apex
        if (vnode.attrs.address.indexOf('www.') === 0 && !vnode.attrs.website.attributes.is_apex) {
            domainMatters = false;
        }

        let showDomain = true;

        // If the unimportant subdomain does not resolve we simply don't show it
        if (!domainMatters && error && error.description === 'Could not resolve host' && !vnode.state.showUnimportantDomain) {
            showDomain = false;
        }

        return m('.list-group-item', [
            m('p', '//' + vnode.attrs.address),
            (showDomain ? [
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
            ] : [
                m('.btn.btn-sm.btn-block.btn-light', {
                    onclick() {
                        vnode.state.showUnimportantDomain = true;
                    },
                }, 'Not used. Show details anyway ?'),
            ]),
        ]);
    },
}
