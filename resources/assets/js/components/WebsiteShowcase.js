import m from 'mithril';
import FlarumVersionString from "./FlarumVersionString";
import icon from '../helpers/icon';

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
    oninit(vnode) {
        vnode.state.showFullDescription = false;
    },
    view(vnode) {
        const {website} = vnode.attrs;

        const meta = website.attributes.showcase_meta;

        let discussionCount = null;
        let userCount = null;
        if (meta) {
            discussionCount = countFormatting(meta.discussionCount);
            userCount = countFormatting(meta.userCount);
        }

        let needForEllipsis = false;
        let description = null;

        if (meta && meta.description) {
            const maxLength = 150;
            needForEllipsis = meta.description.length > maxLength;

            if (needForEllipsis && !vnode.state.showFullDescription) {
                const words = meta.description.split(' ');

                let lengthSoFar = 0;
                for (let i = 0; i < words.length; i++) {
                    lengthSoFar += words[i].length + (i === 0 ? 0 : 1); // +1 for space

                    if (lengthSoFar > maxLength) {
                        if (i === 0) {
                            description = meta.description.slice(0, maxLength);
                        } else {
                            description = words.slice(0, i).join(' ');
                        }
                        break;
                    }
                }
            } else {
                description = meta.description;
            }
        }

        return m('.card.h-100', [
            m('a.card-img-top.showcase-img', {
                href: website.attributes.canonical_url,
                target: '_blank',
                rel: 'nofollow noopener',
                style: website.attributes.screenshot_url ? {
                    backgroundImage: 'url(' + website.attributes.screenshot_url + ')',
                } : {},
            }, website.attributes.screenshot_url ? null : m('.showcase-missing-img', 'No screenshot available')),
            m('.card-body', [
                m('h5.card-title', website.attributes.name),
                m('h6.card-subtitle', [
                    m('a.text-muted', {
                        href: website.attributes.canonical_url,
                        target: '_blank',
                        rel: 'nofollow noopener',
                    }, website.attributes.normalized_url.replace(/\/$/, '')),
                ]),
                description ? m('p.mt-2.showcase-description', {
                    title: 'Forum description',
                }, [
                    description,
                    needForEllipsis ? m('span.text-muted', [
                        vnode.state.showFullDescription ? null : '...',
                        ' ',
                        m('a.text-muted', {
                            href: '#',
                            onclick(event) {
                                event.preventDefault();
                                vnode.state.showFullDescription = !vnode.state.showFullDescription;
                            },
                        }, vnode.state.showFullDescription ? 'Less' : 'More')
                    ]) : null,
                ]) : null,
            ]),
            m('.card-footer', meta ? m('.row.text-center', [
                meta.version ? m('.col', m(FlarumVersionString, {versions: [meta.version]})) : null,
                discussionCount ? m('.col', {
                    title: discussionCount.label,
                }, discussionCount.count + ' discussions') : null,
                userCount ? m('.col', {
                    title: userCount.label,
                }, userCount.count + ' users') : null,
            ]) : null),
        ]);
    }
}
