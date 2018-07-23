import m from 'mithril';
import moment from 'moment';
import icon from '../helpers/icon';
import link from '../helpers/link';
import Rating from './Rating';

export default {
    view(vnode) {
        const scans = vnode.attrs.scans;

        return m('.list-group.list-group-flush', scans.map(
            scan => link('/scans/' + scan.id, {
                className: 'list-group-item list-group-item-action',
            }, [
                m('span.float-right.text-muted', moment(scan.attributes.scanned_at).fromNow()),
                m(Rating, {
                    rating: scan.attributes.rating,
                }),
                ' ',
                scan.relationships.website.data.attributes.name,
                ' - ',
                m('span.text-muted', scan.relationships.website.data.attributes.normalized_url.replace(/\/$/, '')),
                (scan.attributes.hidden ? m('span.text-muted', {
                    title: 'This scan won\'t show up for other users',
                }, [' ', icon('eye-slash')]) : null),
            ])
        ));
    },
}
