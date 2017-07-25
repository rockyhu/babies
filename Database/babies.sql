/*
 Navicat Premium Data Transfer

 Source Server         : Localhost
 Source Server Type    : MySQL
 Source Server Version : 50505
 Source Host           : 127.0.0.1
 Source Database       : babies

 Target Server Type    : MySQL
 Target Server Version : 50505
 File Encoding         : utf-8

 Date: 07/25/2017 16:20:11 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `babies_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `babies_auth_group`;
CREATE TABLE `babies_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='管理员组表';

-- ----------------------------
--  Records of `babies_auth_group`
-- ----------------------------
BEGIN;
INSERT INTO `babies_auth_group` VALUES ('1', '超级管理员', '1', '1,6,7,107,108,118,149,159'), ('14', '系统查看员', '1', '71,77,78,107,108,127,141,143,151,155');
COMMIT;

-- ----------------------------
--  Table structure for `babies_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `babies_auth_group_access`;
CREATE TABLE `babies_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '管理员id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '角色id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员组权限表';

-- ----------------------------
--  Records of `babies_auth_group_access`
-- ----------------------------
BEGIN;
INSERT INTO `babies_auth_group_access` VALUES ('2', '1'), ('7', '1');
COMMIT;

-- ----------------------------
--  Table structure for `babies_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `babies_auth_rule`;
CREATE TABLE `babies_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COMMENT='权限规则表';

-- ----------------------------
--  Records of `babies_auth_rule`
-- ----------------------------
BEGIN;
INSERT INTO `babies_auth_rule` VALUES ('1', 'Admin/Manage/', '管理员管理', '1', '1', ''), ('6', 'Admin/AuthGroup/', '权限控制', '1', '1', ''), ('7', 'Admin/Nav/', '栏目管理', '1', '1', ''), ('67', 'Admin/Message/', '站内信', '1', '1', ''), ('71', 'Admin/Order/', '线上订单列表', '1', '1', ''), ('77', 'Admin/User/', '会员列表', '1', '1', ''), ('78', 'Admin/Notice/', '通知公告', '1', '1', ''), ('88', 'Admin/Agent/', '消费会员等级', '1', '1', ''), ('89', 'Admin/Cashout/', '提现管理', '1', '1', ''), ('91', 'Admin/Refereenet/', '推荐网络图', '1', '1', ''), ('92', 'Admin/Rehousenet/', '安置网络图', '1', '1', ''), ('94', 'Admin/Daili/', '代理分红', '1', '1', ''), ('95', 'Admin/Touzi/', '投资分红', '1', '1', ''), ('96', 'Admin/Yeji/', '业绩统计', '1', '1', ''), ('97', 'Admin/Userpay/', '充值管理', '1', '1', ''), ('98', 'Admin/Change/', '转账记录', '1', '1', ''), ('99', 'Admin/Award/', '奖金明细', '1', '1', ''), ('100', 'Admin/Finance/', '财务报表', '1', '1', ''), ('103', 'Admin/Gather/', '奖金汇总', '1', '1', ''), ('107', 'Admin/DocumentNav/', '文档分类', '1', '1', ''), ('108', 'Admin/Document/', '文档列表', '1', '1', ''), ('114', 'Admin/Shoplevel/', '商家等级', '1', '1', ''), ('115', 'Admin/Purse/', '财务明细', '1', '1', ''), ('117', 'Admin/Dialout/', '每日销售额统计', '1', '1', ''), ('118', 'Admin/System/', '系统参数', '1', '1', ''), ('119', 'Admin/Dialout/', '拨出率', '1', '1', ''), ('120', 'Admin/Yejimonth/', '业绩查询', '1', '1', ''), ('121', 'Admin/UserAgentRecord/', '级别修改记录', '1', '1', ''), ('122', 'Admin/Wallet/', '钱包统计', '1', '1', ''), ('123', 'Admin/Video/', '视频管理', '1', '1', ''), ('125', 'Admin/Clerk/', '业务员聘级', '1', '1', ''), ('127', 'Admin/Shop/', '商家列表', '1', '1', ''), ('128', 'Admin/Referee/', '推荐关系', '1', '1', ''), ('129', 'Admin/Rehouse/', '安置关系', '1', '1', ''), ('130', 'Admin/Settle/', '奖金结算', '1', '1', ''), ('131', 'Admin/Banque/', '账号管理', '1', '1', ''), ('132', 'Admin/Repair/', '账户补扣', '1', '1', ''), ('133', 'Admin/Promotion/', '促销券管理', '1', '1', ''), ('135', 'Admin/Sellthrough/', '销售业绩', '1', '1', ''), ('136', 'Admin/Waityeji/', '待结业绩', '1', '1', ''), ('137', 'Admin/Settledyeji/', '已结业绩', '1', '1', ''), ('138', 'Admin/Upgradepromotion/', '促销升级', '1', '1', ''), ('139', 'Admin/Test/', '测试栏目1', '1', '1', ''), ('141', 'Admin/Merchant/', '招商员列表', '1', '1', ''), ('143', 'Admin/Reseller/', '分销商列表', '1', '1', ''), ('144', 'Admin/ResellerSystem/', '基础设置', '1', '1', ''), ('145', 'Admin/Genlis/', '让利等级', '1', '1', ''), ('146', 'Admin/ResellerLevel/', '分销商等级', '1', '1', ''), ('147', 'Admin/MerchantLevel/', '招商员等级', '1', '1', ''), ('149', 'Admin/Menu/', '自定义菜单', '1', '1', ''), ('150', 'Admin/ShopCashout/', '货款提现', '1', '1', ''), ('151', 'Admin/ShopKind/', '商户类别', '1', '1', ''), ('152', 'Admin/Import/', '积分导入', '1', '1', ''), ('153', 'Admin/Rebate/', '消费增值', '1', '1', ''), ('155', 'Admin/StoreOrder/', '线下订单列表', '1', '1', ''), ('156', 'Admin/Settle/', '系统结算', '1', '1', ''), ('157', 'Admin/ShopStore/', '店铺列表', '1', '1', ''), ('158', 'Admin/Saleroom/', '销售额统计', '1', '1', ''), ('159', 'Admin/Question/', '常见问题', '1', '1', ''), ('160', 'Admin/Plupload/', 'Plupload', '1', '1', '');
COMMIT;

-- ----------------------------
--  Table structure for `babies_document`
-- ----------------------------
DROP TABLE IF EXISTS `babies_document`;
CREATE TABLE `babies_document` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自动编号',
  `name` char(70) NOT NULL DEFAULT '' COMMENT '文档名称',
  `thumb` char(255) NOT NULL DEFAULT '' COMMENT '文档缩略图，建议尺寸: 640 * 640 ，或正方型图片',
  `keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '文档关键字',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字描述',
  `info` varchar(255) NOT NULL DEFAULT '' COMMENT '文档简介',
  `content` text COMMENT '文档描述',
  `isrecommand` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否推荐,0表示否，1表示是',
  `isgun` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否滚动，0表示否，1表示是',
  `ishuan` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否幻灯，0表示否，1表示是',
  `istop` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶，0表是否，1表示是',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档排序，数字大的排名在前,默认排序方式为创建时间',
  `readcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读数',
  `nnid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类id',
  `nid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属文档分类id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文档状态，0表示下架，1表示上架',
  `create` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `nid` (`nid`) USING BTREE,
  KEY `nnid` (`nnid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='文档表';

-- ----------------------------
--  Records of `babies_document`
-- ----------------------------
BEGIN;
INSERT INTO `babies_document` VALUES ('2', '深圳云驿站信息技术有限公司', '{\"source\":\"./Uploads/2017-07-25/5976fbd4f1166.png\"}', 'apple,Mac pro,iPhone', '苹果产品，苹果设计，苹果设备，苹果电脑', '苹果产品，苹果设计，苹果设备，苹果电脑', '苹果产品，苹果设计，苹果设备，苹果手机，苹果电脑', '1', '1', '0', '1', '0', '1211', '10', '14', '0', '1500969975', '1500970772');
COMMIT;

-- ----------------------------
--  Table structure for `babies_document_nav`
-- ----------------------------
DROP TABLE IF EXISTS `babies_document_nav`;
CREATE TABLE `babies_document_nav` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `text` varchar(100) NOT NULL COMMENT '文档分类名称',
  `kind` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '分类类型，1表示多篇，0表示单篇',
  `content` text COMMENT '分类描述',
  `sort` int(10) unsigned NOT NULL COMMENT '菜单排序',
  `nnid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类父id',
  `isshow` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '默认显示，1表示显示，0表示不显示',
  `create` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='文档分类表';

-- ----------------------------
--  Records of `babies_document_nav`
-- ----------------------------
BEGIN;
INSERT INTO `babies_document_nav` VALUES ('2', '品牌优势', '1', '', '2', '0', '1', '1500963283'), ('3', '优质服务', '1', '', '3', '0', '1', '1500963296'), ('4', '赴美生子流程', '1', '', '4', '0', '1', '1500963343'), ('5', '美国月子中心', '1', '', '5', '0', '1', '1500963356'), ('6', '医院医生', '1', '', '6', '0', '1', '1500963367'), ('7', '套餐费用', '1', '', '7', '0', '1', '1500963406'), ('8', '案例中心', '1', '', '8', '0', '1', '1500963428'), ('9', '常见问题', '1', '', '9', '0', '1', '1500963443'), ('10', '关于我们', '1', '', '10', '0', '0', '1500964491'), ('11', '关于第一宝贝', '0', '关于第一宝贝', '11', '10', '1', '1500964416'), ('12', '服务团队', '0', '', '12', '10', '1', '1500964431'), ('13', '服务标准', '0', '', '13', '10', '1', '1500964439'), ('14', '安全中心', '0', '', '14', '10', '1', '1500964457'), ('15', '联系我们', '0', '', '15', '10', '1', '1500964449');
COMMIT;

-- ----------------------------
--  Table structure for `babies_group_rules`
-- ----------------------------
DROP TABLE IF EXISTS `babies_group_rules`;
CREATE TABLE `babies_group_rules` (
  `navdos` text NOT NULL COMMENT '菜单操作集合，序列化字符串',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '角色组id',
  UNIQUE KEY `groupid` (`groupid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色权限分配表';

-- ----------------------------
--  Records of `babies_group_rules`
-- ----------------------------
BEGIN;
INSERT INTO `babies_group_rules` VALUES ('a:6:{i:2;a:3:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:13:\"AuthGroup/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:14:\"AuthGroup/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:16:\"AuthGroup/remove\";}}i:3;a:4:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:10:\"Manage/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:11:\"Manage/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:13:\"Manage/remove\";}i:3;a:2:{s:4:\"text\";s:18:\"查看登陆日志\";s:3:\"url\";s:15:\"Manage/loginlog\";}}i:7;a:3:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:7:\"Nav/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:8:\"Nav/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:10:\"Nav/remove\";}}i:125;a:3:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:15:\"DocumentNav/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:16:\"DocumentNav/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:18:\"DocumentNav/remove\";}}i:126;a:3:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:12:\"Document/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:13:\"Document/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:15:\"Document/remove\";}}i:173;a:3:{i:0;a:2:{s:4:\"text\";s:6:\"添加\";s:3:\"url\";s:12:\"Question/add\";}i:1;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:13:\"Question/edit\";}i:2;a:2:{s:4:\"text\";s:6:\"删除\";s:3:\"url\";s:15:\"Question/remove\";}}}', '1'), ('a:1:{i:143;a:2:{i:0;a:2:{s:4:\"text\";s:6:\"修改\";s:3:\"url\";s:9:\"Shop/edit\";}i:1;a:2:{s:4:\"text\";s:12:\"店铺装修\";s:3:\"url\";s:15:\"Shop/decoration\";}}}', '14');
COMMIT;

-- ----------------------------
--  Table structure for `babies_manage`
-- ----------------------------
DROP TABLE IF EXISTS `babies_manage`;
CREATE TABLE `babies_manage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `manager` char(40) NOT NULL COMMENT '管理员账号，邮箱',
  `realname` char(15) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `password` char(40) NOT NULL COMMENT '管理员密码',
  `create` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `last_login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录的时间',
  `last_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录的IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  UNIQUE KEY `manager` (`manager`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
--  Records of `babies_manage`
-- ----------------------------
BEGIN;
INSERT INTO `babies_manage` VALUES ('2', 'rockyhu@gmail.com', '胡世金', '4fed6ab3fe8c357e11584a0ab9bf05ab2daa2bc7', '1478490660', '1500953649', '0');
COMMIT;

-- ----------------------------
--  Table structure for `babies_manage_login`
-- ----------------------------
DROP TABLE IF EXISTS `babies_manage_login`;
CREATE TABLE `babies_manage_login` (
  `manageid` int(10) unsigned NOT NULL COMMENT '管理员id',
  `loginip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆ip',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登陆时间',
  `loginlocation` char(50) NOT NULL DEFAULT '' COMMENT '登陆地点',
  KEY `manageid` (`manageid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员登陆记录表';

-- ----------------------------
--  Records of `babies_manage_login`
-- ----------------------------
BEGIN;
INSERT INTO `babies_manage_login` VALUES ('2', '0', '1500952069', ''), ('2', '0', '1500952800', ''), ('2', '0', '1500953649', '');
COMMIT;

-- ----------------------------
--  Table structure for `babies_nav`
-- ----------------------------
DROP TABLE IF EXISTS `babies_nav`;
CREATE TABLE `babies_nav` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `text` char(20) NOT NULL COMMENT '菜单名称',
  `state` char(10) NOT NULL COMMENT '菜单状态',
  `url` char(30) DEFAULT '' COMMENT '模块链接',
  `iconCls` char(30) NOT NULL COMMENT '图标',
  `ishide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '菜单状态，0表示显示，1表示隐藏',
  `nid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '菜单层次',
  `sort` int(10) unsigned NOT NULL COMMENT '菜单排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8 COMMENT='系统菜单表';

-- ----------------------------
--  Records of `babies_nav`
-- ----------------------------
BEGIN;
INSERT INTO `babies_nav` VALUES ('1', '系统管理', 'open', '', 'ion-social-windows', '0', '0', '1'), ('2', '权限控制', 'open', 'AuthGroup/index', 'ion-unlocked', '0', '1', '1'), ('3', '管理员管理', 'open', 'Manage/index', 'ion-person-stalker', '0', '1', '2'), ('7', '栏目管理', 'open', 'Nav/index', 'ion-ios-list-outline', '0', '1', '3'), ('124', '文档管理', 'open', '', 'ion-android-playstore', '0', '0', '4'), ('125', '文档分类', 'open', 'DocumentNav/index', 'ion-ios-albums-outline', '0', '124', '2'), ('126', '文档列表', 'open', 'Document/index', 'ion-bag', '0', '124', '3'), ('127', '公告留言', 'open', '', 'ion-ios-paper', '0', '0', '9'), ('136', '系统参数', 'open', 'System/index', 'ion-ios-gear-outline', '0', '1', '4'), ('162', '公众号管理', 'open', '', 'fa fa-wechat', '0', '0', '1'), ('163', '自定义菜单', 'open', 'Menu/index', 'fa-file-picture-o', '1', '162', '163'), ('173', '常见问题', 'open', 'Question/index', 'ion-ios-help', '0', '127', '173');
COMMIT;

-- ----------------------------
--  Table structure for `babies_nav_do`
-- ----------------------------
DROP TABLE IF EXISTS `babies_nav_do`;
CREATE TABLE `babies_nav_do` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自动编号',
  `text` char(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `url` char(50) NOT NULL DEFAULT '' COMMENT '操作编码或操作链接',
  `navid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '导航栏目id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否分配权限，0表示否，1表示是',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `navid` (`navid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COMMENT='栏目操作表';

-- ----------------------------
--  Records of `babies_nav_do`
-- ----------------------------
BEGIN;
INSERT INTO `babies_nav_do` VALUES ('15', '添加', 'AuthGroup/add', '2', '0'), ('16', '修改', 'AuthGroup/edit', '2', '0'), ('17', '删除', 'AuthGroup/remove', '2', '0'), ('18', '添加', 'Manage/add', '3', '0'), ('19', '修改', 'Manage/edit', '3', '0'), ('20', '删除', 'Manage/remove', '3', '0'), ('21', '查看登陆日志', 'Manage/loginlog', '3', '0'), ('22', '添加', 'Nav/add', '7', '0'), ('23', '修改', 'Nav/edit', '7', '0'), ('24', '删除', 'Nav/remove', '7', '0'), ('25', '添加', 'Agent/add', '106', '0'), ('26', '修改', 'Agent/edit', '106', '0'), ('27', '删除', 'Agent/remove', '106', '0'), ('29', '添加', 'User/add', '95', '0'), ('30', '修改', 'User/edit', '95', '0'), ('31', '修改密码', 'User/setUserPassword', '95', '0'), ('32', '修改推荐人', 'User/setUserReferee', '95', '0'), ('33', '修改真实姓名', 'User/setUserRealname', '95', '0'), ('34', '修改会员级别', 'User/setUserAgentlevel', '95', '0'), ('37', '添加', 'Shoplevel/add', '132', '0'), ('38', '修改', 'Shoplevel/edit', '132', '0'), ('39', '删除', 'Shoplevel/remove', '132', '0'), ('40', '通过', 'Cashout/edit', '107', '0'), ('41', '现金充值', 'Userpay/add', '115', '0'), ('42', '添加', 'DocumentNav/add', '125', '0'), ('43', '修改', 'DocumentNav/edit', '125', '0'), ('44', '删除', 'DocumentNav/remove', '125', '0'), ('45', '添加', 'Document/add', '126', '0'), ('46', '修改', 'Document/edit', '126', '0'), ('47', '删除', 'Document/remove', '126', '0'), ('48', '修改', 'Order/edit', '89', '0'), ('49', '添加', 'Message/add', '85', '0'), ('50', '回复', 'Message/reply', '85', '0'), ('51', '删除', 'Message/remove', '85', '0'), ('52', '添加', 'Notice/add', '96', '0'), ('53', '修改', 'Notice/edit', '96', '0'), ('54', '删除', 'Notice/remove', '96', '0'), ('56', '备注', 'UserAgentRecord/setText', '139', '0'), ('57', '添加', 'Clerk/add', '141', '0'), ('58', '修改', 'Clerk/edit', '141', '0'), ('59', '删除', 'Clerk/remove', '141', '0'), ('60', '删除', 'User/remove', '95', '0'), ('61', '添加', 'Shop/add', '143', '0'), ('62', '修改', 'Shop/edit', '143', '0'), ('63', '删除', 'Shop/remove', '143', '0'), ('64', '添加', 'Banque/add', '147', '0'), ('65', '修改', 'Banque/edit', '147', '0'), ('66', '删除', 'Banque/remove', '147', '0'), ('67', '审核', 'Userpay/check', '115', '0'), ('68', '账户补扣', 'Repair/add', '148', '0'), ('69', '添加', 'Promotion/add', '149', '0'), ('70', '按日期条件查询', 'Dialout/condition', '137', '0'), ('71', '按日期条件查询', 'Yejimonth/condition', '138', '0'), ('72', '代理级别升级', 'UserAgentRecord/upgrade', '139', '0'), ('73', '导出excel', 'User/isexport', '95', '0'), ('74', '导出excel', 'Purse/isexport', '133', '0'), ('75', '导出excel', 'Award/isexport', '117', '0'), ('76', '导出excel', 'Waityeji/isexport', '152', '0'), ('77', '导出excel', 'Settledyeji/isexport', '153', '0'), ('78', '发放工资', 'Award/isfafang', '117', '0'), ('79', '导出excel', 'Shop/isexport', '143', '0'), ('80', '导出excel', 'Order/isexport', '89', '0'), ('81', '设置为服务点', 'User/setUserShopCenter', '95', '0'), ('82', '添加', 'Merchant/add', '155', '0'), ('83', '修改', 'Merchant/edit', '155', '0'), ('84', '删除', 'Merchant/remove', '155', '0'), ('85', '绑定招商员', 'Shop/bindMerchant', '143', '0'), ('86', '修改', 'Reseller/edit', '157', '0'), ('87', '删除', 'Reseller/remove', '157', '0'), ('88', '关闭订单', 'Order/isclose', '89', '0'), ('89', '确认发货', 'Order/isdeliver', '89', '0'), ('90', '确认收货', 'Order/issend', '89', '0'), ('91', '确认退款', 'Order/isrefund', '89', '0'), ('92', '添加', 'Genlis/add', '159', '0'), ('93', '修改', 'Genlis/edit', '159', '0'), ('94', '删除', 'Genlis/remove', '159', '0'), ('95', '添加', 'ResellerLevel/add', '160', '0'), ('96', '修改', 'ResellerLevel/edit', '160', '0'), ('97', '删除', 'ResellerLevel/remove', '160', '0'), ('98', '添加', 'MerchantLevel/add', '161', '0'), ('99', '修改', 'MerchantLevel/edit', '161', '0'), ('100', '删除', 'MerchantLevel/remove', '161', '0'), ('101', '通过', 'ShopCashout/edit', '164', '0'), ('102', '添加', 'ShopKind/add', '165', '0'), ('103', '修改', 'ShopKind/edit', '165', '0'), ('104', '删除', 'ShopKind/remove', '165', '0'), ('105', '积分导入', 'Import/add', '166', '0'), ('106', '消费增值', 'Rebate/add', '167', '0'), ('107', '添加', 'Reseller/add', '157', '0'), ('108', '修改后台登陆密码', 'Shop/setShopPassword', '143', '0'), ('109', '修改', 'StoreOrder/edit', '169', '0'), ('110', '导出excel', 'StoreOrder/isexport', '169', '0'), ('111', '关闭订单并退款', 'Order/iscloserefund', '89', '0'), ('112', '添加', 'Settle/add', '170', '0'), ('113', '修改', 'Settle/edit', '170', '0'), ('114', '删除', 'Settle/remove', '170', '0'), ('115', '冻结&amp;解冻', 'User/freeze', '95', '0'), ('116', '店铺装修', 'Shop/decoration', '143', '0'), ('117', '周销售额统计', 'Saleroom/week', '172', '0'), ('118', '日销售额统计', 'Saleroom/day', '172', '0'), ('119', '导出excel', 'Merchant/isexport', '155', '0'), ('120', '导出excel', 'Reseller/isexport', '157', '0'), ('121', '添加', 'Question/add', '173', '0'), ('122', '修改', 'Question/edit', '173', '0'), ('123', '删除', 'Question/remove', '173', '0');
COMMIT;

-- ----------------------------
--  Table structure for `babies_question`
-- ----------------------------
DROP TABLE IF EXISTS `babies_question`;
CREATE TABLE `babies_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自动编号',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '问题名称',
  `content` text NOT NULL COMMENT '问题答案',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字，用,号分割开',
  `readcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读数',
  `saved` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '解决数统计',
  `nosaved` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未解决问题数',
  `create` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题发布时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='常见问题表';

-- ----------------------------
--  Table structure for `babies_question_tags`
-- ----------------------------
DROP TABLE IF EXISTS `babies_question_tags`;
CREATE TABLE `babies_question_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '问题标签',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签问题数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题标签表';

-- ----------------------------
--  Table structure for `babies_sms`
-- ----------------------------
DROP TABLE IF EXISTS `babies_sms`;
CREATE TABLE `babies_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `phone` char(11) CHARACTER SET latin1 NOT NULL COMMENT '手机号',
  `activecode` char(6) CHARACTER SET latin1 NOT NULL COMMENT '短信激活码',
  `create` int(10) unsigned NOT NULL COMMENT '创建时间',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '验证码状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信验证码表';

-- ----------------------------
--  Table structure for `babies_system`
-- ----------------------------
DROP TABLE IF EXISTS `babies_system`;
CREATE TABLE `babies_system` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自动编号',
  `shutdownstate` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统停用是否停用，0表示否，1表示是',
  `shutdowntitle` char(50) NOT NULL DEFAULT '' COMMENT '维护页面标题',
  `shutdowncontent` char(100) NOT NULL DEFAULT '' COMMENT '维护页面内容',
  `create` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新的时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='系统参数表';

-- ----------------------------
--  Records of `babies_system`
-- ----------------------------
BEGIN;
INSERT INTO `babies_system` VALUES ('1', '0', '系统维护', '亲爱的家人，系统正在维护中！给您带来的不便请谅解，我们将尽快完成维护工作，请耐心等候！若您有疑问请咨询客服，客服电话:0755-21002719。', '1490256727');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
