<?php

namespace mykemeynell\FilePreview;

use Imagick;
use mykemeynell\FilePreview\FileSystem\ApplicationMimeTypes;
use mykemeynell\FilePreview\FileSystem\File;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

/**
 * Class FilePreview.
 *
 * @package mykemeynell\FilePreview
 */
class FilePreview
{
    private static $filePreviewInstance;

    /**
     * File object.
     *
     * @var \mykemeynell\FilePreview\FileSystem\File
     */
    public $file;

    /**
     * MIMEs that can be previewed.
     *
     * @var array
     */
    public $canPreview = [
        'image/(.*)',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    /**
     * Output of the preview.
     *
     * @var array
     */
    public $output = [
        'header' => null,
        'content' => null
    ];

    /**
     * Custom handlers for MIME types.
     *
     * @var array
     */
    public $handlers = [];

    /**
     * FilePreview constructor.
     *
     * @param \mykemeynell\FilePreview\FileSystem\File|null $file
     */
    function __construct(File $file = null)
    {
        $this->file = $file;
    }

    /**
     * Add a custom handler for a MIME type.
     *
     * @param string $mime
     * @param        $handler
     *
     * @throws \Exception
     */
    public static function addHandler(string $mime, $handler)
    {
        /** @var \mykemeynell\FilePreview\FilePreview $instance */
        $instance = self::getCurrentInstance();

        $instance->canPreview[] = $mime;
        $instance->handlers[$mime] = $handler;

        self::$filePreviewInstance = $instance;
    }

    /**
     * Get the current instance.
     *
     * @return \mykemeynell\FilePreview\FilePreview
     * @throws \Exception
     */
    public static function getCurrentInstance(): FilePreview
    {
        if(empty(self::$filePreviewInstance)) {
            self::$filePreviewInstance = new self;
        }

        return self::$filePreviewInstance;
    }

    /**
     * Create a new preview instance from a file path.
     *
     * @param string $path
     *
     * @return \mykemeynell\FilePreview\FilePreview
     * @throws \Exception
     */
    public static function fromPath(string $path)
    {
        $instance = self::getCurrentInstance();

        $instance->file = new File($path);

        self::$filePreviewInstance = $instance;
        return self::$filePreviewInstance;
    }

    /**
     * Create a new preview instance from a File object.
     *
     * @param \mykemeynell\FilePreview\FileSystem\File $file
     *
     * @return \mykemeynell\FilePreview\FilePreview
     * @throws \Exception
     */
    public static function fromFileObject(File $file)
    {
        $instance = self::getCurrentInstance();

        $instance->file = $file;

        self::$filePreviewInstance = $instance;
        return self::$filePreviewInstance;
    }

    /**
     * Generate the preview.
     *
     * @throws \ImagickException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws \Exception
     */
    public function preview()
    {
        if(empty($this->file)) {
            throw new \Exception("No file has been set.");
            return;
        }

        $mime = $this->file->mime();
        if(! $this->canGeneratePreview()) {
            throw new Exception("Unable to generate preview for file of MIME [{$mime}].");
        }

        if(array_key_exists($mime, $this->handlers)) {
            return call_user_func_array($this->handlers[$mime], [$this->file]);
        }

        if($this->file->isPdf()) {
            return $this->generatePdfPreview();
        }

        if($this->file->isImage()) {
            return $this->generateImagePreview();
        }

        if($this->file->is(ApplicationMimeTypes::$MicrosoftOffice['Word'])) {
            return $this->generateOfficeApplicationWordPreview();
        }

        throw new Exception("Although it is possible to create a preview of the mime type [{$mime}], no method for handling this has been implemented yet.");
    }

    /**
     * Convert an office application to an image preview.
     *
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws \ImagickException
     * @throws \Exception
     */
    private function generateOfficeApplicationWordPreview()
    {
        $temporaryPdfFile = tempnam(sys_get_temp_dir(), 'FilePreview');

        Settings::setPdfRenderer(Settings::PDF_RENDERER_TCPDF,  __DIR__ . '/../vendor/tecnickcom/tcpdf');

        $reader = IOFactory::createReader();
        $phpWord = $reader->load($this->file->getPath());
        $writer = IOFactory::createWriter($phpWord, 'PDF');

        $writer->save($temporaryPdfFile);

        return self::fromPath($temporaryPdfFile)->preview();
    }

    /**
     * Generate an image preview.
     *
     * @return $this
     * @throws \ImagickException
     */
    private function generateImagePreview()
    {
        $imagick = new Imagick($this->file->getPath());
        $imagick->setImageFormat('jpg');

        $this->output['header'] = 'image/jpg';
        $this->output['content'] = $imagick;

        return $this;
    }

    /**
     * Generate a PDF preview.
     *
     * @throws \ImagickException
     */
    private function generatePdfPreview()
    {
        $imagick = new Imagick($this->file->getPath() . '[0]');
        $imagick->setImageFormat('jpg');

        $this->output['header'] = 'image/jpg';
        $this->output['content'] = $imagick;

        return $this;
    }

    /**
     * Output the stream.
     *
     * @throws \Exception
     */
    public function stream()
    {
        if(! $this->readyToStream()) {
            throw new \Exception("Preview is not ready to stream");
        }

        header("Content-Type: " . $this->output['header']);
        echo $this->output['content'];
    }

    /**
     * Test whether the output is ready to be streamed.
     *
     * @return bool
     */
    private function readyToStream(): bool
    {
        return ! empty($this->output['header'])
            && ! empty($this->output['content']);
    }

    /**
     * Test if the file MIME type is allowed to generate file previews.
     *
     * @return bool
     */
    private function canGeneratePreview()
    {
        foreach($this->canPreview as $pattern) {
            $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

            if(preg_match($pattern, $this->file->mime())) {
                return true;
            }
        }

        return false;
    }
}
