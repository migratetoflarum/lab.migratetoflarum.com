/**
 * Bridge for old scans which had the version in ScanHomePage
 * And new scans where the version is narrowed down in ScanGuessVersion
 */
export default function (scan) {
    if (!scan.relationships.tasks) {
        return [];
    }

    const versionTask = scan.relationships.tasks.data.find(t => t.attributes.job === 'ScanGuessVersion');

    // Check if data is loaded because the Pusher status update doesn't contain it during the progress bar
    if (versionTask && versionTask.attributes.data) {
        return versionTask.attributes.data.versions;
    }

    const homeTask = scan.relationships.tasks.data.find(t => t.attributes.job === 'ScanHomePage');

    if (homeTask && homeTask.attributes.data) {
        return homeTask.attributes.data.versions || [];
    }

    return [];
}
