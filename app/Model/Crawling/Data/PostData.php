<?php

namespace CTMovie\Model\Crawling\Data;

/**
 * Class PostData
 * @package CTMovie\Model\Crawling\Data
 */
class PostData
{
    /** @var string */
    private $title;

    /** @var array */
    private $contents;

    /** @var string|null */
    private $status;

    /** @var string|null */
    private $episode;

    /** @var string|null */
    private $episodeUrlDownloads;

    /** @var array|null */
    private $episodeUrlDownloadList;

    /** @var array|null */
    private $episodeNameDownloadList;

    /** @var string|null */
    private $thumbnail;

    /** @var string|null */
    private $released;

    /** @var array|null */
    private $genre;

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @param $url
     */
    public function setThumbnail($url)
    {
        $this->thumbnail = $url;
    }

    /**
     * @return string|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param $released
     */
    public function setReleased($released)
    {
        $this->released = $released;
    }

    /**
     * @return string|null
     */
    public function getReleased()
    {
        return $this->released;
    }

    /**
     * @return array
     */
    public function getContents() {
        return $this->contents;
    }

    /**
     * @param array $contents
     */
    public function setContents($contents) {
        $this->contents = $contents;
    }

    /**
     * @return string|null
     * @since 1.11.0
     */
    public function getStatus(): ?string {
        return $this->status;
    }

    /**
     * @param string|null $postStatus
     * @since 1.11.0
     */
    public function setStatus(?string $postStatus) {
        $this->status = $postStatus;
    }

    /**
     * @return string|null
     * @since 1.11.0
     */
    public function getEpisode(): ?string {
        return $this->episode;
    }

    /**
     * @param int $episode
     * @since 1.11.0
     */
    public function setEpisode(int $episode) {
        $this->episode = $episode;
    }
    /**
     * @return string|null
     * @since 1.11.0
     */
    public function getEpisodeUrlDownloads(): ?string {
        return $this->episodeUrlDownloads;
    }

    /**
     * @param string $url
     * @since 1.11.0
     */
    public function setEpisodeUrlDownloads(string $url) {
        $this->episodeUrlDownloads = $url;
    }

    /**
     * @return array|null
     * @since 1.11.0
     */
    public function getEpisodeUrlDownloadList(): ?array {
        return $this->episodeUrlDownloadList;
    }

    /**
     * @param array $urls
     * @since 1.11.0
     */
    public function setEpisodeUrlDownloadList(array $urls) {
        $this->episodeUrlDownloadList = $urls;
    }

    /**
     * @return array|null
     * @since 1.11.0
     */
    public function getEpisodeNameDownloadList(): ?array {
        return $this->episodeNameDownloadList;
    }

    /**
     * @param array $names
     * @since 1.11.0
     */
    public function setEpisodeNameDownloadList(array $names) {
        $this->episodeNameDownloadList = $names;
    }

    /**
     * @param $genres
     * return array
     */
    public function setGenre($genres)
    {
        $result = [];
        foreach ($genres as $genre) {
            $result[] = str_replace(', ', '', $genre);
        }
        $this->genre = $result;
    }

    /**
     * @return array|null
     */
    public function getGenre(): ?array
    {
        return $this->genre;
    }
}