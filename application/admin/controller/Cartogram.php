<?php
namespace app\admin\controller;
use think\Db;
//use phpexcel\PHPExcel;
class Cartogram extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("admin");
    }
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //$this->barGraph3();
        /*$rs=$this->tables->order('id desc')->paginate(10);//分页方法，参数表示每页显示条数
        $this->assign("rs",$rs);*/
        return $this->fetch();

    }
    //饼图
    public function pie()
    {
        return $this->fetch();
    }
    //曲线图
    public function curve()
    {
        return $this->fetch();
    }
    //条形统计图,左右对比
    public function barGraph3(){
        include('../extend/jpgraph/jpgraph.php');   //载入基本类
        include('../extend/jpgraph/jpgraph_bar.php');   //载入柱状图
        $data = array(rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100), rand(10,100)); //设置统计数据
        $xdata = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $graph = new \Graph(600, 300);       //设置画布大小
        $graph->SetScale('textlin');        //设置坐标刻度类型
        $graph->SetShadow();                //设置画布阴影
        $graph->img->SetMargin(40, 30, 20, 40);     //设置统计图边距
        $barplot = new \BarPlot($data);              //实例化BarPlot对象
        $barplot->SetFillColor('blue');             //设置柱状图前景色
        $barplot->value->Show();                    //显示
        $graph->Add($barplot);
        $graph->title->Set(iconv('utf-8', 'GB2312//IGNORE','***科技有限公司年度收支'));//设置标题iconv防止中文乱码
        $graph->xaxis->title->Set(iconv('utf-8', 'GB2312//IGNORE','月份'));           //设置X轴名称
        $graph->xaxis->SetTickLabels($xdata);//设置x轴标注
        $graph->yaxis->title->Set(iconv('utf-8', 'GB2312//IGNORE','总金额（万元）'));  //设置y轴名称
        $graph->title->SetFont(FF_SIMSUN, FS_BOLD);                                   //设置标题字体
        $graph->xaxis->title->SetFont(FF_SIMSUN, FS_BOLD);                            //设置x轴字体
        $graph->yaxis->title->SetFont(FF_SIMSUN, FS_BOLD);                            //设置y轴字体
        $graph->Stroke();              //输出图像
    }
    //条形统计图,上下对比
    public function barGraph() {
        $top_y=array(11,1,1,7,30);//上半柱状坐标
        $bottom_y=array(-2,-8,9,3,3);//下半柱状坐标
        $this->draw_pic('title条形统计图,上下对比 ','X轴','Y轴坐标',$top_y,$bottom_y);
    }
    //条形统计图，左右对比
    public function barGraph2() {
        $datea=array(11,1,1,7,30);//上半柱状坐标
        $dateb=array(-2,-8,9,3,3);//下半柱状坐标
        $this->draw_pic2($datea,$dateb);
    }
    /**
     * 绘图函数
     * @param $title ，表格名称
     * @param $str_x ，横坐标名称
     * @param $str_y ，纵坐标名称
     * @param $data2y ，数据的纵坐标 array注意是数组格式
     * @param $data1y ，数据的横坐标 array注意是数组格式
     * @param $width ，图长默认500
     * @param $height ，图宽默认400
     */
    protected function draw_pic($title,$str_x,$str_y,$data2y,$data1y,$width=500,$height=400){
        // 引入必要的文件，格式：include('Jpgraph文件夹.类名')
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_bar.php');   //依具体情况引入

        // 新建图表
        $graph = new \Graph($width,$height); //图片的大小
        $graph->SetScale("textlin");//参数勿动，设置刻度模式
        $graph->SetShadow();
        $graph->img->SetMargin(40,30,20,40);//中间图离表格的距离

        // 绘制柱状图
        $b1plot = new \BarPlot($data1y);
        $b1plot->SetFillColor("red");//柱状图下方颜色
        $b1plot->value->Show();
        $b2plot = new \BarPlot($data2y);
        $b2plot->SetFillColor("blue");//柱状图上方颜色
        $b2plot->value->Show();

        // 创建分组的柱状图
        $gbplot = new \AccBarPlot(array($b1plot,$b2plot));

        // 将柱状图添加到图表上
        $graph->Add($gbplot);

        //其他格式设置
        $graph->title->Set(iconv("UTF-8","GB2312//IGNORE",$title));//图表名称
        //            echo $str_x;
        $str_x=iconv("UTF-8","GB2312//IGNORE",$str_x);
        $str_y=iconv("UTF-8","GB2312//IGNORE",$str_y);
        $graph->xaxis->title->Set($str_x);//横坐标名称
        $graph->yaxis->title->Set($str_y);//纵坐标名称

        $graph->title->SetFont(FF_SIMSUN,FS_BOLD);
        $graph->yaxis->title->SetFont(FF_SIMSUN,FS_BOLD);
        $graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD);

        // 显示图表
        $graph->Stroke();
    }

    /**
     * 绘图函数2
     * @param $data_a ，左柱的值 array注意是数组格式
     * @param $data1b ，右柱的值 array注意是数组格式
     * @param $width ，图长默认500
     * @param $height ，图宽默认400
     * @param $title ，图表名称，默认为result
     */
    protected function draw_pic2($data_a,$data_b,$width=500,$height=400,$title="Result"){
        // 引入必要的文件
        include('../extend/jpgraph/jpgraph.php');   //载入基本类
        include('../extend/jpgraph/jpgraph_bar.php');   //载入柱状图

        $graph=new \Graph($width,$height); //创建一个图表 指定大小
        $graph->SetScale("textlin"); //设置坐标刻度类型
        $graph->img->SetMargin(40,40,30,40);//设置统计图边距 左、右、上、下
        $graph->SetMarginColor("lightblue");//设置画布背景色 淡蓝色
//        $graph->SetBackgroundImage(TEST_ROOT.'Home/img/gwkj.png',BGIMG_COPY); //设置背景图片
//        $graph->img->SetAngle(45); //设置图形在图像中的角度

        //设置标题信息
        $graph->title->Set($title); //设置统计图标题
        $graph->title->SetFont(FF_SIMSUN,FS_BOLD,20); //设置标题字体
        $graph->title->SetMargin(5);//设置标题的边距

        //设置X轴信息
        $str_x=array('D','I','S','C');//横坐标各个坐标点名称
        $graph->xaxis->title->Set(iconv("UTF-8","GB2312//IGNORE",'季度用户活动图')); //标题
        $graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD,10); //标题字体 大小
        $graph->xaxis->title->SetColor('black');//颜色
        $graph->xaxis->SetFont(FF_SIMSUN,FS_BOLD,10);//X轴刻度字体 大小
        $graph->xaxis->SetColor('black');//X轴刻度颜色
        $graph->xaxis->SetTickLabels($str_x); //设置X轴标记
        $graph->xaxis->SetLabelAngle(0);//设置X轴的显示值的角度;

        //设置Y轴的信息
        $graph->yaxis->SetFont(FF_SIMSUN,FS_BOLD,10);//标题
        $graph->yaxis->SetColor('black');//颜色
        $graph->ygrid->SetColor('black@0.9');//X,y交叉表格颜色和透明度 @为程度值
        $graph->yaxis->scale->SetGrace(0);//设置Y轴显示值柔韧度

        //设置数据
        $bplot1 = new \BarPlot($data_a);
        $bplot2 = new \BarPlot($data_b);

        //设置柱状图柱颜色和透明度
        $bplot1->SetFillColor('orange@0.4');
        $bplot2->SetFillColor('brown@0.4');

        //设置值显示
        $bplot1->value->Show(); //显示值
        $bplot1->value->SetFont(FF_SIMSUN,FS_BOLD,10);//显示字体大小
        $bplot1->value->SetAngle(90); //显示角度
        $bplot1->value->SetFormat('%0.2f'); //显示格式 0.2f:精确到小属数点后2位
        $bplot2->value->Show();
        $bplot2->value->SetFont(FF_SIMSUN,FS_BOLD,10);
        $bplot2->value->SetAngle(90);
        $bplot2->value->SetFormat('%0.0f');

        //设置图列标签
        $graph->legend->SetFillColor('lightblue@0.9');//设置图列标签背景颜色和透明度
        $graph->legend->Pos(0.01,0.12,"right","center");//位置
        $graph->legend->SetFont(FF_SIMSUN,FS_NORMAL,10);//显示字体 大小
        $bplot1->SetLegend('A');
        $bplot2->SetLegend('B');

        //设置每个柱状图的颜色和阴影透明度
        $bplot1->SetShadow('black@0.4');
        $bplot2->SetShadow('black@0.4');

        //生成图列
        $gbarplot = new \GroupBarPlot(array($bplot1,$bplot2));
        $gbarplot->SetWidth(0.5); //柱状的宽度
        $graph->Add($gbarplot);
        $graph->Stroke(); //输出图像
    }

    public function curveGraph(){
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_line.php');   //依具体情况引入
        $graph=new \Graph(400,300);   //设置画布
        $graph->setScale('textint');  //设置刻度样式
        $graph->img->setMargin(30,30,80,80);  //设置画布边界
        $graph->title->set("Year to air temperature");  //设置标题
        $data=array(-21,-3,12,19,22,28,32,29,23,18,5,-10);  //定义数组类型数据
        $lineplot=new \LinePlot($data);//定义曲线图
        $lineplot->SetColor("blue");  //定义曲线图颜色为红色
        $lineplot->SetLegend("Temperature");//设置曲线图例
        $graph->Add($lineplot);//将曲线图加入背景图像中
        $graph->Stroke();  //将x-y坐标图输出
    }
    public function curveGraph2(){
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_line.php');   //依具体情况引入
        $data=array(-21,-3,12,19,22,28,32,29,23,18,5,-10);  //第一条数据
        $data2y=array(3,12,17,20,25,32,41,38,30,27,15,10);
        $graph=new \Graph(400,300);
        $graph->SetScale("textint",-30,50); //设置xy轴样式及y轴的最大值及最小值
        $graph->SetY2Scale("int",-30,50); //设置右侧y轴样式及其最大值与最小值
        $graph->setShadow();  //设置图像样式，加入阴影
        $graph->img->setMargin(40,50,20,70);
        $graph->title->set("changchun and changsha air tempetrature");
        $lineplot=new \LinePlot($data);
        $lineplot2y=new \LinePlot($data2y);  //定义第二条曲线
        $graph->Add($lineplot);
        $graph->Addy2($lineplot2y);
        $graph->xaxis->title->Set("Month");
        $graph->yaxis->title->set("changchun1");
        $graph->y2axis->title->set("changsha2");
        $lineplot->SetColor("red");
        $lineplot2y->setcolor('blue');
        $lineplot->setlegend("changchun1");
        $lineplot2y->setlegend("changsha2");
        $graph->legend->setlayout(LEGEND_HOR);
//$graph->legned->Pos(0.45,0.95,"center","bottom");  //Fatal error: Call to a member function Pos() on a non-object in
        $graph->Stroke();
    }
    public function pieGraph(){
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_pie.php');   //依具体情况引入
        include('../extend/jpgraph/jpgraph_pie3d.php');   //依具体情况引入
        $data=array(-21,-3,12,19,22,28,32,29,23,18,5,-10);
        $graph=new \pieGraph(600,400);
        $graph->img->SetMargin(30,30,80,40);
        $graph->title->Set(iconv('utf-8', 'GB2312//IGNORE',"平均温度"));
        $pie3dplot=new \piePlot3d($data);  //定义饼图
        $pie3dplot->SetLegends(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));
        $graph->legend->Pos(0.01,0.45,"left","center");//0.01距左边的偏移 0.45距上方的偏移 后面两个默认为 right top
        $graph->Add($pie3dplot);
        $graph->title->setfont(FF_SIMSUN,FS_BOLD);   //解决中文（标题）乱码问题
        $graph->Stroke();
    }
    public function pieGraph2(){
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_pie.php');   //依具体情况引入
        $data=array(-21,-3,12,19,22,28,32,29,23,18,5,-10);
        $graph=new \pieGraph(600,400);
        $graph->img->SetMargin(30,30,80,40);
        $graph->title->Set(iconv('utf-8', 'GB2312//IGNORE',"平均温度"));
        $pieplot=new \piePlot($data);  //定义饼图
        $pieplot->SetLegends(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));
        $graph->legend->Pos(0.01,0.02,"left","center");//0.01距左边的偏移 0.02距上方的偏移 后面两个默认为 right top
        $graph->Add($pieplot);
        $graph->title->setfont(FF_SIMSUN);   //解决中文（标题）乱码问题
        $graph->Stroke();
    }
    public function pieGraph3(){
        include('../extend/jpgraph/jpgraph.php');   //必须的
        include('../extend/jpgraph/jpgraph_pie.php');   //依具体情况引入
        include('../extend/jpgraph/jpgraph_pie3d.php');   //依具体情况引入

        $data = array(18 ,23, 26 , 27 , 48 , 25 , 49 , 50 , 45 , 23 , 20 ,30); //模拟数据
        //$month = array('北京','上海','广东','天津' , '河南' , '河北' , '浙江' , '山西' , '重庆','香港','台湾','其他');
        $month = array('beijin','shanghai','guangdong','tianjin' , 'helan' , 'hebei' , 'zhejiang' , 'shanxi' , 'chongqin','xianggang','taiwai','qita');

        $graph = new \PieGraph(600 , 450);
        $graph->SetShadow();
        $graph->title->Set(iconv('utf-8', 'GB2312//IGNORE',"注册人数地区分布"));
        $pieplot = new \PiePlot3D($data);
        $graph->title->SetFont(FF_SIMSUN,FS_BOLD);
        $graph->legend->SetFont(FF_SIMSUN,FS_BOLD);

        $graph->legend->SetPos(0.01,0.2,'right','right');//0.01距左边的偏移 0.2距上方的偏移 后面两个默认为 right top

        $graph->legend->SetColumns(1);//设置图例一列显示
        $graph->legend->SetLineSpacing(15);//设置行距
        $pieplot->SetCenter(0.4) ; //设置饼图的中心位置
        $pieplot->SetLegends($month); //设置图例
        $graph->Add($pieplot);
        $graph->Stroke();
    }
}