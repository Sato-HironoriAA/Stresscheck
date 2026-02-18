<?php

require_once 'tcpdf/tcpdf.php';
require_once 'fpdi/src/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

class PDF_Japanese_Protection extends Fpdi
{
    // フォントサイズに応じたベースライン補正値を保持する
    private $baselineFix = 0;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        // デフォルトフォント設定
        $this->SetFont('ipaexm', '', 12);
        $this->setFontSubsetting(false);
        $this->setLanguageArray(['a_meta_charset' => 'UTF-8']);

        // 日本語文字化け対策
        $this->setFontSubsetting(false);
        $this->setLanguageArray(['a_meta_charset' => 'UTF-8']);

        // ヘッダー・フッター無効化
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);

        // 余白設定
        $this->SetMargins(20, 20, 20);
    }

    // setFont をオーバーライドして CID フォントを強制し、フォントサイズに応じた補正値を計算する
    public function setFont($family, $style = '', $size = null, $fontfile = '', $subset = 'default', $out = true)
    {
        parent::setFont($family, $style, $size, $fontfile, false, $out);

        $fontSize   = $this->getFontSize();
        $fontFamily = strtolower($this->FontFamily);

        $fixTableMincho = [
            9  => -1.52,
            10 => -1.68,
            11 => -1.86,
            12 => -2.04,
            13 => -2.20,
            14 => -2.36,
            16 => -2.72,
            20 => -3.40,
        ];

        $fixTableGothic = array_map(fn($v) => $v + 0.30, $fixTableMincho);

        $table = ($fontFamily === 'ipaexg') ? $fixTableGothic : $fixTableMincho;

        $this->baselineFix = $this->interpolateFix($fontSize, $table);

        // baselineFix が正になったら 0 に固定（下ズレ防止）
        if ($this->baselineFix > 0) {
            $this->baselineFix = 0;
        }

        // ★ 最終調整：全行を 0.60mm 上に持ち上げる
        $this->baselineFix -= 0.60;

        return $this;
    }

    // フォントサイズがテーブルに無い場合は線形補間で補正値を求める
    private function interpolateFix($fontSize, $table)
    {
        $sizes = array_keys($table);
        sort($sizes);

        if ($fontSize <= $sizes[0]) {
            return $table[$sizes[0]];
        }

        if ($fontSize >= end($sizes)) {
            return $table[end($sizes)];
        }

        for ($i = 0; $i < count($sizes) - 1; $i++) {
            $s1 = $sizes[$i];
            $s2 = $sizes[$i + 1];

            if ($fontSize >= $s1 && $fontSize <= $s2) {
                $v1 = $table[$s1];
                $v2 = $table[$s2];
                $ratio = ($fontSize - $s1) / ($s2 - $s1);
                return $v1 + ($v2 - $v1) * $ratio;
            }
        }

        return 0;
    }
}