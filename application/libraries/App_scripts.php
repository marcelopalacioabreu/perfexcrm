<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_assets.php');

class App_scripts extends App_assets
{
    protected $scripts = [];

    public function add($name, $data, $group = 'admin')
    {
        if (isset($this->scripts[$group][$name])) {
            return false;
        }

        $this->initializeEmptyGroup($group, 'scripts');

        if (is_string($data)) {
            $data = ['path' => $data];
        }

        $this->scripts[$group][$name] = $data;

        return true;
    }

    public function get($group = 'admin')
    {
        return $group === null ? $this->scripts[$group] : $this->scripts;
    }

    public function compile($group = 'admin')
    {
        $html = '';

        $defaults = [
            'type' => 'text/javascript',
        ];

        foreach ($this->scripts[$group] as $id => $data) {
            $attributes = $defaults;

            $version = isset($data['version']) ? $data['version'] : true;

            $attributes['src'] = $this->compileUrl($data['path'], $version);
            $attributes['id']  = $id;

            $html .= '<script' . $this->attributesToString($id, $attributes, $data) . '></script>' . PHP_EOL;
        }

        return $html;
    }

    public function coreScript($path, $fileName)
    {
        if (get_option('use_minified_files') == 1) {
            $fileName = $this->getMinifiedFileName($fileName, $path);
        }

        $ver = ENVIRONMENT == 'development' ? time() : get_app_version();

        return '<script src="' . base_url($path . '/' . $fileName . '?v=' . $ver) . '"></script>' . PHP_EOL;
    }
}
