function ratingToNumber(rating) {
    let value = 0;

    if (typeof rating === 'string') {
        if (rating.indexOf('+') === 1) {
            value += 0.5;
        } else if (rating.indexOf('-') === 1) {
            value -= 0.5;
        }

        if (rating.indexOf('A') === 0) {
            return value + 4;
        }

        if (rating.indexOf('B') === 0) {
            return value + 3;
        }

        if (rating.indexOf('C') === 0) {
            return value + 2;
        }

        if (rating.indexOf('D') === 0) {
            return value + 1;
        }
    }

    return value;
}

export default function (attribute, order = 'desc') {
    return (a, b) => {
        const pa = ratingToNumber(a.attributes.rating);
        const pb = ratingToNumber(b.attributes.rating);

        if (pa > pb) {
            return order === 'asc' ? 1 : -1;
        } else if (pb > pa) {
            return order === 'asc' ? -1 : 1;
        }

        return 0;
    };
}
