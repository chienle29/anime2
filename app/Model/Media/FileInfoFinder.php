<?php

namespace CTMovie\Model\Media;

use Illuminate\Support\Arr;
use Symfony\Component\Mime\MimeTypes;
use Requests_Utility_CaseInsensitiveDictionary;

/**
 * Class FileInfoFinder
 * @package CTMovie\Model\Media
 */
class FileInfoFinder
{
    /** @var string Name of the URL parameter that stores the preferred extensions (comma-separated) in the file's URL */
    const PARAM_NAME_PREF_EXT = 'wpccPrefExt';

    /**
     * @var string If this URL parameter exists in the file URL, the extension assigned to {@link PARAM_NAME_PREF_EXT}
     *      will be returned as the extension without checking other approaches.
     */
    const PARAM_NAME_OVERRIDE_EXT = 'wpccOverrideExt';

    /**
     * @var string Absolute path of the file whose info is wanted. This is probably the raw (temporary) file
     *      downloaded from the remote host.
     */
    private $absFilePath;

    /** @var string|null URL of the file, if exists */
    private $fileUrl;

    /** @var null|array Caches the parsed URL parameters of {@link fileUrl} */
    private $fileUrlParams = null;

    /**
     * @var null|array HTTP response retrieved from the request made to {@link fileUrl}. The structure of this array is
     *                 the same as the one returned from {@link \WP_Http::request()}. This value is used to retrieve
     *                 certain headers to decide file name and extension. If this is available, no HEAD request will be
     *                 made to {@link fileUrl}. Otherwise, a request may be made. So, providing this eliminates a
     *                 request, i.e. saves time.
     */
    private $httpResponse = null;

    /** @var null|MimeTypes The object used to find MIME types and their extensions */
    private $mimeTypes = null;

    /**
     * If a string matches this regex, it means that string can be used as a file extension. This basically allows the
     * characters of POSIX "fully portable filenames", retrieved from Wikipedia.
     *
     * @var string
     * @see https://en.wikipedia.org/wiki/Filename
     */
    private $validExtRegex = '/^[a-zA-Z0-9._-]+$/';

    /**
     * Headers of the response retrieved from {@link fileUrl}. The keys are the header names in lowercase. Their values
     * can be either a string or an array of strings. The latter happens when the URL is redirected, and each
     * redirection appends its own headers (see
     * <a href="https://www.php.net/manual/en/function.get-headers.php#100113">this comment</a>). So, the last item in
     * the array will be the value of the header from the final destination.
     *
     * @var null|array<string, string>|array<string, string[]>
     */
    private $responseHeaders = null;

    /**
     * @param string      $absFilePath  See {@link absFilePath}
     * @param string|null $fileUrl      See {@link fileUrl}
     * @param array|null  $httpResponse See {@link httpResponse}
     * @since 1.10.2
     */
    public function __construct(string $absFilePath, ?string $fileUrl = null, ?array $httpResponse = null) {
        if (!is_file($absFilePath)) {
            throw new \Exception();
        }

        $this->absFilePath  = $absFilePath;
        $this->fileUrl      = $fileUrl;
        $this->httpResponse = $httpResponse;
    }

    /**
     * Find the extension of the file
     *
     * @param string[]|null $preferredExts Preferred extensions for the file. Some MIME types correspond to more than
     *                                     one extension. In that case, if the preferred extension is among those
     *                                     extensions, the preferred extension will be returned. If preferred extension
     *                                     is unrelated to the MIME type, it will be ignored.
     * @return string|null If found, the extension of the file. Otherwise, null.
     * @since 1.10.2
     */
    public function findExtension(?array $preferredExts = null): ?string {
        // Get the preferred extensions from the URL. If they have to be used, return the first one directly.
        $customPrefExts = $this->getPreferredExtensionsFromUrl();
        if ($customPrefExts !== null && $this->mustUsePreferredExtension()) {
            return $customPrefExts[0];
        }

        // If there are custom preferred extensions, use them instead of the given $preferredExts.
        $preferredExts = $customPrefExts !== null ? $customPrefExts : $preferredExts;
        if ($preferredExts === null) $preferredExts = [];

        // If there is an extension in the URL of the file, add it as a preferred extension. If the MIME type has that
        // extension, we will use it.
        $extFromFileUrl = $this->getExtensionFromBaseName($this->getFileBaseNameFromUrl());
        if ($extFromFileUrl !== null) $preferredExts[] = $extFromFileUrl;

        // Guess the MIME type of the file and get the guessed type's extensions
        $mimeFromFile = $this->guessMimeType($this->getAbsFilePath());
        $extArrFromFileMime = $mimeFromFile !== null ? $this->getExtensions($mimeFromFile) : [];
        if ($extArrFromFileMime) {
            return $this->chooseExtension($extArrFromFileMime, $preferredExts);
        }

        // Get the extension from content-disposition header
        $extFromDispositionHeader = $this->getExtensionFromBaseName($this->getFileBaseNameFromContentDispositionHeader());
        if ($extFromDispositionHeader !== null) {
            return $this->chooseExtension([$extFromDispositionHeader], $preferredExts);
        }

        // Get the extension from the URL of the file
        if ($extFromFileUrl !== null) {
            return $this->chooseExtension([$extFromFileUrl], $preferredExts);
        }

        // Get the MIME type from content-type header and guess its extension
        $mimeFromContentTypeHeader = $this->getContentTypeHeaderValue();
        $extArrFromContentType = $mimeFromContentTypeHeader !== null
            ? $this->getExtensions($mimeFromContentTypeHeader)
            : [];
        if ($extArrFromContentType) {
            return $this->chooseExtension($extArrFromContentType, $preferredExts);
        }

        // If an extension is still not found and MIME type or Content-Type header exists, create an extension by using
        // them.
        $assumedExt = $this->assumeExtensionFromMimeType($mimeFromFile ?: $mimeFromContentTypeHeader);
        return $this->chooseExtension([$assumedExt], $preferredExts);
    }

    /**
     * @return string|null Value of Content-Type header returned for {@link fileUrl}
     * @since 1.10.2
     */
    protected function getContentTypeHeaderValue(): ?string {
        return $this->getResponseHeaderValue('content-type');
    }

    /**
     * Find the name of the file
     *
     * @return string|null Name of the file without extension, if found. Otherwise, null.
     * @since 1.10.2
     */
    public function findFileName(): ?string {
        // Try to get the name from the content-disposition header.
        $nameFromHeader = $this->getFileNameFromBaseName($this->getFileBaseNameFromContentDispositionHeader());
        if ($nameFromHeader !== null) return $nameFromHeader;

        // Try to get the name from the file's URL
        $nameFromUrl = $this->getFileNameFromBaseName($this->getFileBaseNameFromUrl());
        if ($nameFromUrl !== null) return $nameFromUrl;

        return null;
    }

    /**
     * Get the file name from the base name of a file
     *
     * @param string|null $baseName Base name of the file, i.e. file name and extension.
     * @return string|null If the file name is found, it is returned (without extension). Otherwise, null.
     * @since 1.10.2
     */
    protected function getFileNameFromBaseName(?string $baseName): ?string {
        if ($baseName === null) return null;

        $result = pathinfo($baseName, PATHINFO_FILENAME);
        return is_string($result) && $result !== '' ? $result : null;
    }

    /**
     * This method assumes the extension part of a MIME type. This should be used as last resort.
     *
     * @param string|null $mimeType A MIME type
     * @return string|null A part of the given MIME type
     * @since 1.10.2
     */
    protected function assumeExtensionFromMimeType(?string $mimeType): ?string {
        if ($mimeType === null) return null;

        // Replace all special characters with / and explode from / to get the candidates
        $ext = str_replace(['+', '.', '-'], '/', $mimeType);
        $candidates = explode('/', $ext);

        // Get the last candidate
        return $candidates ? $candidates[count($candidates) - 1] : null;
    }

    /**
     * Retrieve the file base name defined in the Content-Disposition header, if it exists.
     *
     * @return string|null If the base name of the file is found, it is returned. Otherwise, null. File base name is the
     *                     file name and extension.
     * @since 1.10.2
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
     */
    protected function getFileBaseNameFromContentDispositionHeader(): ?string {
        $value = $this->getResponseHeaderValue('content-disposition');
        if (!$value) return null;

        // This regex matches the name of the file in the content-disposition header's value. It is capable of correctly
        // finding the file name for these values, except for 2 of them:
        //      form-data; name="fieldName"; filename="filename.jpg"
        //      form-data; name="fieldName"; filename="file-_n?+ame.docx"
        //      form-data; name="fieldName"; filename*="filename.jpg"
        //      form-data; name="fieldName"; filename=filename.jpg
        //      form-data; filename*=filename.jpg; name="fieldName";
        //      form-data; filename=filename.jpg   ; name="fieldName";
        //      form-data; filename=filename.jpg
        //      form-data; filename=filename.jpg ;;;
        //      form-data; filename=filena;me.jpg                           // It fails to find filena;me.jpg
        //      form-data; filename=file"name.jpg;                          // It fails file"name.jpg
        //      form-data; filename='filename.jpg';
        //      attachment; filename=wordpress-5.6.zip
        $regex = '/filename[\s*]*=["\']?([^"\';\s]+)/';
        if(preg_match($regex, $value, $matches) !== 1 || count($matches) !== 2) {
            return null;
        }

        // The first matching group is the file base name
        return $matches[1];
    }

    /**
     * Get an actual extension by choosing a preferred extension if possible.
     *
     * @param array|null $actualExts    Actual extensions
     * @param array|null $preferredExts Preferred extensions
     * @return string|null If a preferred extension exists among the actual extensions, that extension is returned.
     *                     Otherwise, the first actual extension is returned. If there are no actual extensions, returns
     *                     null.
     * @since 1.10.2
     */
    protected function chooseExtension(?array $actualExts, ?array $preferredExts = null): ?string {
        if (!$actualExts) return null;
        $actualExts = array_values($actualExts);

        // If there is no preferred extension, return the first actual extension as a result.
        if (!$preferredExts) {
            return $actualExts[0];
        }

        // There are actual and preferred extensions. Find the preferred extensions that are among the actual
        // extensions. If possible, return a preferred extension. Otherwise, return the first actual extension.
        $preferredExts = array_values($preferredExts);
        $intersection  = array_values(array_intersect($preferredExts, $actualExts));
        return $intersection ? $intersection[0] : $actualExts[0];
    }

    /**
     * @param string $mimeType MIME type whose extensions are wanted
     * @return string[] Extensions of the given MIME type
     * @since 1.10.2
     */
    protected function getExtensions(string $mimeType): array {
        try {
            return $this->getMimeTypes()->getExtensions($mimeType);

        } catch (\Exception $e) {
            // Lam sau...
        }

        return [];
    }

    /**
     * Get value of a response header
     *
     * @param string $headerName Name of the header whose value is wanted
     * @return string|null Value of the header, if it is found. Otherwise, null.
     * @since 1.10.2
     */
    protected function getResponseHeaderValue(string $headerName): ?string {
        $headers = $this->getResponseHeaders();
        if (!$headers) return null;

        // Get the header's value
        $value = $headers[$headerName] ?? null;
        if (!$value) return null;

        // If the value is an array, use the last item from the array, since it is returned by the final destination
        // after all redirections.
        if (is_array($value)) {
            $value = $value[count($value) - 1];
        }

        return is_string($value) && $value !== '' ? trim($value) : null;
    }

    /**
     * @return null|array<string, string>|array<string, string[]> See {@link responseHeaders}
     * @since 1.10.2
     */
    protected function getResponseHeaders(): array {
        // If the headers were cached, return them without making a new request.
        if ($this->responseHeaders !== null) {
            return $this->responseHeaders;
        }

        // Get the headers as an associative array.
        $url = $this->getFileUrl();
        $headers = $url ? $this->retrieveResponseHeadersFromUrl($url) : [];

        // Make the keys lowercase
        $this->responseHeaders = [];
        foreach($headers as $key => $value) {
            $this->responseHeaders[strtolower($key)] = $value;
        }

        return $this->responseHeaders;
    }

    /**
     * @param string $url The URL whose response headers are wanted
     * @return array<string, string>|array<string, string[]> The headers as an associative array, having the same
     *                                                       structure as {@link responseHeaders}.
     * @since 1.10.2
     */
    protected function retrieveResponseHeadersFromUrl(string $url): array {
        $headers = $this->getHttpHeaders($url);
        if (!$headers) return [];

        // The function returns an object but its documentation says it returns an array. This situation necessitates
        // this check. If it returns an object, turn it into an array.
        // We support WP 4.6 and Requests_Utility_CaseInsensitiveDictionary class exists in WP 4.6 until the current
        // version. So, it is OK to refer to it.
        if ($headers instanceof Requests_Utility_CaseInsensitiveDictionary) {
            $headers = $headers->getAll();
        }

        // Make sure the variable is an array.
        if (!is_array($headers)) return [];

        // Turn the headers into the structure we want.
        $result = [];
        foreach ($headers as $key => $value) {
            $result[$key] = is_array($value) && count($value) === 1
                ? $value[0]
                : $value;
        }

        return $result;
    }

    /**
     * @return string See {@link absFilePath}
     * @since 1.10.2
     */
    public function getAbsFilePath(): string {
        return $this->absFilePath;
    }

    /**
     * @param string $filePath Absolute path of the file
     * @return string|null Guessed MIME type of the file
     * @since 1.10.2
     */
    protected function guessMimeType(string $filePath): ?string {
        try {
            return $this->getMimeTypes()->guessMimeType($filePath);

        } catch (\Exception $e) {
            // Làm sau...
        }

        return null;
    }

    /**
     * @return MimeTypes See {@link mimeTypes}
     * @since 1.10.2
     */
    protected function getMimeTypes(): MimeTypes {
        if ($this->mimeTypes === null) {
            $this->mimeTypes = new MimeTypes();
        }

        return $this->mimeTypes;
    }

    /**
     * Retrieve the file base name from {@link fileUrl}.
     *
     * @return string|null If the base name of the file is found, it is returned. Otherwise, null. File base name is the
     *                     file name and extension.
     * @since 1.10.2
     */
    protected function getFileBaseNameFromUrl(): ?string {
        $url = $this->getFileUrl();
        if (!$url) return null;

        // Strip parameters and get the base name
        $fileName = basename(preg_replace('/[?#].*/', '', $url));
        return $fileName !== '' ? $fileName : null;
    }

    /**
     * Get the extension from the base name of a file
     *
     * @param string|null $baseName Base name of the file, i.e. file name and extension.
     * @return string|null If the extension is found, it is returned in lowercase. Otherwise, null.
     * @since 1.10.2
     */
    protected function getExtensionFromBaseName(?string $baseName): ?string {
        if ($baseName === null) return null;

        $result = pathinfo($baseName, PATHINFO_EXTENSION);
        $ext = is_string($result) && $result !== '' ? strtolower($result) : null;
        return $this->isValidExtension($ext) ? $ext : null;
    }

    /**
     * @param string|null $ext The extension whose validity should be checked
     * @return bool True if the extension is valid. Otherwise, false.
     * @since 1.10.2
     */
    protected function isValidExtension(?string $ext): bool {
        if ($ext === null || $ext === '') return false;

        return preg_match($this->validExtRegex, $ext) === 1;
    }

    /**
     * Get the preferred extensions from the URL of the file. The URL must define the value of
     * {@link PARAM_NAME_PREF_EXT}.
     *
     * @return string[]|null Preferred extensions retrieved from the URL, if exist. The extensions do not contain dots
     *                       at the beginning and end of it. If the extensions are not defined, returns null.
     * @since 1.10.2
     */
    protected function getPreferredExtensionsFromUrl(): ?array {
        // Get the extension
        $value = $this->getFileUrlParam(static::PARAM_NAME_PREF_EXT);
        if ($value === null) return null;

        // Prepare the extensions. Make sure the array does not contain any null or empty values.
        $extensions = array_filter(
            array_map(function($ext) {
                // Make sure the extension contains only the allowed characters. If so, return the extension by trimming
                // dots from the beginning and the end of it. Otherwise, make it null.
                return $this->isValidExtension($ext) ? trim($ext, '.') : null;
            }, explode(',', $value))
        );

        // If the array is empty, return null. Otherwise, return the array.
        return $extensions ?: null;
    }

    /**
     * Get the value of a URL parameter of {@link fileUrl}
     *
     * @param string $paramName Name of a URL parameter
     * @return string|null If the value of the parameter exists in {@link fileUrl}, the value is returned. Otherwise,
     *                     null.
     * @since 1.10.2
     */
    protected function getFileUrlParam(string $paramName): ?string {
        $params = $this->getFileUrlParams();
        if (!$params) return null;

        $value = Arr::get($params, $paramName);
        if ($value === null || !is_string($value) || $value === '') return null;

        return $value;
    }

    /**
     * @return bool True if the preferred extension returned by {@link getPreferredExtensionsFromUrl()} must be used
     *              instead of extracting the extension by other approaches.
     * @since 1.10.2
     */
    protected function mustUsePreferredExtension(): bool {
        $params = $this->getFileUrlParams();
        return $params && isset($params[static::PARAM_NAME_OVERRIDE_EXT]);
    }

    /**
     * @return array|null URL parameters of {@link fileUrl}, if exist. Otherwise, null.
     * @since 1.10.2
     */
    protected function getFileUrlParams(): ?array {
        // If the params were not extracted before, extract them.
        if ($this->fileUrlParams === null) {
            // Store an empty array if there are no params so that we do not process the URL the next time this method
            // is called.
            $this->fileUrlParams = $this->extractFileUrlParams() ?: [];
        }

        // If there are params, return them. Otherwise, return null.
        return $this->fileUrlParams ?: null;
    }

    /**
     * Extracts the URL parameters by processing {@link fileUrl}
     *
     * @return array|null URL parameters of {@link fileUrl}, if exist. Otherwise, null.
     * @since 1.10.2
     */
    protected function extractFileUrlParams(): ?array {
        $url = $this->getFileUrl();
        if (!$url) return null;

        $query = parse_url($url, PHP_URL_QUERY);
        if ($query === null || $query === false || !is_string($query) || $query === '') return null;

        parse_str($query, $params);
        if (!$params) return null;

        return $params;
    }

    /**
     * @return string|null See {@link fileUrl}
     * @since 1.10.2
     */
    public function getFileUrl(): ?string {
        return $this->fileUrl;
    }

    /**
     * @param string $url The URL whose response headers are wanted
     * @return null|array|Requests_Utility_CaseInsensitiveDictionary Response headers
     * @since 1.10.2
     * @uses  wp_get_http_headers()
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    protected function getHttpHeaders(string $url) {
        // We need to make the request via WordPress to utilize the cookies and other arguments assigned via request
        // filters, e.g. via 'http_request_args' filter.

        $response = $this->getHttpResponse();
        if ($response !== null && isset($response['headers'])) {
            return $response['headers'];
        }

        return wp_get_http_headers($url) ?: null;
    }

    /**
     * @return array|null See {@link httpResponse}
     * @since 1.10.2
     */
    public function getHttpResponse(): ?array {
        return $this->httpResponse;
    }
}