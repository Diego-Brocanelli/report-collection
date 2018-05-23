<?php

namespace ReportCollection\Libs;

class Collector extends Reader
{
    /** @var ReportCollection\Libs\Styler */
    private $styler = null;

    /** @var ReportCollection\Libs\Writer */
    private $writer = null;

    protected function getStyler() : Styler
    {
        if($this->styler == null) {
            $this->styler = Styler::createFromReader($this);
        }
        return $this->styler;
    }

    protected function getWriter() : Writer
    {
        if($this->writer == null) {
            $this->writer = Writer::createFromStyler($this->getStyler());
        }
        return $this->writer;
    }

    // Métodos Publicos

    /**
     * Seta a informação de criador do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoCreator(string $text) : Collector
    {
        $this->getWriter()->setInfoCreator($text);
        return $this;
    }

    /**
     * Seta a última pessoa a alterar o documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoLastModifiedBy(string $text) : Collector
    {
        $this->getWriter()->setInfoLastModifiedBy($text);
        return $this;
    }

    /**
     * Seta o título do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoTitle(string $text) : Collector
    {
        $this->getWriter()->setInfoTitle($text);
        return $this;
    }

    /**
     * Seta o assunto do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoSubject(string $text) : Collector
    {
        $this->getWriter()->setInfoSubject($text);
        return $this;
    }

    /**
     * Seta a descrição do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoDescription(string $text) : Collector
    {
        $this->getWriter()->setInfoDescription($text);
        return $this;
    }

    /**
     * Seta palavras chave para o documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoKeywords(string $text) : Collector
    {
        $this->getWriter()->setInfoKeywords($text);
        return $this;
    }

    /**
     * Seta a categoria do documento.
     *
     * @param string $text
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoCategory(string $text) : Collector
    {
        $this->getWriter()->setInfoCategory($text);
        return $this;
    }

    /**
     * Aplica estilos com base nos indices do Excel.
     *
     * @param  mixed $range
     * @param  array $styles
     * @return ReportCollection\Libs\Collector
     */
    public function setStyles($range, array $styles = []) : Collector
    {
        $this->getStyler()->setStyles($range, $styles);
        return $this;
    }

    /**
     * Especifica o formato das datas no arquivo resultante da gravação.
     * O formato deve ser uma string com o código da formatação.
     * Ex: O formato d/m/Y resultará em 31/12/9999
     *
     * @see https://secure.php.net/manual/pt_BR/datetime.createfromformat.php
     * @param string $format
     * @return ReportCollection\Libs\Collector
     */
    public function setOutputDateFormat(string $format) : Collector
    {
        $this->getWriter()->setOutputDateFormat($string);
        return $this;
    }

    /**
     * Define a largura padrão de uma coluna.
     * O valor de $col pode ser especificado como vogal (no estilo excel)
     * ou como índice numérico (começando com 0)
     *
     * @param mixed $col
     * @param int $value
     * @throws \InvalidArgumentException
     * @return ReportCollection\Libs\Collector
     */
    public function setColumnWidth($col, $value) : Collector
    {
        $this->getWriter()->setColumnWidth($col, $value);
        return $this;
    }

    /**
     * Grava o resultado em arquivo.
     *
     * @param  string  $filename
     * @param  boolean $download
     * @return void
     */
    public function save(string $filename, bool $force_download = false)
    {
        return $this->getWriter()->save($filename, $force_download = false);
    }

    /**
     * Libera o buffer e força o download.
     *
     * @param string $filename
     * @return void
     */
    public function output(string $filename)
    {
        return $this->getWriter()->output($filename);
    }
}
