<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

class Base64FileInput extends FileInput
{
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var UploadedFileFactoryInterface
     */
    private $uploadedFileFactory;

    /**
     * Base64FileInput constructor.
     *
     * @param StreamFactoryInterface $streamFactory
     * @param UploadedFileFactoryInterface $uploadedFileFactory
     * @param string|null $name
     */
    public function __construct(
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        $name = null
    ) {
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        parent::__construct($name);
    }

    public function setValue($value)
    {
        $value = $this->decodeValue($value);
        return parent::setValue($value);
    }

    /**
     * @param mixed $value
     * @return UploadedFileInterface
     */
    private function decodeValue($value)
    {
        if (! is_string($value) || null === ($mediaType = self::matchMediaType($value))) {
            throw new Exception\InvalidArgumentException('Provided value is not base64 encoded');
        }

        $fileName = tempnam(sys_get_temp_dir(), 'file_');
        $file = fopen($fileName, 'wb');
        $data = explode(',', $value);
        fwrite($file, base64_decode($data[1]));

        $fileStream = $this->streamFactory->createStreamFromFile($fileName);
        return $this->uploadedFileFactory->createUploadedFile($fileStream, null, UPLOAD_ERR_OK, $fileName, $mediaType);
    }

    /**
     * @param string $value
     * @return string|null
     */
    private static function matchMediaType(string $value)
    {
        $pattern = '/data\:(\w+\/\w+)\;base64\,/';
        preg_match($pattern, $value, $matches);

        return empty($matches) ? null : $matches[1];
    }
}
