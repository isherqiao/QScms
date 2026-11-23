<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         verify.php
 * @CreatedTime  18/04/25 17:21:00
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Email verification and account security module: token checks, login/operation logging and client-IP resolution.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

//verify.php

// 全局统一开启 session（最关键修复）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 写入登录日志
 * @param string $username 账号
 * @param int $status 1成功 0失败
 * @param string $msg 附加信息
 */
//日志相关
function get_client_ip()
{
    // 如果后端前面是可信反向代理，才解析X‑Forwarded‑For；没有代理优先REMOTE_ADDR
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // 仅当是本地代理过来的时候，才解析X‑Forwarded‑For，避免公网用户伪造
    if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.')) {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $realIp = trim($arr[0]);
            if(filter_var($realIp, FILTER_VALIDATE_IP)){
                $ip = $realIp;
            }
        }
    }
    return $ip;
}
function write_login_log($username, $status, $msg)
{
    $logFile = DATA_LOG_PATH . 'login_log.php';
    $logDir = dirname($logFile);

    //递归创建目录，0755
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    //目录不可写直接返回，不阻断业务
    if (!is_writable($logDir)) {
        return;
    }

    if(!file_exists($logFile)){
        $init = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$login_log = [];\n";
        file_put_contents($logFile,$init);
    }
    //初始化失败，跳过require，防止Fatal
    if(!file_exists($logFile)){
        return;
    }

    require $logFile;
if (!isset($operate_log) || !is_array($operate_log)) {
    $operate_log = [];
}
    $item = [
        'time'=>time(),
        'username'=>$username,
        'status'=>$status,
        'ip'=>get_client_ip(),
        'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? '',
        'msg'=>$msg
    ];
    array_unshift($login_log,$item);
    //最多保留2000条
    if(count($login_log)>2000){
        $login_log = array_slice($login_log,0,2000);
    }
    $export = var_export($login_log,true);
    $code = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$login_log = ".$export.";\n";
    file_put_contents($logFile,$code);
}

function write_operate_log($username, $type, $target, $msg)
{
    $logFile = DATA_LOG_PATH . 'operate_log.php';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    if (!is_writable($logDir)) {
        return;
    }

    if(!file_exists($logFile)){
        $init = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$operate_log = [];\n";
        file_put_contents($logFile,$init);
    }
    if(!file_exists($logFile)){
        return;
    }

    require $logFile;
if (!isset($operate_log) || !is_array($operate_log)) {
    $operate_log = [];
}
    $item = [
        'time'=>time(),
        'username'=>$username,
        'type'=>$type,
        'target'=>$target,
        'ip'=>get_client_ip(),
        'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? '',
        'msg'=>$msg
    ];
    array_unshift($operate_log,$item);
    if(count($operate_log)>2000){
        $operate_log = array_slice($operate_log,0,2000);
    }
    $export = var_export($operate_log,true);
    $code = "<?php\nif(!defined('SITE_ROOT'))exit;\n\$operate_log = ".$export.";\n";
    file_put_contents($logFile,$code);
}

/**
 * 获取当前后台登录用户名，兼容超级管理员 / 子账号
 * @return string
 */
function qs_get_current_admin_name()
{
    if(isset($_SESSION['Qsis_adminname'])){
        return $_SESSION['Qsis_adminname'];
    }
    if(isset($_SESSION['Qsis_user_name'])){
        return $_SESSION['Qsis_user_name'];
    }
    return 'unknown';
}

function Qsis_token() {
    global $sp_config, $qs_config;
    $user_dotip = $_SERVER['REMOTE_ADDR'];
    $user_ip = str_replace('.', '', $user_dotip);
    $user_number = base64_encode($user_ip);
    $nick_code = $qs_config['nick_code'] ?? '';
    return md5($sp_config['user_name'] . $sp_config['user_pass'] . $user_number . $nick_code);
}

function hash_admin_password($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

function verify_admin_password($input, $stored) {
    return password_verify($input, $stored);
}

function encrypt_github_token($token, $password, $username, $nick_code) {
    $key = hash('sha256', $password . '_' . $username . '_' . $nick_code);
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($token, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt_github_token($encrypted, $password, $username, $nick_code) {
    $key = hash('sha256', $password . '_' . $username . '_' . $nick_code);
    $raw = base64_decode($encrypted);
    $iv = substr($raw, 0, 16);
    $data = substr($raw, 16);
    return openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

// 管理员统一验证
function qs_Pass($url = '') {
    global $sp_config, $qs_config, $controldata_url;

    if (isset($_SESSION['Qsis_token']) && $_SESSION['Qsis_token'] === Qsis_token()) {
        if (!empty($url)) {
            header("Location: " . qs_admin_base() . $controldata_url . $url);
            exit;
        }
        return true;
    }

    if (isset($_COOKIE['Qsis_token']) && $_COOKIE['Qsis_token'] === Qsis_token()) {
        $_SESSION['Qsis_token'] = $_COOKIE['Qsis_token'];
        if (!empty($url)) {
            header("Location: " . qs_admin_base() . $controldata_url . $url);
            exit;
        }
        return true;
    }

    return false;
}

// 统一登录函数（删除冲突函数，只留这一个）
function qs_Login() {
    global $sp_config, $sp_config_users, $qs_config, $controldata_url, $captcha;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) return;

    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    // 验证码校验（off 状态跳过；其余情况由当前启用的验证码插件提供）
    if (!empty($captcha) && empty($GLOBALS['_captcha_off'])) {
        $captchaPassed = qs_captcha_boot() && captcha_plugin_check($_POST);
        if (!$captchaPassed) {
            echo "<div style='color:red'>验证码错误</div>";
            write_login_log($user,0,'登录：验证码错误');
            return;
        }
    }

    // 管理员登录
    if ($user === $sp_config['user_name']) {
        if (verify_admin_password($pass, $sp_config['user_pass'])) {
            // 清空旧 session
            session_regenerate_id(true);
            $_SESSION['Qsis_adminname'] = $user;
            $_SESSION['Qsis_role'] = 'admin';
            $_SESSION['admin_level'] = '0';
            $_SESSION['Qsis_token'] = Qsis_token();
             write_login_log($user,1,'超级管理员后台登录成功');

            header("Location: " . qs_admin_base() . $controldata_url . "/set.php");
            exit;
        } else {
            echo "<div style='color:red'>管理员密码错误</div>";
             write_login_log($user,0,'超级管理员登录：密码错误');
            return;
        }
    }

    // 普通用户登录
    if (isset($sp_config_users[$user])) {
        $stored = $sp_config_users[$user]['qsis_userpassword'] ?? '';
        if (verify_password_complex($pass, $stored)) {
            session_regenerate_id(true);
            $_SESSION['Qsis_user_name'] = $user;
            $_SESSION['user_role'] = qs_user_role($user);
            $_SESSION['admin_level'] = qs_user_admin_level($user);
            $_SESSION['Qsis_user_token'] = Qsis_user_token($user);
            $_SESSION['user_displayname'] = $sp_config_users[$user]['displayname'] ?? '';
            write_login_log($user,1,'user后台登录成功');

            // 按功能权限决定登录后首页：无文章权限的一般用户进入个人信息页
            $loginHome = qs_can('post_manage') ? "/post.php" : "/user/userset.php";
            header("Location: " . qs_admin_base() . $controldata_url . $loginHome);
            exit;
        } else {
            echo "<div style='color:red'>密码错误</div>";
            write_login_log($user,0,'子账号登录：密码错误');
            return;
        }
    }

    echo "<div style='color:red'>用户名不存在</div>";
    write_login_log($user,0,'登录：用户名不存在');
    return;
}

function hash_password_complex($password) {
    $salt = bin2hex(random_bytes(16));
    $iterations = 10000;
    $hash = $password . $salt;
    for ($i = 0; $i < $iterations; $i++) {
        $hash = hash('sha256', $hash);
    }
    return $salt . '$' . $hash;
}

function verify_password_complex($input_password, $stored) {
    if (strpos($stored, '$') === false) return false;
    list($salt, $stored_hash) = explode('$', $stored);
    $iterations = 10000;
    $hash = $input_password . $salt;
    for ($i = 0; $i < $iterations; $i++) {
        $hash = hash('sha256', $hash);
    }
    return hash_equals($stored_hash, $hash);
}

function Qsis_user_token($u = null) {
    global $sp_config_users, $qs_config;
    if ($u === null) $u = $_SESSION['Qsis_user_name'] ?? null;
    if (!$u || !isset($sp_config_users[$u])) return '';

    $user_dotip = $_SERVER['REMOTE_ADDR'];
    $user_ip = str_replace('.', '', $user_dotip);
    $user_number = base64_encode($user_ip);
    $nick_code = $qs_config['nick_code'] ?? '';
    $password = $sp_config_users[$u]['qsis_userpassword'] ?? '';

    return md5($password . $user_number . $nick_code);
}

// 用户验证
function qs_UserPass($url = '') {
    global $controldata_url;
    if (!isset($_SESSION['Qsis_user_token'])) return false;
    if ($_SESSION['Qsis_user_token'] === Qsis_user_token()) {
        if (!empty($url)) {
            header("Location: " . qs_admin_base() . $controldata_url . $url);
            exit;
        }
        return true;
    }
    return false;
}

function is_admin($max_level = 99) {
    $level = $_SESSION['admin_level'] ?? 99;
    if ($level === '0') return true;
    return intval($level) <= intval($max_level);
}

function render_admin_level_select($name = 'admin_level', $selected = null) {
    $func = new ReflectionFunction('is_admin');
    $default_max_level = $func->getParameters()[0]->getDefaultValue() ?? 99;
    $html = "<select name=\"$name\">\n";
    $html .= "<option value=\"Not_an_admin\"" . ($selected == 'Not_an_admin' ? ' selected' : '') . ">" . L('Not_an_admin') . "</option>\n";
    for ($i = 0; $i <= $default_max_level; $i++) {
        $html .= "<option value=\"$i\"" . ($selected == $i ? ' selected' : '') . ">admin_level $i</option>\n";
    }
    $html .= "</select><div>0 最高权限，$default_max_level 最低</div>";
    return $html;
}

function is_developer() {
    return ($_SESSION['admin_level'] ?? '') === '0';
}

// ============================================================
// 新的按功能授权体系
// ============================================================

// 获取某用户所属的用户组（未设置时返回空数组）
function qs_user_groups($username) {
    global $sp_config_users;
    if (!isset($sp_config_users[$username])) return [];
    $groups = $sp_config_users[$username]['groups'] ?? [];
    return is_array($groups) ? array_values($groups) : [];
}

// 用户显示角色：由所属组推导（含全权限组→SuperAdmin，否则默认 member）。
// 仅供后台展示（会话 user_role），不参与任何鉴权判定，避免越权。
function qs_user_role($username) {
    global $qs_groups;
    foreach (qs_user_groups($username) as $g) {
        if (in_array('*', $qs_groups[$g]['perms'] ?? [])) return 'SuperAdmin';
    }
    return 'member';
}

// 用户 admin_level：新模型下由所属组推导（含全权限组→'0'，否则 '99'）；
// 存量用户（未设置 groups）沿用存储的 admin_level 兜底，避免权限回退。
function qs_user_admin_level($username) {
    global $qs_groups, $sp_config_users;
    $groups = qs_user_groups($username);
    if (!empty($groups)) {
        foreach ($groups as $g) {
            if (in_array('*', $qs_groups[$g]['perms'] ?? [])) return '0';
        }
        return '99';
    }
    return $sp_config_users[$username]['admin_level'] ?? '99';
}

// 存量用户（未设置 groups）按旧的 admin_level 折算功能，保证升级后权限不回退
// 折算规则忠实复刻原 is_admin() 各阈值：
//   0   => 全部
//   <=1 => 完整后台设置
//   <=2 => 网页/插件
//   <=3 => 备份
//   <=5 => 留言
//   基础（任何用户）：文章、版权信息、我的个人信息
function qs_legacy_perms($username) {
    global $sp_config_users;
    $level = $sp_config_users[$username]['admin_level'] ?? 99;
    if ($level === '0' || $level === 0) return ['*'];

    $perms = ['post_manage', 'copyright', 'user_set'];
    $lvl = is_numeric($level) ? (int)$level : 99;

    if ($lvl <= 5) $perms[] = 'messages';
    if ($lvl <= 3) { $perms[] = 'backup_system'; $perms[] = 'set_backup'; }
    if ($lvl <= 2) { $perms[] = 'page_manage'; $perms[] = 'plugins'; }
    if ($lvl <= 1) {
        $perms[] = 'system_setting';
        $perms = array_merge($perms, [
            'set_basic', 'set_articles', 'set_account', 'set_extensions',
            'set_menu', 'set_link', 'set_backend', 'set_bookgenres',
            'set_ssg', 'set_github', 'set_remote', 'set_api',
            'mail_setting', 'picmanage', 'set_markdown', 'multi_ssg',
            'lang_editor', 'theme_editor',
        ]);
    }
    return array_values(array_unique($perms));
}

// 计算某用户的"有效功能"集合。
// 规则：若已设置 groups，则 有效 = (各组权限 ∪ 个人追加 perms) − 个人禁用 deny_perms；
//       否则回退到 qs_legacy_perms 按 admin_level 折算。
// 返回数组；若包含 '*' 表示拥有全部功能。
function qs_effective_perms($username) {
    global $sp_config_users, $qs_groups;

    if (!isset($sp_config_users[$username])) return [];

    $user = $sp_config_users[$username];

    // 新模型：已设置 groups
    if (array_key_exists('groups', $user)) {
        $perms = [];
        foreach (qs_user_groups($username) as $g) {
            $gperms = $qs_groups[$g]['perms'] ?? [];
            $perms = array_merge($perms, $gperms);
        }
        // 个人追加（可在"功能分配"中额外勾选）
        $extra = $user['perms'] ?? [];
        if (is_array($extra)) $perms = array_merge($perms, $extra);

        // 个人禁用（明确去掉某些功能）
        $deny = $user['deny_perms'] ?? [];
        if (is_array($deny) && !empty($deny)) {
            $perms = array_values(array_diff($perms, $deny));
        }
        $perms = array_values(array_unique($perms));

        // 保留通配语义
        if (in_array('*', $perms)) return ['*'];
        return $perms;
    }

    // 旧模型回退
    return qs_legacy_perms($username);
}

// 当前登录用户是否拥有指定功能。超级管理员恒为真。
// $perm 为功能标识，如 'post_manage'；传 '*' 时表示判断是否超级管理员。
function qs_can($perm = '*') {
    global $qs_perms;

    // 超级管理员主账号（Qsis_adminname）
    if (!empty($_SESSION['Qsis_adminname'] ?? '')) return true;
    // 旧数值体系中 level 0 亦视为超级管理员
    if (($_SESSION['admin_level'] ?? '') === '0') return true;

    $username = $_SESSION['Qsis_user_name'] ?? '';
    if ($username === '') return false;

    $perms = qs_effective_perms($username);
    if (in_array('*', $perms)) return true;

    return in_array($perm, $perms);
}

// 保存 $sp_config_users 回 confp.php（统一封装，供各管理页复用）
function qs_save_users() {
    global $sp_config_users, $datapaths, $config_user;
    $content = "<?php\n\$sp_config_users = " . var_export($sp_config_users, true) . ";\n?>";
    return file_put_contents($datapaths . $config_user, $content, LOCK_EX);
}

class Crypto {
    private static $key = 'a4o4o5ibcv5jbzyt@5345qerasfpopoti[pdfuhvav';
    private static $cipher = 'AES-256-CBC';

    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $encrypted = openssl_encrypt($data, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encrypted) {
        $raw = base64_decode($encrypted);
        $ivLen = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($raw, 0, $ivLen);
        $data = substr($raw, $ivLen);
        return openssl_decrypt($data, self::$cipher, self::$key, OPENSSL_RAW_DATA, $iv);
    }
}
?>