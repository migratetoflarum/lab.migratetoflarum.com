import m from 'mithril';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;
        const version = vnode.attrs.version;

        let expectedNumberOfReceivedStrings = null;

        version.relationships.translations_received.data.forEach(translation => {
            if (translation.relationships.extension_version_provider.data.id !== version.id) {
                return null;
            }

            if (translation.attributes.locale_code !== 'en') {
                return;
            }

            expectedNumberOfReceivedStrings = translation.attributes.strings_count;
        });

        let extensionsProvidedShown = 0;
        let extensionsReceivedShown = 0;

        return [
            m('h5', 'Analysis for version ' + version.attributes.version),
            (version.attributes.locale_errors && version.attributes.locale_errors.length ? m('.alert.alert-warning', [
                m('p', 'We found issues with the locale files provided by this extension:'),
                m('ul', version.attributes.locale_errors.map(error => m('li', error))),
            ]) : null),
            m('h6', 'Translations provided'),
            m('table.table', [
                m('thead', m('tr', [
                    m('th', 'Locale'),
                    m('th', 'Destination package'),
                    m('th', 'Coverage'),
                ])),
                m('tbody', [
                    version.relationships.translations_provided.data.map(
                        translation => {
                            const receiever = translation.relationships.extension_receiver.data;
                            const providedToPackage = receiever ? receiever.attributes.package : null;

                            // Do not show self, it's already shown below in "received"
                            if (providedToPackage === extension.attributes.package) {
                                return null;
                            }

                            extensionsProvidedShown++;

                            return m('tr', [
                                m('td', translation.attributes.locale_code),
                                m('td', receiever ? [
                                    receiever.attributes.title,
                                    ' ',
                                    m('small', m('em', providedToPackage)),
                                ] : [
                                    m('code', translation.attributes.namespace),
                                    ' (not a known extension id)',
                                ]),
                                m('td', translation.attributes.strings_count + ' strings'),
                            ]);
                        },
                    ),
                    (extensionsProvidedShown === 0 ? m('tr', m('td[colspan=3]', m('em', 'This packages does not provide translations to other packages'))) : null),
                ]),
            ]),
            m('h6', 'Translations received'),
            m('table.table', [
                m('thead', m('tr', [
                    m('th', 'Locale'),
                    m('th', 'Source package'),
                    m('th', 'Coverage'),
                ])),
                m('tbody', [
                    version.relationships.translations_received.data.map(
                        translation => {
                            const versionProvider = translation.relationships.extension_version_provider.data;
                            const extensionProvider = versionProvider.relationships.extension.data;

                            // Do not show other versions of this extension as a translation provider
                            if (extensionProvider.id === extension.id && versionProvider.id !== version.id) {
                                return null;
                            }

                            extensionsReceivedShown++;

                            const providedByPackage = extensionProvider.attributes.package;

                            const doesntHaveExpectedStringCount = expectedNumberOfReceivedStrings && expectedNumberOfReceivedStrings !== translation.attributes.strings_count;

                            return m('tr', [
                                m('td', translation.attributes.locale_code),
                                m('td', providedByPackage === extension.attributes.package ? m('em', 'Self') : [
                                    extensionProvider.attributes.title,
                                    ' ',
                                    versionProvider.attributes.version,
                                    ' ',
                                    m('small', m('em', providedByPackage)),
                                ]),
                                m('td', m('.progress', m('.progress-bar', {
                                    className: doesntHaveExpectedStringCount ? 'w-75 bg-warning' : 'w-100 bg-success',
                                }, translation.attributes.strings_count + ' strings'))),
                            ]);
                        },
                    ),
                    (extensionsReceivedShown === 0 ? m('tr', m('td[colspan=3]', m('em', 'No translations found. The package might not contain any string or is using a translation namespace that doesn\'t match the extension id'))) : null),
                ]),
            ]),
        ];
    },
}
