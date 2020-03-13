import m from 'mithril';
import moment from 'moment';
import icon from '../helpers/icon';
import link from '../helpers/link';
import Rating from './Rating';
import FlarumVersionString from './FlarumVersionString';

function getVersions(scan) {
    const task = scan.relationships.tasks && scan.relationships.tasks.data.find(t => t.attributes.job === 'ScanHomePage');

    if (task && task.attributes.data.versions) {
        return task.attributes.data.versions;
    }

    return [];
}

export default {
    view(vnode) {
        const scans = vnode.attrs.scans;

        return m('.list-group.list-group-flush', scans.map(
            scan => link('/scans/' + scan.id, {
                className: 'list-group-item list-group-item-action',
            }, m('.row', [
                m('.col-1', m(Rating, {
                    rating: scan.attributes.rating,
                })),
                m('.col-8', [
                    m('div', [
                        scan.relationships.website.data.attributes.name,
                        ' - ',
                        m('span.text-muted', scan.relationships.website.data.attributes.normalized_url.replace(/\/$/, '')),
                        (scan.attributes.hidden || scan.relationships.website.data.attributes.ignore ? m('span.text-muted', {
                            title: 'This scan won\'t show up for other visitors, and the link will be hidden once you refresh the page',
                        }, [' ', icon('eye-slash')]) : null),
                    ]),
                    m('.text-muted', [
                        m(FlarumVersionString, {
                            versions: getVersions(scan),
                        }),
                        ' - ',
                        (scan.relationships.extensions ? scan.relationships.extensions.data.length : '?') + ' extensions',
                    ]),
                ]),
                m('.col-3.text-muted.text-right', moment(scan.attributes.scanned_at).fromNow()),
            ]))
        ));
    },
}
