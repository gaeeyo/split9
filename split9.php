<?php
/**

使い方

 php split9.php [filename.png] [size]

  filename 入力ファイル名
  size 4隅の画像サイズ

9-patch風の描画を行う layer-drawable を作成。
filename_数字.png と filename.xml が出力されます。

ファイル名の数字と画像の対応。
   ---------
  | 7  8  9 |
  | 4  5  6 |
  | 1  2  3 |
   ---------

size の指定は実行時の density を考慮する必要があります。
たとえば、50 を指定すると ldpi(0.75倍) の環境では 37.5 となり
少数になってしまうため描画領域が重複し、画像のつなぎ目が見えてしまいます。

このため size には 0.75倍、1.25倍、1.5倍 にしても整数になる数値を指定します。
(たとえば 48, 52, 56 等)

出力された Drawable(filename.xml) は、4隅の 7, 9, 1, 3 は伸縮されずに表示され
その他の部分は引き延ばして表示されます。
5 の部分がベタ塗りや単純な模様の場合、ColorDrawable や
tileMode を使った BitmapDrawable に変えることで、メモリの節約や
描画速度の改善ができるかもしれません。

*/

mb_internal_encoding('UTF-8');
mb_http_output('CP932');
ob_start('mb_output_handler');

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
        die("ファイル名が .png ではありません");
        return ;
    }

    $name = $m[1];
    $ext = $m[2];

    // 画像を読み込んで高さと幅を取得
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
