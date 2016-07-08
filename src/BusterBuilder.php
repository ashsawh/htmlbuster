<?php

namespace Sawh\HtmlBuster;

use BadMethodCallException;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Collective\Html\HtmlBuilder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class BusterBuilder extends HtmlBuilder {
    const VERSION_PATH = 'dist/versions.json';
    const ASSETS_VERSION_PATH = 'versions/';
    protected $separator = '_';

    /*
     * Overloaded from parent class. Maintains the same functional as Collective\Html\HtmlBuilder with the addition
     * of getting the path to the most updated version of the specified style sheet.
     */
    public function style($url, $attributes = array(), $secure = NULL)
    {
        return parent::style($this->getHashedUrl($url), $attributes, $secure);
    }

    /*
     * Overloaded from parent class. Maintains the same functional as Collective\Html\HtmlBuilder with the addition
     * of getting the path to the most updated version of the specified JavaScript files.
     */
    public function script($url, $attributes = array(), $secure = NULL)
    {
        return parent::script($this->getHashedUrl($url), $attributes, $secure);
    }

    /**
     * Set the file pathway separator that was established through grunt's hashmap. The default is '-'
     * @param string $separator
     * @return Sawh\HtmlBuster\BusterBuilder
     */
    public function setFileSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Function takes the url input gets all listed information about it's path, from there the hashfile is then read
     * from config defaults and output being the most current version for the asset.
     *
     * @param string $url
     * @return string
     */
    protected function getHashedUrl($url)
    {
        try
        {
            $hashMap = $this->getJsonFile();
        }
        catch (FileNotFoundException $exception)
        {
            return $url;
        }

        $info = pathinfo($url);
        $name = $info['basename'];
        $hash = isset($hashMap[$name]) ? $hashMap[$name] : false;

        if (!$hash) return $url;
        return $this->getConfigVar('assets_version_path') . $info['filename'] . $this->separator . $hash .'.'. $info['extension'];
    }

    /**
     * Uses the listed configured pathway for the hash file, which is expected to be JSON.
     * @return array Decoded JSON data
     */
    protected function getJsonFile()
    {
        return json_decode(File::get(public_path($this->getConfigVar('version_path'))), true);
    }

    /**
     * Checks and retrieves specified variable from app configuration. If one does not exist established class
     * constants are used.
     *
     * @param string $var
     * @return string
     */
    protected function getConfigVar($var)
    {
        $result = Config::get("app.{$var}");
        return $result ? $result : self::strtoupper($var);
    }

}
