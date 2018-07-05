<?php

/**
 * Created by PhpStorm.
 * User: Oli
 * Date: 04/07/2018
 * Time: 12:12
 */
Class Extension
{

    /**
     *
     */
    const TEMP_DIRECTORY = 'TEMP-DIR-FOLDER';
    /**
     * @var
     */
    private $file;

    /**
     * @var
     */
    private $folderName;
    /**
     * @var
     */
    private $destination;

    /**
     * @var
     */
    private $tempDirectory;

    /**
     * @var
     */
    private $action;

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFolderName()
    {
        return $this->folderName;
    }

    /**
     * @param mixed $folderName
     */
    public function setFolderName($folderName)
    {
        $this->folderName = $folderName;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * @param mixed $tempDirectory
     */
    public function setTempDirectory($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $actions
     */
    public function setAction($action)
    {
        $this->action = $action;
    }


    /**
     * @param $argv
     */
    public function run($argv)
    {

        $checkParam = $this->checkParameter($argv);
        if ($checkParam['status'] === 'failed') {
            die($checkParam['message']);
        }
        $this->setParameters($argv, $checkParam);

        if ($this->getAction() !== 'nozip') {
            $this->unzipFile();
        } else {
            $this->copyToTempLocation();
        }

        $this->moveFiles($this->getTempDirectory() . DIRECTORY_SEPARATOR . $this->getFolderName());
        $this->removeTempDirectory(self::TEMP_DIRECTORY);
        echo "COMPLETE\n";


    }

    /**
     * @param $argv
     * @param $paramInfo
     */
    private function setParameters($argv, $paramInfo)
    {

        // has parameter but missing destination
        if ($argv[2] === '--' . $paramInfo['action']) {
            $dst = __DIR__;
        } else {
            $dst = (isset($argv[2]) ? $argv[2] : __DIR__);
        }

        if ($paramInfo['action'] !== '') {
            $this->setAction($paramInfo['action']);
        } else {
            $this->setAction('');
        }

        $this->setFile($argv[1]);
        $this->setTempDirectory(self::TEMP_DIRECTORY);
        $this->setDestination($dst);

    }

    /**
     * @param $params
     * @return array
     */
    private function checkParameter($params)
    {
        $response = array(
            'status' => 'success',
            'message' => '',
            'action' => '',
        );
        $actions = array('--nozip', '--straight', '--help');
        $requestedAction = '';
        foreach ($params as $param) {
            if (in_array(trim($param), $actions)) {
                $requestedAction = trim(str_replace('--', '', $param));
                if (trim($param) === '--help') {
                    $response['status'] = 'failed';
                    $response['message'] = $this->help();
                    return $response;
                }
            }
        }
        if ($requestedAction !== '') {
            $response['action'] = $requestedAction;
        }

        if (!isset($params[1])) {
            $response['status'] = 'failed';
            $response['message'] = "ERROR --- Source not set.\n";
        }

        return $response;
    }


    /**
     * @return bool
     */
    private function unzipFile()
    {

        $zip = new ZipArchive;
        if ($zip->open($this->getFile()) === TRUE) {


            if (is_dir($this->getTempDirectory())) {
                die("ERROR --- The temporary directory (" . $this->getTempDirectory() . ") already exists. Remove this folder and run again.\n");
            }

            if ($this->getAction() === 'straight') {
                $fileInfo = pathinfo($this->getFile());
                $filename = $fileInfo['filename'];
                mkdir($this->getTempDirectory() . DIRECTORY_SEPARATOR . $filename, 0777, true);
                $this->setFolderName($filename);
                $zip->extractTo($this->getTempDirectory() . DIRECTORY_SEPARATOR . $filename);
            } else {
                /* TODO: an extra check here to confirm that the folder being set is the correct one
                eg, if the zipped folder have a documents folder in there
                */
                $this->setFolderName($zip->getNameIndex(0));
                $zip->extractTo($this->getTempDirectory());
            }

            $zip->close();

            return true;
        } else {
            die("ERROR --- This file cannot be unzipped\n");
        }
    }

    /**
     *
     */
    private function copyToTempLocation()
    {
        if (is_dir($this->getTempDirectory())) {
            die("ERROR --- The temporary directory (" . $this->getTempDirectory() . ") already exists. Remove this folder and run again.\n");
        }
        $filename = basename($this->getFile());
        mkdir($this->getTempDirectory());
        copy($this->getFile(), $this->getTempDirectory() . DIRECTORY_SEPARATOR . $filename);
    }


    /**
     * @param $src
     * @param bool $dest
     */
    private function moveFiles($src, $dest = false)
    {

        if (!$dest) {
            $dest = $this->getDestination();
        }

        $stringsToIgnore = array('.DS_Store', '.', '..', '__MACOSX');


        foreach (scandir($src) as $file) {

            $srcfile = rtrim($src, '/') . '/' . $file;
            $destfile = rtrim($dest, '/') . '/' . $file;

            if (!in_array($file, $stringsToIgnore)) {
                if (is_dir($srcfile)) {
                    if (!file_exists($destfile)) {
                        mkdir($destfile);
                    }
                    $this->moveFiles($srcfile, $destfile);
                } else {
                    if (file_exists($destfile)) {
                        echo "NOTICE --- " . $destfile . " already exists. This file will be skipped\n";
                    } else {
                        copy($srcfile, $destfile);
                    }
                }

            }


        }

    }


    /**
     * @param $dir
     */
    private function removeTempDirectory($dir)
    {

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->removeTempDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }


    }


    /**
     * @return string
     */
    private function help()
    {
        $result = "\n\n"
            . "These are the parameters available for use:"
            . "\n\n"
            . "1st - The extension you want to add"
            . "\n"
            . "2nd - The destination of your Magento root. If none is selected, / will be used"
            . "\n\n"
            . "You can also use these built in actions anywhere in CLI (you cannot use multiple actions):"
            . "\n"
            . "     --help          Shows this help page"
            . "\n"
            . "     --nozip         Use this if the extension is a folder, not a zipped file"
            . "\n"
            . "     --straight      Use this if your folder / zip file is not in a contained folder. "
            . "\n"
            . "                     E.g. when unzipping the file, does it have a folder housing the files, or are all the files housed."
            . "\n\n\n";
        return $result;
    }

}

$extension = new Extension();
$extension->run($argv);
