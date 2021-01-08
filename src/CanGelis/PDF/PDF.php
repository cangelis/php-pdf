<?php

namespace CanGelis\PDF;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;

class PDF
{
    /**
     * Random name that will be the name of the temporary files.
     *
     * @var string
     */
    protected $fileName;

    /**
     * Folder in which temporary files will be saved.
     *
     * @var string
     */
    protected $folder;

    /**
     * HTML content that will be converted to PDF.
     *
     * @var string
     */
    protected $htmlContent = null;

    /**
     * Params to be executed by wkhtmltopdf.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Input Path that will be generated to PDF Doc.
     *
     * @var string
     */
    protected $path = null;

    /**
     * PDF File's Binary content.
     *
     * @var mixed
     */
    protected $contents = null;

    /**
     * Available command parameters for wkhtmltopdf.
     *
     * @var array
     */
    protected $availableParams = [
        'grayscale', 'orientation', 'page-size',
        'lowquality', 'dpi', 'image-dpi', 'image-quality',
        'margin-bottom', 'margin-left', 'margin-right', 'margin-top',
        'page-height', 'page-width', 'no-background', 'encoding', 'enable-forms',
        'no-images', 'disable-internal-links', 'disable-javascript',
        'password', 'username', 'footer-center', 'footer-font-name',
        'footer-font-size', 'footer-html', 'footer-left', 'footer-line',
        'footer-right', 'footer-spacing', 'header-center', 'header-font-name',
        'header-font-size', 'header-html', 'header-left', 'header-line', 'header-right',
        'header-spacing', 'print-media-type', 'zoom', 'disable-smart-shrinking',
    ];

    /**
     * wkhtmltopdf executable path.
     *
     * @var string
     */
    protected $cmd;

    /**
     * Initialize temporary file names and folders.
     *
     * @param $cmd
     * @param null $tmpFolder
     */
    public function __construct($cmd, $tmpFolder = null)
    {
        $this->cmd = $cmd;

        $this->fileName = uniqid(rand(0, 99999));

        if (is_null($tmpFolder)) {
            $this->folder = sys_get_temp_dir();
        } else {
            $this->folder = $tmpFolder;
        }
    }

    /**
     * Loads the HTML Content from plain text.
     *
     * @param string $html
     *
     * @return $this
     */
    public function loadHTML($html)
    {
        $this->htmlContent = $html;

        return $this;
    }

    /**
     * Loads the input source as a URL.
     *
     * @param string $url
     *
     * @return $this
     */
    public function loadUrl($url)
    {
        return $this->setPath($url);
    }

    /**
     * Loads the input source as an HTML File.
     *
     * @param $file
     *
     * @return $this
     */
    public function loadHTMLFile($file)
    {
        return $this->setPath($file);
    }

    /**
     * Generates the PDF and save the PDF content for the further use.
     *
     * @throws PDFException
     *
     * @return string
     */
    public function generate()
    {
        $returnVar = $this->executeCommand($output);

        if ($returnVar == 0) {
            $this->contents = $this->getPDFContents();
        } else {
            throw new PDFException($output);
        }

        $this->removeTmpFiles();

        return $this;
    }

    /**
     * Saves the pdf content to the specified location.
     *
     * @param                  $fileName
     * @param AdapterInterface $adapter
     * @param bool             $overwrite
     *
     * @return $this
     */
    public function save($fileName, AdapterInterface $adapter, $overwrite = false)
    {
        $fs = new Filesystem($adapter);

        if ($overwrite == true) {
            $fs->put($fileName, $this->get());
        } else {
            $fs->write($fileName, $this->get());
        }

        return $this;
    }

    public function get()
    {
        if (is_null($this->contents)) {
            $this->generate();
        }

        return $this->contents;
    }

    /**
     * Remove temporary HTML and PDF files.
     */
    public function removeTmpFiles()
    {
        if (file_exists($this->getHTMLPath())) {
            @unlink($this->getHTMLPath());
        }
        if (file_exists($this->getPDFPath())) {
            @unlink($this->getPDFPath());
        }
    }

    /**
     * Gets the contents of the generated PDF.
     *
     * @return string
     */
    public function getPDFContents()
    {
        return file_get_contents($this->getPDFPath());
    }

    /**
     * Execute wkhtmltopdf command.
     *
     * @param array &$output
     *
     * @return int
     */
    public function executeCommand(&$output)
    {
        $descriptorspec = [
            0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
        ];

        $process = proc_open($this->cmd.' '.$this->getParams().' '.$this->getInputSource().' '.$this->getPDFPath(), $descriptorspec, $pipes);

        $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process);
    }

    /**
     * Gets the parameters defined by user.
     *
     * @return string
     */
    protected function getParams()
    {
        $result = '';
        foreach ($this->params as $key => $value) {
            if (is_numeric($key)) {
                $result .= '--'.$value;
            } else {
                $result .= '--'.$key.' '.'"'.$value.'"';
            }
            $result .= ' ';
        }

        return $result;
    }

    /**
     * Sets the input argument for wkhtmltopdf.
     *
     * @param $path
     *
     * @return $this
     */
    protected function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Adds a wkhtmltopdf parameter.
     *
     * @param string $key
     * @param string $value
     */
    protected function addParam($key, $value = null)
    {
        if (is_null($value)) {
            $this->params[] = $key;
        } else {
            $this->params[$key] = $value;
        }
    }

    /**
     * Converts a method name to a wkhtmltopdf parameter name.
     *
     * @param string $method
     *
     * @return string
     */
    protected function methodToParam($method)
    {
        $replace = '$1-$2';

        return strtolower(preg_replace('/(.)([A-Z])/', $replace, $method));
    }

    /**
     * Gets the Input source which can be an HTML file or a File path.
     *
     * @return string
     */
    protected function getInputSource()
    {
        if (!is_null($this->path)) {
            return $this->path;
        }

        file_put_contents($this->getHTMLPath(), $this->htmlContent);

        return $this->getHTMLPath();
    }

    /**
     * Gets the temporary saved PDF file path.
     *
     * @return string
     */
    protected function getPDFPath()
    {
        return $this->folder.'/'.$this->fileName.'.pdf';
    }

    /**
     * Gets the temporary save HTML file path.
     *
     * @return string
     */
    protected function getHTMLPath()
    {
        return $this->folder.'/'.$this->fileName.'.html';
    }

    /**
     * Gets the error file's path in which stderr will be written.
     */
    protected function getTmpErrFilePath()
    {
        return $this->folder.'/'.$this->fileName.'.log';
    }

    /**
     * Handle method<->parameter conventions.
     *
     * @param string $method
     * @param string $args
     *
     * @throws PDFException
     *
     * @return $this
     */
    public function __call($method, $args)
    {
        $param = $this->methodToParam($method);
        if (in_array($param, $this->availableParams)) {
            if (isset($args[0])) {
                $this->addParam($param, $args[0]);
            } else {
                $this->addParam($param);
            }

            return $this;
        } else {
            throw new PDFException('Undefined method: '.$method);
        }
    }
}
