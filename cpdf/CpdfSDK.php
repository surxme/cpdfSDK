<?php
/**
 * Created by PhpStorm.
 * User: black box
 * Date: 2019/9/20
 * Time: 18:40
 */

namespace cpdf;

use ErrorException;

//在线文档地址：https://www.coherentpdf.com/cpdfmanual.pdf
//github：https://github.com/coherentgraphics/cpdf-binaries
//pdf工具集 pdf合并、加页码、指定页前后插入、加密、分割、添加空白页或指定文件页
class CpdfSDK
{
    //SDK操作的pdf全部传服务器上的绝对路径 因为有时候可能遇到路径比较特殊的，没法做统一的处理
    private $_cpdf_executable_file_path = '';

    /**
     * CpdfSDK constructor.
     * @throws ErrorException
     */
    public function __construct()
    {
        $this->cpdfIsExecutable();
    }

    /**
     * 合并pdf
     * @param array $pdf_path_arr []  合并pdf文件的绝对路径数组
     * @param string $output_path 文书合并后输出的文件目录地址
     * @param string $file_name 文件合并后输出的文件名 如果没有传就随机生成
     * @return array 成功true 失败false
     * @throws ErrorException
     */
    public function pdfMerge($pdf_path_arr,$output_path,$file_name = ''){
        //command line example:cpdf -merge 1.pdf 2.pdf -o all.pdf
        if(!is_array($pdf_path_arr) || empty($pdf_path_arr)){
            throw new ErrorException('合并文件不能为空');
        }

        //判断目录是否存在 不存在创建
        $this->makeDir($output_path);

        $shell = $this->_cpdf_executable_file_path.' -merge ';
        foreach ($pdf_path_arr as $pdf_path){
            if($this->getExtFromPath($pdf_path) != 'pdf' || !file_exists($pdf_path)){
                throw new ErrorException('合并文件不存在');
            }
            $shell .= $pdf_path.' ';
        }

        if (!$file_name){
            $file_name = $this->genRandFileName();
        }

        $des_file_path = $output_path.'/'.$file_name;

        $shell = $shell.' -o '.$des_file_path.' 2>&1';

        exec($shell,$out_put,$return_val);

        $exec_res = false;

        //正确返回并且目标文件存在
        if(!$return_val && $this->isGenerateSuccess($des_file_path)){
            $exec_res = true;
        }

        return [$exec_res,$des_file_path];
    }

    /**
     * 在pdf的最开始添加页面
     * @param string $des_file_path 目标文件绝对地址
     * @param string $output_path 输出文件目录地址
     * @param string $output_file_name 输出文件名 没传的话就随机生成
     * @param string $add_file_path 需要添加的页面文件地址 如果传'' 表示添加空白页
     * @return array
     * @throws ErrorException
     */
    public function addPageBeforeFirstPage($des_file_path,$output_path,$output_file_name = '',$add_file_path = ''){
        //不知道为啥 在指定pdf最开始添加指定页面添加不起，但是添加空白页就是对的 cao
        //cpdf -pad-before one.pdf 1  [-pad-with xx.pdf ] -o one_add_blank_page.pdf
        $this->isFileExist($des_file_path);

        //如果是在第一页前添加指定页，就改用merge的方式
        if ($add_file_path){
            $this->isFileExist($add_file_path);
            return $this->pdfMerge([$add_file_path,$des_file_path],$output_path,$output_file_name);
        }else{
            $shell = $this->_cpdf_executable_file_path.' -pad-before ';
            $shell .= $des_file_path.'  1 ';

            if($this->getExtFromPath($des_file_path) != 'pdf' || !file_exists($des_file_path)){
                throw new ErrorException('目标文件类型有误或不存在');
            }

            $this->makeDir($output_path);
            if(!$output_file_name){
                $output_file_name = $this->genRandFileName();
            }
            $output_path = $output_path.'/'.$output_file_name;

            $shell .= ' -o '.$output_path.' 2>&1';

            exec($shell,$out_put,$return_val);

            //正确返回并且目标文件存在
            if(!$return_val && $this->isGenerateSuccess($output_path)){
                $exec_res = true;
            }else{
                throw new ErrorException($out_put[5]);
            }

            return [$exec_res,$output_path];
        }
    }

    /**
     * 在pdf的某页后面插入指定文件
     * @param string $des_file_path 目标文件地址
     * @param string $output_path 输出文件目录
     * @param mixed $position 插入pdf在源文件的第几页后面  这个页数应该大于1  如果是在末尾插入 传 end
     * @param string $output_file_name 输出文件名 不传随机生成
     * @param string $add_file_path 需要添加的页面文件地址 如果传'' 表示添加空白页
     * @return array
     * @throws \ErrorException
     */
    public function addPageAfterAppointPage($des_file_path,$output_path,$position,$output_file_name = '',$add_file_path = ''){
        //cpdf -pad-after one.pdf 1 -pad-with xx.pdf -o out.pdf
        $this->isFileExist($des_file_path);

        if($this->getExtFromPath($des_file_path) != 'pdf' || !file_exists($des_file_path)){
            throw new ErrorException('目标文件类型有误或不存在');
        }

        $shell = $this->_cpdf_executable_file_path.' -pad-after ';
        $shell .= $des_file_path.' '.$position;
        if ($add_file_path){
            $shell .= ' -pad-with '.$add_file_path.' ';
        }

        $this->makeDir($output_path);
        if(!$output_file_name){
            $output_file_name = $this->genRandFileName();
        }

        $output_path = $output_path.'/'.$output_file_name;

        $shell .= ' -o '.$output_path.' 2>&1';
        exec($shell,$out_put,$return_val);

        //正确返回并且目标文件存在
        if(!$return_val && self::isGenerateSuccess($output_path)){
            $exec_res = true;
        }else{
            throw new ErrorException($out_put[5]);
        }

        return [$exec_res,$output_path];
    }


    //位置大概有这些
    //-top 10           Center of baseline 10 pts down from the top center
    //-topleft 10       Left of baseline 10 pts down and in from top left
    //-topright 10      Right of baseline 10 pts down and left from top right
    //-left 10          Left of baseline 10 pts in from center left
    //-bottomleft 10    Left of baseline 10 pts in and up from bottom left
    //-bottom 10        Center of baseline 10 pts up from bottom center
    //-bottomright 10   Right of baseline 10 pts up and in from bottom right
    //-right 10         Right of baseline 10 pts in from the center right
    //-diagonal         Diagonal, bottom left to top right, centered on page
    //-reverse-diagonal Diagonal, top left to bottom right, centered on page
    //-center           Centered on page
    /**
     * 给pdf添加页码
     * @param string $des_path 目标源pdf文件路径
     * @param string $output_path 输出新文件的目录路径
     * @param string $output_file_name 输出新文件的文件名 不传随机生成
     * @param string $position 页码的位置  不传默认在底部的中间位置
     * @param int $page_begin_num 页码开始的页号  默认从1开始递增
     * @return array
     * @throws ErrorException
     */
    public function addPageNumToPdf($des_path,$output_path,$output_file_name = '',$position = 'bottom',$page_begin_num = 1){
        //cpdf -add-text "-%Bates-" -bottomright 10 -bates 1 out.pdf -o out_add_num.pdf 这样写可以指定开始的页码
        //cpdf -add-text "-%Page-" -bottom 10 out.pdf -o out_add_num.pdf 这样开始的页码是1
        $this->isFileExist($des_path);

        if($this->getExtFromPath($des_path) != 'pdf' || !file_exists($des_path)){
            throw new ErrorException('目标文件类型有误或不存在');
        }

        $this->makeDir($output_path);
        if(!$output_file_name){
            $output_file_name = $this->genRandFileName();
        }

        $output_path = $output_path.'/'.$output_file_name;
        $shell = $this->_cpdf_executable_file_path.' -add-text "-%Bates-" -'.$position.' 30 -bates '.$page_begin_num.' '.$des_path.' -o '.$output_path.' 2>&1';
        exec($shell,$out_put,$return_val);

        //正确返回并且目标文件存在
        if(!$return_val && self::isGenerateSuccess($output_path)){
            $exec_res = true;
        }else{
            throw new ErrorException($out_put[5]);
        }

        return [$exec_res,$output_path];
    }

    /**
     * 判断文件是否存在
     * @param $path
     * @throws ErrorException
     */
    public function isFileExist($path){
        if(!file_exists($path)){
            throw new ErrorException('文件不存在或已被删除');
        }
    }

    /**
     * 判断文件是否生成成功
     * @param $path
     * @return bool
     */
    public function isGenerateSuccess($path){
        if(!file_exists($path)){
            return false;
        }
        return true;
    }

    /**
     * 获取pdf的总页数
     * @param $pdf_path
     * @return int
     * @throws \ErrorException
     */
    public function getPdfPageCount($pdf_path) {
        $this->isFileExist($pdf_path);

        $shell = $this->_cpdf_executable_file_path.' -pages '.$pdf_path.' 2>&1';
        exec($shell,$out_put,$rst);

        $pages = $out_put[3];
        if($rst>0){
            throw new ErrorException('get pdf page count err');
        }
        return $pages;
    }

    private function makeDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
            @chmod($dir,0777);
        }
    }

    /**
     * 判断cpdf 的可执行文件是否存在并且可执行
     * @throws ErrorException
     */
    private function cpdfIsExecutable(){
        $cpdf_path = __DIR__.'/cpdf';

        if (!file_exists($cpdf_path)){
            throw new \ErrorException('cpdf可执行源不存在');
        }
        if(is_executable($cpdf_path)){
            throw new \ErrorException('cpdf没有可执行权限');
        }
        $this->_cpdf_executable_file_path = $cpdf_path;
    }

    /**
     * 根据路径获取文件后缀名
     * @param $url
     * @return mixed
     */
    private function getExtFromPath($url){
        $path = parse_url($url)['path'];
        return pathinfo(basename($path),PATHINFO_EXTENSION);
    }

    /**
     * 根据路径获取文件名
     * @param $url
     * @return mixed
     */
    private function getFileNameFromPath($url){
        $path = parse_url($url)['path'];
        return pathinfo($path,PATHINFO_BASENAME);
    }

    /**
     * 生成一个随机的文件名
     * @return mixed
     */
    private function genRandFileName(){
        return uniqid('pdf_'.date('ymd')).'.pdf';
    }
}