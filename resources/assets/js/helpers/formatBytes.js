export default function (bytes) {
    // Return unknown values as-it
    if (!bytes || bytes === '?') {
        return bytes;
    }

    if (bytes > 1000000) {
        return (Math.floor(bytes / 100000) / 10) + ' MB';
    }

    if (bytes > 1000) {
        return Math.floor(bytes / 1000) + ' kB';
    }

    return bytes + ' B';
}
