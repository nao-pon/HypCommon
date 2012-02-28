<?php
if (! function_exists('XC_CLASS_EXISTS')) {
	require dirname(dirname(__FILE__)) . '/XC_CLASS_EXISTS.inc.php';
}

/* �ϊ���̊G�����^�C�v */
define('MPC_TO_FOMA'    , 'FOMA');
define('MPC_TO_EZWEB'   , 'EZWEB');
define('MPC_TO_SOFTBANK', 'SOFTBANK');
/* �ϊ��O�̊G�����^�C�v */
define('MPC_FROM_FOMA'    , 'FOMA');
define('MPC_FROM_EZWEB'   , 'EZWEB');
define('MPC_FROM_SOFTBANK', 'SOFTBANK');
/* �ϊ��O�̊G�����̌n */
define('MPC_FROM_OPTION_RAW' , 'RAW'); // �o�C�i���R�[�h
define('MPC_FROM_OPTION_WEB' , 'WEB'); // Web���̓R�[�h
define('MPC_FROM_OPTION_IMG' , 'IMG'); // �摜
define('MPC_FROM_OPTION_MODKTAI' , 'MODKTAI'); // mod_ktai
/* �ϊ��O�̕�����̕����R�[�h */
define('MPC_FROM_CHARSET_SJIS', 'SJIS');
define('MPC_FROM_CHARSET_UTF8', 'UTF-8');
/* �ϊ���̕�����̕����R�[�h */
define('MPC_TO_CHARSET_SJIS', 'SJIS');
define('MPC_TO_CHARSET_UTF8', 'UTF-8');

// {{{ class MobilePictogramConverter
/**
* �G�����ϊ��N���X
*
* <pre>
* MobilePictogramConverter  Factory Method �N���X
*
* MPC_Common      �S�ẴL�����A�ɑ΂��ċ��ʂ���@�\��������x�[�X�N���X
* |
* +-MPC_FOMA      FOMA�G�������瑼�̊G�����ɕϊ�����ۂɃx�[�X�N���X
* |               MobilePictogramConverter::factory�̑�������MPC_FROM_FOMA���w�肵���ꍇ�ɌĂяo����܂��B
* |
* +-MPC_EZweb     EZweb�G�������瑼�̊G�����ɕϊ�����ۂ̃x�[�X�N���X
* |               MobilePictogramConverter::factory�̑�������MPC_FROM_EZWEB���w�肵���ꍇ�ɌĂяo����܂��B
* |
* +-MPC_SoftBank  SoftBank�G�������瑼�̊G�����ɕϊ�����ۂ̃x�[�X�N���X
*                 MobilePictogramConverter::factory�̑�������MPC_FROM_SOFTBANK���w�肵���ꍇ�ɌĂяo����܂��B
* </pre>
*
* @author   ryster <ryster@php-develop.org>
* @license  http://www.opensource.org/licenses/mit-license.php The MIT License
* @version  Release: 1.2.0
* @link     http://php-develop.org/MobilePictogramConverter/
*/
class MobilePictogramConverter
{
    /**
    * �^�C�v�ɍ��킹�āA��p�̃N���X�I�u�W�F�N�g�𐶐�
    *
    * ��.
    * <code>
    * require_once("MobilePictogramConverter.php");
    *
    * $mpc =& MobilePictogramConverter::factory($str, MPC_FROM_FOMA, MPC_FROM_CHARSET_SJIS);
    * if (is_object($mpc) == false) {
    *     die($mpc);
    * }
    * </code>
    *
    * @param string  $str     �ϊ��O������
    * @param string  $carrier $str�̊G�����L�����A (MPC_FROM_FOMA, MPC_FROM_EZWEB, MPC_FROM_SOFTBANK)
    * @param string  $charset �����R�[�h         (MPC_FROM_CHARSET_SJIS, MPC_FROM_CHARSET_UTF8)
    * @param string  $type    $str�̊G�����^�C�v  (MPC_FROM_OPTION_RAW, MPC_FROM_OPTION_WEB, MPC_FROM_OPTION_IMG)
    * @return mixed
    */
    function &factory($str, $carrier, $charset, $type = MPC_FROM_OPTION_RAW)
    {
        $filepath = dirname(__FILE__).'/Carrier/'.strtolower($carrier).'.php';
        if (file_exists($filepath) == false) {
            $error = 'The file doesn\'t exist.';
            return $error;
        }

        require_once($filepath);
        $classname = 'MPC_'.$carrier;

        if (XC_CLASS_EXISTS($classname) == false) {
            $error = 'The class doesn\'t exist.';
            return $error;
        }

        $mpc =& new $classname;
        $mpc->setFromCharset($charset);
        $mpc->setString($str);
        $mpc->setFrom(strtoupper($carrier));
        $mpc->setStringType($type);

        return $mpc;
    }

    function &factory_common($charset = MPC_FROM_CHARSET_SJIS)
    {
        static $mpc = NULL;

        if ($mpc) return $mpc;

        $filepath = dirname(__FILE__).'/Carrier/common.php';
        if (file_exists($filepath) == false) {
            $error = 'The file doesn\'t exist.';
            return $error;
        }

        require_once($filepath);

        $mpc =& new MPC_Common();
        $mpc->setFromCharset($charset);

        if (is_object($mpc) == false) {
            die($mpc);
        }

        return $mpc;
    }
}
// }}}

// file_get_contents -- Reads entire file into a string
// (PHP 4 >= 4.3.0, PHP 5)
if (! function_exists('file_get_contents')) {
function file_get_contents($filename, $incpath = false, $resource_context = null, $offset = -1, $maxlen = -1)
{
	if (false === $fh = fopen($filename, 'rb', $incpath)) {
		trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
		return false;
	}

	if ($offset > -1 && $maxlen > -1) {
		$readsize = $offset + $maxlen;
	} else {
		$readsize = -1;
	}

	clearstatcache();
	$fsize = @filesize($filename);
	if ($readsize > -1 && $fsize > $readsize) {
		$data = fread($fh, $readsize);
		if ($offset > 0) {
			$data = substr($data, $offset);
		}
	} else {
		if ($fsize) {
			$data = fread($fh, $fsize);
		} else {
			$data = '';
			while (!feof($fh)) {
				$data .= fread($fh, 8192);
			}
		}
	}

	fclose($fh);
	return $data;
}
}

/**
 * Replace file_put_contents()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.file_put_contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @internal    resource_context is not supported
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
// file_put_contents
// (PHP 5)
if (! function_exists('file_put_contents')) {
function file_put_contents($filename, $content, $flags = null, $resource_context = null)
{
    // If $content is an array, convert it to a string
    if (is_array($content)) {
        $content = implode('', $content);
    }

    // If we don't have a string, throw an error
    if (!is_scalar($content)) {
        user_error('file_put_contents() The 2nd parameter should be either a string or an array',
            E_USER_WARNING);
        return false;
    }

    // Get the length of data to write
    $length = strlen($content);

    // Check what mode we are using
    $mode = ($flags & FILE_APPEND) ?
                'a' :
                'wb';

    // Check if we're using the include path
    $use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
                true :
                false;

    // Open the file for writing
    if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
        user_error('file_put_contents() failed to open stream: Permission denied',
            E_USER_WARNING);
        return false;
    }

    // Attempt to get an exclusive lock
    $use_lock = ($flags & LOCK_EX) ? true : false ;
    if ($use_lock === true) {
        if (!flock($fh, LOCK_EX)) {
            return false;
        }
    }

    // Write to the file
    $bytes = 0;
    if (($bytes = @fwrite($fh, $content)) === false) {
        $errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s',
                        $length,
                        $filename);
        user_error($errormsg, E_USER_WARNING);
        return false;
    }

    // Close the handle
    @fclose($fh);

    // Check all the data was written
    if ($bytes != $length) {
        $errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
                        $bytes,
                        $length);
        user_error($errormsg, E_USER_WARNING);
        return false;
    }

    // Return length
    return $bytes;
}
}
?>