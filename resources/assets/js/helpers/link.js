import m from 'mithril';

export default function (url, param1, param2) {
    let attrs = {};
    let children;

    // Support for mithril-like parameters
    if (typeof param2 === 'undefined') {
        children = param1;
    } else {
        attrs = param1;
        children = param2;
    }

    if (url === null) {
        attrs.href = 'javascript:;';
        attrs.className = (attrs.className ? attrs.className + ' ' : '') + 'disabled';

        return m('a', attrs, children);
    }

    attrs.href = url;

    if (m.route.get() === url) {
        attrs.className = (attrs.className ? attrs.className + ' ' : '') + 'active';
    }

    return m(m.route.Link, attrs, children);
}
