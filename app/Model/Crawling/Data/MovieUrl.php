<?php

namespace CTMovie\Model\Crawling\Data;

/**
 * Class MovieUrl
 * @package CTMovie\Model\Crawling\Data
 */
class MovieUrl
{
    /** @var string */
    private $url;

    /** @var array|null Stores the output of {@link toArray()} */
    private $arrayCache = null;

    /**
     * @param string      $url
     * @since 1.11.0
     */
    public function __construct(string $url) {
        $this->url          = $url;
    }

    /**
     * @return string
     * @since 1.11.0
     */
    public function getUrl(): string {
        return $this->url;
    }

    /**
     * @param string $url
     * @return MovieUrl
     * @since 1.11.0
     */
    public function setUrl(string $url): MovieUrl {
        $this->url = $url;
        $this->invalidateArrayCache();
        return $this;
    }

    public function toArray(): array {
        if ($this->arrayCache === null) {
            $this->arrayCache = [
                'type'  => 'url'
            ];
        }

        return $this->arrayCache;
    }

    protected function invalidateArrayCache() {
        $this->arrayCache = null;
    }

    /**
     * Create a {@link PostUrl} from an associative array
     *
     * @param string|null $string See the method's implementation to learn the array keys
     * @return MovieUrl|null
     * @since 1.11.0
     */
    public static function fromArray(string $string): ?MovieUrl {
        if ($string === null) return null;

        return new MovieUrl($string);
    }
}