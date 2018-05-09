import m from 'mithril';
import icon from '../helpers/icon';

// Matches hex, rgb and rgba colors
const colorRegex = /^(#[0-9a-f]{3,6})|(rgba?\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3}(,[0-9]{1,3})?\))$/i;

// Matches fontawesome names after fa-
const iconNameRegex = /^[a-z0-9-]+$/;

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;
        const iconData = extension.attributes.icon;
        let iconStyle = {};
        let iconName = null;

        if (iconData) {
            if (iconData.color && colorRegex.test(iconData.color)) {
                iconStyle.color = iconData.color;
            }

            if (iconData.backgroundColor && colorRegex.test(iconData.backgroundColor)) {
                iconStyle.backgroundColor = iconData.backgroundColor;
            }

            if (iconData.name && iconNameRegex.test(iconData.name)) {
                iconName = iconData.name;
            }

            if (iconData.image) {
                const iconExt = iconData.image.match(/\.(png|jpg|jpeg|svg)$/);

                if (iconExt) {
                    iconStyle.backgroundImage = 'url(https://flagrow.io/storage/icons/' + extension.attributes.package.replace('/', '$') + '-icon.' + iconExt[1] + ')';
                }
            }
        }

        return m('.extension-icon', {
            style: iconStyle,
        }, iconName ? icon(iconName) : null);
    },
}
