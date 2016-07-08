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
     * of getting the path to the most updated version of the specified style sheet.
     */
    public function script($url, $attributes = array(), $secure = NULL)
    {
        return parent::script($this->getHashedUrl($url), $attributes, $secure);
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
        $assetsPath = Config::get('app.assets_version_path') ? Config::get('app.assets_version_path') : self::ASSETS_VERSION_PATH;
        #dd($assetsPath . $info['filename'] .'.'. $hash .'.'. $info['extension']);
        return $assetsPath . $info['filename'] .'_'. $hash .'.'. $info['extension'];
    }

    protected function getJsonFile()
    {
        $path = Config::get('app.version_path') ? Config::get('app.version_path') : self::VERSION_PATH;
        $file = File::get(public_path($path));
        return json_decode($file, true);
    }

}
