<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_assets.php');

class App_css extends App_assets
{
    protected $css = [];

    public function add($name, $data, $group = 'admin')
    {
        if (isset($this->css[$group][$name])) {
            return false;
        }

        $this->initializeEmptyGroup($group, 'css');

        if (is_string($data)) {
            $data = ['path' => $data];
        }

        $this->css[$group][$name] = $data;

        return true;
    }

    public function get($group = 'admin')
    {
        return $group === null ? $this->css[$group] : $this->css;
    }

    public function compile($group = 'admin')
    {
        $html = '';

        $defaults = [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
        ];

        foreach ($this->css[$group] as $id => $data) {
            $attributes = $defaults;

            $version = isset($data['version']) ? $data['version'] : true;

            $attributes['href'] = $this->compileUrl($data['path'], $version);
            $attributes['id']   = $id;

            $html .= '<link' . $this->attributesToString($id, $attributes, $data) . '>' . PHP_EOL;
        }

        return $html;
    }

    public function coreStylesheet($path, $fileName)
    {
        if (file_exists(FCPATH . $path . '/my_' . $fileName)) {
            $fileName = 'my_' . $fileName;
        }

        if (get_option('use_minified_files') == 1) {
            $fileName = $this->getMinifiedFileName($fileName, $path);
        }

        $ver = ENVIRONMENT == 'development' ? time() : get_app_version();

        return '<link href="' . base_url($path . '/' . $fileName . '?v=' . $ver) . '" rel="stylesheet">' . PHP_EOL;
    }
}
