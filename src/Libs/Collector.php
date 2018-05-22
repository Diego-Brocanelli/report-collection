<?php

namespace ReportCollection\Libs;

class Collector extends Reader
{
    private $styler = null;

    private $writer = null;

    protected function getStyler()
    {
        if($this->styler == null) {
            $this->styler = Styler::createFromReader($this);
        }
        return $this->styler;
    }

    protected function getWriter()
    {
        if($this->writer == null) {
            $this->writer = Writer::createFromStyler($this->getStyler());
        }
        return $this->writer;
    }

    // Métodos Publicos

    /**
     * Seta a informação de criador do documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoCreator($string)
    {
        $this->getWriter()->setInfoCreator($string);
        return $this;
    }

    /**
     * Seta a última pessoa a alterar o documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoLastModifiedBy($string)
    {
        $this->getWriter()->setInfoLastModifiedBy($string);
        return $this;
    }

    /**
     * Seta o título do documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoTitle($string)
    {
        $this->getWriter()->setInfoTitle($string);
        return $this;
    }

    /**
     * Seta o assunto do documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoSubject($string)
    {
        $this->getWriter()->setInfoSubject($string);
        return $this;
    }

    /**
     * Seta a descrição do documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoDescription($string)
    {
        $this->getWriter()->setInfoDescription($string);
        return $this;
    }

    /**
     * Seta palavras chave para o documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoKeywords($string)
    {
        $this->getWriter()->setInfoKeywords($string);
        return $this;
    }

    /**
     * Seta a categoria do documento.
     * @param string $string
     * @return ReportCollection\Libs\Collector
     */
    public function setInfoCategory($string)
    {
        $this->getWriter()->setInfoCategory($string);
        return $this;
    }

    /**
     * Aplica estilos com base nos indices do Excel.
     * @return ReportCollection\Libs\Collector
     */
    public function setStyles($range, $styles = [])
    {
        $this->getStyler()->setStyles($range, $styles);
        return $this;
    }

    /**
     * Especifica o formato das datas no arquivo resultante da gravação.
     * O formato deve ser uma string com o código da formatação.
     * Ex: O formato d/m/Y resultará em 31/12/9999
     * @see https://secure.php.net/manual/pt_BR/datetime.createfromformat.php
     * @param string $format
     * @return ReportCollection\Libs\Collector
     */
    public function setOutputDateFormat($format)
    {
        $this->getWriter()->setOutputDateFormat($string);
        return $this;
    }

    /**
     * Define a largura padrão de uma coluna.
     * O valor de $col pode ser especificado como vogal (no estilo excel)
     * ou como índice numérico (começando com 0)
     * @param mixed $col
     * @param int $value
     * @throws \InvalidArgumentException
     * @return ReportCollection\Libs\Collector
     */
    public function setColumnWidth($col, $value)
    {
        $this->getWriter()->setColumnWidth($col, $value);
        return $this;
    }

    public function save($filename, $download = false)
    {
        return $this->getWriter()->save($filename, $download = false);
    }

    public function output($filename)
    {
        return $this->getWriter()->output($filename);
    }
}
