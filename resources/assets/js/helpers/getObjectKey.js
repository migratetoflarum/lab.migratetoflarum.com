export default function reportKey(data, key, defaultValue = null) {
    const parts = key.split('.');

    for (let part in parts) {
        if (typeof data !== 'object' || data === null || typeof data[parts[part]] === 'undefined') {
            return defaultValue;
        }

        data = data[parts[part]];
    }

    return data;
}
