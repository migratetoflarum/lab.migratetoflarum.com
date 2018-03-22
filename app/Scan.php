<?php

namespace App;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $website_id
 * @property string $uid
 * @property bool $hidden
 * @property array $report
 * @property Carbon $scanned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Website $website
 */
class Scan extends UidModel
{
    protected $casts = [
        'hidden' => 'bool',
        'report' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function computeRating():? string
    {
        if (!$this->report) {
            return null;
        }

        $baseUrl = array_get($this->report, 'homepage.boot.base_url');
        $expectedBaseUrl = rtrim(array_get($this->report, 'canonical_url'), '/');

        if (!$baseUrl) {
            return null;
        }

        if (
            array_get($this->report, 'malicious_access.vendor') ||
            array_get($this->report, 'malicious_access.storage') ||
            array_get($this->report, 'malicious_access.composer')
        ) {
            return 'D';
        }

        if (
            array_get($this->report, 'urls.apex-http.type') === 'ok' ||
            array_get($this->report, 'urls.www-http.type') === 'ok' ||
            array_get($this->report, 'multiple_urls') ||
            $baseUrl !== $expectedBaseUrl
        ) {
            return 'C';
        }

        $httpsCanonical = starts_with(array_get($this->report, 'canonical_url'), 'https://');
        $wwwCanonical = starts_with(array_get($this->report, 'canonical_url'), 'https://www.');

        // We check www for the rating only if it was scanned and is under the apex or does resolve
        $checkWwwStatus = array_has($this->report, 'urls.www-http') &&
            (
                $wwwCanonical ||
                $this->website->is_apex ||
                !str_contains(array_get($this->report, 'urls.www-http.exception_message'), 'cURL error 6:') // Couldn't resolve host. The given remote host was not resolved.
            );

        if ($httpsCanonical) {
            if (
                array_get($this->report, 'urls.apex-http.status') !== 301 ||
                ($checkWwwStatus && array_get($this->report, 'urls.www-http.status') !== 301)
            ) {
                return 'B';
            }

            $canonicalDomain = $wwwCanonical ? 'www' : 'apex';
            $nonCanonicalDomain = $wwwCanonical ? 'apex' : 'www';

            // Check if the second https domain is using a 301 redirect, but only if that different domain is the apex or that www should be checked
            if (array_get($this->report, "urls.$nonCanonicalDomain-https.status") !== 301 && ($nonCanonicalDomain !== 'www' || $checkWwwStatus)) {
                return 'B';
            }

            if (array_get($this->report, "urls.$canonicalDomain-https.headers.Strict-Transport-Security")) {
                return 'A+';
            }

            return 'A';
        }

        return 'C';
    }
}
