<?php
/**
 * Created by PhpStorm.
 * User: www44
 * Date: 2020/2/25
 * Time: 16:45
 */

require_once __DIR__.'/./cpdf/CpdfSDK.php';

use cpdf\CpdfSDK;

function merge(){
    $pdf1 = __DIR__.'/file/merge1.pdf';
    $pdf2 = __DIR__.'/file/merge2.pdf';
    $output_dir = __DIR__;

    $cpdfsdk = new CpdfSDK();

    $pdf_arr = [$pdf1,$pdf2];

    list($rst,$des_path) = $cpdfsdk->pdfMerge($pdf_arr,$output_dir,uniqid('merge_').'.pdf');

    if ($rst){
        echo '<p style="color: deepskyblue;font-size: 25px">success:'.$des_path.'</p>';
    }
}

/**
 * 在最开始添加指定也或者空白页
 * @throws ErrorException
 */
function padPageFirst(){
    $des_pdf = __DIR__.'/file/template.pdf';
    $pad_pdf = __DIR__.'/file/pad-first.pdf';
    $output_dir = __DIR__;

    $cpdfsdk = new CpdfSDK();

    list($rst,$des_path) = $cpdfsdk->addPageBeforeFirstPage($des_pdf,
        $output_dir,
        uniqid('pad_').'.pdf',$pad_pdf);

    if ($rst){
        echo '<p style="color: deepskyblue;font-size: 25px">success:'.$des_path.'</p>';
    }
}

/**
 * 在pdf的指定页后面插入空白或指定pdf
 * @throws ErrorException
 */
function padPageAfter(){
    $des_pdf = __DIR__.'/file/template.pdf';
    $pad_pdf = __DIR__.'/file/pad-first.pdf';
    $output_dir = __DIR__;

    $cpdfsdk = new CpdfSDK();

    list($rst,$des_path) = $cpdfsdk->addPageAfterAppointPage($des_pdf,
        $output_dir,1);//插入空白页

//    list($rst,$des_path) = $cpdfsdk->addPageAfterAppointPage($des_pdf,
//        $output_dir,1,'',$pad_pdf);//插入指定pdf

    if ($rst){
        echo '<p style="color: deepskyblue;font-size: 25px">success:'.$des_path.'</p>';
    }
}

/**
 * 获取pdf总页数
 * @throws ErrorException
 */
function getPdfPageCount(){
    $des_pdf = __DIR__.'/file/template.pdf';
    $cpdfsdk = new CpdfSDK();
    $page_count = $cpdfsdk->getPdfPageCount($des_pdf);

    echo 'page_count:'.$page_count;
}

/**
 * 添加页码
 * @throws ErrorException
 */
function addPageNum(){
    $des_pdf = __DIR__.'/file/template.pdf';
    $output_dir = __DIR__;

    $cpdfsdk = new CpdfSDK();
    list($rst,$des_path) = $cpdfsdk->addPageNumToPdf($des_pdf,$output_dir);

    if ($rst){
        echo '<p style="color: deepskyblue;font-size: 25px">success:'.$des_path.'</p>';
    }
}
