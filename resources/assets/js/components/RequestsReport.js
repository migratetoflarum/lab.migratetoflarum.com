import m from 'mithril';
import RequestReport from './RequestReport';

export default {
    view(vnode) {
        const requests = vnode.attrs.requests;

        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', [
                    'Requests made for the scan ',
                    m('span.badge.badge-light', requests.length),
                ]),
                m('.list-group', requests.map(
                    request => m(RequestReport, {
                        request,
                    })
                )),
            ]),
        ]);
    },
}
