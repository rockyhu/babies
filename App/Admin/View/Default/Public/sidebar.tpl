<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="__BASE__/AdminLTE/dist/img/user1-128x128.jpg" class="img-circle" alt="User Image" />
      </div>
      <div class="pull-left info">
        <p>{:session('admin.manager')}</p>
        <a href="javascript:void(0);">超级管理员</a>
      </div>
    </div>
    <!-- search form -->
    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="快速打开菜单" />
        <span class="input-group-btn">
          <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
        </span>
      </div>
    </form>
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li class="header">导航栏</li>
      <li class="<eq name="Think.const.CONTROLLER_NAME" value="Index">active</eq>"><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> <span>控制面板</span></a></li>

      <!-- 菜单栏目循环 -->
      <volist name="allNavMenu" id="navMenu">
        <empty name="navMenu.url">
          <li class="treeview {$navMenu.class}">
        <else />
          <li class="{$navMenu.class}">
        </empty>
          <a href='<empty name="navMenu.url">javascript:void(0);<else />{$navMenu.url}</empty>'><i class="fa {$navMenu.iconCls}"></i> <span>{$navMenu.text}</span>
            <empty name="navMenu.url"><i class="fa fa-angle-left pull-right"></i></empty>
          </a>
          <empty name="navMenu.url">
          <ul class="treeview-menu">
            <volist name="navMenu.subNav" id="subNav">
            <li class="{$subNav.class}"><a href='{$subNav.url}'><i class="fa {$subNav.iconCls}"></i> {$subNav.text}</a></li>
            </volist>
          </ul>
          </empty>
        </li>
      </volist>
      <!-- 菜单栏目循环 -->
      
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>