<?php

namespace Appstract\HostsFile;

use Exception;
use Illuminate\Support\Collection;

class Processor
{
    /**
     * @var string
     */
    protected $filePath;


    protected $lines;

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

        $this->lines = new Collection();

        $this->readFile();
    }

    /**
     * Return lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines->all();
    }

    /**
     * Adds a line without checking if it already exists.
     *
     * @param        $ip
     * @param        $domain
     * @param string $aliases
     *
     * @return $this
     * @throws Exception
     */
    public function addLine($ip, $domain, $aliases = [])
    {
        $this->lines->push(['ip' => trim($ip), 'domain' => trim($domain), 'aliases' => $aliases]);

        return $this;
    }

    /**
     * Removes old value and adds new line.
     *
     * @param        $ip
     * @param        $domain
     * @param array $aliases
     *
     * @return Processor
     */
    public function set($ip, $domain, $aliases = [])
    {
        $this->removeLine($domain);

        if (! is_array($aliases)) {
            $aliases = [$aliases];
        }

        return $this->addLine($ip, $domain, $aliases);
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

        $this->lines = $this->lines->reject(function ($item) use ($domain) {
            return $item['domain'] == $domain;
        });

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
        $matches = $this->explodeLine(trim($line));

        if (isset($matches[1], $matches[2])) {
            $ip = $matches[1];
            $domainLine = $this->explodeLine($matches[2]);

            if (isset($domainLine[1])) {
                $domain = $domainLine[1];
                $aliases = isset($domainLine[2]) ? explode(' ', trim($domainLine[2])) : [];
            } else {
                $domain = $matches[2];
                $aliases = [];
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

        foreach ($this->getLines() as $line) {
            $aliases = '';

            if (count($line['aliases']) > 0) {
                $aliases = ' '.$aliases.implode(' ', $line['aliases']);
            }

            fwrite($file, $line['ip']."\t\t".$line['domain'].$aliases." \r\n");
        }

        fclose($file);
    }
}
