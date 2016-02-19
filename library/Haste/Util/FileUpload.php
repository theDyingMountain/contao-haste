<?php

namespace Haste\Util;

class FileUpload extends \FileUpload
{

    /**
     * @var bool
     */
    protected $doNotOverwrite = false;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @var int
     */
    protected $maxFileSize;

    /**
     * @var int
     */
    protected $imageWidth;

    /**
     * @var int
     */
    protected $imageHeight;

    /**
     * @var int
     */
    protected $gdMaxImgWidth;

    /**
     * @var int
     */
    protected $gdMaxImgHeight;

    /**
     * Temporary store target from uploadTo() to make it available to getFilesFromGlobal()
     * @var string
     */
    private $target;

    public function __construct($name)
    {
        parent::__construct();

        $this->setName($name);

        $this->extensions     = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['uploadTypes']));
        $this->maxFileSize    = $GLOBALS['TL_CONFIG']['maxFileSize'];
        $this->imageWidth     = $GLOBALS['TL_CONFIG']['imageWidth'];
        $this->imageHeight    = $GLOBALS['TL_CONFIG']['imageHeight'];
        $this->gdMaxImgWidth  = $GLOBALS['TL_CONFIG']['gdMaxImgWidth'];
        $this->gdMaxImgHeight = $GLOBALS['TL_CONFIG']['gdMaxImgHeight'];
    }

    public function getName()
    {
        return $this->strName;
    }

    /**
     * @return bool
     */
    public function doNotOverwrite()
    {
        return $this->doNotOverwrite;
    }

    /**
     * @param bool $doNotOverwrite
     *
     * @return $this
     */
    public function setDoNotOverwrite($doNotOverwrite)
    {
        $this->doNotOverwrite = (bool) $doNotOverwrite;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param array $extensions
     *
     * @return $this
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = array_map('strtolower', $extensions);

        return $this;
    }

    /**
     * @param string $extension
     *
     * @return $this
     */
    public function addExtension($extension)
    {
        $this->extensions[] = strtolower($extension);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     *
     * @return $this
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageWidth()
    {
        return $this->imageWidth;
    }

    /**
     * @param int $imageWidth
     *
     * @return $this
     */
    public function setImageWidth($imageWidth)
    {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageHeight()
    {
        return $this->imageHeight;
    }

    /**
     * @param int $imageHeight
     *
     * @return $this
     */
    public function setImageHeight($imageHeight)
    {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getGdMaxImgWidth()
    {
        return $this->gdMaxImgWidth;
    }

    /**
     * @param int $gdMaxImgWidth
     *
     * @return $this
     */
    public function setGdMaxImgWidth($gdMaxImgWidth)
    {
        $this->gdMaxImgWidth = $gdMaxImgWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getGdMaxImgHeight()
    {
        return $this->gdMaxImgHeight;
    }

    /**
     * @param int $gdMaxImgHeight
     *
     * @return $this
     */
    public function setGdMaxImgHeight($gdMaxImgHeight)
    {
        $this->gdMaxImgHeight = $gdMaxImgHeight;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function uploadTo($strTarget)
    {
        $this->target = $strTarget;

        $uploadTypes = $GLOBALS['TL_CONFIG']['uploadTypes'];
        $GLOBALS['TL_CONFIG']['uploadTypes'] = implode(',', $this->extensions);

        $result = parent::uploadTo($strTarget);

        $GLOBALS['TL_CONFIG']['uploadTypes'] = $uploadTypes;

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getFilesFromGlobal()
    {
        $files = parent::getFilesFromGlobal();

        if ($this->doNotOverwrite) {
            foreach ($files as $k => $file) {
                $files[$k]['name'] = static::getFileName($file['name'], $this->target);
            }
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    protected function getMaximumUploadSize()
    {
        $maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];
        $GLOBALS['TL_CONFIG']['maxFileSize'] = $this->maxFileSize;

        $return = parent::getMaximumUploadSize();

        $GLOBALS['TL_CONFIG']['maxFileSize'] = $maxFileSize;

        return $return;
    }

    /**
     * @inheritdoc
     */
    protected function resizeUploadedImage($strImage)
    {
        $imageWidth     = $GLOBALS['TL_CONFIG']['imageWidth'];
        $imageHeight    = $GLOBALS['TL_CONFIG']['imageHeight'];
        $gdMaxImgWidth  = $GLOBALS['TL_CONFIG']['gdMaxImgWidth'];
        $gdMaxImgHeight = $GLOBALS['TL_CONFIG']['gdMaxImgHeight'];

        $GLOBALS['TL_CONFIG']['imageWidth']     = $this->imageWidth;
        $GLOBALS['TL_CONFIG']['imageHeight']    = $this->imageHeight;
        $GLOBALS['TL_CONFIG']['gdMaxImgWidth']  = $this->gdMaxImgWidth;
        $GLOBALS['TL_CONFIG']['gdMaxImgHeight'] = $this->gdMaxImgHeight;

        $return = parent::resizeUploadedImage($strImage);

        $GLOBALS['TL_CONFIG']['imageWidth']     = $imageWidth;
        $GLOBALS['TL_CONFIG']['imageHeight']    = $imageHeight;
        $GLOBALS['TL_CONFIG']['gdMaxImgWidth']  = $gdMaxImgWidth;
        $GLOBALS['TL_CONFIG']['gdMaxImgHeight'] = $gdMaxImgHeight;

        return $return;
    }

    /**
     * Get the new file name if it already exists in the folder
     * @param string
     * @param string
     * @return string
     */
    public static function getFileName($strFile, $strFolder)
    {
        if (!file_exists(TL_ROOT . '/' . $strFolder . '/' . $strFile)) {
            return $strFile;
        }

        $offset = 1;
        $pathinfo = pathinfo($strFile);
        $name = $pathinfo['filename'];

        $arrAll = scan(TL_ROOT . '/' . $strFolder);
        $arrFiles = preg_grep('/^' . preg_quote($name, '/') . '.*\.' . preg_quote($pathinfo['extension'], '/') . '/', $arrAll);

        foreach ($arrFiles as $file) {
            if (preg_match('/__[0-9]+\.' . preg_quote($pathinfo['extension'], '/') . '$/', $file)) {
                $file = str_replace('.' . $pathinfo['extension'], '', $file);
                $intValue = (int) substr($file, (strrpos($file, '_') + 1));

                $offset = max($offset, $intValue);
            }
        }

        return str_replace($name, $name . '__' . ++$offset, $strFile);
    }
}