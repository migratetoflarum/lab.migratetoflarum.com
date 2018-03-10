import m from 'mithril';

export default function(url, param1, param2) {
    let attrs = {};
    let children;

    // Support for mithril-like parameters
    if (typeof param2 === 'undefined') {
        children = param1;
    } else {
        attrs = param1;
        children = param2;
    }

    attrs.href = url === null ? 'javascript:;' : url;

    if (url === null) {
        attrs.className = (attrs.className ? attrs.className + ' ' : '') + 'disabled';
    } else if (url[0] === '/') {
        attrs.oncreate = m.route.link;

        if (m.route.get() === url ? 'active' : '') {
            attrs.className = (attrs.className ? attrs.className + ' ' : '') + 'active';
        }
    }

    return m('a', attrs, children);
}
