<?php

namespace CTMovie\Model\Media;

/**
 * Class MediaSavingOptions
 * @package CTMovie\Model\Media
 */
class MediaSavingOptions
{
    /**
     * @var null|string Value of the user agent header of the request made to save a file. If null, WP's default user
     *      agent will be used.
     */
    private $userAgent = null;

    /**
     * @var null|array<string, string> Cookies that will be attached to the request. Key-value pairs where the keys are
     *                                 cookie names and the values are the cookie values.
     */
    private $cookies = null;

    /** @var int Timeout for the request, in seconds. */
    private $timeoutSeconds = 10;

    /**
     * @return string|null See {@link userAgent}
     * @since 1.10.2
     */
    public function getUserAgent(): ?string {
        return $this->userAgent;
    }

    /**
     * @param string|null $userAgent See {@link userAgent}
     * @return MediaSavingOptions
     * @since 1.10.2
     */
    public function setUserAgent(?string $userAgent): MediaSavingOptions {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return string[]|null See {@link cookies}
     * @since 1.10.2
     */
    public function getCookies(): ?array {
        return $this->cookies;
    }

    /**
     * @param string[]|null $cookies See {@link cookies}
     * @return MediaSavingOptions
     * @since 1.10.2
     */
    public function setCookies(?array $cookies): MediaSavingOptions {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * Set the value of {@link cookies} from a cookie setting's value
     *
     * @param array|null $cookies Value of the {@link SettingKey::COOKIES}
     * @return MediaSavingOptions
     * @since 1.10.2
     */
    public function setCookiesFromCookieSetting(?array $cookies): MediaSavingOptions {
        // If there are no cookies, set the value to null and stop.
        if (!$cookies) {
            $this->cookies = null;
            return $this;
        }

        // Create the Cookie header's value
        $cookieMap = [];
        foreach($cookies as $cookieData) {
            // Make sure the keys of the array are valid
            if (!isset($cookieData['key']) || !isset($cookieData['value'])) continue;

            // Get the cookie key and value
            $cookieKey = $cookieData['key'];
            $cookieVal = $cookieData['value'];

            // A cookie must have a key.
            if ($cookieKey === null || $cookieKey === '') continue;

            // Add the new cookie to the map
            $cookieMap[$cookieKey] = $cookieVal;
        }

        // Assign the cookies
        $this->cookies = $cookieMap ?: null;
        return $this;
    }

    /**
     * @return int See {@link timeoutSeconds}
     * @since 1.10.2
     */
    public function getTimeoutSeconds(): int {
        return $this->timeoutSeconds;
    }

    /**
     * @param int $timeoutSeconds See {@link timeoutSeconds}
     * @return MediaSavingOptions
     * @since 1.10.2
     */
    public function setTimeoutSeconds(int $timeoutSeconds): MediaSavingOptions {
        $this->timeoutSeconds = $timeoutSeconds;
        return $this;
    }

    /**
     * @param string|null $timeoutSeconds A numeric value for {@link timeoutSeconds}
     * @return $this
     * @since 1.10.2
     */
    public function setTimeoutSecondsFromString(?string $timeoutSeconds): MediaSavingOptions {
        $timeoutSeconds = $timeoutSeconds === null || !is_numeric($timeoutSeconds)
            ? 0
            : (int) $timeoutSeconds;

        return $this->setTimeoutSeconds($timeoutSeconds);
    }

    /*
     * STATIC METHODS
     */

    /**
     * Create {@link MediaSavingOptions} from a site's settings
     *
     * @return MediaSavingOptions The options created from the given site settings
     * @since 1.10.2
     */
    public static function fromSiteSettings($timeout): MediaSavingOptions {
        return (new static())
            ->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36')
            ->setCookiesFromCookieSetting(null)
            ->setTimeoutSecondsFromString($timeout);
    }
}