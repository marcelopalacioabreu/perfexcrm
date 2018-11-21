<?php

defined('BASEPATH') or exit('No direct script access allowed');

class App_assets
{
    public function remove($name, $group = 'admin')
    {
        $property = str_replace('app_', '', strtolower(get_class($this)));

        if (isset($this->{$property}[$group][$name])) {
            unset($this->{$property}[$group][$name]);

            return true;
        }

        return false;
    }

    public function getMinifiedFileName($nonMinifiedFileName, $path)
    {
        $fileNameArray = explode('.', $nonMinifiedFileName);
        $last          = count($fileNameArray) - 1;
        $extension     = $fileNameArray[$last];
        unset($fileNameArray[$last]);

        $filename = '';
        foreach ($fileNameArray as $t) {
            $filename .= $t . '.';
        }

        $filename .= 'min.' . $extension;

        if (file_exists($path . '/' . $filename)) {
            $nonMinifiedFileName = $filename;
        }

        return $nonMinifiedFileName;
    }

    protected function initializeEmptyGroup($group, $property)
    {
        $exists = array_key_exists($group, $this->{$property});

        if (!$exists || ($exists && !is_array($this->{$property}[$group]))) {
            $this->{$property}[$group] = [];
        }
    }

    protected function compileUrl($path, $version = true)
    {
        $url = $path;

        if (!$this->strStartsWith($path, 'http') && !$this->strStartsWith($path, '//')) {
            $url = base_url($path);

            if ($version) {
                // Returns a string if the URL has parameters or NULL if not
                if (parse_url($url, PHP_URL_QUERY)) {
                    $url .= '&v=' . get_app_version();
                } else {
                    $url .= '?v=' . get_app_version();
                }
            }
        }

        return $url;
    }

    protected function attributesToString($id, $defaults, $asset)
    {
        if (isset($asset['attributes'])) {
            $defaults = array_merge($defaults, $asset['attributes']);
        }

        return $this->removeEmptyStringAttributes(_attributes_to_string(
            $this->removeEmptyAttributes($defaults)
        ), $id);
    }

    protected function removeEmptyAttributes($attributes)
    {
        foreach ($attributes as $key => $val) {
            if (empty($val)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    protected function removeEmptyStringAttributes($parsedAttributes, $id)
    {
        // E.q. // 0="defer" becomes defer
        $re = '/(\d\=\")([a-zA-Z0-9-_]+)\"/m';

        return preg_replace($re, '$2', $parsedAttributes);
    }

    protected function strStartsWith($haystack, $needle)
    {
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}
