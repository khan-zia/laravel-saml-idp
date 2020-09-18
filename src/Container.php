<?php

namespace ZiaKhan\SamlIdp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

use SAML2\Compat\AbstractContainer;

class Container extends AbstractContainer
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Array that contains the contents of a SAMLResponse.
     * 
     * @var array
     */
    protected array $data;


    /**
     * Initialize a new container.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->logger = Log::getLogger();
    }


    /**
     * Get the logger instance
     * 
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }


    /**
     * Generate a pseudo-random, saml compatible ID for saml messages.
     * 
     * @return string
     */
    public function generateId(): string
    {
        return "_" . Str::random(42);
    }


    /**
     * Write a debug message
     * 
     * @param mixed $message
     * @param string $type
     * @return void
     */
    public function debugMessage($message, string $type): void
    {
        // /** @psalm-suppress UndefinedClass */
        // XML::debugSAMLMessage($message, $type);
    }


    /**
     * {@inheritdoc}
     * @param string $url
     * @param array $data
     * @return void
     */
    public function redirect(string $url, array $data = []): void
    {
        // /** @psalm-suppress UndefinedClass */
        // HTTP::redirectTrustedURL($url, $data);
    }


    /**
     * Sets SAMLResponse data for a POST redirect on the container instance.
     * This array of data can then be used by the main application in any way suitable.
     * @see getData()
     * 
     * @param string $url The destination URL
     * @param array $data The data for SAMLResponse
     * @return void
     */
    public function postRedirect(string $url, array $data = []): void
    {
        $this->data = $data;
    }

    /**
     * Get the data set for SAMLResponse.
     * Use this data in the main application code. Either POST it to the destination URL
     * or attach it to the destination URL for a REDIRECT binding.
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }


    /**
     * Get the temporary directory
     * 
     * @return string
     */
    public function getTempDir(): string
    {
        // /** @psalm-suppress UndefinedClass */
        // return System::getTempDir();
        return '/tmp';
    }


    /**
     * Write data to a new file.
     * 
     * @param string $filename
     * @param string $data
     * @param int|null $mode
     * @return void
     */
    public function writeFile(string $filename, string $data, int $mode = null): void
    {
        // if ($mode === null) {
        //     $mode = 0600;
        // }
        // /** @psalm-suppress UndefinedClass */
        // System::writeFile($filename, $data, $mode);
    }
}
