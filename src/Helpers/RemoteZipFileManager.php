<?php

declare(strict_types=1);

namespace UnicoETL\Helpers;

use GuzzleHttp\Client as GuzzleHttpClient;
use Exception;
use ZipArchive;

/**
 * Class RemoteZipFileManager
 * Implements an expressive interface for downloading and working with remote zip files.
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class RemoteZipFileManager
{
    /**
     * Used as sub-directory inside the temporary directory
     * for debugging and isolation purposes.
     * 
     * @var string
     */
    private const APP_DIR_NAME = 'UnicoETL';

    /**
     * Defines the working directory for downloading and manipulating files.
     * This should inside the temporary directory on the host OS.
     * Since this is different for each OS, we set it during in the constructor.
     * 
     * @var string
     */
    private string $workDir = '';

    /**
     * A unique generated file name with the full absolute path.
     * This will be used as our 'file pointer' to access
     * and mutate the file.
     * 
     * @var string
     */
    private string $filePointer = '';

    /**
     * Initializes the file manager interface.
     * 
     * @return void
     */
    public final function __construct()
    {
        $this->workDir = sys_get_temp_dir() . '/' . self::APP_DIR_NAME;
        $this->createDir(dirPath: $this->workDir);
        $this->filePointer = md5(uniqid());
    }

    /**
     * Create a directory if it doesn't exist.
     * 
     * @param string $dirPath The absolute path to the directory.
     *
     * @return void
     */
    private function createDir(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777);
        }
    }

    /**
     * Download a zip file from given URL.
     * 
     * @param string $url The URL containing a valid zip file to be downloaded.
     * @param array $headers A key-value pair array containing headers to be sent with the request for download.
     * 
     * @throws Exception
     * @return RemoteZipFileManager
     */
    public final function getRemoteFile(string $url, array $headers = null): RemoteZipFileManager
    {
        $this->filePointer = "{$this->workDir}/{$this->filePointer}";

        $client = new GuzzleHttpClient(['headers' => $headers]);
        $res = $client->request('GET', $url, ['sink' => $this->filePointer]);

        if ($res->getStatusCode() !== 200) {
            throw new Exception("Failed to download remote file from URL {$url}");
        }

        return $this;
    }

    /**
     * Extracts a specific file inside a .zip file.
     * This will lookup at any level of depth inside the zip file.
     * 
     * @param string $fileName The name of the file for lookup and extraction
     * 
     * @throws Exception Throws if unable to open the zip content or lookup the specified file
     * @return string The raw file contents as a text representation
     */
    public final function extractFile(string $fileName): string
    {
        $zip = new ZipArchive;
        $pointerStatus = $zip->open($this->filePointer);

        if ($pointerStatus !== true) {
            throw new Exception("Unable to open the file {$this->filePointer}");
        }

        /** Attemps to find the file inside the .zip */
        $lookupIndex = $zip->locateName($fileName, ZIPARCHIVE::FL_NODIR);
        if ($lookupIndex === false) {
            $zip->close();
            throw new Exception("File name `$fileName` not found inside .zip file");
        }

        /** Attempts to extract and fetch the file contents */
        $fileContents = $zip->getFromIndex($lookupIndex);
        if ($fileContents === false) {
            $zip->close();
            throw new Exception("Unable to parse contents of file `$fileName`");
        }

        $zip->close();

        return $fileContents;
    }
}
