<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/14
 * Time: 下午2:52
 * URL: https://github.com/samundra/remotefiledownloader
 */

class RemoteFileDownloader {
    protected $_collections = array();
    protected $prefix;
    protected $suffix;
    protected $_current_queue;
    protected $preserve_filename = false;
    protected $create;
    protected $basedir;
    protected $_dirname;
    protected $_error;
    protected $_files;
    /**
     * Initialises instance
     *
     * @param string $prefix Prefix that is appended to the target filename
     * @param string $suffix Suffix that is appended to the target filename
     * @param boolean $preserve_filename If false, filename is computed
     * dynamically by the system
     */
    public function __construct($prefix = '', $suffix = '', $preserve_filename = false) {
        $this->basedir = __DIR__;
        if (!empty($prefix)) {
            $this->prefix = $prefix;
        }
        if (!empty($suffix)) {
            $this->suffix = $suffix;
        }
        if ($preserve_filename) {
            $this->preserve_filename = $preserve_filename;
        }
    }
    /**
     * Sources of the files to download. Currently this function accepts string
     * and array as source. Object is not supported. If object is passed then function
     * throws the InvalidSourceException which user has to manually catch.
     *
     * @param mixed $collections <p>can be array or string</p>
     * @throws InvalidSourceException <p>Exception thrown when source is not supported</p>
     */
    public function set_sources($collections) {
        if (is_object($collections)) {
            throw new InvalidSourceException('Object collection is not supported', 3);
        }
        if (is_array($collections)) {
            $this->_collections = $collections;
        } else {
            $this->_collections = array($collections);
        }
    }
    /**
     * Initiate the actual download
     * @param boolean $is_new <p>Set this to new when you are newly creating
     * a download collections. If you want the downloaded images to be appended
     * with new collection list, set this to false.</p>
     *
     * @see filewriter
     */
    public function init($is_new = true) {
        // Empty the array for the first time
        if ($is_new === true) {
            $this->_files = array();
        }
        foreach ($this->_collections as $item) {
            $file = $item;
            $this->_current_queue = pathinfo($file);
            ob_clean();
            ob_start(array('RemoteFileDownloader', 'filewriter'));
            $p = file_get_contents($file);
            //如果不是图片
//            if ($http_response_header['Content-Type'] == 'text/plain'){
//
//            }
            // The source url is invalid so, couldn't download the image
            if ($p == false) {
                ob_end_flush();
                $this->_error[] = $file;
                continue;
            }
            echo $p;
            ob_end_flush();
        }
        return $this->_files;
    }
    /**
     * Writes output buffer to the file.
     *
     * @param type $buffer Output buffer which is in the memory
     * @see init
     */
    public function filewriter($buffer) {
        if ($this->preserve_filename == true) {
            $filename = '';
            if ($this->prefix) {
                $filename .= $this->prefix;
            }
            $filename .= mt_rand();
            if ($this->suffix) {
                $filename .= $this->suffix;
            }
            $ext = isset($this->_current_queue['extension']) ? $this->_current_queue['extension'] : 'jpg';
            $filename .= '.' . $ext;
            //$filename = $this->prefix.'_'.$t.'_'. $this->suffix . '.'.$this->_current_queue['extension'];
        } else {
            $fn = $this->_current_queue['filename'];
            $ext = isset($this->_current_queue['extension']) ? $this->_current_queue['extension'] : 'jpg';
            $filename = md5($fn . me_rand() . rand(5, 15)) . '.' . $ext;
            //$filename = $this->_current_queue['basename'];
        }
        $this->_files[] = $filename;
        $filepath = $this->_dirname . $filename;
        file_put_contents($filepath, $buffer);
        //如果不是图片
//        if (exif_imagetype($filepath) === false){
//            @unlink($filepath);
//        }
//        else $this->_files[] = $filename;
        // Just give some time to other PHP files as well
        // Sleep for 100 microsecond
        usleep(100);
    }
    /**
     * <p>Sets the destination path, if second parameter is false then destination
     * path is not created.</p>
     * @param string $dirname <p>Destination path</p>
     * @param int $mode File permission mode
     * @param boolean $recursive <p>If true, the destination directory is created, if
     * it doesn't exists. </p>
     */
    public function set_destination($path, $mode = 0755, $recursive = false) {
        $this->create = $recursive;
        if (file_exists($path) === false && $recursive === false) {
            throw new InvalidDestinationException('Remotefiledownloader::Destination does not exists', 2);
        }
        //$path = $this->basedir.'/'.$dirname;
        if ($recursive === true) {
            if (!file_exists($path)) {
                if (!mkdir($path, $mode, true)) {
                    throw new RemoteFileDownloaderException('Remotefiledownloader::Couldn\'t create directory.', 3);
                }
            }
        }
        $this->_dirname = $path;
    }
}
/**
 * <b>InvalidSourceException</b> thrown when the destination is invalid.
 * @link http://php.net/manual/en/class.exception.php
 * @see set_destination()
 */
class InvalidDestinationException extends RemoteFileDownloaderException {

}
/**
 * <b>InvalidSourceException</b> thrown when source is invalid
 * @link http://php.net/manual/en/class.exception.php
 */
class InvalidSourceException extends RemoteFileDownloaderException {

}
/**
 * <b>RemoteFileDownloaderException</b> thrown when downloader meets any exception
 * @link http://php.net/manual/en/class.exception.php
 */
class RemoteFileDownloaderException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}