<?php

namespace CTMovie\Model\Crawling\Data;

use CTMovie\Model\Crawling\Data\MovieUrl;

/**
 * Class MovieUrlList
 * @package CTMovie\Model\Crawling\Data
 */
class MovieUrlList
{
    /** @var MovieUrl[] */
    private $items;

    /**
     * @param MovieUrl[] $items
     * @since 1.11.0
     */
    public function __construct(?array $items = null) {
        $this->setItems($items);
    }

    /**
     * @return bool True if the list is empty. Otherwise, false.
     * @since 1.11.0
     */
    public function isEmpty() {
        return empty($this->items);
    }

    /**
     * @return MovieUrl[]
     * @since 1.11.0
     */
    public function getItems(): array {
        return $this->items;
    }

    /**
     * @param MovieUrl[] $items
     * @since 1.11.0
     */
    public function setItems(?array $items): void {
        $this->items = $items === null ? [] : $items;
    }

    /**
     * Add a URL to the list
     *
     * @param MovieUrl $url
     * @since 1.11.0
     */
    public function addItem(MovieUrl $url) {
        $this->items[] = $url;
    }

    /**
     * Remove an item with its index
     *
     * @param mixed $index Index of the item
     * @since 1.11.0
     */
    public function removeItem($index) {
        if (!isset($this->items[$index])) return;
        unset($this->items[$index]);
    }

    /**
     * Reverse the list
     *
     * @since 1.11.0
     */
    public function reverse() {
        $this->setItems(array_reverse($this->getItems()));
    }

    public function toArray(): array {
        $result = [];
        foreach($this->getItems() as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }
}