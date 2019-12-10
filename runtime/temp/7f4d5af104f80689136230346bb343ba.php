<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:58:"E:\admin\public/../application/admin\view\index\login.html";i:1575437893;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>芈小兔管理平台</title>
    <!--STYLESHEET-->
   <link href="/static/hack/layui/css/layui.css" rel="stylesheet" >
    <link href="/static/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/admin/css/nifty.min.css" rel="stylesheet">
    <link href="/static/admin/css/demo/nifty-demo-icons.min.css" rel="stylesheet">
    <link href="/static/admin/plugins/magic-check/css/magic-check.min.css" rel="stylesheet">
    <link href="/static/admin/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="/static/admin/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <link href="/static/admin/css/my.css" rel="stylesheet">

    <!--JAVASCRIPT-->
    <script src="/static/admin/js/jquery-2.2.4.min.js"></script>
    <script src="/static/admin/js/jquery.cookie.js"></script>
    <script src="/static/hack/layui/layui.js"></script>
    <script src="/static/admin/js/myself.min.js"></script>
    <!--Background Image [ DEMONSTRATION ]-->
    <script src="/static/admin/js/demo/bg-images.js"></script>
   
</head>
<body>
	<div id="container" class="cls-container">
		<!-- BACKGROUND IMAGE -->
		<div id="bg-overlay" class="bg-img" style="background-image: url('/static/admin/img/bg-img/bg-img-<?php echo rand(1, 7); ?>.jpg');"></div>
		<!-- LOGIN FORM -->
		<div class="cls-content">
		    <div class="cls-content-sm panel" style="height: 360px;">
		        <div class="panel-body">
		            <div class="mar-ver pad-btm">
		                <h3 class="h4 mar-no">芈小兔管理平台</h3>
		                <p class="text-muted"></p>
		            </div>
		            <form action="loginDo" method="post" id="login_form" name="form">
		                <div class="form-group">
		                    <input type="text" class="form-control" placeholder="手机号" autofocus name="user_name" value="<?php echo $user_name; ?>">
		                </div>
		                <div class="form-group">
		                    <input type="password" class="form-control" placeholder="密码" name="password" value="<?php echo $password; ?>">
		                </div>
						<div class="input-group mar-btm ">
							<input type="input" class="form-control" placeholder="验证码" name="captcha">
							<span class="input-group-addon pad-no"><a href="javascript:void(0)" onclick="refreshCapt(this, false)"><?php echo captcha_img(); ?></a></span>
						</div>
		                <div class="checkbox pad-btm text-left">
		                    <input id="demo-form-checkbox" class="magic-checkbox" type="checkbox" name="remember" value="0" >
		                    <label for="demo-form-checkbox">记住密码</label>
							<p style="margin-top:-18px;margin-left: 193px;"><a href="/register/index">还没有账号，去注册</a></p>
		                </div>
		                <button class="btn btn-primary btn-lg btn-block" type="button" id="code"  onclick="submit_check('login_form', 1)">登录</button>
						<div class="checkbox pad-btm text-left" style="height:40px">
							<p style="text-align: center;font-size: 17px"><a href="/register/resetPass">忘记密码？</a></p>
						</div>
		            </form>
		        </div>
		
		        <!-- <div class="pad-all">
		            <a href="javascript:void(0)" class="btn-link mar-rgt add-tooltip" data-toggle="tooltip" data-container="body" data-placement="top" data-original-title="暂未开放密码找回功能">忘记密码 ?</a>
		            <a href="<?php echo url('user/register'); ?>" class="btn-link mar-lft">向管理员申请账号</a>

		            
		        </div> -->
		    </div>
		</div>
		<!-- DEMO PURPOSE ONLY -->
		<div class="demo-bg hidden">
		    <div id="demo-bg-list">
		        <div class="demo-loading"><i class="psi-repeat-2"></i></div>
		        <img class="demo-chg-bg bg-trans active" src="/static/admin/img/bg-img/thumbs/bg-trns.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-1.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-2.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-3.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-4.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-5.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-6.jpg" alt="Background Image">
		        <img class="demo-chg-bg" src="/static/admin/img/bg-img/thumbs/bg-img-7.jpg" alt="Background Image">
		    </div>
		</div>
	</div>
<script>
$(document).keydown(function (event) {
    var e = event || window.event;
    var k = e.keyCode || e.which;
    if (k == 13) {
        //enter 健
        submit_prev('login_form', 1);
    }
});
</script>
	<!-- END OF CONTAINER -->
		</body>
</html>
