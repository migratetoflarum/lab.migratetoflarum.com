import m from 'mithril';

export default {
    view(vnode) {
        const rating = vnode.attrs.rating;

        return m('.scan-rating', {
            'data-rating': rating,
        }, rating || '-');
    },
}
