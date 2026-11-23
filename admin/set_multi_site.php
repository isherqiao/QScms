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
 * @File         set_multi_site.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 01/09/26 19:20:00
 * @Description  Multi-site settings: per-site domain, theme and language; fields not shown on the page are preserved when saving.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

require 'head.php';
$msg = '';
$msgType = ''; // success / error
function h($val):string{
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES);
}
$multiItems = $datapaths . $staticmultisites;

$getPost = function($arr, $key, $default = '') {
    if (!is_array($arr) || !array_key_exists($key, $arr)) return $default;
    return is_array($arr[$key]) ? $arr[$key] : trim((string)$arr[$key]);
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postedSites = $_POST['sites'] ?? [];

    if (!is_array($postedSites)) {
        $msg = L('Invalid_Site_Data_Format');
        $msgType = 'error';
    } else {
        // 只更新页面真正编辑的字段。
        // 页面没有显示的旧配置必须保留，不能因为未提交而被清空。
        $editableSsgFields = [
            'static_root_url',
            'static_output',
            'static_theme_name',
            'static_sitelogo',
            'static_ico',
            'static_per_page',
            'static_box_post_number',
            'static_url_ends',
            'static_footer_code',
            'static_other_code',
            'static_head_code',
            'static_site_tags',
            'static_language_name',
            'static_language_file',
            'IndexNow',
            'static_robots',
            'google_ads',
        ];

        $boolFields = [
            'gen_robots',
            'gen_sitemap',
            'gen_rss',
            'gen_search_index',
            'gen_page_404',
            'gen_urls_txt',
            'gen_cname',
            'gen_indexnow',
        ];

        // 现在表单必须提交为 sites[0][...]、sites[1][...]……。
        // 不再依赖 JS 在 submit 时临时重写 name，也不尝试猜测错误的 sites[][] 数据。
        $normalizedSites = [];
        $invalidCount = 0;
        foreach ($postedSites as $site) {
            if (!is_array($site) || !isset($site['id']) || trim((string)$site['id']) === '') {
                $invalidCount++;
                continue;
            }
            $normalizedSites[] = $site;
        }

        // 只要出现无效站点，就拒绝整次保存，避免一次异常 POST 删除/覆盖正常配置。
        if ($invalidCount > 0) {
            $msg = L('Invalid_Site_Data_Received_Refused_Save');
            $msgType = 'error';
            $normalizedSites = [];
        }

        // 读取原配置，用于保留页面没有展示的配置项。
        $oldSites = [];
        if (file_exists($multiItems)) {
            // 配置文件实际定义的是 $multi_sites，不是 $old_multi_sites。
            // 先清掉同名变量，避免 require 时受到当前作用域影响。
            unset($multi_sites);
            require $multiItems;
            if (isset($multi_sites) && is_array($multi_sites)) {
                $oldSites = $multi_sites;
            }
            unset($multi_sites);
        }

        // 旧配置按站点 ID 建索引。这样删除/新增站点后，顺序变化也不会把 A 站点的配置套到 B 站点。
        $oldById = [];
        foreach ($oldSites as $oldSite) {
            if (is_array($oldSite) && !empty($oldSite['id'])) {
                $oldById[(string)$oldSite['id']] = $oldSite;
            }
        }

        $savedSiteList = [];

        foreach ($normalizedSites as $site) {
            $siteIdFromPost = trim((string)($site['id'] ?? ''));
            $old = $oldById[$siteIdFromPost] ?? [];

            $siteId = $siteIdFromPost !== '' ? $siteIdFromPost : ($old['id'] ?? '');
            $siteName = $getPost($site, 'name', $old['name'] ?? '');
            $siteTitle = $getPost($site, 'title', $old['title'] ?? '');
            $filterTags = $getPost($site, 'filter_tags', $old['filter_tags'] ?? '');

            // enable 必须按当前复选框状态保存，未勾选就是 false。
            $site['id'] = $siteId;
            $site['name'] = $siteName;
            $site['title'] = $siteTitle;
            $site['filter_tags'] = $filterTags;
            $site['enable'] = isset($site['enable']);

            $oldSsg = is_array($old['ssg'] ?? null) ? $old['ssg'] : [];
            $postedSsg = is_array($site['ssg'] ?? null) ? $site['ssg'] : [];
            $site['ssg'] = $oldSsg;

            foreach ($editableSsgFields as $field) {
                if (array_key_exists($field, $postedSsg)) {
                    $site['ssg'][$field] = $getPost($postedSsg, $field, '');
                }
            }

            // 页面上的复选框没有 name 嵌套在 ssg 中，而是在 site 下。
            foreach ($boolFields as $field) {
                $site['ssg'][$field] = isset($site[$field]);
                unset($site[$field]);
            }
// ============================================================
// GitHub Sync 配置
// 每个站点拥有自己独立的 GitHub 配置
// repo / token / branch 均直接保存为文本
// ============================================================

$oldGithub = is_array($old['github_sync'] ?? null)
    ? $old['github_sync']
    : [];

// 先拿到原始明文token
$rawToken = $getPost($site, 'gh_token', '');

$postedGithub = [
    'repo'   => $getPost($site, 'gh_repo', $oldGithub['repo'] ?? ''),
    'token'  => $oldGithub['token'] ?? '',
    'branch' => $getPost($site, 'gh_branch', $oldGithub['branch'] ?? 'gh-pages'),
];

// 用户输入了新 Token，才重新加密
if ($rawToken !== '') {
    $postedGithub['token'] = Crypto::encrypt(
        $rawToken,
        $sp_config['user_pass']
    );
}

$site['github_sync'] = $postedGithub;

unset(
    $site['gh_repo'],
    $site['gh_token'],
    $site['gh_branch']
);

            $savedSiteList[] = $site;
        }

        // 没有任何有效站点时不覆盖原配置，防止异常 POST 直接清空配置。
        if ($msgType === 'error') {
            // 已在上面给出错误信息，不写文件。
        } elseif (empty($multiItems) && !empty($oldSites)) {
            $msg = L('No_Valid_Site_Data_Refused_Overwrite');
            $msgType = 'error';
        } else {
            $staticMultiCode  = "<?php\n";
            $staticMultiCode .= "// Multi site config (独立多站点配置文件)\n";
            $staticMultiCode .= "\$multi_sites = " . var_export($savedSiteList, true) . ";\n";
            $staticMultiCode .= "?>\n";

            // LOCK_EX 防止并发写入；先写临时文件，再原子替换，避免配置文件写到一半。
            $tmpFile = $multiItems . '.tmp.' . getmypid();
            $written = file_put_contents($tmpFile, $staticMultiCode, LOCK_EX);

            if ($written !== false && rename($tmpFile, $multiItems)) {
                $msg = L('settings_saved_successfully!');
                $msgType = 'success';
                write_operate_log(
                    qs_get_current_admin_name(),
                    'save_multi_site_config',
                    L('Log_Target_Multi_Site_Config'),
                    L('Log_Msg_Modify_Multi_Site_SSG_Config')
                );
            } else {
                if (file_exists($tmpFile)) {
                    @unlink($tmpFile);
                }
                $msg = L('File_Write_Failed_Check_Multisite_Config_Permission');
                $msgType = 'error';
            }
        }
    }
}

//读取多站点数组，文件不存在则为空数组
$multiSites = [];
if(file_exists($multiItems)){
    require $multiItems;
    if(isset($multi_sites) && is_array($multi_sites)){
        $multiSites = $multi_sites;
    }
}
//主题目录扫描，和原版保持一致
$theme_list = [];
$canScan = true;
if(is_dir($static_theme_dir)){
    $folders = glob($static_theme_dir . '*', GLOB_ONLYDIR);
    if($folders !== false){
        foreach($folders as $fullpath){
            $theme_list[] = basename($fullpath);
        }
    }else{
        // glob扫描失败
        $canScan = false;
    }
} else {
	echo 'Static theme directory does not exist. Please reinstall or copy‑overwrite the directory and styles to the current program.';
	}
// 容错补充：没有识别到任何主题文件夹，预置默认值
if(empty($theme_list)){
    $theme_list[] = 'Qsiscms';
}
// 当前选中主题
$current_theme = $static_theme_name ?? $theme_name ?? 'Qsiscms';
?>
<div class="admin_page_name">
	<a href="set.php"><?= L('system_settings'); ?></a> 
    > 
<a href=set_multi_ssg.php  <?php echo $page_file == 'set_multi_ssg.php' ? 'class="current"' : ''; ?>><?= L('Total_Config_Items'); ?></a>
<a href=set_multi_site.php  <?php echo $page_file == 'set_multi_site.php' ? 'class="current"' : ''; ?>><?= L('multi_site_manage'); ?></a>
</div>

        <div class="small_form small_form2">
        	<form method="post" action="">
	<?php if ($msg): ?>
<div style="padding:10px;margin:8px 0;border:1px solid <?=$msgType==='success'?'#28a745':'#dc3545'?>;background:<?=$msgType==='success'?'#e8f5e9':'#ffebee'?>;">
<?=h($msg)?>
</div>
<?php endif; ?>
            <div style="margin:8px 0;">
                <button type="button" class="button" onclick="addNewSiteCard()"><?= L('Add_Site_Button'); ?></button>
                <button type="submit" class="button"><?= L('Save_All_Sites'); ?></button>
                <button type="button" class="button" onclick="batchGenerateAllSites()"><?= L('Batch_Generate_All_Enabled_Sites'); ?></button>
            </div>
            <div class="clear"></div>

            <!-- 唯一卡片容器 -->
            <div id="site_cards_container">
<?php foreach ($multiSites as $siteIndex => $site): ?>
<?php
        $ssg = $site['ssg'] ?? [];
        $gh  = $site['github_sync'] ?? [];
        // filter_tags 表单为逗号分隔字符串，直接取值
        $filterTagsStr = $site['filter_tags'] ?? '';
    ?>
    <div class="site-card" data-site-index="<?= (int)$siteIndex ?>" style="border:1px #ccc solid;padding:12px;margin-bottom:16px;">
        <div class="card-header">
            <h4 class="site-name" style="display:inline-block;margin:0 12px 8px 0;"><?=h($site['name'])?></h4>
            <label class="switch">
                <input type="checkbox" class="site-enable" name="sites[<?= (int)$siteIndex ?>][enable]" <?= !empty($site['enable']) ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
            <button class="button" type="button" onclick="removeSiteCard(this)"><?= L('Delete'); ?></button>
            <div class="clear"></div>
        </div>
        <div class="card-tabs" style="margin:8px 0;">
            <a class="card-tab-link button active" data-subtab="ssg"><?= L('SSG_Config_Tab'); ?></a>
            <a class="card-tab-link button" data-subtab="github"><?= L('GitHub_Sync_Config_Tab'); ?></a>
        </div>
        <!-- ===== SSG子面板，完整还原原版全部字段 ===== -->
        <div class="card-subtab-content" data-subtab="ssg">
            <div class="field">
                <div class="label"><?= L('Site_ID_Label_Tip'); ?></div>
                <div class="price">
                    <input class="input site-id" readonly name="sites[<?= (int)$siteIndex ?>][id]" value="<?=h($site['id'])?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('Site_Display_Name_Label'); ?></div>
                <div class="price">
                    <input class="input site-display-name" name="sites[<?= (int)$siteIndex ?>][name]" value="<?=h($site['name'])?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('Site_Tags_Label_Tip'); ?></div>
                <div class="price">
                    <input class="input site-filter-tags" name="sites[<?= (int)$siteIndex ?>][filter_tags]" value="<?=h($filterTagsStr)?>">
                </div>
                <div class="clear"></div>
            </div>
            <div style="margin:6px 0;">
                <label><input type="checkbox" class="ssg-gen-robots" name="sites[<?= (int)$siteIndex ?>][gen_robots]" <?=!empty($ssg['gen_robots'])?'checked':''?>><?= L('Generate_Robots_Txt'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-sitemap" name="sites[<?= (int)$siteIndex ?>][gen_sitemap]" <?=!empty($ssg['gen_sitemap'])?'checked':''?>><?= L('Generate_Sitemap_Xml'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-rss" name="sites[<?= (int)$siteIndex ?>][gen_rss]" <?=!empty($ssg['gen_rss'])?'checked':''?>><?= L('Generate_Rss_Xml'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-search_index" name="sites[<?= (int)$siteIndex ?>][gen_search_index]" <?=!empty($ssg['gen_search_index'])?'checked':''?>><?= L('Generate_Search_Index_Json'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-page_404" name="sites[<?= (int)$siteIndex ?>][gen_page_404]" <?=!empty($ssg['gen_page_404'])?'checked':''?>><?= L('Generate_Page_404_Html'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-urls_txt" name="sites[<?= (int)$siteIndex ?>][gen_urls_txt]" <?=!empty($ssg['gen_urls_txt'])?'checked':''?>><?= L('Generate_Urls_Txt'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-cname" name="sites[<?= (int)$siteIndex ?>][gen_cname]" <?=!empty($ssg['gen_cname'])?'checked':''?>><?= L('Generate_Cname'); ?></label><br>
                <label><input type="checkbox" class="ssg-gen-indexnow" name="sites[<?= (int)$siteIndex ?>][gen_indexnow]" <?=!empty($ssg['gen_indexnow'])?'checked':''?>><?= L('Generate_IndexNow'); ?></label><br>
            </div>
            <div class="field">
                <div class="label"><?= L('static_root_url'); ?></div>
                <div class="price">
                    <input class="input site-static-root-url" name="sites[<?= (int)$siteIndex ?>][ssg][static_root_url]" placeholder="<?php echo L('root_directory'); ?>" type="text" value="<?= h($ssg['static_root_url'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('static_output'); ?></div>
                <div class="price">
                    <input class="input site-output-dir" name="sites[<?= (int)$siteIndex ?>][ssg][static_output]" placeholder="<?php echo L('output_directory'); ?>" type="text" value="<?php echo h($ssg['static_output'] ?? ''); ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('static_theme_name'); ?></div>
                <div class="price">
<?php if($canScan): ?>
                    <select class="input site-theme" name="sites[<?= (int)$siteIndex ?>][ssg][static_theme_name]">
                        <?php foreach ($theme_list as $t): ?>
                        <option value="<?=h($t)?>" <?= ($t === ($ssg['static_theme_name']??'')) ? 'selected' : '' ?>>
                            <?=h($t)?>
                        </option>
                        <?php endforeach; ?>
                    </select>
<?php else: ?>
                    <!-- 目录读取受限，降级为手动输入 -->
                    <input class="input site-theme" name="sites[<?= (int)$siteIndex ?>][ssg][static_theme_name]" placeholder="<?php echo L('theme_name_placeholder'); ?>" type="text" value="<?php echo h($ssg['static_theme_name'] ?? ''); ?>" />
<?php endif; ?>
                </div>
                <div class="clear"></div>
            </div>
            <!-- static_sitelogo -->
            <div class="field">
                <div class="label"><?= L('static_sitelogo'); ?></div>
                <div class="price">
                    <input class="input site-sitelogo" name="sites[<?= (int)$siteIndex ?>][ssg][static_sitelogo]" placeholder="<?= L('static_sitelogo'); ?>" type="text" value="<?= h($ssg['static_sitelogo'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- static_ico -->
            <div class="field">
                <div class="label"><?= L('static_ico'); ?></div>
                <div class="price">
                    <input class="input site-ico" name="sites[<?= (int)$siteIndex ?>][ssg][static_ico]" placeholder="<?= L('static_ico'); ?>" type="text" value="<?= h($ssg['static_ico'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- per_page -->
            <div class="field">
                <div class="label"><?= L('static_per_page'); ?></div>
                <div class="price">
                   <input class="input site-per-page" name="sites[<?= (int)$siteIndex ?>][ssg][static_per_page]" placeholder="<?= L('per_page_placeholder'); ?>" type="number" min="1" value="<?= h($ssg['static_per_page'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- box_post_number -->
            <div class="field">
                <div class="label"><?= L('static_box_post_number'); ?></div>
                <div class="price">
                    <input class="input site-box-post-number" name="sites[<?= (int)$siteIndex ?>][ssg][static_box_post_number]" placeholder="<?= L('static_box_post_numbere_placeholder'); ?>" type="number" min="1" value="<?= h($ssg['static_box_post_number'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- url_ends -->
            <div class="field">
                <div class="label"><?= L('static_url_ends'); ?></div>
                <div class="price">
                    <input class="input site-url-ends" name="sites[<?= (int)$siteIndex ?>][ssg][static_url_ends]" placeholder="<?= L('url_ends_placeholder'); ?>" type="text" value="<?= h($ssg['static_url_ends'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- footer_code -->
            <div class="field">
                <div class="label"><?= L('static_footer_code'); ?></div>
                <div class="price">
                    <textarea class="input site-footer-code" name="sites[<?= (int)$siteIndex ?>][ssg][static_footer_code]" placeholder="<?= L('footer_code_placeholder'); ?>" rows="3"><?= h($ssg['static_footer_code'] ?? '') ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
            <!-- other_code -->
            <div class="field">
                <div class="label"><?= L('static_other_code'); ?></div>
                <div class="price">
                    <textarea class="input site-other-code" name="sites[<?= (int)$siteIndex ?>][ssg][static_other_code]" placeholder="<?= L('other_code_placeholder'); ?>" rows="3"><?= h($ssg['static_other_code'] ?? '') ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
            <!-- head_code -->
            <div class="field">
                <div class="label"><?= L('static_head_code'); ?></div>
                <div class="price">
                    <textarea class="input site-head-code" name="sites[<?= (int)$siteIndex ?>][ssg][static_head_code]" placeholder="<?= L('head_code_placeholder'); ?>" rows="5"><?= h($ssg['static_head_code'] ?? '') ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
            <!-- site_tags -->
            <div class="field">
                <div class="label"><?= L('static_site_tags'); ?></div>
                <div class="price">
                    <input class="input site-site-tags" name="sites[<?= (int)$siteIndex ?>][ssg][static_site_tags]" placeholder="<?= L('site_tags_placeholder'); ?>" type="text" value="<?= h($ssg['static_site_tags'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- language_name -->
            <div class="field">
                <div class="label"><?= L('language_name'); ?></div>
                <div class="price">
                    <input class="input site-language-name" name="sites[<?= (int)$siteIndex ?>][ssg][static_language_name]" placeholder="<?= L('language_file_placeholder'); ?>" type="text" value="<?= h($ssg['static_language_name'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- language_file -->
            <div class="field">
                <div class="label"><?= L('language_file'); ?></div>
                <div class="price">
                    <input class="input site-language-file" name="sites[<?= (int)$siteIndex ?>][ssg][static_language_file]" placeholder="<?= L('language_file_placeholder'); ?>" type="text" value="<?= h($ssg['static_language_file'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- IndexNow -->
            <div class="field">
                <div class="label"><?= L('IndexNow'); ?></div>
                <div class="price">
                    <input class="input site-indexnow" name="sites[<?= (int)$siteIndex ?>][ssg][IndexNow]" placeholder="<?= L('IndexNow'); ?>" type="text" value="<?= h($ssg['IndexNow'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <!-- google_ads -->
<?php if( ($qs_adv_config['ads'] ?? '') === 'open' ): ?>
            <div class="field">
                <div class="label"><?= L('google_ads'); ?></div>
                <div class="price">
                    <input class="input site-google-ads" name="sites[<?= (int)$siteIndex ?>][ssg][google_ads]" placeholder="<?= L('google_ads'); ?>" type="text" value="<?= h($ssg['google_ads'] ?? '') ?>" />
                </div>
                <div class="clear"></div>
            </div>
<?php endif; ?>
            <!-- robots -->
            <div class="field">
                <div class="label"><?= L('robots'); ?></div>
                <div class="price">
                    <textarea class="input site-robots" name="sites[<?= (int)$siteIndex ?>][ssg][static_robots]" placeholder="<?= L('robots'); ?>" rows="5"><?= h($ssg['static_robots'] ?? '') ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <!-- ===== GitHub Sync子面板 ===== -->
        <div class="card-subtab-content" data-subtab="github" style="display:none;">
            <div class="field">
                <div class="label"><?= L('GitHub_Repo_Address_Label'); ?></div>
                <div class="price"><input class="input gh-repo" name="sites[<?= (int)$siteIndex ?>][gh_repo]" value="<?=h($gh['repo']??'')?>"></div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('GitHub_Token_Label_Tip'); ?></div>
                <div class="price"><input class="input gh-token"  placeholder="<?= L('GitHub_Token_Placeholder'); ?>" name="sites[<?= (int)$siteIndex ?>][gh_token]" type="text" value=""></div>
                <div class="clear"></div>
            </div>
            <div class="field">
                <div class="label"><?= L('Push_Branch_Label'); ?></div>
                <div class="price"><input class="input gh-branch" name="sites[<?= (int)$siteIndex ?>][gh_branch]" value="<?=h($gh['branch']??'gh-pages')?>"></div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="card-footer" style="margin-top:10px;">
        	<button type="submit" class="button"><?= L('Save_All_Sites'); ?></button>
            <button class="button btn-gen-site" type="button" onclick="generateSingleSite(this)"><?= L('Generate_This_Site_Static'); ?></button>
            <button class="button btn-sync-github" type="button" onclick="syncSingleSiteGithub(this)"><?= L('Sync_GitHub_Button'); ?></button>
        </div>
    </div>
<?php endforeach; ?>
            </div>

            <!-- 隐藏样板卡片，用于JS克隆新增站点 -->
            <div id="site_card_sample" class="site-card" style="display:none;border:1px #ccc solid;padding:12px;margin-bottom:16px;">
                <div class="card-header">
                    <h4 class="site-name" style="display:inline-block;margin:0 12px 8px 0;"><?= L('New_Site_Default_Name'); ?></h4>
                    <label class="switch">
                        <input disabled type="checkbox" class="site-enable" name="sites[][enable]" checked>
                        <span class="slider"></span>
                    </label>
                    <button class="button" type="button" onclick="removeSiteCard(this)"><?= L('Delete'); ?></button>
                    <div class="clear"></div>
                </div>
                <div class="card-tabs" style="margin:8px 0;">
                    <a class="card-tab-link button active" data-subtab="ssg"><?= L('SSG_Config_Tab'); ?></a>
                    <a class="card-tab-link button" data-subtab="github"><?= L('GitHub_Sync_Config_Tab'); ?></a>
                </div>
                <div class="card-subtab-content" data-subtab="ssg">
                    <div class="field">
                        <div class="label"><?= L('Site_ID_Label_Tip'); ?></div>
                        <div class="price">
                            <input disabled class="input site-id" readonly name="sites[][id]" value="">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('Site_Display_Name_Label'); ?></div>
                        <div class="price">
                            <input disabled class="input site-display-name" name="sites[][name]" value="<?= L('New_Site_Default_Name'); ?>">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('Site_Tags_Label_Tip'); ?></div>
                        <div class="price">
                            <input disabled class="input site-filter-tags" name="sites[][filter_tags]" value="">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div style="margin:6px 0;">
                        <label><input disabled type="checkbox" class="ssg-gen-robots" name="sites[][gen_robots]" checked><?= L('Generate_Robots_Txt'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-sitemap" name="sites[][gen_sitemap]" checked><?= L('Generate_Sitemap_Xml'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-rss" name="sites[][gen_rss]" checked><?= L('Generate_Rss_Xml'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-search_index" name="sites[][gen_search_index]" checked><?= L('Generate_Search_Index_Json'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-page_404" name="sites[][gen_page_404]" checked><?= L('Generate_Page_404_Html'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-urls_txt" name="sites[][gen_urls_txt]" checked><?= L('Generate_Urls_Txt'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-cname" name="sites[][gen_cname]"><?= L('Generate_Cname'); ?></label><br>
                        <label><input disabled type="checkbox" class="ssg-gen-indexnow" name="sites[][gen_indexnow]"><?= L('Generate_IndexNow'); ?></label><br>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_root_url'); ?></div>
                        <div class="price">
                            <input disabled class="input site-static-root-url" name="sites[][ssg][static_root_url]" placeholder="<?php echo L('root_directory'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_output'); ?></div>
                        <div class="price">
                            <input disabled class="input site-output-dir" name="sites[][ssg][static_output]" placeholder="<?php echo L('output_directory'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_theme_name'); ?></div>
                        <div class="price">
                            <input disabled class="input site-theme" name="sites[][ssg][static_theme_name]" placeholder="<?php echo L('theme_name_placeholder'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_sitelogo'); ?></div>
                        <div class="price">
                            <input disabled class="input site-sitelogo" name="sites[][ssg][static_sitelogo]" placeholder="<?= L('static_sitelogo'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_ico'); ?></div>
                        <div class="price">
                            <input disabled class="input site-ico" name="sites[][ssg][static_ico]" placeholder="<?= L('static_ico'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_per_page'); ?></div>
                        <div class="price">
                           <input disabled class="input site-per-page" name="sites[][ssg][static_per_page]" placeholder="<?= L('per_page_placeholder'); ?>" type="number" min="1" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_box_post_number'); ?></div>
                        <div class="price">
                            <input disabled class="input site-box-post-number" name="sites[][ssg][static_box_post_number]" placeholder="<?= L('static_box_post_numbere_placeholder'); ?>" type="number" min="1" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_url_ends'); ?></div>
                        <div class="price">
                            <input disabled class="input site-url-ends" name="sites[][ssg][static_url_ends]" placeholder="<?= L('url_ends_placeholder'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_footer_code'); ?></div>
                        <div class="price">
                            <textarea disabled class="input site-footer-code" name="sites[][ssg][static_footer_code]" placeholder="<?= L('footer_code_placeholder'); ?>" rows="3"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_other_code'); ?></div>
                        <div class="price">
                            <textarea disabled class="input site-other-code" name="sites[][ssg][static_other_code]" placeholder="<?= L('other_code_placeholder'); ?>" rows="3"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_head_code'); ?></div>
                        <div class="price">
                            <textarea disabled class="input site-head-code" name="sites[][ssg][static_head_code]" placeholder="<?= L('head_code_placeholder'); ?>" rows="5"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('static_site_tags'); ?></div>
                        <div class="price">
                            <input disabled class="input site-site-tags" name="sites[][ssg][static_site_tags]" placeholder="<?= L('site_tags_placeholder'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('language_name'); ?></div>
                        <div class="price">
                            <input disabled class="input site-language-name" name="sites[][ssg][static_language_name]" placeholder="<?= L('language_file_placeholder'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('language_file'); ?></div>
                        <div class="price">
                            <input disabled class="input site-language-file" name="sites[][ssg][static_language_file]" placeholder="<?= L('language_file_placeholder'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('IndexNow'); ?></div>
                        <div class="price">
                            <input disabled class="input site-indexnow" name="sites[][ssg][IndexNow]" placeholder="<?= L('IndexNow'); ?>" type="text" />
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('robots'); ?></div>
                        <div class="price">
                            <textarea disabled class="input site-robots" name="sites[][ssg][static_robots]" placeholder="<?= L('robots'); ?>" rows="5"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="card-subtab-content" data-subtab="github" style="display:none;">
                    <div class="field">
                        <div class="label"><?= L('GitHub_Repo_Address_Label'); ?></div>
                        <div class="price"><input disabled class="input gh-repo" name="sites[][gh_repo]"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label">Personal Access Token</div>
                        <div class="price"><input disabled class="input gh-token" name="sites[][gh_token]" type="text"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="field">
                        <div class="label"><?= L('Push_Branch_Label'); ?></div>
                        <div class="price"><input disabled class="input gh-branch" name="sites[][gh_branch]" value="gh-pages"></div>
                    </div>
                </div>
                <div class="card-footer" style="margin-top:10px;">
                    <button class="button btn-gen-site" type="button" onclick="generateSingleSite(this)"><?= L('Generate_This_Site_Static'); ?></button>
                    <button class="button btn-sync-github" type="button" onclick="syncSingleSiteGithub(this)"><?= L('Sync_GitHub_Button'); ?></button>
                </div>
            </div>

        </div>
</form>
<script>
//添加站点
function addNewSiteCard(){
    const container = document.getElementById('site_cards_container');
    const sample = document.getElementById('site_card_sample');
    if (!container || !sample) return;

    const newCard = sample.cloneNode(true);
    newCard.removeAttribute('id');
    newCard.style.display = '';

    // 找到当前最大的站点索引，避免删除站点后出现 name 冲突。
    let maxIndex = -1;
    container.querySelectorAll(':scope > .site-card').forEach(card => {
        const n = parseInt(card.dataset.siteIndex, 10);
        if (Number.isFinite(n) && n > maxIndex) maxIndex = n;
    });
    const index = maxIndex + 1;
    newCard.dataset.siteIndex = String(index);

    // 样板字段默认 disabled，因此不会参与提交；克隆后恢复并绑定明确的数组索引。
    newCard.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = false;
        if (el.name) {
            el.name = el.name.replace(/^sites\[\]\[/, 'sites[' + index + '][');
        }
    });

    const newSiteId = 'site_' + new Date().toISOString().replace(/[-T:.Z]/g,'') + '_' + Math.random().toString(36).slice(2, 8);

    const idInput = newCard.querySelector('.site-id');
    if (idInput) idInput.value = newSiteId;

    container.appendChild(newCard);
}

//删除站点卡片
function removeSiteCard(btn){
    const card = btn.closest('.site-card');
    if(card) card.remove();
}

//标签切换事件（事件委托，新增卡片自动生效）
document.addEventListener('click',e=>{
    const tabLink = e.target.closest('.card-tab-link');
    if(!tabLink) return;
    e.preventDefault();
    const targetTab = tabLink.dataset.subtab;
    const card = tabLink.closest('.site-card');
    if(!card) return;
    //按钮激活状态
    card.querySelectorAll('.card-tab-link').forEach(el=>el.classList.remove('active'));
    tabLink.classList.add('active');
    //面板切换
    card.querySelectorAll('.card-subtab-content').forEach(panel=>{
        panel.style.display = panel.dataset.subtab === targetTab ? 'block' : 'none';
    });
});

// 预留空函数占位，后续再实现业务
function generateSingleSite(btn){
    alert('<?= L('Single_Site_Generate_Not_Implemented'); ?>');
}
function syncSingleSiteGithub(btn){
    alert('<?= L('GitHub_Sync_Not_Implemented'); ?>');
}
function batchGenerateAllSites(){
    alert('<?= L('Batch_Generate_Not_Implemented'); ?>');
}
</script>
<?php require 'foot.php'; ?>
