export default function (header) {
    // Take first header
    if (Array.isArray(header)) {
        header = header[0];
    }

    let report = {
        maxAge: null,
        includeSubDomains: false,
        preload: false,
    };

    header.split(';').forEach(directive => {
        if (directive.indexOf('preload') !== -1) {
            report.preload = true;

            return;
        }

        if (directive.indexOf('includeSubDomains') !== -1) {
            report.includeSubDomains = true;

            return;
        }

        const parts = directive.split('=');

        if (parts[0].indexOf('max-age') !== -1 && parts.length > 1) {
            report.maxAge = parseInt(parts[1]);
        }
    });

    return report;
}
