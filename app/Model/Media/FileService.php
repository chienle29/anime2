<?php

namespace CTMovie\Model\Media;

use CTMovie\ObjectFactory;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class FileService
 * @package CTMovie\Model\Media
 */
class FileService
{
    /** @var int Maximum length of file name */
    const MAX_FILE_NAME_LENGTH = 240;

    /** @var string Opening brackets for the short codes in the name of a file. */
    const SC_OPENING_BRACKETS = 'sc123sc';

    /** @var string Closing brackets for the short codes in the name of a file. */
    const SC_CLOSING_BRACKETS = 'cs321cs';

    /** @var FileService */
    private static $instance = null;

    /** @var Filesystem */
    private $fs = null;

    /** @var string */
    private $tempDir = null;

    /** @var string Temporary file storage directory path relative to WP's uploads directory */
    protected $relativeTempDirPath = '/tc-temp';

    /**
     * Get the instance
     *
     * @return FileService
     * @since 1.8.0
     */
    public static function getInstance() {
        if (static::$instance === null) static::$instance = new FileService();
        return static::$instance;
    }

    /**
     * @return Filesystem
     * @since 1.8.0
     */
    public function getFileSystem() {
        if ($this->fs === null) $this->fs = ObjectFactory::fileSystem();
        return $this->fs;
    }

    /**
     * Get URL of the file that is located in WordPress' uploads directory.
     *
     * @param string $path Path of the file that is under WP's uploads directory
     * @return null|string If the directory information cannot be retrieved from WordPress, null. Otherwise, URL for the
     *                     file.
     * @since 1.8.0
     */
    public function getUrlForPathUnderUploadsDir($path) {
        // Get WordPress' upload directory path and URL
        $dirArr = $this->getUploadDirArray();
        if (isset($dirArr['error']) && $dirArr['error']) return null;

        // Make sure the base URL does not end with a forward slash
        $baseUploadsUrl = rtrim($dirArr['baseurl'], '/');

        // Make sure the real path of the base directory is retrieved without a leading directory separator
        $baseUploadsDir = realpath(rtrim($dirArr['basedir'], DIRECTORY_SEPARATOR));

        // Remove the base uploads directory path from the local file path
        $relativePath = trim(str_replace($baseUploadsDir, '', $path), DIRECTORY_SEPARATOR);

        // Replace directory separators in the relative file path with a forward slash, since URLs use forward slashes.
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

        // Create the local URL by appending relative URL to the base uploads URL
        return $baseUploadsUrl . '/' . $relativePath;
    }

    /**
     * Get uploads directory details of WordPress.
     *
     * @return array See {@link wp_upload_dir()}
     * @since 1.9.0
     * @uses wp_upload_dir()
     */
    private function getUploadDirArray() {
        // wp_get_upload_dir exists starting from 4.5. Hence, we use wp_upload_dir to support older versions of
        // WordPress. The function call below is the same call as wp_get_upload_dir does.
        return wp_upload_dir(null, false);
    }

    /**
     * Get a valid file name. This makes sure there is no directory separators in the file name.
     *
     * @param string $fileName File name to be validated.
     * @return false|string If the name cannot be made valid, false. Otherwise, a valid name.
     * @since 1.8.0
     */
    public function validateFileName($fileName) {
        // Make sure the new name does not contain any directory separators
        $fileName = $this->forceDirectorySeparator($fileName);
        if (!$fileName) return false;

        // Get the parts
        $parts = explode(DIRECTORY_SEPARATOR, $fileName);

        // Get the last part.
        $fileName = array_pop($parts);

        // If the new name does not exist, return false.
        if (!$fileName) return false;

        // Make the name suitable for a URL. This also limits the length to 200 chars.
        $fileName = sanitize_title($fileName);

        // Make sure the file name length is in the limits
        if (mb_strlen($fileName) > static::MAX_FILE_NAME_LENGTH) {
            // If not, trim it such that it is in the limits.
            $fileName = mb_substr($fileName, 0, static::MAX_FILE_NAME_LENGTH);
        }

        return $fileName;
    }

    /**
     * Changes forward and backward slashes with {@link DIRECTORY_SEPARATOR}
     *
     * @param string $path A path
     * @return string
     * @since 1.8.0
     */
    public function forceDirectorySeparator($path) {
        if (!$path) return '';

        return str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    /**
     * Get unique file path under a directory.
     *
     * @param string      $baseName    File name with extension
     * @param string      $directory   Directory path. The file name will be unique to this directory.
     * @param null|string $currentPath Current path of the file, if exists.
     * @return string Absolute file path that is unique to the given directory
     * @since 1.8.0
     */
    public function getUniqueFilePath($baseName, $directory, $currentPath = null) {
        $directory = rtrim($this->forceDirectorySeparator($directory), DIRECTORY_SEPARATOR);

        // If the new path is the same as the old one, do nothing and return the path.
        if ($currentPath === $directory . DIRECTORY_SEPARATOR . $baseName) return $currentPath;

        // Get required file information
        $ext        = $this->getFileSystem()->extension($baseName);
        $fileName   = $this->getFileSystem()->name($baseName);

        $count = 0;
        do {
            // Create the new path. If this is not the first try, then append a number to the file name.
            $newBaseName = $count > 0 ? "{$fileName}-{$count}.{$ext}" : "{$fileName}.{$ext}";
            $newPath = $directory . DIRECTORY_SEPARATOR . $newBaseName;

            $count++;
            // Check if the new path is a path to an existing file. If so, rename the file by appending a number to it.
        } while($this->getFileSystem()->exists($newPath));

        return $newPath;
    }
}