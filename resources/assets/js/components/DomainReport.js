import m from 'mithril';
import icon from '../helpers/icon';
import urlError from '../helpers/urlError';
import Warning from './Warning';

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
