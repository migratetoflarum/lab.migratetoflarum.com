import m from 'mithril';

export default {
    view(vnode) {
        const {website} = vnode.attrs;

        return m('.card', m('.card-body', [
            m('h5.card-title', website.attributes.name),
            m('h6.card-subtitle', m('a', {
                href: website.attributes.canonical_url,
                target: '_blank',
                rel: 'nofollow noopener',
            }, website.attributes.normalized_url.replace(/\/$/, ''))),
        ]));
    }
}
