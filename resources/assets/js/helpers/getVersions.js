/**
 * Bridge for old scans which had the version in ScanHomePage
 * And new scans where the version is narrowed down in ScanGuessVersion
 */
export default function (scan) {
    if (!scan.relationships.tasks) {
        return [];
    }

    const versionTask = scan.relationships.tasks.data.find(t => t.attributes.job === 'ScanGuessVersion');

    if (versionTask) {
        return versionTask.attributes.data.versions;
    }

    const homeTask = scan.relationships.tasks.data.find(t => t.attributes.job === 'ScanHomePage');

    if (homeTask) {
        return homeTask.attributes.data.versions || [];
    }

    return [];
}
