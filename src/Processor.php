<?php

namespace Appstract\HostsFile;

use Exception;

class Processor
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $bakPath;

    /**
     * @var array
     */
    protected $lines = [];

    /**
     * Hosts constructor.
     *
     * @param $filePath
     *
     * @throws Exception
     */
    public function __construct($filePath = null)
    {
        if (is_null($filePath)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $filePath = 'C:/Windows/System32/drivers/etc/hosts';
            } else {
                $filePath = '/etc/hosts';
            }
        }

        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new Exception(sprintf('Unable to read file: %s', $filePath));
        }

        $this->filePath = realpath($filePath);
        $this->bakPath = realpath($filePath).'.bak';

        $this->readFile();
    }

    /**
     * Return lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Add a line.
     *
     * @param        $ip
     * @param        $domain
     * @param string $aliases
     *
     * @return $this
     * @throws Exception
     */
    public function addLine($ip, $domain, $aliases = '')
    {
        $this->lines[$domain] = ['ip' => $ip, 'aliases' => $aliases];

        return $this;
    }

    /**
     * @param $domain
     *
     * @return $this
     * @throws Exception
     */
    public function removeLine($domain)
    {
        if (! filter_var($domain, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-zA-Z0-9\\.]*[a-zA-Z0-9]+?/']])) {
            throw new Exception(sprintf("'%s', is not a valid domain", $domain));
        }

        unset($this->lines[$domain]);

        return $this;
    }

    /**
     * Save the file.
     *
     * @param null $filePath
     */
    public function save($filePath = null)
    {
        if (is_null($filePath)) {
            $filePath = $this->filePath;
        }

        $this->writeFile($filePath);
    }

    /**
     * Read the File.
     */
    protected function readFile()
    {
        $file = fopen($this->filePath, 'r');

        while (($line = fgets($file)) !== false) {
            $this->parseLine($line);
        }

        fclose($file);
    }

    /**
     * Parse a line.
     *
     * @param $line
     */
    protected function parseLine($line)
    {
        $matches = $this->explodeLine($line);

        if (isset($matches[1], $matches[2])) {
            $ip = $matches[1];
            $domainLine = $this->explodeLine($matches[2]);

            if (isset($domainLine[1])) {
                $domain = $domainLine[1];
                $aliases = isset($domainLine[2]) ? $domainLine[2] : '';
            } else {
                $domain = $matches[2];
                $aliases = '';
            }

            $this->addLine($ip, $domain, $aliases);
        }
    }

    /**
     * Explode entry by whitespace regex.
     *
     * @param $line
     *
     * @return mixed
     */
    protected function explodeLine($line)
    {
        $line = preg_replace("/\#.+/", '', $line);

        preg_match("/^\s*?(.+?)\s+(.+?)$/i", $line, $matches);

        return $matches;
    }

    /**
     * Write lines to the file.
     *
     * @param $filePath
     *
     * @return $this
     * @throws Exception
     */
    protected function writeFile($filePath)
    {
        if (is_file($filePath) && ! is_writable($filePath)) {
            throw new Exception(sprintf("File '%s' is not writable, run with sudo?", $filePath));
        }

        $file = fopen($filePath, 'w');

        foreach ($this->lines as $domain => $attributes) {
            fwrite($file, $attributes['ip']."\t\t".$domain.' '.$attributes['aliases']." \r\n");
        }

        fclose($file);
    }
}
