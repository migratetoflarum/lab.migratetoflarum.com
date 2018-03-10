import m from 'mithril';

export default function(name, attrs = {}) {
    return m('i.fa.fa-' + name, attrs);
}
