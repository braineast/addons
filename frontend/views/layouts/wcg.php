<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\WcgAsset;
use frontend\widgets\Alert;

/**
 * @var \yii\web\View $this
 * @var string $content
 */
WcgAsset::register($this);

$company = isset(Yii::$app->params['company']) ? Yii::$app->params['company'] : 'My Company';
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width,maximum-scale=0.5,target-densitydpi=300, user-scalable=no;"  />
        <title><?= Html::encode($this->title) ?></title>
<script src="/template/default/Public/js/jquery.js" type="text/javascript"></script>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <div id="top_conbg">
        <div id="top_con">
            <span style="padding:2px 0 0 20px;">理财靠谱，旺财谷！</span>
            <?php if (Yii::$app->user->isGuest): ?>
            <ul><li class="no_fg"><a href="/user/reg" target="_blank">注册</a></li><li><a href="/">登录</a></li><li><a href="/help/xszy" style=" color:#f00">新手指引</a></li></ul>
            <?php else: ?>
            <ul>
                <li class="no_fg orange"><a href="/user/content"><?= Yii::$app->user->getIdentity()->getName() ?></a></li>
                <li><a href="/user/content.html">账户中心</a></li>
                <li><a href="/help/xszy.html" style=" color:#f00">新手指引</a></li>
                <li><a href="/user/logout.html">退出</a></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="top_bg">
    <div class="top">
        <div class="logo"><a href="/"><img src="/template/default/Public/images/logo.png" width="195px;" height="67px;" title="旺财谷理财平台" alt="旺财谷理财平台"></a></div>
        <div class="daohang">
            <ul>
               	<li><a href="/">首页</a></li>
                <li><a href="/deal.html">我要理财</a></li>   
                <li><a href="/safe.html">安全保障</a></li>
                <li><a href="/help.html">理财帮手</a></li>
				<li><a href="/help/xszy.html">新手指引</a></li>
            </ul>
        </div>
        <div class="wenhou">
            客服热线<p>400-888-6268</p>
        </div>
                       <div class="clear"></div>
    </div>
</div>
    <?= $content ?>


    <div class="footbg">
    <div class="footer">
       <div class="foot_content"> 
       <div class="foot_contentup">
        <div class="foottxt clearfix">
            <ul>
                <li class="ft-title title1">关于我们</li>
                <li><a href="/about.html" target="_blank">旺财谷介绍</a></li>
				<li><a href="/about/youshi.html" target="_blank">四大优势</a></li>
                <li><a href="/about/Lxwm.html" target="_blank">联系我们</a></li>
				<li><a href="/about/ditu.html" target="_blank">网站地图</a></li>
            </ul>
            <ul>
                <li class="ft-title title2">理财帮手</li>
                <li><a href="/help/show/id-1.html" target="_blank">理财管理</a></li>
                <li><a href="/help/show/id-2.html" target="_blank">免费理财</a></li>
				<li><a href="/help/show/id-4.html" target="_blank">名词解释</a></li>
                <li><a href="/notice.html" target="_blank">网站公告</a></li>
            </ul>
            <ul class="foot-con3">
                <li class="ft-title title3">关注我们</li>
                <li>
                    <span style="margin-top:3px;"><img src="/template/default/Public/images/xl_wb.png" border="0"></span><span style="padding-left:10px;">新浪微博</span>
                </li>
                <li style="padding-top:10px;">
                    <span style="margin-top:3px;"><img src="/template/default/Public/images/tx_wb.png" border="0"></span><span style="padding-left:10px;">腾讯微博</span>
                </li>
            </ul>
             <div class="foot_img"><img src="/template/default/Public/images/foot_img.png" border="0" style="width:120px;height:120px"></div>
         </div>
         
         <div class="foot_message">
            <ul>
               <li>客服热线：400-888-6268 (工作时间 9:00-17:00)</li>
               <li>地址：北京市朝阳区朝外大街甲6号万通中心C座1303室</li>
               <li>Copyright @ Reserved 2014 | <a href="http://www.wangcaigu.com/">旺财谷</a> 版权所有 </li>
			   <li><a href="http://www.miitbeian.gov.cn/" target="_blank">苏ICP备14011658号-2</a></li>
            </ul>
         </div>

         </div>

		<style>
		.foot_friend
		{
			width: 960px;
			margin: auto;
			line-height: 35px;
			font-size: 14px;
			padding-left: 24px;
			float: left;
		}
		.fdleft
		{
			width: 70px;
			float: left;
			font-size: 14px;
		}
		.fdright
		{
			width: 890px;
			float: right;
			font-size: 14px;
		}
		</style>

		<!--友情连接start-->
		<div class="foot_friend clearfx">
			<div class="fdleft">友情链接：</div>
			<div class="fdright">
			  <a href="http://www.iheima.com/" target="_blank">黑马营</a>
			  <a href="http://finance.21cn.com/" target="_blank">21财经</a>
			  <a href="http://www.enfodesk.com/" target="_blank">易观智库</a>
			  <a href="http://www.wangdaizhijia.com/" target="_blank">网贷之家</a>
			  <a href="http://iof.hexun.com/" target="_blank">和讯互联网金融</a>
			  <a href="http://www.wangcaigu.com/deal.html" target="_blank">旺财谷理财频道</a>
			</div>
		</div>
		<!--友情连接end-->
		
         <div class="foot_contentdown">
		 <!--可信网站图片LOGO安装开始-->
		 <a href="http://webscan.360.cn/index/checkwebsite?url=www.wangcaigu.com"><img src="/template/default/Public/images/webscan/web360.jpg" style="border:none;" alt="360网站安全检测" target="_blank"></a>
        
         <!--可信网站图片LOGO安装结束-->

         </div>
       </div>
    </div>
	<div class="fanhuidingbu"><a href="#" class="go-top"></a></div>
	<div class="clear"></div>
</div>
    <div class="contactuswcg" style="right: -230px;">
  <div class="hoverbtn">
     <span>联</span><span>系</span><span>我</span><span>们</span>
  </div>
  
  <div class="conter">
	<div class="con1"> 
		<dl class="clearfx">
		     <dt></dt>
		     <dd class="f1">服务热线：</dd>
		     <dd class="f2"></dd>
		 </dl>
	</div> 
  
<!-- 	<p class="con2 kefu clearfx">
		 <a class="a1" href="javascript:consult_online();"></a>
		 <span>在线客服：</span>
		 <a href="javascript:consult_online();">点击交流</a>
	</p>  -->
	  
<!-- 	<p class="con2 weibo clearfx">
		 <a class="a1" href="http://e.weibo.com/wcg" target="_blank"><img src="/images/index/contactuswcg_3.png"></a>
		 <span>新浪微博：</span>
		 <a href="http://e.weibo.com/wcg" target="_blank">点击关注</a>
	</p> -->
  
  	<div class="qqcall"> 
		<dl class="clearfx">
		     <dt></dt>
		     <dd class="f1">QQ交流群：</dd>
	         <dd>群1：<a target="_blank" href="http://jq.qq.com/?_wv=1027&amp;k=Lfhl2V">302696514</a> </dd>
		 </dl>
	</div> 
    <div class="weixincall"> 
		<dl class="clearfx">
		     <dt></dt>
		     <dd class="f1">微信服务号：</dd>
	         <dd>wcg6268</dd>
	         <dd class="f3"></dd>
		 </dl>
	</div> 
	
	<div class="wcgtimer">
	   <span style="font-weight: bold;">工作时间：</span>
	   <span>9:00-17:00</span>
	</div>
	
  </div>

</div>
<script type="text/javascript">
    var env = 0;
    $(document).ready(function() {
        $(".zj dd").click(function() {
            env = 1;
        });
        $(".zj").click(function() {
            if (env === 0) {
                $(this).children(".zjxl").toggle();
            }
            env = 0;
        });
        $('.verify-code').addClass('.verify-code');
        
        //返回顶部操作
         $(window).scroll(function() {
            if ($(this).scrollTop() > 200) {
                $('.go-top').fadeIn(200);
            } else {
                $('.go-top').fadeOut(200);
            }
        });
        $('.go-top').click(function(event) {
            event.preventDefault();
            $('html, body').animate({scrollTop: 0}, 300);
        });
		
		
		//联系我们
		$(".contactuswcg").hover(function()
		{
			$(this).stop().animate({
				right:"0px"
			},800);
		},function()
		{
			$(this).stop().animate({
				right:"-230px"
			});
		});
		

    });
</script>
<script type="text/javascript">
	$(document).ready(function() {
		$(".zj").click(function() {
			$(this).next(".aaa").toggle();
		})

	});
</script>
<style type="text/css">
.contactuswcg{position:fixed; right:-230px; bottom:0px;_position:absolute; width:230px;height:100%; background:#2fa9e6; z-index:99999999999;}
.contactuswcg .hoverbtn{width:30px; height:90px; padding-top:23px; cursor: pointer; position:absolute; top:50%; margin-top:-66px; left:-30px; font-size:14px; color:#fff; background:#0085b8;
 -webkit-border-radius: 4px 0px  0px 4px; -moz-border-radius: 4px 0px  0px 4px; -o-border-radius: 4px 0px  0px 4px;      border-radius: 4px 0px  0px 4px;
 -moz-box-shadow: -1px -1px 6px 0px #999; -webkit-box-shadow: -1px -1px 6px 0px #999; box-shadow: -1px -1px 6px 0px #999;}
.contactuswcg .hoverbtn span{width:30px; height:18px; text-align: center; overflow:hidden; float:left;}
.contactuswcg .hoverbtn img{width:13px; height:9px; margin:10px 0px 0px 10px; float:left; display:inline;}
.contactuswcg .conter{width:186px; height:560px; margin-left:22px;   position:absolute; top:50%; margin-top:-280px;}
.contactuswcg .conter dl dt,.contactuswcg .conter .con2 .a1,.contactuswcg .conter .con1 dl .f2{background:url(/template/default/Public/images/creditbg.png) no-repeat;}
.contactuswcg .conter .con1{ border-bottom:1px solid #2c9ac5; padding-bottom:14px;}
.contactuswcg .conter .con1 dl dt{width:31px; height:31px; float:left; background-position:0px -200px;}
.contactuswcg .conter .con1 dl dd{width:142px; float:right; font-size:18px; color:#fff;}
.contactuswcg .conter .con1 dl .f1{height:19px; line-height:16px; font-size:14px; vertical-align: top; font-weight: bold; }
.contactuswcg .conter .con1 dl .f2{background-position:0px -243px; height:17px;}
.contactuswcg .conter .con2{ border-bottom:1px solid #2c9ac5; padding:18px 0px 14px 0px;}
.contactuswcg .conter .con2 .a1{margin-right:10px;}
.contactuswcg .conter .con2 a{float:left; height:31px; line-height:31px; color:#f3e7b2; }
.contactuswcg .conter .con2 span{float:left;height:31px; line-height:31px; font-size:14px; color:#fff;}
.contactuswcg .conter .kefu .a1{width:31px; height:31px; display:block; background-position:0px -167px;}
.contactuswcg .conter .weibo .a1{width:31px; height:31px; display:block; background-position:-34px -167px;}
.contactuswcg .qqcall{padding:19px 0px 10px 0px; border-bottom:1px solid #2c9ac5;}
.contactuswcg .qqcall dl dt{width:31px; height:31px; float:left; background-position:-68px -167px;}
.contactuswcg .qqcall dl dd {width:142px; float:right; font-size:12px; color:#fff; height:22px; float:right;}
.contactuswcg .qqcall dl dd .full{color:#ccc; cursor: text; text-decoration: none; font-size:12px;}
.contactuswcg .qqcall dl dd a{color:#fcebaf; text-decoration: underline;}
.contactuswcg .weixincall{padding:22px 0px 22px 0px; border-bottom:1px solid #2c9ac5;}
.contactuswcg .weixincall dl dt{width:31px; height:31px; float:left; background-position:-34px -200px;}
.contactuswcg .weixincall dl dd {width:142px; float:right; font-size:12px; color:#fff; height:22px; float:right;}
.contactuswcg .weixincall .f1{height:25px; font-size:14px; vertical-align: top; color:#fff; font-weight: bold; }
.contactuswcg .weixincall .f3{width:73px; height:73px; margin-right:69px; display:inline;}
.contactuswcg .wcgtimer{width:177px; height:40px; padding:16px 0px 15px 26px; background:#2d9dc9; margin:28px 0px 0px -8px;}
.contactuswcg .wcgtimer span{display:block; height:20px; line-height:20px; font-size:14px; color:#0e6384;}
.clearfx:after {
display: block;
clear: both;
content: "";
}
.clearfx {
zoom: 1;
}
</style>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>
