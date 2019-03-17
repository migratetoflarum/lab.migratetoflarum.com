import m from 'mithril';

/**
 * Helper to get a Mithril node for a fontawesome icon
 * @param name Single names will be applied with solid style and the fa- prefix will be added. To specify the type of icon, separate type and icon name by space, and use fa- prefix (Flarum style)
 * @param attrs
 */
export default function (name, attrs = {}) {
    return m('i.fa.' + (name.indexOf(' ') === -1 ? 'fas.fa-' + name : name.split(' ').join('.')), attrs);
}
