<?php
namespace app\admin\controller;
use think\Db;
class Excel extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("phonemsg");
    }
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        $rs=$this->tables->order('id desc')->paginate(10);//分页方法，参数表示每页显示条数
        $this->assign("rs",$rs);
        return $this->fetch();
    }
    //导出excel
    public function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gbk', $expTitle);//文件名称
        $fileName = 'userTest'.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        require "../extend/PHPExcel.php";
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        //$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    /**
     *
     * 导出Excel
     */
    function expuser(){//导出Excel
        $xlsName  = "User";
        $xlsCell  = array(
            array('id','账号序列'),
            array('phone','手机号码'),
            array('content','内容'),
            array('add_time','发送时间'),
            array('status','状态'),
        );
        $Data  = $this->tables->paginate(5);
        $rsArr=$Data->toArray();//object对象，转换为数组
        $xlsData=$rsArr['data'];//取数组
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['status']=$v['status']==1?'成功':'失败';
            $xlsData[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
        }
        $this->exportExcel($xlsName,$xlsCell,$xlsData);

    }
    //导入excel数据
    function impuser(){
        if (!empty($_FILES['myfile']['name'])) {
            $file_name = $this->upload();//上传文件
            require "../extend/PHPExcel.php";
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            $rs=true;
            for($i=3;$i<=$highestRow;$i++)
            {
                $data['phone'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                $data['content'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                $addtime=$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                $data['add_time'] = strtotime($addtime);
                $status= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
                $data['status']=$status=='成功'?1:0;
                if(!Db::table("phonemsg")->insert($data)){
                    $rs=fasle;
                }
            }
            if($rs){
                $this->success('导入成功！');
            }else{
                $this->error('导入失败！');
            }
        }else{
            $this->error("请选择上传的文件");
        }

    }

    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg3.
        $file = request()->file('myfile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>1567800,'ext'=>'xls,xlsx'])->move(ROOT_PATH .'public' . DS . 'uploads');
        if($info){
            //var_dump($info);die;
            return 'uploads\\'.$info->getSaveName();//返回文件路径，文件夹+图片地址
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}