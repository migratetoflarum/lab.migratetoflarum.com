import m from 'mithril';
import icon from '../helpers/icon';
import formatBytes from '../helpers/formatBytes';

function bodySizeInfo(request) {
    if (!request.response_body_size) {
        return 'Empty body';
    }

    if (request.response_body_compressed_size) {
        return formatBytes(request.response_body_compressed_size) + ' compressed, ' + formatBytes(request.response_body_size) + ' uncompressed';
    }

    return formatBytes(request.response_body_size) + ' (not compressed)';
}

export default {
    oninit(vnode) {
        vnode.state.extended = false;
    },
    view(vnode) {
        const request = vnode.attrs.request.attributes;

        let resultClass = 'secondary';
        let resultMessage = '??';

        if (request.exception) {
            resultClass = 'warning';
            resultMessage = 'Error';
        } else if (request.response_status_code) {
            resultClass = 'success';
            resultMessage = request.response_status_code + ' ' + request.response_reason_phrase + ', ' + request.duration + 'ms';

            if (request.response_body_compressed_size) {
                resultMessage += ', ' + formatBytes(request.response_body_compressed_size);
            } else if (request.response_body_size) {
                resultMessage += ', ' + formatBytes(request.response_body_size);
            }
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
                    m('code', request.method + ' ' + request.url),
                    ' ',
                    m('.badge.badge-' + resultClass, resultMessage),
                ]),
            ]),
            (vnode.state.extended ? m('.list-group-item', [
                m('h5', 'Request'),
                m('dl.row', [
                    m('dt.col-sm-3', 'Date'),
                    m('dd.col-sm-9', request.fetched_at),
                    m('dt.col-sm-3', 'URL'),
                    m('dd.col-sm-9', m('a', {
                        href: request.url,
                        target: '_blank',
                        rel: 'nofollow noopener',
                    }, request.url)),
                    (request.request_headers ? [
                        m('dt.col-sm-12', 'Headers'),
                        Object.keys(request.request_headers).map(headerName => [
                            m('dt.col-sm-3', m('code', headerName)),
                            m('dd.col-sm-9', request.request_headers[headerName]),
                        ])
                    ] : null),
                ]),
                m('h5', 'Response'),
                (request.response_status_code ? [
                    m('dl.row', [
                        m('dt.col-sm-3', 'Time'),
                        m('dd.col-sm-9', [
                            request.duration + 'ms',
                        ]),
                        m('dt.col-sm-3', 'Body size'),
                        m('dd.col-sm-9', bodySizeInfo(request)),
                        (request.response_headers ? [
                            m('dt.col-sm-12', 'Headers'),
                            Object.keys(request.response_headers).map(headerName => [
                                m('dt.col-sm-3', m('code', headerName)),
                                m('dd.col-sm-9', request.response_headers[headerName]),
                            ])
                        ] : null),
                    ]),
                    (request.method === 'HEAD' ? m('div', m('em', 'Only headers were fetched to save time')) : m('pre', request.response_body)),
                ] : null),
                (request.exception ? [
                    m('.alert.alert-warning', request.exception.message),
                ] : null),
            ]) : null),
        ];
    },
}
