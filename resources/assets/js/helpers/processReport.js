function getKey(data, key, defaultValue = null) {
    const parts = ('attributes.report.' + key).split('.');

    for (let part in parts) {
        if (typeof data[parts[part]] !== 'undefined') {
            data = data[parts[part]];
        } else {
            return defaultValue;
        }
    }

    return data;
}

export default function(report) {
    let finalReport = {

    };

    return finalReport;
}
