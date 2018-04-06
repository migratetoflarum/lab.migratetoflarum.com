import m from 'mithril';
import icon from '../helpers/icon';

export default {
    oninit(vnode) {
        vnode.state.extended = false;
    },
    view(vnode) {
        const request = vnode.attrs.request;

        let resultClass = 'secondary';
        let resultMessage = '??';

        if (request.response) {
            resultClass = 'success';
            resultMessage = request.response.status_code + ' ' + request.response.reason_phrase + ', ' + request.response.time + 'ms';
        } else if (request.exception) {
            resultClass = 'warning';
            resultMessage = 'Error';
        }

        return [
            m('a.list-group-item.list-group-item-action', {
                href: '#',
                onclick(event) {
                    event.preventDefault();

                    vnode.state.extended = !vnode.state.extended;
                },
            }, [
                m('', [
                    icon(vnode.state.extended ? 'chevron-up' : 'chevron-down', {
                        className: 'float-right',
                    }),
                    m('code', 'GET ' + request.request.url),
                    ' ',
                    m('.badge.badge-' + resultClass, resultMessage),
                ]),
            ]),
            (vnode.state.extended ? m('.list-group-item', [
                m('h5', 'Request'),
                m('dl.row', [
                    m('dt.col-sm-3', 'Date'),
                    m('dd.col-sm-9', request.request.time),
                    m('dt.col-sm-3', 'URL'),
                    m('dd.col-sm-9', m('a', {
                        href: request.request.url,
                        target: '_blank',
                        rel: 'nofollow',
                    }, request.request.url)),
                    (request.request.headers ? [
                        m('dt.col-sm-12', 'Headers'),
                        Object.keys(request.request.headers).map(headerName => [
                            m('dt.col-sm-3', m('code', headerName)),
                            m('dd.col-sm-9', request.request.headers[headerName]),
                        ])
                    ] : null),
                ]),
                m('h5', 'Response'),
                (request.response ? [
                    m('dl.row', [
                        m('dt.col-sm-3', 'Time'),
                        m('dd.col-sm-9', [
                            request.response.time + 'ms',
                        ]),
                        (request.response.headers ? [
                            m('dt.col-sm-12', 'Headers'),
                            Object.keys(request.response.headers).map(headerName => [
                                m('dt.col-sm-3', m('code', headerName)),
                                m('dd.col-sm-9', request.response.headers[headerName]),
                            ])
                        ] : null),
                    ]),
                    m('pre', request.response.body),
                ] : null),
                (request.exception ? [
                    m('.alert.alert-warning', request.exception.message),
                ] : null),
            ]) : null),
        ];
    },
}
