import moment from 'moment';

export default function (date) {
    const d = moment(date);

    return d.format('YYYY-MM-DD HH:mm:ss') + ' (' + d.fromNow() + ')';
}
