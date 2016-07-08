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

    public function setFileSeparator($separator)
    {
        $this->separator = $separator;
    }

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

    protected function getJsonFile()
    {
        return json_decode(File::get(public_path($this->getConfigVar('version_path'))), true);
    }

    protected function getConfigVar($var)
    {
        $result = Config::get("app.{$var}");
        return $result ? $result : self::strtoupper($var);
    }

}
