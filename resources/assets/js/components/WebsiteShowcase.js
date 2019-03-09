import m from 'mithril';
import FlarumVersionString from "./FlarumVersionString";

function countFormatting(count) {
    if (typeof count === 'undefined' || count === null) {
        return null;
    }

    let visibleCount = count;
    let label = count;

    if (count >= 50000) {
        visibleCount = '50k+';
        label = '50000+';
    } else if (count >= 1000) {
        visibleCount = Math.floor(count / 1000) + 'k';
    }

    return {
        count: visibleCount,
        label,
    }
}

export default {
    view(vnode) {
        const {website} = vnode.attrs;

        const meta = website.attributes.showcase_meta;

        let discussionCount = null;
        let userCount = null;
        if (meta) {
           discussionCount = countFormatting(meta.discussionCount);
           userCount = countFormatting(meta.userCount);
        }

        return m('.card', [
            website.attributes.screenshot_url ? m('a.card-img-top.showcase-img', {
                href: website.attributes.canonical_url,
                target: '_blank',
                rel: 'nofollow noopener',
                style: {
                    backgroundImage: 'url(' + website.attributes.screenshot_url + ')',
                },
            }) : null,
            m('.card-body', [
                m('h5.card-title', website.attributes.name),
                m('h6.card-subtitle', m('a', {
                    href: website.attributes.canonical_url,
                    target: '_blank',
                    rel: 'nofollow noopener',
                }, website.attributes.normalized_url.replace(/\/$/, ''))),
                meta ? [
                    meta.description ? m('p.mt-2', {
                        title: 'Forum description',
                    }, meta.description) : null,
                    m('.row.text-center.mt-3', [
                        meta.version ? m('.col', m(FlarumVersionString, {version: meta.version})) : null,
                        discussionCount ? m('.col', {
                            title: discussionCount.label,
                        }, discussionCount.count + ' discussions') : null,
                        userCount ? m('.col', {
                            title: userCount.label,
                        }, userCount.count + ' users') : null,
                    ]),
                ] : null,
            ]),
        ]);
    }
}
