export default function (attribute, order = 'asc') {
    return (a, b) => {
        const pa = a.attributes[attribute];
        const pb = b.attributes[attribute];

        if (pa > pb) {
            return order === 'asc' ? 1 : -1;
        } else if (pb > pa) {
            return order === 'asc' ? -1 : 1;
        }

        return 0;
    };
}
