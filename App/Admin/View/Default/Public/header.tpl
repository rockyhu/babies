<header class="main-header">
  <!-- Logo -->
  <a href="{:U('Index/index')}" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"></span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg">第一宝贝运营系统</span>
  </a>
  <!-- Header Navbar: style can be found in header.less -->
  <nav class="navbar navbar-static-top" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- Messages: style can be found in dropdown.less-->
        <li class="dropdown messages-menu">
          <a href="javascript:void(0);" class="dropdown-toggle">
          	<i class="fa fa-envelope-o icon-animated-vertical"></i>
            <span class="label label-danger">0</span>
          </a>
        </li>
        <!-- Notifications: style can be found in dropdown.less -->
        <li class="dropdown notifications-menu">
          <a href="{:U('Message/index')}" class="dropdown-toggle">
            <i class="fa fa-bell-o icon-animated-bell"></i>
            <span class="label label-warning">0</span>
          </a>
        </li>
        <!-- User Account: style can be found in dropdown.less -->
        <li class="dropdown user user-menu">
          <a href="#">
            <img src="__BASE__/AdminLTE/dist/img/user1-128x128.jpg" class="user-image" alt="User Image" />
            <span class="hidden-xs">{:session('admin.manager')}</span>
          </a>
        </li>
        <!-- Control Sidebar Toggle Button -->
        <li>
          <a href="{:U('Login/logout')}" title="退出登录"><i class="fa fa-sign-out"></i></a>
        </li>
      </ul>
    </div>
  </nav>
</header>