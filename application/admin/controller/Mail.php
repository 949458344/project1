<?php
namespace app\admin\controller;
use think\Db;
header("Content-type:text/html;charset=utf-8");
use phpmailer\PHPMailer;
class Mail extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("mail");
    }
    /**
     * 默认方法，查询列表数据
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->order('id desc')->paginate(10);//分页方法，参数表示每页显示条数
        $rsArr=$rs->toArray();//object对象，转换为数组
        $rsDate=$rsArr['data'];
        foreach($rsDate as $key=>$val){
            if($val['status']==1){
                $rsDate[$key]['statusName']='成功';
            }else{
                $rsDate[$key]['statusName']='失败';
            }
        }
        $this->assign('rsDate',$rsDate);
        $this->assign("rs",$rs);
        return $this->fetch();
    }
    //发送邮件qq
    public function add(){
        $toemail=I('toemail');
        //判断用户名为空，跳转html添加数据
        if(empty($toemail)){
            return $this->fetch();
        }else {//不为空有内容，添加数据到数据库
            //获取输入值，
            $toemail = isset($_REQUEST['toemail']) ? $_REQUEST['toemail'] : '';
            $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
            //调用发送邮件方法
            if ($this->sendEmail($toemail, $content)) {
                $this->success("邮件发送成功", url($this->controllers . '/index'));
            } else {
                $this->error("邮件发送失败");
            }
        }
    }

    /**
     * url http://www.cnblogs.com/richerdyoung/p/6489924.html
     * 发送邮件方法
     * @param $toemail，接收邮件账号，邮箱地址
     * @param $content,邮件内容
     * @return bool ,返回bool值，true成功，false失败
     * @throws \Exception
     * @throws \phpmailer\phpmailerException
     */
    private function sendEmail($toemail,$content){
        $rs=false;//发送邮件是否成功，0失败，1成功
        $sendmail = '949458344@qq.com'; //发件人邮箱
        $sendmailpswd = "zmmejdtdmjubbfed"; //客户端授权密码,而不是邮箱的登录密码！zmmejdtdmjubbfed,rbpgeqminarobfaj
        $send_name = 'hhhhh';// 设置发件人信息，如邮件格式说明中的发件人，
        $toemail = empty($toemail)?'sky1003@yeah.net':$toemail;//定义收件人的邮箱,3元表达式判断，空默认值为sky1003@yeah.net
        $to_name = '华育国际测试邮件';//设置收件人信息，如邮件格式说明中的收件人
        $mail = new PHPMailer();
        $mail->isSMTP();// 使用SMTP服务
        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
        $mail->Host = "smtp.qq.com";// 发送方的SMTP服务器地址
        $mail->SMTPAuth = true;// 是否使用身份验证
        $mail->Username = $sendmail;//// 发送方的
        $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
        $mail->SMTPSecure = "ssl";// 使用ssl协议方式
        $mail->Port = 465;//  qq端口465或587）
        $mail->setFrom($sendmail,$send_name);// 设置发件人信息，如邮件格式说明中的发件人，
        $mail->addAddress($toemail,$to_name);// 设置收件人信息，如邮件格式说明中的收件人，
        $mail->addReplyTo($sendmail,$send_name);// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
        //$mail->addCC("xxx@qq.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
        //$mail->addBCC("xxx@qq.com");// 设置秘密抄送人(这个人也能收到邮件)
        //$mail->addAttachment("bug0.jpg");// 添加附件
        $mail->Subject = "华育国际php";// 邮件标题
        $content=empty($content)?"邮件内容是 <b>您的验证码是：123456</b>，哈哈哈！":$content;//3元表达式判断，内容为空，给默认值
        $mail->Body = $content;// 邮件正文
        //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用
        if(!$mail->send()){// 发送邮件
            echo "Message could not be sent.";
            echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
        }else{
            $rs=true;
            echo '发送成功';
        }
        //添加发送邮件内容到数据库
        $date=array(
            'toemail'=>$toemail,
            'content'=>$content,
            'add_time'=>time(),
            'status'=>intval($rs),
        );
        $mailrs = Db::table("mail")->insert($date);
        //如果邮件发送成功，返回添加数据库状态，否则失败
        if($rs){
            $rs=$mailrs;
        }
        return $rs;
    }
}