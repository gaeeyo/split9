<?php
/**

�g����

 php split9.php [filename.png] [size]

  filename ���̓t�@�C����
  size 4���̉摜�T�C�Y

9-patch���̕`����s�� layer-drawable ���쐬�B
filename_����.png �� filename.xml ���o�͂���܂��B

�����Ɖ摜�̑Ή��B
   ---------
  | 7  8  9 |
  | 4  5  6 |
  | 1  2  3 |
   ---------

density=1.0f ��z�肵�Ă��܂��B
size ��1.25�{�����Ƃ��ɐ����ł���K�v������܂��B(48,52 ��)

50�Ȃǂ�1.25�{�����Ƃ��ɐ����łȂ��Ȃ鐔�l���g�����ꍇ�A
���������摜��1.25�{�ŕ`�悷��Ƃ��ɕ`��̈悪�d�Ȃ镔����������
���E�ɐ����\������Ă��܂��܂��B

*/
define('DENSITY', "1.0f");


main($argv);

function main($argv) {
    if (count($argv) < 3) {
        echo "php split9.php [filename] [size]";
        die();
    }
    split9($argv[1], $argv[2]);
}

function split9($path, $size) {

    if (!preg_match("/^(.*)(\\.png)$/i", $path, $m)) {
        die("�t�@�C������ .png �ł͂���܂���");
        return ;
    }

    $name = $m[1];
    $ext = $m[2];

    // �摜��ǂݍ���ō����ƕ����擾
    $src = imagecreatefrompng($path);
    $width = imagesx($src);
    $height = imagesy($src);

    $xx = array(0, $size, $width - $size);
    $yy = array(0, $size, $height - $size);
    $ww = array($size, $width - $size * 2, $size);  
    $hh = array($size, $height - $size * 2, $size);

    $names = array(
        array(7, 8, 9),
        array(4, 5, 6),
        array(1, 2, 3),
    );

    for ($y=0; $y<3; $y++) {
        for ($x=0; $x<3; $x++) {
            writeImage($src, $xx[$x], $yy[$y], 
            $ww[$x], $hh[$y], 
            $name.'_'.$names[$y][$x].$ext);
        }
    }

    file_put_contents($name.'.xml', getXml($name, $size, $size));

}

function writeImage($src, $x, $y, $w, $h, $filename) {
    echo $filename."\n";
    $dst = imagecreatetruecolor($w, $h);

    imagealphablending($dst, false);
    imagecopy($dst, $src, 0, 0, $x, $y, $w, $h);

    imagesavealpha($dst, true);
    imagepng($dst, $filename);
    imagedestroy($dst);
}

function getXml($name, $width, $height) {

  $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<layer-list xmlns:android="http://schemas.android.com/apk/res/android" >

    <item>
        <bitmap
            android:gravity="left|top"
            android:src="@drawable/NAME_7" />
    </item>
    <item android:left="WIDTH" android:right="WIDTH">
        <bitmap
            android:gravity="fill_horizontal|top"
            android:src="@drawable/NAME_8" />
    </item>
    <item>
        <bitmap
            android:gravity="right|top"
            android:src="@drawable/NAME_9" />
    </item>
    
    <item android:top="HEIGHT" android:bottom="HEIGHT">
        <bitmap
            android:gravity="left|fill_vertical"
            android:src="@drawable/NAME_4" />
    </item>
    <item android:left="WIDTH" android:right="HEIGHT"
      android:top="HEIGHT" android:bottom="HEIGHT">
        <bitmap
            android:gravity="fill_horizontal|fill_vertical"
            android:src="@drawable/NAME_5" />
    </item>
    <item android:top="HEIGHT" android:bottom="HEIGHT">
        <bitmap
            android:gravity="right|fill_vertical"
            android:src="@drawable/NAME_6" />
    </item>
    
    <item>
        <bitmap
            android:gravity="left|bottom"
            android:src="@drawable/NAME_1" />
    </item>
    <item android:left="WIDTH" android:right="WIDTH">
        <bitmap
            android:gravity="fill_horizontal|bottom"
            android:src="@drawable/NAME_2" />
    </item>
    <item>
        <bitmap
            android:gravity="right|bottom"
            android:src="@drawable/NAME_3" />
    </item>

</layer-list>
XML;

    $xml = str_replace('NAME', basename($name), $xml);
    $xml = str_replace('WIDTH', ($width*DENSITY).'dip', $xml);
    $xml = str_replace('HEIGHT', ($height*DENSITY).'dip', $xml);
    return $xml; 
}
