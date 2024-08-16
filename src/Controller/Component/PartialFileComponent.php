<?php

namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Log\Log;

class PartialFileComponent extends Component
{

    /**
     * The RangeHeaderComponent on which the data transmission will be based
     *
     * @var RangeHeaderComponent|null
     */
    private $rangeHeader;

    protected array $components = ['RangeHeader'];

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry for this component
     * @param array $config Array of config.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
    }

    public function initialize(array $config): void
    {
        $header = $this->RangeHeader->getRequestHeader('Range');
        $this->rangeHeader = $this->RangeHeader->createFromHeaderString($header);
    }

    /**
     * Send part of the data in a seekable stream resource to the output buffer
     *
     * @param resource $fp Stream resource to read data from
     * @param int $start Position in the stream to start reading
     * @param int $length Number of bytes to read
     * @param int $chunkSize Maximum bytes to read from the file in a single operation
     */
    private function sendDataRange($fp, $start, $length, $chunkSize = 8192)
    {
        if ($start > 0) {
            fseek($fp, $start, SEEK_SET);
        }

        while ($length) {
            $read = ($length > $chunkSize) ? $chunkSize : $length;
            $length -= $read;
            echo fread($fp, $read);
        }
    }

    /**
     * Send the headers that are included regardless of whether a range was requested
     *
     * @param string $fileName
     * @param int $contentLength
     * @param string $contentType
     */
    private function sendDownloadHeaders($fileName, $contentLength, $contentType = 'application/octet-stream')
    {
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Accept-Ranges: bytes');
        header('Content-Type:' . $contentType);
        header('Content-Length:' . $contentLength);
    }

    /**
     * Send data from a file based on the current Range header
     *
     * @param RangeHeaderComponent $range RangeHeader Component
     * @param string $path Local file system path to serve
     * @param string $contentType MIME type of the data stream
     */
    public function sendFile($path, $contentType = 'application/octet-stream')
    {

        // Make sure the file exists and is a file, otherwise we are wasting our time
        $localPath = realpath($path);
        if ($localPath === false || !is_file($localPath)) {
            throw new NonExistentFileException(
                $path . ' does not exist or is not a file'
            );
        }

        // Make sure we can open the file for reading
        if (!$fp = fopen($localPath, 'r')) {
            throw new UnreadableFileException(
                'Failed to open ' . $localPath . ' for reading'
            );
        }

        $fileSize = filesize($localPath);

        if ($this->rangeHeader == null) {
            header('HTTP/1.1 403 Forbidden');
            throw new DownloadNotAllowed(
                'You are not allowed to download this video'
            );
            die();

            $this->sendDownloadHeaders(basename($localPath), $fileSize, $contentType);

            // No range requested, just send the whole file
            fpassthru($fp);
        } else {
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: ' . $this->rangeHeader->getContentRangeHeader($fileSize));
            $this->sendDownloadHeaders(
                basename($localPath),
                $this->rangeHeader->getLength($fileSize),
                $contentType
            );

            $this->sendDataRange(
                $fp,
                $this->rangeHeader->getStartPosition($fileSize),
                $this->rangeHeader->getLength($fileSize)
            );
        }

        fclose($fp);
    }
}
