<?php

namespace ReportCollection\Libs;

class Collector extends Reader
{
    /** @var ReportCollection\Libs\Styler */
    private $styler = null;

    /** @var ReportCollection\Libs\Writer */
    private $writer = null;

    /**
     * @return ReportCollection\Libs\Reader
     */
    public function getReader()
    {
        return $this;
    }

    /**
     * @return ReportCollection\Libs\Styler
     */
    public function getStyler()
    {
        if ($this->styler == null) {
            $this->styler = Styler::createFromReader($this);
        }

        return $this->styler;
    }

    /**
     * @return ReportCollection\Libs\Writer
     */
    public function getWriter()
    {
        return Writer::createFromReader($this);
    }

    public function save($filename)
    {
        return $this->getWriter()->save($filename);
    }

    public function output($extension, $name = null)
    {
        return $this->getWriter()->output($extension, $name);
    }
}
