<?php namespace Tashkar18\AssetVersion;

class Formater {
    protected $instance;
    protected $style;

    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->style = 'append';
    }

    public function format()
    {
        if ('directory' == $this->style) {
            return str_replace($this->origin(), $this->destination()."/".$this->version(), $this->path());
        }

        $path = explode('.', $this->path());

        return str_replace($this->origin(), $this->destination(), $path[0]."-".$this->version().".".$path[1]);
    }

    protected function origin()
    {
        return $this->instance['origin'];
    }

    protected function destination()
    {
        return $this->instance['dist'];
    }

    protected function version()
    {
        return $this->instance['version'];
    }

    protected function path()
    {
        return $this->instance['path'];
    }

    public function style($styleName)
    {
        $this->style = $styleName;

        return $this;
    }
}