<?php

namespace Application\Export\Xlsx\Writer;

abstract class XlsxWriter implements XlsxWriterInterface
{
    protected string $inputFilePath;
    protected array $data;

    public function setInputFilePath(string $inputFilePath): void
    {
        $this->inputFilePath = $inputFilePath;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    abstract public function writeToFile(string $outpuFilePath): void;
    
    public function writeToString(): string
    {
        $outpuFilePath = tempnam(sys_get_temp_dir(), __CLASS__);
        $this->writeToFile($outpuFilePath);
        $content = file_get_contents($outpuFilePath);
        unlink($outpuFilePath);

        return $content;
    }
}