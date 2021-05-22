<?php

namespace CTMovie\Model\Media;

use Illuminate\Filesystem\Filesystem;

/**
 * Class MediaFile
 * @package CTMovie\Model\Media
 */
class MediaFile
{
    /** @var string Raw source URL after "find-replace in image URLs" options applied to it. */
    private $sourceUrl;

    /** @var string Raw source URL retrieved from the target site */
    private $originalSourceUrl;

    /** @var string */
    private $localUrl;

    /** @var string */
    private $localPath;

    /** @var bool True if this file is a gallery image. */
    private $isGalleryImage = false;

    /** @var string|null */
    private $mediaTitle;

    /** @var string|null */
    private $mediaDescription;

    /** @var string|null */
    private $mediaCaption;

    /** @var string|null */
    private $mediaAlt;

    /** @var string */
    private $originalFileName;

    /** @var int|null Media ID of this file, retrieved by inserting the media into the database. */
    private $mediaId;

    /** @var array Stores the paths of the copies of the file */
    private $copyFilePaths = [];

    /**
     * @param string $sourceUrl See {@link $sourceUrl}
     * @param string $localPath See {@link $localPath}
     */
    public function __construct($sourceUrl, $localPath) {
        $this->sourceUrl = $sourceUrl;
        $this->setLocalPath($localPath);
        $this->originalFileName = $this->getFileSystem()->name($this->localPath ?: $sourceUrl);
    }

    /**
     * @return string
     */
    public function getSourceUrl() {
        return $this->sourceUrl;
    }

    /**
     * @param string $sourceUrl
     * @return MediaFile
     */
    public function setSourceUrl($sourceUrl) {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalSourceUrl() {
        return $this->originalSourceUrl ?: $this->getSourceUrl();
    }

    /**
     * @param string $originalSourceUrl
     * @return MediaFile
     */
    public function setOriginalSourceUrl($originalSourceUrl) {
        $this->originalSourceUrl = $originalSourceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalUrl() {
        // If there is no local URL, create it.
        if ($this->localUrl === null) {
            $url = FileService::getInstance()->getUrlForPathUnderUploadsDir($this->getLocalPath());
            if ($url !== null) {
                $this->localUrl = $url;
            }
        }

        return $this->localUrl ? $this->localUrl : '';
    }

    /**
     * @param string $localUrl
     * @return MediaFile
     */
    public function setLocalUrl($localUrl) {
        $this->localUrl = $localUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFileName() {
        return $this->originalFileName;
    }

    /**
     * @return string
     */
    public function getLocalPath() {
        return $this->localPath;
    }

    /**
     * @param string $localPath
     * @return MediaFile
     */
    public function setLocalPath($localPath) {
        if ($localPath !== null) {
            $this->localPath = realpath($localPath) ?: null;
        } else {
            $this->localPath = null;
        }

        // Make the local URL null, since the path of the file has just changed.
        $this->localUrl = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGalleryImage() {
        return $this->isGalleryImage;
    }

    /**
     * @param bool $isGalleryImage
     * @return MediaFile
     */
    public function setIsGalleryImage($isGalleryImage) {
        $this->isGalleryImage = $isGalleryImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaTitle() {
        return $this->mediaTitle !== null ? $this->mediaTitle : preg_replace('/\.[^.]+$/', '', $this->getBaseName());
    }

    /**
     * @param null|string $mediaTitle
     * @return MediaFile
     */
    public function setMediaTitle($mediaTitle) {
        $this->mediaTitle = $mediaTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaDescription() {
        return $this->mediaDescription !== null ? $this->mediaDescription : '';
    }

    /**
     * @param null|string $mediaDescription
     * @return MediaFile
     */
    public function setMediaDescription($mediaDescription) {
        $this->mediaDescription = $mediaDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaCaption() {
        return $this->mediaCaption !== null ? $this->mediaCaption : '';
    }

    /**
     * @param null|string $mediaCaption
     * @return MediaFile
     */
    public function setMediaCaption($mediaCaption) {
        $this->mediaCaption = $mediaCaption;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediaAlt() {
        return $this->mediaAlt !== null ? $this->mediaAlt : '';
    }

    /**
     * @param null|string $mediaAlt
     * @return MediaFile
     */
    public function setMediaAlt($mediaAlt) {
        $this->mediaAlt = $mediaAlt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMediaId() {
        return $this->mediaId;
    }

    /**
     * @param int|null $mediaId
     */
    public function setMediaId($mediaId) {
        $this->mediaId = $mediaId;
    }

    /*
     * OPERATIONS
     */

    /**
     * @return bool True if the media file exists.
     * @since 1.8.0
     */
    public function exists() {
        return $this->getFileSystem()->exists($this->getLocalPath());
    }

    /**
     * @return bool True if the media file path is a file.
     * @since 1.8.0
     */
    public function isFile() {
        return strlen($this->getFileSystem()->extension($this->getLocalPath())) > 0;
    }

    /**
     * @return string Directory of the media file
     * @since 1.8.0
     */
    public function getDirectory() {
        return $this->getFileSystem()->dirname($this->getLocalPath());
    }

    /**
     * @return string Name of the media file
     * @since 1.8.0
     */
    public function getName() {
        return $this->getFileSystem()->name($this->getLocalPath());
    }

    /**
     * @return string Base name of the file, i.e. file name with extension.
     * @since 1.8.0
     */
    public function getBaseName() {
        return $this->getFileSystem()->basename($this->getLocalPath());
    }

    /**
     * @return string Extension of the media file
     * @since 1.8.0
     */
    public function getExtension() {
        return $this->getFileSystem()->extension($this->getLocalPath());
    }

    /**
     * @return string Mime type or empty string if mime type does not exist.
     * @since 1.8.0
     */
    public function getMimeType() {
        $mimeType = $this->getFileSystem()->mimeType($this->getLocalPath());
        return $mimeType ? $mimeType : '';
    }

    /**
     * @return string MD5 hash of the file.
     * @since 1.8.0
     */
    public function getMD5Hash() {
        return $this->getFileSystem()->hash($this->getLocalPath());
    }

    /**
     * @return string A unique SHA1 string created by using {@link uniqid()} and the base name of the file
     * @since 1.8.0
     */
    public function getRandomUniqueHash() {
        return sha1($this->getBaseName() . uniqid('wpcc'));
    }

    /**
     * @return int File size in kilobytes.
     * @since 1.8.0
     */
    public function getFileSizeByte() {
        return $this->getFileSize();
    }

    /**
     * @return int File size in kilobytes.
     * @since 1.8.0
     */
    public function getFileSizeKb() {
        return (int) ($this->getFileSize() / 1000);
    }

    /**
     * @return int File size in megabytes.
     * @since 1.8.0
     */
    public function getFileSizeMb() {
        return (int) ($this->getFileSize() / 1000000);
    }

    /**
     * Rename the file.
     *
     * @param string $newName New name of the file. Without extension.
     * @return bool True if the renaming was successful.
     * @since 1.8.0
     */
    public function rename($newName) {
        $newName = FileService::getInstance()->validateFileName($newName);

        // If there is no name after validation, assign a default name.
        if (!$newName) $newName = 'no-name';

        // If the new name is the same as the old name, stop by indicating success.
        if ($newName === $this->getName()) return true;

        // Rename the file
        $newPath = $this->getUniqueFilePath($newName, $this->getDirectory());
        $success = $this->move($newPath);
        if (!$success) return false;

        // If there are copies, rename them as well.
        $copyFilePaths = $this->copyFilePaths;

        // First, clear the copy file paths since we are gonna rename them.
        $this->clearCopyFilePaths();

        // Rename the copy files
        foreach($copyFilePaths as $copyFilePath) {
            // Get the directory of the copy file
            $directoryPath = $this->getFileSystem()->dirname($copyFilePath);

            // Get a unique name for the copy file in the same directory
            $newCopyFilePath = $this->getUniqueFilePath($this->getName(), $directoryPath);

            // Try to rename the file
            if (@$this->getFileSystem()->move($copyFilePath, $newCopyFilePath)) {
                // If renamed, store it.
                $this->addCopyFilePath($newCopyFilePath);

                // If testing, remove the old copy file path from test file paths.
                MediaService::getInstance()->removeTestFilePath($copyFilePath);

            } else {
                // Otherwise, inform the user.
            }
        }

        return $success;
    }

    /**
     * @param string $newPath New path of the file
     * @return bool True if the operation has been successful.
     * @since 1.8.0
     */
    public function move($newPath) {
        $newPath = FileService::getInstance()->forceDirectorySeparator($newPath);
        if (!$newPath) return false;

        $result = @$this->getFileSystem()->move($this->getLocalPath(), $newPath);

        // If the file was moved, set the new path.
        if ($result) {
            // If this is a test, remove the previous local path.
            MediaService::getInstance()->removeTestFilePath($this->getLocalPath());

            $this->setLocalPath($newPath);

        } else {
            // Otherwise, inform the user.
           //File %1$s could not be moved to ... Need log
        }

        return $result;
    }

    /**
     * @param string $newDirectoryPath New directory path
     * @return bool True if the file has been successfully moved. Otherwise, false.
     * @since 1.8.0
     */
    public function moveToDirectory($newDirectoryPath) {
        $newDirectoryPath = FileService::getInstance()->forceDirectorySeparator($newDirectoryPath);
        if (!$newDirectoryPath) return false;

        // Make sure the directories exist. If not, create them. Stop if they do not exist.
        if (!$this->makeDirectory($newDirectoryPath)) return false;

        // We now have the target directory created. Let's move the file to that directory.
        return $this->move($newDirectoryPath . DIRECTORY_SEPARATOR . $this->getBaseName());
    }

    /**
     * @param string $directoryPath Target directory path
     * @return false|string False if the operation was not successful. Otherwise, copied file's path.
     * @since 1.8.0
     */
    public function copyToDirectory($directoryPath) {
        $directoryPath = FileService::getInstance()->forceDirectorySeparator($directoryPath);
        if (!$directoryPath) return false;

        // Make sure the directories exist. If not, create them. Stop if they do not exist.
        if (!$this->makeDirectory($directoryPath)) return false;

        // Get the new name
        $copyPath = $this->getUniqueFilePath($this->getName(), $directoryPath);

        // Copy the file
        $success = $this->getFileSystem()->copy($this->getLocalPath(), $copyPath);

        // If the file is copied, store the copy file's path.
        if ($success) $this->addCopyFilePath($copyPath);

        return $success === false ? false : $copyPath;
    }

    /**
     * Delete the local file.
     *
     * @return bool True if the file has been successfully deleted. Otherwise, false.
     * @since 1.8.0
     */
    public function delete() {
        $result = $this->getFileSystem()->delete($this->getLocalPath());
        if (!$result) {
            // Inform the user if the file could not be deleted
            //File xxx could not be deleted.
        }

        return $result;
    }

    /**
     * Delete copies of the local file.
     *
     * @return bool True if all of the copy file have been deleted. Otherwise, false.
     * @since 1.8.0
     */
    public function deleteCopyFiles() {
        if (!$this->copyFilePaths) return true;
        $success = true;

        // Delete a copy file
        foreach($this->copyFilePaths as $copyPath) {
            if (!$this->getFileSystem()->delete($copyPath)) {
                $success = false;

                // Inform the user if the file could not be deleted
                //File xxx could not be deleted.
            } else {
                // If file is deleted and this is a test, remove the path from test file paths.
                MediaService::getInstance()->removeTestFilePath($copyPath);
            }
        }

        return $success;
    }

    /**
     * Get URLs of the files that are copies of the file.
     *
     * @return array An array of copy file URLs
     * @since 1.8.0
     */
    public function getCopyFileUrls() {
        if (!$this->copyFilePaths) return [];

        $urls = [];

        foreach($this->copyFilePaths as $filePath) {
            $url = FileService::getInstance()->getUrlForPathUnderUploadsDir($filePath);
            if (!$url) continue;

            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * @return Filesystem
     * @since 1.8.0
     */
    public function getFileSystem() {
        return FileService::getInstance()->getFileSystem();
    }

    /**
     * Clear copy file paths.
     *
     * @since 1.8.0
     */
    private function clearCopyFilePaths() {
        // Clear the copy file paths
        $this->copyFilePaths = [];
    }

    /**
     * @return int Size of the file in bytes
     * @since 1.8.0
     */
    private function getFileSize() {
        $size = $this->getFileSystem()->size($this->getLocalPath());
        return $size ? $size : 0;
    }

    /**
     * Get unique file path under a directory.
     *
     * @param string $fileName  File name without extension
     * @param string $directory Directory path. The file name will be unique to this directory.
     * @return string Absolute file path that is unique to the given directory
     * @since 1.8.0
     */
    private function getUniqueFilePath($fileName, $directory) {
        return FileService::getInstance()->getUniqueFilePath(
            $fileName . '.' . $this->getExtension(),
            $directory,
            $this->getLocalPath()
        );
    }

    /**
     * @param string $directoryPath Directory or file path.
     * @return bool True if the directories of the file or the given path exist.
     * @since 1.8.0
     */
    private function makeDirectory($directoryPath) {
        // Get the directory path of the given path.
        $directoryPath = rtrim(FileService::getInstance()->forceDirectorySeparator($directoryPath), DIRECTORY_SEPARATOR);
        $directoryPath = strlen($this->getFileSystem()->extension($directoryPath)) ? $this->getFileSystem()->dirname($directoryPath) : $directoryPath;

        // If the directories do not exist, create them.
        if (!$this->getFileSystem()->isDirectory($directoryPath)) {
            $result = $this->getFileSystem()->makeDirectory($directoryPath, 0755, true);

            // Stop if the directories could not be created.
            if (!$result) {
                //xxx directory could not be created.

                return false;
            }
        }

        return true;
    }
}