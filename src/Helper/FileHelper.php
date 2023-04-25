<?php


namespace Fuliang\PhpTools\Helper;


use Fuliang\PhpTools\Constants\ErrorCode;
use Fuliang\PhpTools\Constants\ErrorMsg;
use Fuliang\PhpTools\Exceptions\ToolsException;

class FileHelper
{

    /**
     * 复制文件到指定目录.
     *
     * @param string $source 源文件
     * @param string $dest 目的文件
     * @return bool
     */
    public function fileCopy(string $source, string $dest): bool
    {
        if (!file_exists($source)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::FILE_NOT_EXIST);
        }
        $basedir = dirname($dest);
        if (!is_dir($basedir) && !mkdir($basedir, 0755, true)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::CREATE_DIR_FAILED);
        }
        return copy($source, $dest);
    }

    /**
     * 将图片转换成base64编码
     *
     * @param string $imagePath 图片路径
     * @return string
     */
    public function imageBase64Encode(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::FILE_NOT_EXIST);
        }
        $imageInfo = getimagesize($imagePath);
        $imageData = fread(fopen($imagePath, 'r'), filesize($imagePath));

        return 'data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode($imageData));
    }

    /**
     * 生成缩略图.
     *
     * @param string $imagePath
     * @param float $scale 默认缩放比例 默认0.5原图的一半
     * @param int $dst_w 最大宽度
     * @param int $dst_h 最大高度
     * @param string $dir 缩略图保存路径
     * @param string $pre 默认缩略图前缀thumb_
     * @param bool $delSource 是否删除源文件标志(谨慎使用)
     * @return string 最终保存路径及文件名
     */
    public function mkThumbImage(string $imagePath, string $dir, float $scale = 0.5, ?int $dst_w = null, ?int $dst_h = null, string $pre = 'thumb_', bool $delSource = false): string
    {
        $fileInfo = $this->getImageInfo($imagePath);
        $src_w = $fileInfo['width'];
        $src_h = $fileInfo['height'];
        //如果指定最大宽度和高度，按照等比例缩放进行处理
        if (is_numeric($dst_w) && is_numeric($dst_h)) {
            $ratio_orig = $src_w / $src_h;
            if ($dst_w / $dst_h > $ratio_orig) {
                $dst_w = $dst_h * $ratio_orig;
            } else {
                $dst_h = $dst_w / $ratio_orig;
            }
        } else {
            $dst_w = ceil($src_w * $scale);
            $dst_h = ceil($src_h * $scale);
        }
        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        $src_image = $fileInfo['createFun']($imagePath);
        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        if ($dir && !file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $randNum = substr(sha1(uniqid() . time()), 0, 20);
        $dstName = "{$pre}{$randNum}" . $fileInfo['ext'];
        $destination = $dir ? $dir . '/' . $dstName : $dstName;
        $fileInfo['outFun']($dst_image, $destination);
        imagedestroy($src_image);
        imagedestroy($dst_image);
        if ($delSource) {
            @unlink($imagePath);
        }

        return $destination;
    }

    /**
     * 获取图片信息.
     *
     * @param string $imagePath 图片地址
     * @return array
     * @throws ToolsException
     */
    public function getImageInfo(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::FILE_NOT_EXIST);
        }
        if (!$info = getimagesize($imagePath)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::FILE_ERROR);
        }

        $fileInfo['width'] = $info[0]; // 宽度
        $fileInfo['height'] = $info[1]; // 高度
        $fileInfo['size'] = $this->getFilesize($imagePath) / 1000; // 大小，单位:kb
        $mime = image_type_to_mime_type($info[2]); // 图片类型 image/jpeg
        $fileInfo['createFun'] = str_replace('/', 'createfrom', $mime); // 生成缩略图时对应的方法
        $fileInfo['outFun'] = str_replace('/', '', $mime); // 生成缩略图时对应的输出方法
        $fileInfo['ext'] = strtolower(image_type_to_extension($info[2])); // 后缀名

        return $fileInfo;
    }

    /**
     * 获取文件大小,以kb为单位.
     *
     * @param string $filePath 文件路径
     * @return float
     */
    public function getFilesize(string $filePath): float
    {
        return ceil(filesize($filePath));
    }

    /**
     * 递归移动文件及文件夹.
     *
     * @param string $source 源目录或源文件
     * @param string $target 目的目录或目的文件
     * @return bool
     */
    public function moveFile(string $source, string $target): bool
    {
        if (!file_exists($source)) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::FILE_NOT_EXIST);
        }

        // 如果要移动文件
        if ('file' == filetype($source)) {
            $basedir = dirname($target);
            if (!is_dir($basedir)) {
                mkdir($basedir, 0755, true);
            } //目标目录不存在时给它创建目录
            copy($source, $target);
            unlink($source);
        } else { // 如果要移动目录
            if (!file_exists($target)) {
                mkdir($target, 0755, true);
            } //目标目录不存在时就创建

            $files = []; //存放文件
            $dirs = []; //存放目录
            $fh = opendir($source);

            if (false != $fh) {
                while ($row = readdir($fh)) {
                    $src_file = $source . '/' . $row; //每个源文件
                    if ('.' != $row && '..' != $row) {
                        if (!is_dir($src_file)) {
                            $files[] = $row;
                        } else {
                            $dirs[] = $row;
                        }
                    }
                }
                closedir($fh);
            }

            foreach ($files as $v) {
                copy($source . '/' . $v, $target . '/' . $v);
                unlink($source . '/' . $v);
            }

            if (count($dirs)) {
                foreach ($dirs as $v) {
                    $this->moveFile($source . '/' . $v, $target . '/' . $v);
                }
            }
        }

        return true;
    }

    /**
     * 删除指定文件夹.
     * @param string $path
     * @param bool $delDir
     * @return bool
     */
    public function delDirAndFile(string $path, bool $delDir = false): bool
    {
        if ($delDir && !is_dir($path)) {
            return true;
        }

        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ('.' != $item && '..' != $item) {
                    is_dir("{$path}/{$item}") ? $this->delDirAndFile("{$path}/{$item}", $delDir) : unlink("{$path}/{$item}");
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            }

            return false;
        }
    }
}