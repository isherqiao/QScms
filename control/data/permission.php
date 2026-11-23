<?php
/**
 * QsCms 功能权限配置
 *
 * 本文件定义后台"功能清单"与"用户组"，是新的按功能授权体系的数据源。
 * - $qs_perms      功能清单（按类别分组），对应后台主菜单与 set.php 各栏目
 * - $qs_groups     用户组定义（组名 => 拥有的功能数组）
 * - $qs_page_perm  页面级守卫映射：后台入口页 => 所需功能
 * - $qs_default_group 前台注册默认分组
 *
 * 判定函数在 control/verify.php：qs_can() / qs_effective_perms() / qs_legacy_perms()
 * 授权规则：超级管理员('*') 通配；用户有效权限 = (所属组权限 ∪ 个人追加权限) − 个人禁用权限
 *
 * @copyright 2016-2026 乔硕 (isher) All rights reserved.
 */

// ============================================================
// 一、功能清单（按类别分组）。key 为功能标识，value 为中文说明
// ============================================================
$qs_perm_groups = [
    '内容管理' => [
        'post_manage'   => '文章管理（文章列表 / 新建 / 编辑）',
        'page_manage'   => '网页管理（页面列表 / 新建 / 编辑）',
        'set_markdown'  => 'Markdown 工具',
        'picmanage'     => '图片管理',
        'set_bookgenres'=> '小说分类（仅在小说模式下显示）',
        'mail_setting'  => '邮件设置',
        'messages'      => '留言查看',
        'vote_manage'   => '投票管理（投票列表 / 新建 / 编辑 / 记录查看）',
        'keyword_filter' => '关键词过滤管理',
    ],
    '系统设置' => [
        'system_setting' => '系统设置总入口（set.php）',
        'set_basic'      => '设置-基础参数',
        'set_articles'   => '设置-文章设置',
        'set_account'    => '设置-账号相关',
        'set_extensions' => '设置-扩展代码',
        'set_menu'       => '设置-菜单控制',
        'set_link'       => '设置-友情链接',
        'set_backend'    => '设置-后台控制',
    ],
    '系统运维' => [
        'backup_system' => '备份系统（backups.php）',
        'backup_verify' => '配置备份到验证服务器（backup_verify.php）',
        'set_backup'    => '设置-备份系统',
        'log_operate'   => '操作日志',
        'set_github'    => '设置-GitHub 同步',
        'set_ssg'       => '设置-静态站 SSG',
        'set_remote'    => '设置-远程文章源',
        'set_api'       => '设置-API 接口',
        'multi_ssg'     => '设置-多静态站',
        'lang_editor'   => '语言文件编辑',
        'theme_editor'  => '主题代码编辑',
    ],
    '用户与权限' => [
        'user_manage'   => '用户管理（用户列表 / 添加 / 编辑 / 删除）',
        'user_set'      => '我的个人信息（userset.php）',
        'group_manage'  => '用户组管理',
        'perm_manage'   => '功能分配',
    ],
    '其他' => [
        'plugins'   => '插件管理',
        'ads'       => '广告',
        'advset'    => '广告设置',
        'copyright' => '版权信息（version.php）',
    ],
];

// 展开为扁平表，便于快速遍历
$qs_perms = [];
foreach ($qs_perm_groups as $cat => $items) {
    foreach ($items as $key => $label) {
        $qs_perms[$key] = ['name' => $label, 'group' => $cat];
    }
}

// ============================================================
// 二、用户组定义。组名 => ['perms'=>功能数组, 'desc'=>说明]
// 'perms' 中的 '*' 表示拥有全部功能（超级管理员）
// ============================================================
$qs_groups = [
    '超级管理员' => [
        'perms' => ['*'],
        'desc'  => '拥有后台全部功能',
    ],
    '作者' => [
        'perms' => ['post_manage', 'set_articles', 'set_markdown', 'set_bookgenres', 'picmanage', 'copyright'],
        'desc'  => '可创作与管理文章、图片',
    ],
    '编辑' => [
        'perms' => ['post_manage', 'page_manage', 'set_articles', 'set_account', 'set_extensions', 'set_menu', 'set_link', 'set_markdown', 'set_bookgenres', 'picmanage', 'mail_setting', 'messages', 'vote_manage', 'keyword_filter', 'user_set', 'copyright'],
        'desc'  => '可管理文章、网页与站点结构',
    ],
    '用户' => [
        'perms' => ['user_set'],
        'desc'  => '注册默认分组，登录后仅能看到个人信息',
    ],
    '备份运维管理员' => [
        'perms' => ['backup_system', 'backup_verify', 'set_backup', 'log_operate', 'messages', 'set_github', 'set_ssg', 'set_remote', 'set_api', 'user_set', 'copyright'],
        'desc'  => '负责备份与系统运维',
    ],
];

// 前台注册成功后的默认分组
$qs_default_group = '用户';

// ============================================================
// 三、页面级守卫映射：后台入口页(相对 admin/) => 所需功能
// ============================================================
$qs_page_perm = [
    'post.php'                      => 'post_manage',
    'post-edit.php'                 => 'post_manage',
    'page.php'                      => 'page_manage',
    'page-edit.php'                 => 'page_manage',
    'set.php'                       => 'system_setting',
    'plugins.php'                   => 'plugins',
    'backups.php'                   => 'backup_system',
    'backup_verify.php'             => 'backup_verify',
    'ads.php'                       => 'ads',
    'Adv/advset.php'                => 'advset',
    'log.php'                       => 'log_operate',
    'version.php'                   => 'copyright',
    'liuyanshow.php'                => 'messages',
    'vote.php'                      => 'vote_manage',
    'vote_edit.php'                 => 'vote_manage',
    'keywords/keywords_manage.php'  => 'keyword_filter',
    'mail/mail_setting.php'         => 'mail_setting',
    'picmanage.php'                 => 'picmanage',
    'set_markdown.php'              => 'set_markdown',
    'set_multi_ssg.php'             => 'multi_ssg',
    'lang_editor.php'               => 'lang_editor',
    'theme_code_editor.php'         => 'theme_editor',
    'user/userset.php'              => 'user_set',
    'user/sysset.php'               => 'user_set',
    'user/user_manage.php'          => 'user_manage',
    'user/user_manage_add_user.php' => 'user_manage',
    'user/user_manage_edit_user.php'=> 'user_manage',
    'user/user_manage_delete_user.php' => 'user_manage',
    'user/group_manage.php'         => 'group_manage',
    'user/perm_manage.php'          => 'perm_manage',
];
?>
