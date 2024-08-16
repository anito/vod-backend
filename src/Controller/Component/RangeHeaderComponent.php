<?php
namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Log\Log;

class DownloadNotAllowed extends \RuntimeException
{}
class NonExistentFileException extends \RuntimeException
{}
class UnreadableFileException extends \RuntimeException
{}
class UnsatisfiableRangeException extends \RuntimeException
{}
class InvalidRangeHeaderException extends \RuntimeException
{}

class RangeHeaderComponent extends Component
{
    /**
     * The first byte in the file to send (0-indexed), a null value indicates the last
     * $end bytes
     *
     * @var int|null
     */
    private $firstByte;

    /**
     * The last byte in the file to send (0-indexed), a null value indicates $start to
     * EOF
     *
     * @var int|null
     */
    private $lastByte;

    /**
     * Create a new instance from a Range header string
     *
     * @param string $header
     * @return RangeHeaderComponent
     */
    public function createFromHeaderString($header)
    {
        if ($header === null) {
            return null;
        }

        if (!preg_match('/^\s*(\S+)\s*=\s*(\d+)\s*-\s*(\d*)\s*(?:,|$)/', $header, $info)) {
            throw new InvalidRangeHeaderException('Invalid header format');
        } else if (strtolower($info[1]) !== 'bytes') {
            throw new InvalidRangeHeaderException('Unknown range unit: ' . $info[1]);
        }

        return $this->setBytes(
            $info[2] === '' ? null : $info[2],
            $info[3] === '' ? null : $info[3]
        );
    }

    /**
     * @param int|null $firstByte
     * @param int|null $lastByte
     * @throws InvalidRangeHeaderException
     * @return RangeHeaderComponent
     */
    public function setBytes($firstByte, $lastByte) {
        $this->firstByte = $firstByte === null ? $firstByte : (int) $firstByte;
        $this->lastByte = $lastByte === null ? $lastByte : (int) $lastByte;

        if ($this->firstByte === null && $this->lastByte === null) {
            throw new InvalidRangeHeaderException(
                'Both start and end position specifiers empty'
            );
        } else if ($this->firstByte < 0 || $this->lastByte < 0) {
            throw new InvalidRangeHeaderException(
                'Position specifiers cannot be negative'
            );
        } else if ($this->lastByte !== null && $this->lastByte < $this->firstByte) {
            throw new InvalidRangeHeaderException(
                'Last byte cannot be less than first byte'
            );
        }

        return $this;
    }

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);

    }

    /**
     * Get the start position when this range is applied to a file of the specified size
     *
     * @param int $fileSize
     * @return int
     * @throws UnsatisfiableRangeException
     */
    public function getStartPosition($fileSize)
    {
        $size = (int) $fileSize;

        if ($this->firstByte === null) {
            return ($size - 1) - $this->lastByte;
        }

        if ($size <= $this->firstByte) {
            throw new UnsatisfiableRangeException(
                'Start position is after the end of the file'
            );
        }

        return $this->firstByte;
    }

    /**
     * Get the end position when this range is applied to a file of the specified size
     *
     * @param int $fileSize
     * @return int
     * @throws UnsatisfiableRangeException
     */
    public function getEndPosition($fileSize)
    {
        $size = (int) $fileSize;

        if ($this->lastByte === null) {
            return $size - 1;
        }

        if ($size <= $this->lastByte) {
            throw new UnsatisfiableRangeException(
                'End position is after the end of the file'
            );
        }

        return $this->lastByte;
    }

    /**
     * Get the length when this range is applied to a file of the specified size
     *
     * @param int $fileSize
     * @return int
     * @throws UnsatisfiableRangeException
     */
    public function getLength($fileSize)
    {
        $size = (int) $fileSize;

        return $this->getEndPosition($size) - $this->getStartPosition($size) + 1;
    }

    /**
     * Get a Content-Range header corresponding to this Range and the specified file
     * size
     *
     * @param int $fileSize
     * @return string
     */
    public function getContentRangeHeader($fileSize)
    {
        return 'bytes ' . $this->getStartPosition($fileSize) . '-'
        . $this->getEndPosition($fileSize) . '/' . $fileSize;
    }

    /**
     * Get the value of a header in the current request context
     *
     * @param string $name Name of the header
     * @return string|null Returns null when the header was not sent or cannot be retrieved
     */
    public function getRequestHeader($name)
    {
        $name = strtoupper($name);

        // IIS/Some Apache versions and configurations
        if (isset($_SERVER['HTTP_' . $name])) {
            return trim($_SERVER['HTTP_' . $name]);
        }

        // Various other SAPIs
        foreach (apache_request_headers() as $header_name => $value) {
            if (strtoupper($header_name) === $name) {
                return trim($value);
            }
        }

        return null;
    }

}
